<?php

    class UserModule extends Module {

        function index() {
            $this->signin();
        }
        
        function signin(){
            
            if( User::get_user_id() ){
                $view = new View();
                $view->draw( "user/info" );
                
            }
            else{
                $this->loader->set_layout("layout.user");
            
            }

        }

    }

    // -- end