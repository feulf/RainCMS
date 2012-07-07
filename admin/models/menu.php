<?php

        class Menu_Model extends Model{

            function load_menu( $selected = null){
                $menu = dir_list( CONTROLLERS_DIR );
                foreach( $menu as $voice ){
                                    if( $voice != 'Login' )
                                        $menu_list[] = array('name'=>$voice, 'link'=> basename($_SERVER['SCRIPT_NAME']). '/' . $voice . '/', 'selected' => $selected==$voice );
                }
                return $menu_list;
            }

        }

    // -- end