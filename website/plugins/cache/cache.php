<?php

    // Cache plugin
    add_action( "plugin_loaded", "cache", "check" );

    class CachePlugin extends Plugin{

        static protected $cache_directory, 
                         $cache_filepath;
        

        // check if there is a cache file
        static function check(){

            $request_uri = $_SERVER["REQUEST_URI"];
            self::$cache_directory = str_replace( '//', '/', CACHE_DIR . "html/" . $request_uri . "/" );

            if( $request_uri == "" )
                $request_uri = "index.php";

            self::$cache_filepath = self::$cache_directory . md5( $request_uri ) . ".php";

            if( file_exists( self::$cache_filepath ) ){

                $html = file_get_contents( self::$cache_filepath );

                // - BENCHMARK ------
                list( $timer, $memory ) = self::$context['loader']->get_benchmark();
                $script =   "<script>" . 
                            "$('#execution_time').html('$timer');".
                            "$('#memory_used').html('$memory');".
                            "$('#included_files').html('".sizeof(get_included_files())."');".
                            "$('#n_query').html(0);".
                            "</script>";
                $html = preg_replace( "/<\/body>/", "$script</body>", $html );
                echo $html;
                load_actions( "after_draw", array("loader"=>self::$context['loader']) );
                die;
            }
            else{
                add_action( "before_draw", "cache", "save" );
            }

        }

        // save a new cache
        static function save(){

            if (self::$cache_directory && !is_dir(self::$cache_directory))
                mkdir(self::$cache_directory, 0755, $recursive = true);

            file_put_contents(self::$cache_filepath, self::$context['html'], $create_folder = true);

        }

    }
    
// -- end
    
