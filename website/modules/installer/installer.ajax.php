<?php

class InstallerAjaxModule extends Module {

    var $app_download = "http://localhost/RainInstaller/modules/",
        $app_list_url = "http://localhost/RainInstaller/module_list.php";

    function index() {

    }

    function install( $module ) {


        // check if the module is already installed
//        if (Content::get_module($module))
//            return true;

        // read the manifest
        $manifest_filepath = MODULES_DIR . $module . "/install/manifest.json";
        $manifest_json = file_get_contents($manifest_filepath);
        $module_info = json_decode($manifest_json, $assoc = true);

        // install dependecies
        if (isset($module_info["dependency"]["module"])) {
            foreach ($module_info["dependency"]["module"] as $dmodule) {
                $this->install($dmodule);
            }
        }

        // install the types
        $this->_install_type($module);

        // install the templates for this module
        $this->_install_templates($module);
    }

    protected function _install_type($module) {

        // read the type.json
        $type_filepath = MODULES_DIR . $module . "/install/type.json";
        $type_json = file_get_contents($type_filepath);
        $type_info = json_decode($type_json, $assoc = true);
        
        // get the type list
        $type_list = $type_info["type"];

        // get the block type list
        $block_type_list = $type_info["block_type"];

        // install the content types and fields
        foreach ($type_list as $type) {

            // get the fields
            $type_field_list = $type["fields"];

            // unset the fields key
            unset($type["fields"]);

            // get the parents
            $type_parents = $type["parents"];

            //unset the parents key
            unset($type["parents"]);

            // check if the content type is already into the database
            if (!Content::get_content_type($type["type_id"])) {
                // insert the new type into the database
                DB::insert(DB_PREFIX . "content_type", $type);
            }

            // install the type fields
            foreach ($type_field_list as $field) {
                $field["type_id"] = $type["type_id"];
                $field["published"] = 1;
                if (!Content::get_content_type_field($type["type_id"], $field["name"])) {
                    DB::insert(DB_PREFIX . "content_type_field", $field);
                }
            }

            // set the parent
            foreach ($type_parents as $parent) {
                if (!Content::get_content_type_tree($type["type_id"], $parent)) {
                    DB::insert(DB_PREFIX . "content_type_tree", array("type_id" => $type["type_id"], "parent_id" => $parent));
                }
            }

        }
        
        // install the type for the blocks
        foreach ($block_type_list as $type) {
            DB::insert(DB_PREFIX . "block_type", array("block_type_id" => $type["block_type_id"], "type" => $type["type"], "template" => $type["template"] ));
        }
        
    }

    protected function _install_templates($module) {
        
        $themes = dir_list(THEMES_DIR);
        foreach ($themes as $theme) {
            $template_folder = THEMES_DIR . $theme . "/" . $module . "/";
            if (!is_dir($template_folder)) {
                mkdir($template_folder);

                // copy all templates to the templates folder
                $destination_folder = THEMES_DIR . $theme;
                dir_copy( MODULES_DIR . $module . "/install/templates/", $destination_folder );
            }
        }
    }

    function deactivate($module) {
        DB::query("UPDATE " . DB_PREFIX . "module SET published=0 WHERE LOWER(module)=LOWER(?)", array($module));
    }

    function activate($module) {
        if( Content::get_module($module) )
            DB::query("UPDATE " . DB_PREFIX . "module SET published=1 WHERE LOWER(module)=LOWER(?)", array($module));
        else
            DB::query("INSERT INTO " . DB_PREFIX . "module (module,published) VALUES (LOWER(?),1)", array($module));
    }

    function download($module) {
        
        $module = strtolower($module);

        // Download the zip
        $module_url = $this->app_download . $module . ".zip";
        $ch = curl_init( $module_url );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $data = curl_exec($ch);
        curl_close($ch);
        
        $zip_filepath = MODULES_DIR . $module . ".zip";
        $destination_folder = MODULES_DIR;
        


        // Save on the disk
        file_put_contents( $zip_filepath, $data);


        // Deflate the zip
        $zip = new ZipArchive;
        $res = $zip->open( $zip_filepath );
        if ($res === TRUE) {
            $zip->extractTo( $destination_folder );
            $zip->close();
            // deflated
            
            // remove file
            unlink( $zip_filepath );
            

        } else {
            // error
        }

        
        // Install 
        $this->install( $module );
    }

    
    /**
     * Remove completely the module and all modules that depends by this module
     * @param type $module 
     */
    function remove($module) {
        // check that 
        if( file_exists(MODULES_DIR . $module ) && $module_row = Content::get_module($module) ){
            DB::query("DELETE FROM " . DB_PREFIX . "module WHERE LOWER(module)=LOWER(?)", array($module));
            dir_del( MODULES_DIR . $module );
        }
    }

}

// -- end