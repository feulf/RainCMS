<?php

    require CONSTANTS_DIR   . "rain.constants.php";
    require LIBRARY_DIR     . "Loader.php";

    class RainLoader extends Loader {

        protected static $controllers_dir = MODULES_DIR,
                         $controller_extension = MODULE_EXTENSION,
                         $controller_class_name = MODULE_CLASS_NAME;
        
        protected   $ajax_mode = false,
                    $head = "",
                    $layout_vars = "",
                    $loaded_controller = array(),
                    $content_id = null,
                    $type_id = null,
                    $type = null,
                    $layout = null,
                    $layout_id = null,
                    $content_path = null,
                    $selected_module = null,
                    $content = null,
                    $module = null,
                    $action = null,
                    $params = null,
                    $path = "";

        function __construct() {
            $this->_start_benchmark();
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

            if ($language_list = DB::get_all("SELECT * FROM " . DB_PREFIX . "language WHERE published=1", array(), 'lang_id')) {

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

            // path
            $this->path = get('path');

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
                    if (strlen($path) == 1) {
                        $this->_page_not_found("content not found");
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
            } else {
                $this->_page_not_found("content not found");
            }

            $this->selected_module = $this->module;
            User::init_localization($this->path, $this->content_id);
        }

        // check route
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

        function init_theme() {

            require LIBRARY_DIR . "View.php";

            // get the theme directory
            $this->theme_dir = str_replace("//", "/", THEMES_DIR . $this->theme . "/");

            // if theme is not found load the default theme
            if (!is_dir($this->theme_dir))
                $this->theme_dir = THEMES_DIR . "default_theme/";

            if (!$this->ajax_mode)
                $this->set_layout($this->layout);

            // selected theme
            define("THEME", $this->theme);

            View::configure("tpl_dir", $this->theme_dir);
            View::configure("cache_dir", CACHE_DIR . THEMES_DIR);
            View::configure("link_url", URL);
            View::configure("file_url", URL);
            View::configure( "base_url", URL );
            View::configure("path_replace", true);
        }

        function load_head() {
            // add javascript
            add_script("jquery.min.js", JQUERY_DIR, JQUERY_URL);
            add_javascript("var url='" . URL . "';");
        }
        
        function auto_load_controller(){
            require_once LIBRARY_DIR . "Module.php";
            require_once LIBRARY_DIR . "Content.php";
            parent::auto_load_controller();
        }

        function load_module($module = null, $action = null, $params = array(), $load_area = "center", $selected = true, $content = null, $controller_extension = null, $controller_class_name = null) {

            require_once LIBRARY_DIR     . "Module.php";

            if (!$controller_extension)
                $controller_extension = self::$controller_extension;

            if (!$controller_class_name)
                $controller_class_name = self::$controller_class_name;


            // - SET INFO ------
            if ($module === null && $this->module)
                $module = $this->module;
            if ($content === null && $this->content)
                $content = $this->content;

            
            // load the language for this module
            load_lang( $module );

            // - LOAD MODULE ------
            if (file_exists($module_filepath = self::$controllers_dir . $module . "/" . $module . $controller_extension)) {
                require_once $module_filepath;

                // - RENDER THE MODULE ------
                try {

                    $this->_start_benchmark("module");

                    // define module class
                    $controller_class = $module . $controller_class_name;

                    // action
                    if (!$action)
                        $action = $this->action;

                    // params
                    if (!$params)
                        $params = $this->params;


                    // init module object
                    $init_params = array("loader" => $this, "selected" => $selected, "content" => $content );

                    $controller_obj = new $controller_class($init_params);

                    if (!is_callable(array($controller_obj, $action)))
                        $this->_page_not_found("module not found");

                    ob_start(); // start the output buffer
                    call_user_func_array(array($controller_obj, $action), $params);            // call the selected action
                    $html = ob_get_clean();    // close the output buffer

                    list( $time, $memory ) = $this->_get_benchmark("module");
                    $this->loaded_controller[] = array("module" => $module, "execution_time" => $time, "execution_memory" => $memory);
                } catch (Exception $e) {
                    $html = null;
                    echo "error " . $e->getMessage();
                }
            } else {
                $html = get_msg("module not found");
            }


            // if it is in ajax mode print and stop the execution of the script
            if ($this->ajax_mode)
                $this->_draw_ajax($html);
            else {
                $this->load_area_array[$load_area][] = array("html" => $html, "module" => $module, "content" => $content);
            }
        }

        function load_blocks() {

            require_once LIBRARY_DIR     . "Module.php";
            require_once LIBRARY_DIR     . "Block.php";

            // load blocks
            if ($block_list = Content::get_block_list($this->layout_id, $this->type_id, $this->content_id)) {
                foreach ($block_list as $block) {
                    // if selected is true the block can read the parameters
                    $this->block($block);
                }
            }
            
        }

        function block($block) {
            // get the settings and the parameters
            $params = Content::get_block_settings( $block["block_id"] );
            $this->load_module($block['module'], $action = "draw", $params, $block['load_area'], $selected = false, $block, BLOCK_EXTENSION, BLOCK_CLASS_NAME);

        }
        
        function load_menu() {
            $this->assign("menu", db::get_all("SELECT c.content_id, c.title AS name, c.path AS link, IF( ? LIKE CONCAT(c.path,'%') AND ( c.path != '' OR ? = '' ), 1, 0 ) AS selected 
                                               FROM " . DB_PREFIX . "content c
                                               JOIN " . DB_PREFIX ."content_rel r ON c.content_id=r.content_id AND r.rel_type='parent'
                                               WHERE c.menu_id = 2
                                               ORDER BY r.position", 
                                               array($this->path, $this->path)
                    )
            );

            $this->assign("menu2", db::get_all("SELECT c.content_id, c.title AS name, c.path AS link, IF( ? LIKE CONCAT(c.path,'%') AND ( c.path != '' OR ? = '' ), 1, 0 ) AS selected
                                                FROM " . DB_PREFIX . "content c
                                                JOIN " . DB_PREFIX ."content_rel r ON c.content_id=r.content_id AND r.rel_type='parent'
                                                WHERE c.menu_id = 3
                                                ORDER BY r.position", 
                                                array($this->path, $this->path)
                    )
            );
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
            list( $timer, $memory ) = $this->_get_benchmark();
            $tpl->assign("execution_time", $timer);
            $tpl->assign("memory_used", $memory);
            $tpl->assign("loaded_controller", $this->loaded_controller);
            $tpl->assign("included_files", get_included_files());
            $tpl->assign("n_query", class_exists("DB") ? DB::get_executed_query() : null );
            $html = $tpl->draw( $this->layout, $to_string = true);
            
            $context = load_actions( "before_draw", array( "loader"=>$this, "html"=>$html ) );
            $html = $context["html"];
            echo $html;

            load_actions( "after_draw", array( "loader"=>$this ) );
            
        }

        function ajax_mode($load_javascript = false, $load_style = false, $ajax_mode = true) {
            $this->ajax_mode = $ajax_mode;
            $this->load_javascript = $load_javascript;
            $this->load_style = $load_style;
        }

        // for ajax call
        function auto_load_load_module() {

            // load the Router library and get the URI
            require_once LIBRARY_DIR . "Router.php";
            $router = new Router;
            $router->get_route();
            $module = $router->get_controller();
            $action = $router->get_action();
            $params = $router->get_params();

            $this->load_module($module, $action, $params);
        }

        function set_layout($layout) {
            $this->layout           = $layout;
            $this->load_area_array  = $this->_get_load_area();
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

            $this->layout_id = LAYOUT_ID_NOT_FOUND;
            $this->type_id = null;
            $this->layout = Content::get_layout($this->layout_id);
            $this->layout = "layout." . $this->layout['template'];
            $this->load_area_array = $this->_get_load_area();

            if (empty($this->theme_dir))
                $this->init_theme();

            $this->assign("not_found_msg", get_msg( $msg ) );
            $this->load_menu();
            $this->load_blocks($this->layout_id);
            $this->draw();
            die;
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

        protected function _get_benchmark($benchmark = null) {
            return array(timer($benchmark), memory_usage($benchmark));
        }

    }

    // end