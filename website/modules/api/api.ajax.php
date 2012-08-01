<?php

    class APIAjaxModule extends Module {

        function index() {            
            $modules_list = dir_list( MODULES_DIR );
            
            foreach( $modules_list as $module ){
                // available APIs
                $class = MODULES_DIR . $module . "/$module.ajax.php";
                if( file_exists($class) ){
                    echo $module . "<br>";
                }

            }
            
            
        }

    }

    // -- end