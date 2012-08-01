<?php

    // Read the HTML, search for all CSS and JS files then minify them and put the contents into a single js file and a single css file
    // actions
    add_action( "before_draw", "compress", "compress" );

    class CompressPlugin extends Plugin {

        static function compress(){
            $html = self::$context['html'];
            $html = self::compress_all_css( $html );
            $html = self::compress_all_js( $html );
            $html = self::compress_html( $html );

            self::$context['html'] = $html;
            
        }
        
        static function compress_html( $html ){
            return preg_replace( array('/\n|\r|\t/','/ {2,}/'), array('', ' '), $html );
        }

        static function compress_all_css( $html ) {

            // search for all stylesheet
            if (!preg_match_all("/<link.*href=\"(.*?\.css)\".*>/", $html, $matches))
                return $html; // return the HTML if doesn't find any

            // prepare the variables
            $external_url = array();
            $css = $css_name = "";
            $url_array = array();

            // read all the CSS found
            foreach ($matches[1] as $url) {

                // if a CSS is repeat it takes only the first
                if( empty( $url_array[$url] ) ){
                    $url_array[ $url ] = 1;

                    // parse the URL
                    $parse = parse_url($url);

                    // read file
                    $stylesheet_file = file_get_contents($url);

                    if (preg_match_all("/url\({0,1}(.*?)\)/", $stylesheet_file, $matches)) {
                        foreach ($matches[1] as $image_url) {
                            $image_url = preg_replace("/'|\"/", "", $image_url);
                            dirname($url) . "/" . $image_url;
                            $real_path = reduce_path("../../../" . dirname($url) . "/" . $image_url);

                            $stylesheet_file = str_replace($image_url, $real_path, $stylesheet_file);
                        }
                    }

                    // minify the CSS
                    $stylesheet_file = preg_replace("/\n|\r|\t|\s{4}/", "", $stylesheet_file);

                    $css .= "\n\n/*---\n CSS compressed in Rain \n {$url} \n---*/\n\n" . $stylesheet_file;


                    // get the filename
                    $css_name .= basename($url);
                }
            }


            // create the filename
            $name = md5($css_name);

            // css cache folder
            $css_cache_folder = PUBLIC_CACHE_DIR . "css/";

            if( !is_dir($css_cache_folder) )
                mkdir($css_cache_folder,0755,$recursive=true);

            // create the complete filepath
            $css_filepath = $css_cache_folder . $name . ".css";

            // save the stylesheet
            file_put_contents($css_filepath, $css);

            // remove all the old stylesheet from the page
            $html = preg_replace("/<link.*href=\"(.*?\.css)\".*>/", "", $html);

            // create the tag for the stylesheet 
            $stylesheet_tag = '<link href="' . $css_filepath . '" rel="stylesheet" type="text/css">';

            // add the tag to the end of the <head> tag
            $html = str_replace("</head>", $stylesheet_tag . "\n</head>", $html);

            // return the stylesheet
            return $html;
        }

        static function compress_all_js($html) {

            $html_to_check = preg_replace("<!--.*?-->", "", $html);

            preg_match_all("/<script.*src=\"(.*?\.js)\".*>/", $html_to_check, $matches);
            $external_url = array();
            $js = $js_name = "";
            $js_array = array();

            foreach ($matches[1] as $url) {
                
                // if a JS is repeat it takes only the first
                if( empty( $url_array[$url] ) ){
                    $url_array[ $url ] = $url;

                    $url = reduce_path($url);
                    $javascript_file = file_get_contents($url);

                    // minify the js
                    $javascript_file = preg_replace( "#/\*.*?\*/#", "", $javascript_file );
                    $javascript_file = preg_replace( "/\n+/", "\n", $javascript_file );
                    $javascript_file = preg_replace( "/\t+| +/", " ", $javascript_file );

                    $js .= "\n\n/*---\n CSS compressed in Rain \n {$url} \n---*/\n\n" . $javascript_file;
                    $js_name .= basename($url);
                }

            }

            $name = md5($js_name);
            
            // css cache folder
            $js_cache_folder = PUBLIC_CACHE_DIR . "js/";

            if( !is_dir($js_cache_folder) )
                mkdir($js_cache_folder,0755,$recursive=true);
            
            $js_filepath = $js_cache_folder . $name . ".js";
            file_put_contents($js_filepath, $js);
            $html = preg_replace("/<script.*src=\"(.*?\.js)\".*>/", "", $html);
            $script_tag = '<script src="' . $js_filepath . '"></script>';
            $html = preg_replace("/<\/body>/", $script_tag . "</body>", $html);
            return $html;
        }

    }

// -- end