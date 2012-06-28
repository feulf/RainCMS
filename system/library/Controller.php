<?php

    /**
    * Controller class
    */
    class Controller {

        protected $loader;

        function __construct($init_params) {
            $this->loader = $init_params['loader'];
            $this->selected = $init_params['selected'];
        }

        function ajax_mode($load_javascript = false, $load_style = false, $load_layout = false) {
            $this->loader->ajax_mode($load_javascript, $load_style, $load_layout);
        }

        function load_library($library) {
            $obj = strtolower($library);
            require_once LIBRARY_DIR . ucfirst(strtolower($library)) . ".php";
            $this->$obj = new $library;
        }

        function load_model($model) {
            // assign the model to the object name, so now it's accessible from the controller
            $this->$model = $this->loader->load_model($model);
        }

        static function configure($setting, $value) {
            if (is_array($setting))
                foreach ($setting as $key => $value)
                    $this->configure($key, $value);
            else if (property_exists(__CLASS__, $setting))
                self::$$setting = $value;
        }

        function filter_before() {

        }

        function filter_after() {

        }

    }

    // -- end