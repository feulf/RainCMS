<?php

    class UserModule extends Module {

        function index() {
            $this->signin();
        }
        
        function signin(){
            $view = new View();
            $view->draw( "signin/signin" );
        }

    }

    // -- end