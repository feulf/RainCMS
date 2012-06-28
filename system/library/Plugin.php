<?php

    /**
    * Controller class
    */
    class Plugin {

        static protected $context;

        static function set_context( $context ){
            self::$context = $context;
        }
        
        static function get_context(){
            return self::$context;
        }

    }

    // -- end