<?php

    /**
    *  RainFramework
    *  -------------
    *  Realized by Federico Ulfo & maintained by the Rain Team
    *  Distributed under MIT license http://www.opensource.org/licenses/mit-license.php
    */
    require CONSTANTS_DIR . "constants.php";
    require LIBRARY_DIR . "functions.php";
    require LIBRARY_DIR . "Plugin.php";


    /**
    * Load and init all the classes of the framework
    */
    class Loader {

        // controller settings
        protected static $controllers_dir = CONTROLLERS_DIR,
                $controller_extension = CONTROLLER_EXTENSION,
                $controller_class_name = CONTROLLER_CLASS_NAME,
                $models_dir = MODELS_DIR;
        protected $theme = "", // default theme
                $layout = "index", // default page layout
                $not_found_layout = "not_found", // default page layout not found
                $head = "";           // css and javascript
        // ajax variables
        protected $ajax_mode = false,
                $load_javascript = false,
                $load_style = false;
        // variables
        protected $layout_vars = array(), // variables assigned to the page layout
                $load_area_array = array();   // variables assigned to the page layout
        // selected controller
        protected $selected_controller = null,
                $selected_action = null,
                $selected_params = null,
                $loaded_controller = array();

        function __construct() {
            $this->_start_benchmark();            
        }

        function load_plugins( $manifest_filename = "default" ){

            $plugins_filepath = PLUGINS_DIR . $manifest_filename . ".json";
            $plugins_json = file_get_contents( $plugins_filepath );
            $plugins = json_decode($plugins_json, $assoc=true);

            foreach( $plugins["plugins_load"] as $plugin => $plugin_status ){
                if( $plugin_status == "enabled" )
                    require PLUGINS_DIR . $plugin . "/" . $plugin . ".php";
            }
            load_actions( "plugin_loaded", array("loader"=>$this) );
            
            foreach( $plugins["plugins"] as $plugin => $plugin_status ){
                if( $plugin_status == "enabled" )
                    require PLUGINS_DIR . $plugin . "/" . $plugin . ".php";
            }

            load_actions( "after_init", array("loader"=>$this) );

        }

        function init_session() {
            session_start();
        }

        function init_db() {
            require LIBRARY_DIR . "DB.php";
            db::init();
        }

        function init_settings() {
            global $settings;
            $settings_list = DB::get_all("SELECT * FROM setting");    // load global settings
            foreach ($settings_list as $setting_row) {
                $settings[$setting_row['setting']] = $setting_row['value'];
                if ($setting_row['const'])
                    define($setting_row['setting'], $setting_row['const']);
            }
            $this->theme = $settings['theme'];

            require CONFIG_DIR . "url.php";
        }
        
        function init_language() {

            $installed_language = get_installed_language();
            $installed_language = array_flip( $installed_language );
            
            // get the language
            if (get('set_lang_id'))
                $lang_id = get('set_lang_id');
            elseif (isset($_SESSION['lang_id']))
                $lang_id = $_SESSION['lang_id'];
            else
                $lang_id = get_setting('lang_id');

            // language not found, load the default language
            if (!isset($installed_language[$lang_id])) {
                $default_language = array_pop($installed_language);
                $lang_id = $default_language['lang_id'];
            }

            // set the language in session
            $_SESSION['lang_id'] = $lang_id;

            // define the constant
            define("LANG_ID", $lang_id);

            // load the dictionaries
            load_lang('generic');
            
        }

        function auth_user() {
            require LIBRARY_DIR . "User.php";
            User::login(post('login'), post('password'), post('cookie'), get_post('logout'));
        }

        function auto_load_controller() {

            // load the Router library and get the URI
            require_once LIBRARY_DIR . "Router.php";
            $router = new Router;
            $this->selected_route = $router->get_route();
            $this->selected_controller_dir = static::$controllers_dir;
            $this->selected_controller = $controller = $router->get_controller();
            $this->selected_action = $action = $router->get_action();
            $this->selected_params = $params = $router->get_params();
            $this->controller($controller, $action, $params);
        }

        function controller($controller = null, $action = null, $params = array(), $load_area = "center") {

            require_once LIBRARY_DIR . "Controller.php";

            try {
                $controller = strtolower($controller);
                require_once $controller_file = static::$controllers_dir . "$controller/$controller" . static::$controller_extension;
            } catch (Exception $e) {
                $this->_page_not_found("File $controller_file not found");
            }

            try {
                $class = $controller . static::$controller_class_name;
                $init_params = array("loader" => $this, "selected" => true);
                $controller_obj = new $class($init_params);
            } catch (Exception $e) {
                $this->_page_not_found("Controller class $class not found");
            }

            if ($action) {

                $this->_start_benchmark("controller");


                // start the output buffer
                ob_start();
                call_user_func_array(array($controller_obj, "filter_before"), $params);    // call the method filter_before
                $action_status = call_user_func_array(array($controller_obj, $action), $params); // call the selected action
                call_user_func_array(array($controller_obj, "filter_after"), $params);    // call the method filter_after
                $html = ob_get_clean();
                // close the output buffer
                // verify that the action was executed
                if (false === $action_status)
                    $html = "Action <b>$action</b> not found in controller <b>$class</b>! Method not declared or declared with different private access";


                list( $time, $memory ) = $this->get_benchmark();
                $this->loaded_controller[] = array("controller" => $controller, "execution_time" => $time, "memory_used" => $memory);

                // if it is in ajax mode print and stop the execution of the script
                if ($this->ajax_mode) {
                    $this->_draw_ajax($html);
                } else {
                    // save the output into the load_area array
                    if (!isset($this->load_area[$load_area]))
                        $this->load_area[$load_area] = array();
                    $this->load_area[$load_area][] = array("controller" => $controller, "html" => $html);
                }
            }
        }

        function load_model($model) {

            // load the model class
            // require_once LIBRARY_DIR . "Model.php";

            try {
                $model = strtolower($model);
                require_once $model_file = static::$models_dir . $model . ".php";
            } catch (Exception $e) {
                $this->_page_not_found("File $model_file not found");
            }

            $class = $model . "Model";

            // test if the class exists
            if (class_exists($class))
                return new $class;
            else {
                $this->_page_not_found("Model $model not found");
            }
        }

        function init_theme() {
            
            require LIBRARY_DIR . "View.php";

            $this->theme_dir = str_replace("//", "/", VIEWS_DIR . $this->theme . "/");

            // configure views
            if (is_dir($this->theme_dir)) {
                View::configure("tpl_dir", $this->theme_dir);
                View::configure("cache_dir", CACHE_DIR . "views/");
                View::configure("link_url", URL);
                View::configure("file_url", URL);
                View::configure("path_replace", true);
            }
            else
                $this->_page_not_found("theme_not_found");
        }

        function load_head() {
            // all javascript and stylesheets go here
        }

        function load_menu() {
            // function to load menu goes here
            $menu = array();
            $this->assign("menu", $menu);
        }

        function assign($variable, $value = null) {
            if (is_array($variable))
                $this->layout_vars += $variable;
            else
                $this->layout_vars[$variable] = $value;
        }

        function draw($to_string = false) {

            $tpl = new View;

            // assign all variable
            $tpl->assign($this->layout_vars);

            // - HEAD & FOOT ------
            $tpl->assign("head", get_style() ); // style on the header
            $tpl->assign("foot", get_javascript() . get_javascript_onload() ); // all javascript in the footer

            // - LOAD AREA ----
            // wrap all the blocks in a load area
            $load_area = array();
            foreach ($this->load_area as $load_area_name => $blocks)
                $load_area[$load_area_name] = $this->_blocks_wrapper($blocks, $load_area_name);
            $tpl->assign("load_area", $load_area);


            // - BENCHMARK ------
            list( $timer, $memory ) = $this->get_benchmark();
            $tpl->assign("execution_time", $timer);
            $tpl->assign("memory_used", $memory);
            $tpl->assign("loaded_controller", $this->loaded_controller);
            $tpl->assign("included_files", get_included_files());
            $tpl->assign("n_query", class_exists("DB") ? DB::get_executed_query() : null );

            return $tpl->draw($this->layout, $to_string);
        }

        function set_layout($layout) {
            $this->layout = $layout;
            $this->_get_load_area();
        }

        function ajax_mode($load_javascript = false, $load_style = false, $ajax_mode = true) {
            $this->ajax_mode = $ajax_mode;
            $this->load_javascript = $load_javascript;
            $this->load_style = $load_style;
        }

        static function configure($setting, $value) {
            if (is_array($setting))
                foreach ($setting as $key => $value)
                    $this->configure($key, $value);
            else if (property_exists(__CLASS__, $setting))
                static::$$setting = $value;
        }

        function _page_not_found($msg = null) {

            header("HTTP/1.0 404 Not Found");

            $this->load_area['content'] = $msg;
            $this->set_layout("layout." . $this->not_found_layout);

            if (empty($this->theme_dir))
                $this->theme();

            $this->load_menu();
            $this->draw();
            die;
        }

        protected function _get_load_area() {

            if (!is_dir($load_area_dir = CACHE_DIR . THEMES_DIR))
                mkdir($load_area_dir, 0777, $recursive = true);

            $layout_file = $this->theme_dir . $this->layout . '.html';
            $load_area_file = $load_area_dir . "load_area." . $this->layout . md5($this->theme) . ".json";

            if (!file_exists($load_area_file) || filemtime($load_area_file) < filemtime($layout_file)) {
                preg_match_all('/\{\$load_area\.(.*?)\}/si', file_get_contents($layout_file), $match);

                // write on file the load_area found
                foreach($match[1] as $l)
                    $load_area[$l] = array();

                $load_area_json = json_encode( $load_area );
                file_put_contents($load_area_file, $load_area_json );
            }
            else{
                $load_area_json = file_get_contents( $load_area_file );
                $load_area = json_decode( $load_area_json, $assoc=true );
            }

            $this->load_area = $load_area;
        }

        protected function _blocks_wrapper($block_array = array(), $load_area_name) {
            $html = "";
            if (is_array($block_array))
                foreach ($block_array as $block)
                    $html .= $block["html"];
            return $html;
        }

        protected function _draw_ajax($html = null) {
            echo $this->load_style ? get_style() : null;
            echo $this->load_javascript ? get_javascript() : null;
            echo $html;
            die;
        }

        protected function _start_benchmark($benchmark = null) {
            timer_start($benchmark);
            memory_usage_start($benchmark);
        }

        public function get_benchmark($benchmark = null) {
            return array(timer($benchmark), memory_usage($benchmark));
        }

    }

    // -- end