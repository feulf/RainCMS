<?php

    require "RainLoader.php";

    class RainEditLoader extends RainLoader {
        
        protected $edit_mode = true,
                  $cache_enabled = false;


        function init_language() {
            parent::init_language();
            load_lang("admin.generic");
        }

        function load_head() {

            parent::load_head();

            // folders
            $js = "";
            $js .= "var admin_file              = '" . ADMIN_FILE_URL . "';" . "\n";
            $js .= "var ajax_file               = '" . AJAX_URL . "';" . "\n";
            $js .= "var admin_ajax_file         = '" . ADMIN_AJAX_FILE_URL . "';" . "\n";
            $js .= "var views_dir               = '" . VIEWS_DIR . "';" . "\n";
            $js .= "var uploads_url             = '" . UPLOADS_URL . "';" . "\n";
            $js .= "var images_url              = '" . IMAGES_URL . "';" . "\n";
            $js .= "var javascript_url          = '" . JAVASCRIPT_URL . "';" . "\n";
            $js .= "var css_url                 = '" . CSS_URL . "';" . "\n";
            $js .= "var admin_images_url        = '" . ADMIN_IMAGES_URL . "';" . "\n";
            $js .= "var admin_javascript_url    = '" . ADMIN_JAVASCRIPT_URL . "';" . "\n";
            $js .= "var admin_css_url           = '" . ADMIN_CSS_URL . "';" . "\n";
            $js .= "var admin_views_images_url  = '" . ADMIN_VIEWS_IMAGES_URL . "';" . "\n";
            $js .= "var themes_dir              = '" . THEMES_DIR . "';" . "\n";
            $js .= "var modules_dir             = '" . MODULES_DIR . "';" . "\n";

            // variables
            $js .= "var content_id              = '{$this->content_id}';" . "\n";
            $js .= "var lang_id                 = '" . LANG_ID . "';" . "\n";
            $js .= "var selected_theme          = '{$this->theme}';" . "\n";
            $js .= "var selected_layout         = '{$this->layout_id}';" . "\n";
            
            $js .= "var user_name               = '".User::get_user_field("name")."';" . "\n";

            add_javascript($js);

            // aloha
            add_script( "rain/aloha-config.js", JAVASCRIPT_DIR, JAVASCRIPT_URL);
            add_script( "aloha/lib/aloha.js",   JAVASCRIPT_DIR, JAVASCRIPT_URL, array("data-aloha-plugins" => "common/format,common/highlighteditables,common/list,common/link,common/undo,common/paste,common/block"));
            add_style(  "aloha/css/aloha.css",  JAVASCRIPT_DIR, JAVASCRIPT_URL);
            

            // block sort
            add_script('ui/jquery-ui-1.8.16.custom.js', JQUERY_DIR, JQUERY_URL); // all jquery ui

            // jquery
            add_style("rain.edit.css", CSS_DIR, CSS_URL);
            add_script("rain/edit.js", JAVASCRIPT_DIR, JAVASCRIPT_URL);
            
            add_javascript("RainEdit.init();");



        }
        
        function block($block) {
            load_lang( 'admin.' . $block["module"] );
            parent::block( $block );
        }

        protected function _blocks_wrapper($block_array = array(), $load_area_name) {

            if ($this->edit_mode) {
                
                $view = new View( PUBLIC_DIR );
                $view->assign( "load_area_name", $load_area_name );
                $view->assign( "block", $block_array );
                return $view->draw( "../default/rain/wrapper", true );

            }
            else
                return parent::_blocks_wrapper($block_array, $load_area_name);
        }

    }

    // end