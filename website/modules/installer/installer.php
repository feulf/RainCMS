<?php

class InstallerModule extends Module {

    function index() {

        // installed modules
        $installed_modules = Content::get_module_list();

        // available modules
        $available_modules = dir_list( MODULES_DIR );
        $modules_list = array();
        
        foreach( $available_modules as $module_dir ){
            if( file_exists( $file_manifest = MODULES_DIR . $module_dir . "/install/manifest.json" ) ){
                $manifest_json = file_get_contents( $file_manifest );
                $manifest = json_decode( $manifest_json, $assoc = true );
                $module = $manifest["module"];
                $modules_list[$module] = $manifest;
                $modules_list[$module]["installed"] = isset($installed_modules[$module]) ? true : false;
            }
        }
        
        $download_list = array(
            array("module"=>"News", "installed"=>false, "description"=> "Manage the news", "dependency"=> array("modules"=>"content") )
        );
        
        
        $tpl = new View;
        $tpl->assign("modules_list", $modules_list );
        $tpl->assign("download_list", $download_list );
        $tpl->draw("installer/installer");

    }

}

// -- end