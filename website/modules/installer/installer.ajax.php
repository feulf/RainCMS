<?php

class InstallerAjaxModule extends Module {

    function index() {
    }

    function install($module) {

        // check if the module is already installed
        if (Content::get_module($module))
            return true;

        // read the manifest
        $manifest_filepath = MODULES_DIR . $module . "/manifest.json";
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
        $type_filepath = MODULES_DIR . $module . "/type.json";
        $type_json = file_get_contents($type_filepath);
        $type_info = json_decode($type_json, $assoc = true);

        // get the type list
        $type_list = $type_info["type"];

        // install the types
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
                if (!Content::get_content_type_field($type["type_id"], $field["name"])) {
                    DB::insert(DB_PREFIX . "content_type_field", $field);
                }
            }

            // set the parent
            foreach ($type_parents as $parent) {
                if (!Content::get_content_type_tree($type["type_id"],$parent)) {
                    DB::insert(DB_PREFIX."content_type_tree", array("type_id"=>$type["type_id"],"parent_id"=>$parent));
                }
            }
        }
    }
    
    protected function _install_templates( $module ){
        $themes = dir_list( THEMES_DIR );
        foreach( $themes as $theme ){
            $template_folder = THEMES_DIR . $theme . "/" . $module;
            if( !is_dir( $template_folder ) ){
                mkdir( $template_folder );
                
                // copy all templates to the templates folder
                $templates = glob( MODULES_DIR . $module . "/templates/*.html" );
                foreach( $templates as $template ){
                    $destination_file = BASE_DIR . THEMES_DIR . $theme . "/" . $module . "/" . basename($template);
                    copy( $template, $destination_file );
                }
            }
            
        }
    }
    
    
    function unistall( $module ){
        echo $module;
    }
    
    function download( $module ){
        echo $module;
    }

}

// -- end