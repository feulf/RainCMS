<?php

    class UserModule extends Module {

        function index() {
            $this->signin();
        }
        
        function signin(){
            $this->set_layout("layout.user");
        }

    }

    // -- end