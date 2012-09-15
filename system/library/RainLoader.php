<?php

    require CONSTANTS_DIR   . "constants.php";
    require CONSTANTS_DIR   . "rain.constants.php";
    require LIBRARY_DIR     . "error.functions.php";
    require LIBRARY_DIR     . "functions.php";
    require LIBRARY_DIR     . "Plugin.php";


    /**
     * 
     * RainLoader is the main class that load all the component6 of Rain 
     * 
     */
    class RainLoader{

        protected static $modules_dir        = MODULES_DIR,
                         $module_extension   = MODULE_EXTENSION,
                         $module_class_name  = MODULE_CLASS_NAME;

        protected   $params = array(),
                    $loaded_modules = array();

        protected   $ajax_mode,
                    $head,
                    $layout_vars,
                    $content,
                    $content_id,
                    $content_path,
                    $path,
                    $type,
                    $type_id,
                    $layout,
                    $layout_id = LAYOUT_ID_GENERIC,
                    $selected_module,
                    $block_list = array();

        
        
        /**
         *  Start the benchmark 
         */
        function __construct() {
            $this->_start_benchmark();
        }
        
        
        
        /**
         * Load the plugins
         * @param string $manifest_filename You can chose which plugins set to load, for example the plugins loaded in the admin area will be different by the plugins loaded on the frontend
         */
        
        function load_plugins( $manifest_filename = "default" ){

            $plugins_filepath = PLUGINS_DIR . $manifest_filename . ".json";
            $plugins_json = file_get_contents( $plugins_filepath );
            $plugins = json_decode($plugins_json, $assoc=true);

            foreach( $plugins["plugins_load"] as $plugin ){
                if( $plugin["status"] == "enabled" )
                    require PLUGINS_DIR . $plugin["name"] . "/" . $plugin["name"] . ".php";
            }
            load_actions( "plugin_loaded", array("loader"=>$this) );

            foreach( $plugins["plugins"] as $plugin ){
                if( $plugin["status"] == "enabled" )
                    require PLUGINS_DIR . $plugin["name"] . "/" . $plugin["name"] . ".php";
            }

            load_actions( "after_init", array("loader"=>$this) );

        }

        
        
        /**
         * Initialize the session 
         */
        function init_session() {
            session_start();
        }

        

        /**
         * Initialize the database 
         */
        function init_db() {
            require LIBRARY_DIR . "DB.php";
            db::init();
        }
        
        
        /**
         * Initialize the settings
         * @global type $settings The settings are saved in a global var accessible for example with get_setting("website_domain");
         */
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
            
            load_actions( "after_init_settings", array("loader"=>$this) );

        }



        /**
         * Authenticate the user 
         */
        function auth_user() {
            require LIBRARY_DIR . "User.php";
            User::login(post('login'), post('password'), post('cookie'), get_post('logout'));
        }
        
        

        /**
         * Initialize the language of the website 
         */
        function init_language() {

            if ( $language_list = DB::get_all( "SELECT * FROM " . DB_PREFIX . "language WHERE published=1", array(), "lang_id") ) {

                // get the language
                if (get('set_lang_id'))
                    $lang_id = get('set_lang_id');
                elseif (isset($_SESSION['lang_id']))
                    $lang_id = $_SESSION['lang_id'];
                else
                    $lang_id = get_setting('lang_id');

                // language not found, load the default language
                if (!isset($language_list[$lang_id])) {
                    $default_language = array_pop($language_list);
                    $lang_id = $default_language['lang_id'];
                }

                // set the language in session
                $_SESSION['lang_id'] = $lang_id;

                // define the constant
                define("LANG_ID", $lang_id);

                // load the dictionaries
                load_lang('generic');
                load_lang('rain.generic');
            } else {
                $this->_page_not_found('Language not installed');
            }
        }

        

        /**
        * Loads the information of the selected content
        */
        function init_content() {

            require LIBRARY_DIR     . "Content.php";

            // get the path
            require_once LIBRARY_DIR . "Router.php";
            $router = new Router;
            $this->path = $router->get_route();
            

            // if null path = ""
            if (null === $this->path)
                $this->path = "";

            // The content exists
            if ($content = Content::get_content_by_path($this->path)) {
                $this->content = $content;
                $this->action = $content['action'] ? $content['action'] : "draw";
                $this->params = array();
            }

            // Content not found for that path
            else {

                // when a path doesn't match any content it checks the path reducing the breadcrumb,
                // for example News/2012/ may not correspond to any content, this code will check if News/
                // correspond to a content, in that case it will call method filter of the Module News and send
                // 2012/ as parameter

                $path = $this->path;
                do {

                    $path = $this->_check_route($path) . "/";
                    
                    // no more element in the path
                    if (strlen($path) == 1) {
                        break;
                    }

                } while (!$content = Content::get_content_by_path($path));

                $this->content = $content;

                $extra_path = substr($this->path, strlen($path));
                if (is_numeric($extra_path[0])) {
                    $this->action = "filter";
                    $params = $extra_path;
                } else {
                    $this->action = rtrim(substr($this->path, strlen($path)), "/");
                    $params = null;
                }

                $this->params = array($params);

            }

            // if the content exists and is published load it
            if ($this->content && $this->content['published']) {
                $this->content_id   = $this->content['content_id'];
                $this->type_id      = $this->content['type_id'];
                $this->layout_id    = $this->content['layout_id'];
                $this->type         = $this->content['type'];
                $this->module       = $this->content['module'];
                $this->layout       = "layout." . $this->content['layout'];
                $this->content_path = Content::get_path($this->content_id);
                $this->selected_module = $this->module;
                User::init_localization($this->path, $this->content_id);
            } else {

                $this->module = $router->get_controller();
                $this->action = $router->get_action();
                $this->params = $router->get_params();
                $layout = Content::get_layout( $this->layout_id );
                $this->layout = "layout." . $layout["template"];

                User::init_localization($this->path, 0 );

            }

        }



        /**
         * Initialize the theme 
         */
        function init_theme() {

            require LIBRARY_DIR . "View.php";

            // get the theme directory
            $this->theme_dir = str_replace("//", "/", THEMES_DIR . $this->theme . "/");

            // if theme is not found load the default theme
            if (!is_dir($this->theme_dir))
                $this->theme_dir = THEMES_DIR . "default/";

            if (!$this->ajax_mode)
                $this->set_layout($this->layout);

            View::configure("cache_dir", CACHE_DIR . THEMES_DIR);
            View::configure("link_url", URL);
            View::configure("file_url", URL);
            View::configure("base_url", URL );
            View::configure("tpl_dir", $this->theme_dir);
        }

        
        
        /**
         * Load all the javascript and stylesheet 
         */
        function load_head() {
            //add_style("rain.edit.css", CSS_DIR, CSS_URL);

            // add javascript
            add_script("jquery.min.js", JQUERY_DIR, JQUERY_URL);
            add_script("rain/generic.js", JAVASCRIPT_DIR, JAVASCRIPT_URL );
            add_script("rain/popup.js", JAVASCRIPT_DIR, JAVASCRIPT_URL );
            
            // urls
            add_javascript("var url             ='" . URL . "';");
            add_javascript("var css_url         ='" . CSS_URL . "';");
            add_javascript("var javascript_url  ='" . JAVASCRIPT_URL . "';");
            add_javascript("var ajax_file       ='" . AJAX_URL . "';");
            add_javascript("var admin_file      ='" . ADMIN_FILE_URL . "';");
            
            // variables
            add_javascript("var content_id='" . $this->content_id . "';");

            
        }

        
        
        /**
         * Load the selected module
         * 
         * @param string $module Module to load
         * @param string $action Action to load 
         * @param array $params Parameters of the module
         * @param string $load_area Where is loaded
         * @param bool $selected Is the module selected?
         * @param array $content Content selected for this module
         * @param string $module_extension Filename of the module
         * @param string $module_class_name Classname of the module
         */
        function load_module($module = null, $action = null, $params = array(), $load_area = "center", $selected = true, $content = null, $module_extension = null, $module_class_name = null, $page_not_found_on_error = true ) {

            // load the Module class
            require_once LIBRARY_DIR     . "Module.php";

            // set the module name
            if (!$module_extension) 
                $module_extension = self::$module_extension;

            // set the class name
            if (!$module_class_name) 
                $module_class_name = self::$module_class_name;


            // - Set info ------
            if ($module === null && $this->module)
                $module = $this->module;

            if ($content === null && $this->content)
                $content = $this->content;

            
            // load the language for this module
            load_lang( $module );
            
            // check if the module is activated
//            $module_row = Content::get_module($module);
//            if( !$module_row["published"] )
//                $this->_page_not_found ();


            // everything is OK
            $status         = SUCCESS;
            $html           = "";

            
            // - LOAD MODULE ------
            if (file_exists( $module_filepath = self::$modules_dir . $module . "/" . $module . $module_extension)) {
                require_once $module_filepath;

                // - RENDER THE MODULE ------

                $this->_start_benchmark("module");

                // define module class
                $controller_class = $module . $module_class_name;

                // action
                if (!$action)
                    $action = $this->action;
                
                // params
                if (!$params)
                    $params = $this->params;

                // init module object
                $init_params = array("loader" => $this, "selected" => $selected, "content" => $content );

                if( class_exists($controller_class) ){
                    
                    $controller_obj = new $controller_class($init_params);

                    if (is_callable(array($controller_obj, $action))){
                        
                        try{

                            ob_start();                                                         // start the output buffer
                            call_user_func_array( array($controller_obj, $action), $params );   // call the selected action
                            $html = ob_get_clean();                                             // close the output buffer

                        }catch( Exception $e ){
                            
                            $status = ERROR;
                            $html = $e->getMessage();

                        }
                        
                    }
                    else{
                        $status = ERROR;
                        $html = "module not found";
                    }

                    
                }
                else{
                    
                    $status = ERROR;
                    $html = "module not found";
                    
                }

            } else {
                
                $status         = ERROR;
                $html           = "module not found";

            }


            if( $status == ERROR && $page_not_found_on_error ){
                $this->_page_not_found( $html );
            }
            
            // benchmark
            list( $time, $memory ) = $this->get_benchmark("module");
            $this->loaded_modules[] = array( "module" => $module, "execution_time" => $time, "execution_memory" => $memory, "status" => $status );

            
            // if it is in ajax mode print and stop the execution of the script
            if ( $this->ajax_mode )
                $this->_draw_ajax($html);
            else {
                $this->load_area_array[$load_area][] = array("html" => $html, "module" => $module, "content" => $content);
            }
            


        }



        /**
         * Load the blocks of the page
         */
        function load_blocks() {

            require_once LIBRARY_DIR     . "Module.php";
            require_once LIBRARY_DIR     . "Block.php";

            // load blocks
            $this->block_list = Content::get_block_list($this->layout_id, $this->type_id, $this->content_id);
            if ( $this->block_list ) {
                foreach ( $this->block_list as $block) {
                    // if selected is true the block can read the parameters
                    $this->block( $block );
                }
            }
            
        }

        

        /**
         * Load the selected block
         * @param string $block 
         */
        function block($block) {

            // get the settings and the parameters
            $params = Content::get_block_settings( $block["block_id"] );
            $this->load_module($block['module'], $action = "draw", $params, $block['load_area'], $selected = false, $block, BLOCK_EXTENSION, BLOCK_CLASS_NAME, $page_not_found_on_error = false );

        }



        /**
         * Load the menu for this page
         */
        function load_menu() {
            $this->assign("menu", db::get_all("SELECT c.content_id, c.title AS name, c.path AS link, IF( ? LIKE CONCAT(c.path,'%') AND ( c.path != '' OR ? = '' ), 1, 0 ) AS selected 
                                               FROM " . DB_PREFIX . "content c
                                               JOIN " . DB_PREFIX ."content_rel r ON c.content_id=r.content_id AND r.rel_type='parent'
                                               WHERE c.menu_id = 2 AND c.published=1
                                               ORDER BY r.position", 
                                               array($this->path, $this->path)
                    )
            );

            $this->assign("menu2", db::get_all("SELECT c.content_id, c.title AS name, c.path AS link, IF( ? LIKE CONCAT(c.path,'%') AND ( c.path != '' OR ? = '' ), 1, 0 ) AS selected
                                                FROM " . DB_PREFIX . "content c
                                                JOIN " . DB_PREFIX ."content_rel r ON c.content_id=r.content_id AND r.rel_type='parent'
                                                WHERE c.menu_id = 3 AND c.published=1
                                                ORDER BY r.position", 
                                                array($this->path, $this->path)
                    )
            );
        }

        
        /**
         * Assign a value to the layout
         * 
         * @param string $variable Name of the variable
         * @param mixed $value Value
         */
        function assign($variable, $value = null) {
            if (is_array($variable))
                $this->layout_vars += $variable;
            else
                $this->layout_vars[$variable] = $value;
        }
        
        
        
        /**
         * Draw the website
         * @param boolean $to_string Set true if you want to get the page in a string
         */
        function draw($to_string = false) {

            $tpl = new View;

            // assign all variable
            $tpl->assign($this->layout_vars);

            // - WEB PAGE INFO ------
            $tpl->assign('title', $this->content['title']);
            $tpl->assign('description', strip_tags($this->content['content']));
            $tpl->assign('content', $this->content);
            $tpl->assign('keywords', get_setting('keywords') + $this->content['tags']);
            $tpl->assign('copyright', get_setting('copyright'));
            $tpl->assign('last_edit', get_setting('last_edit_time'));
            $tpl->assign('path', Content::draw_path());


            // - HEAD & FOOT ------
            $tpl->assign("head", get_style() ); // style on the header
            $tpl->assign("foot", get_javascript() . get_javascript_onload() ); // all javascript in the footer


            // - LOAD AREA ----
            // wrap all the blocks in a load area
            $load_area = array();
            if( $this->load_area_array )
                foreach ($this->load_area_array as $load_area_name => $blocks)
                    $load_area[$load_area_name] = $this->_blocks_wrapper($blocks, $load_area_name);
            $tpl->assign("load_area", $load_area);


            // - BENCHMARK ------
            list( $timer, $memory ) = $this->get_benchmark();
            $tpl->assign("execution_time", $timer);
            $tpl->assign("memory_used", $memory);
            $tpl->assign("loaded_modules", $this->loaded_modules);
            $tpl->assign("included_files", get_included_files());
            $tpl->assign("n_query", class_exists("DB") ? DB::get_executed_query() : null );
            $tpl->assign("user", User::get_user() );
            $html = $tpl->draw( $this->layout, $to_string = true);
            
            $context = load_actions( "before_draw", array( "loader"=>$this, "html"=>$html ) );
            $html = $context["html"];
            echo $html;

            load_actions( "after_draw", array( "loader"=>$this ) );
            
        }

        
        
        /**
         * Enable the ajax_mode
         * 
         * @param boolean $load_javascript
         * @param boolean $load_style
         * @param boolean $ajax_mode 
         */
        function ajax_mode($load_javascript = false, $load_style = false, $ajax_mode = true) {
            $this->ajax_mode = $ajax_mode;
            $this->load_javascript = $load_javascript;
            $this->load_style = $load_style;
        }



        /**
         * Load the module 
         */
        function auto_load_module() {

            // load the Content class
            require_once LIBRARY_DIR . "Content.php";
            
            // load the Router library and get the URI
            require_once LIBRARY_DIR . "Router.php";
            $router = new Router;
            $router->get_route();
            $module = $router->get_controller();
            $action = $router->get_action();
            $params = $router->get_params();
            
            $this->load_module( $module, $action, $params );
        }



        /**
         * Set the layout of the page
         * @param string $layout Layout
         */
        function set_layout($layout) {
            $this->layout           = $layout;
            $this->load_area_array  = $this->_get_load_area();
        }



        /**
         * Configure the loader
         * @param string $setting
         * @param mixed $value 
         */
        static function configure($setting, $value) {
            if (is_array($setting))
                foreach ($setting as $key => $value)
                    $this->configure($key, $value);
            else if (property_exists(__CLASS__, $setting))
                static::$setting = $value;
        }



        /**
         * Draw the Page Not Found
         * @param string $msg Message printed on the page
         */
        function _page_not_found($msg = null) {

            header("HTTP/1.0 404 Not Found");

            $this->layout_id = LAYOUT_ID_NOT_FOUND;
            $this->type_id = null;
            $this->layout = Content::get_layout($this->layout_id);
            $this->layout = "layout." . $this->layout['template'];

            if (empty($this->theme_dir))
                $this->init_theme();

            $this->load_area_array = $this->_get_load_area();

            $this->assign("not_found_msg", get_msg( $msg ) );
            $this->load_menu();
            // if there was an error in loading the blocks it won't try to load again them
            if( !$this->block_list ){
                $this->load_blocks($this->layout_id);
            }
            $this->draw();
            die;
        }

        
        
        /**
         * Wrap the blocks to give space
         * 
         * @param type $block_array
         * @param type $load_area_name
         * @return type 
         */
        protected function _blocks_wrapper($block_array = array(), $load_area_name) {
            $html = "";
            if (is_array($block_array))
                foreach ($block_array as $block)
                    $html .= $block["html"];
            return $html;
        }

        
        /**
         * If ajax_mode is true this function is called on draw
         * 
         * @param type $html 
         */
        protected function _draw_ajax($html = null) {
            echo $this->load_style ? get_style() : null;
            echo $this->load_javascript ? get_javascript() : null;
            echo $html;
            die;
        }

        
        /**
         * Start the benchmark
         * @param string $benchmark Benchmark name
         */
        protected function _start_benchmark($benchmark = null) {
            timer_start($benchmark);
            memory_usage_start($benchmark);
        }



        /**
         * Get the benchmark results
         * @param type $benchmark
         * @return type 
         */
        function get_benchmark($benchmark = null) {
            return array(timer($benchmark), memory_usage($benchmark));
        }

        
        
        /**
         * Get all the load area of the selected layout
         * @return mixed Load Area
         */
        protected function _get_load_area() {

            if (!is_dir($load_area_dir = CACHE_DIR . THEMES_DIR))
                mkdir($load_area_dir, 0777, $recursive = true);

            $layout_file = $this->theme_dir . $this->layout . '.html';
            $load_area_file = $load_area_dir . "load_area." . $this->layout . "." . md5($this->theme) . ".json";

            if ( !file_exists($load_area_file) || (file_exists($layout_file) && filemtime($load_area_file) < filemtime($layout_file) ) ) {
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

            return $load_area;
        }
        
        

        /**
         * Remove the last part of the 
         * @param type $path
         * @return type 
         */
        function _check_route($path) {

            // if last char is slash, remove it
            if ($path && $path[strlen($path) - 1] == "/")
                $path = substr($path, 0, -1);

            // remove the last part of the path
            $path_array = explode("/", $path);
            array_pop($path_array);

            // new path
            return implode("/", $path_array);
        }

    }

    // end