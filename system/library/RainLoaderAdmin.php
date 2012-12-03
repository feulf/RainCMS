<?php

    require LIBRARY_DIR     . "Loader.php";
    require CONSTANTS_DIR   . "rain.constants.php";
    require LIBRARY_DIR     . "Content.php";


    class RainLoaderAdmin extends Loader{

        public $title = "Rain CMS - Admin";

        function init_settings(){
            parent::init_settings();
            $this->theme = null;
        }
        
        function init_language(){

            $language_list = DB::get_all( "SELECT * FROM ".DB_PREFIX."language WHERE admin_published=1", array(), 'lang_id' );

            if( get('set_admin_lang_id') )
                $lang_id = get('set_admin_lang_id');
            elseif( isset( $_SESSION['admin_lang_id'] ) )
                $lang_id = $_SESSION['admin_lang_id'];
            else
                $lang_id = get_setting('lang_id_admin' );

            if( isset( $language_list[ $lang_id ] ) ){
                $_SESSION['admin_lang_id'] = $lang_id;
                define( 'LANG_ID', $lang_id );
                load_lang( 'generic' );
                load_lang( 'rain.generic' );
                load_lang( 'admin.generic' );
            }
            else{
                // language not found
                $this->_page_not_found('Language not installed');
            }

        }

        function load_head(){
            
            $folders =  "";
            $folders .= "var url                    = '" . URL . "';" . "\n";
            $folders .= "var admin_file                = '" . ADMIN_FILE_URL . "';" . "\n";
            $folders .= "var ajax_file                = '" . ADMIN_AJAX_FILE_URL . "';" . "\n";
            $folders .= "var views_dir                = '" . VIEWS_DIR . "';" . "\n";
            $folders .= "var uploads_url            = '" . UPLOADS_URL . "';" . "\n";
            $folders .= "var images_url                = '" . IMAGES_URL . "';" . "\n";
            $folders .= "var javascript_url            = '" . JAVASCRIPT_URL . "';" . "\n";
            $folders .= "var css_url                = '" . CSS_URL . "';" . "\n";
            $folders .= "var library_dir            = '" . LIBRARY_DIR . "';" . "\n";
            $folders .= "var library_url            = '" . LIBRARY_URL . "';" . "\n";
            $folders .= "var admin_images_url        = '" . ADMIN_IMAGES_URL . "';" . "\n";
            $folders .= "var admin_javascript_url    = '" . ADMIN_JAVASCRIPT_URL . "';" . "\n";
            $folders .= "var admin_css_url            = '" . ADMIN_CSS_URL . "';" . "\n";
            $folders .= "var admin_views_images_url    = '" . ADMIN_VIEWS_IMAGES_URL . "';" . "\n";
            
            Layout::addJavascript( $folders );


            Layout::addScript( 'jquery.min.js', JQUERY_DIR, JQUERY_URL );
            Layout::addScript( 'ui/jquery-ui-1.8.16.custom.js', JQUERY_DIR, JQUERY_URL );   // all jquery ui
            Layout::addScript( 'jquery.tooltip.min.js', JQUERY_DIR, JQUERY_URL );           // tooltip
            Layout::addScript( 'admin.js', ADMIN_JAVASCRIPT_DIR, ADMIN_JAVASCRIPT_URL );    // generic admin function
            
            Layout::addScript( 'jquery.lightbox-0.5.min.js', JQUERY_DIR, JQUERY_URL );      // lightbox
            Layout::addStyle( 'jquery.lightbox-0.5.css', CSS_DIR, CSS_URL );

        }
        
        function load_menu(){
            $menu = DB::get_all( "SELECT *, IF( :selected_controller=name, 1, 0 ) as selected
                                  FROM ".DB_PREFIX."menu
                                  WHERE parent_id = 1
                                  ORDER BY position",
                                  array( ":selected_controller" => $this->selected_controller ) );
            
            // set the correct URL
            foreach( $menu as &$v )
                $v["link"] = str_replace( array("{URL}","{ADMIN_FILE_URL}" ), array( URL, ADMIN_FILE_URL ), $v["link"] );
            Layout::assign( "menu", $menu );
        }
        
        function draw( $to_string = false ){
            Layout::assign( "title", $this->title );
            Layout::assign( "user", User::get_user() );
            return parent::draw( $to_string );
        }

        
    }
    
    // -- end