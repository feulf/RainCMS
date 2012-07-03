<?php

    class Rain_EditAjaxModule extends Module {
        
        
        //-------------------------------------------------------------
        //
        //                          Block
        //
        //-------------------------------------------------------------

        
        /** 
         * Sort the block in a page 
         */
        function block_sort() {

            $load_area = post("load_area");
            $sortable = post("sortable");
            $sortable = explode("&", $sortable);

            if ( User::is_admin() ) {

                for ($position = 0, $n = count($sortable); $position < $n; $position++) {
                    list( $null, $block_id ) = explode("=", $sortable[$position]);

                    db::query("UPDATE " . DB_PREFIX . "block 
                               SET position = ?, 
                               load_area= ?
                               WHERE block_id=?
                               LIMIT 1;", array($position, $load_area, $block_id)
                    );
                    $block = Content::get_block($block_id);
                    Content::clean_cache($block['content_id']);
                }
            }
        }



        /**
         * Delete a block
         * @param int $block_id 
         */
        function block_delete($block_id) {
            $block = Content::get_block($block_id);
            $content_id = $block['content_id'];
            $content = Content::get_content($content_id);
            db::query( "DELETE FROM ".DB_PREFIX."block WHERE block_id=?", array($block_id) );
            
            Content::clean_cache($content_id);
        }
        
        

        /**
         * Get the setting of a block
         * @param int $block_id 
         */
        function block_settings_get( $block_id ){
            $block      = Content::get_block( $block_id );
            $option_list = Content::get_block_options_and_values( $block["block_type_id"] );
            
            // load the language for this block
            load_lang( "admin." . $block["module"] );
            load_lang( $block["module"] );
            
            foreach( $option_list as $i => $option ){
                $option_list[$i]["title"] = get_msg( $option["name"] );
            }
            
            echo json_encode( array( "block"=>$block, "options"=>$option_list ) );
            
        }

        
        
        /**
         * Save the settings of a block
         * @param int $block_id 
         */
        function block_settings_save( $block_id ){
            
            $block      = Content::get_block( $block_id );
            $option_list = Content::get_block_options( $block["block_type_id"] );
            $field = array();
            foreach( $option_list as $option ){
                
                $setting    = $option["name"];
                $value      = get_post( $setting );
                
                DB::query("INSERT INTO ".DB_PREFIX."block_setting 
                           (block_id, setting, value) 
                           VALUES (:block_id,:setting,:value) 
                           ON DUPLICATE KEY UPDATE value=:value",
                           array( ":block_id"=>$block_id, ":setting"=>$setting, ":value"=>$value, ":value"=>$value )
                         );

            }
            // refresh the cache
            if( $block['content_id'] )
                Content::clean_cache($block['content_id']);
            
        }


        //-------------------------------------------------------------
        //
        //                          Content
        //
        //-------------------------------------------------------------


        /**
         * Save the change of the content into the page
         * @param int $content_id 
         */
        function content_edit( $content_id ) {

            $title = stripslashes( post("title") );
            $content = stripslashes( post("content") );
            $summary = stripslashes( post("summary") );

            if( $title != 'null' ) $field['title'] = $title;
            if( $content  != 'null' ) $field['content'] = $content;
            if( $summary  != 'null' ) $field['summary'] = $summary;

            if ($content_id > 0 && $content_row = content::get_content($content_id)) {
                Content::clean_cache($content_id);
                DB::update( DB_PREFIX."content", $field, "content_id=$content_id" );
            }
            elseif ($content_id == 0 && ($title != 'null' or $content != 'null')) {
                db::insert("content", array('title' => $title, 'content' => $content));
                $content_id = db::get_last_id();
                db::insert("block", array('content_id' => $content_id, 'global' => 1, 'load_area' => $load_area));
            }
        }
        
        
        /**
         * Type of content you can create in this page
         */
        function content_type_childs( $content_id ){
            
            $content = Content::get_content($content_id);

            // get all the content type I can add to the site
            $site_type_childs = Content::get_content_type_childs( 1 );

            $selected_content_type_childs = array();

            // if the content is not root I load also the specific content type I can add to this node
            if( $content['type_id'] == 1 || $content['parent_id'] != ROOT_ID ){
                // get the type childs for this node
                $selected_content_type_childs = Content::get_content_type_childs($content['type_id']);
            }

            
            echo json_encode( array("parent_name"=>$content['title'],"type_childs"=>$site_type_childs, "selected_type_childs"=> $selected_content_type_childs ) );
        }
        
        
        /**
        * Content new
        */
        function content_new($parent_id) {

            $type_id = get_post('type_id');
            $title = get_post('title');

            if (!$parent_id)
                $parent_id = 0;
            elseif (!($parent = Content::get_content($parent_id))){
                echo json_encode( array("error"=>true, "message"=> "Content not found") ); // content not found
                return false;
            }

            // Get the type
            if ( !$type = Content::get_content_type($type_id) ){
                echo json_encode( array("error"=>true, "message"=> "Content not found") ); // type not found
                return false;
            }

            // Select the Template
            $template_index = THEMES_DIR . get_setting('theme') . "/" . ( $type['template_index'] ? $type['template_index'] : null );
            $template_list_temp = glob($template_index . "*");
            $strlen_index = strlen($template_index);

            for ($i = 0, $template_list = array(); $i < count($template_list_temp); $i++) {
                $l = file_name(substr($template_list_temp[$i], $strlen_index));
                // i get all layout that has not "block." in the name
                if (!preg_match('#^block\.#', $l) && !is_dir($template_list_temp[$i]))
                    $template_list[$l] = $l;
            }

            $template = array_shift($template_list);
            if (null === $template)
                $template = "";
            // ! Select the template 

            // Get the position
            $position = Content::get_content_last_position($parent_id) + 1;

            // Get the layout
            $layout_id = $parent_id > 0 ? $parent['layout_id'] : LAYOUT_ID_GENERIC;

            // Published
            $published = true;

            // In menu
            $menu_id = 2;

            // LANG_ID
            $lang_id = LANG_ID;

            // Get content id
            $content_id = Content::get_last_content_id() + 1;

            // Set sitemap vars
            $changefreq = $type['changefreq'];
            $priority = $type['priority'];

            DB::insert(DB_PREFIX . "content", array(
                // primary key
                "content_id" => $content_id, "lang_id" => LANG_ID,
                // indexes
                "type_id" => $type_id, "layout_id" => $layout_id,
                // content
                "title" => $title, "date" => TIME,
                // settings
                "template" => $template, "published" => $published, "last_edit_time" => TIME, "menu_id" => $menu_id,
                // google sitemap
                "changefreq" => $changefreq, "priority" => $priority
                    )
            );

            DB::insert(DB_PREFIX . "content_rel", array(
                "content_id" => $content_id, 
                "rel_id" => $parent_id, "rel_type" => "parent", 
                "position" => $position ) );

            $path = $this->_content_set_path($content_id, $lang_id, $parent_path = null);
            
            echo json_encode( array("success"=>true, "path"=>$path) );
            return true;
        }      
        
        
        function content_delete($content_id){
            
            if( $content = Content::get_content($content_id) ){
                
                if( $content['parent_id'] > 0 )
                    $content_parent = Content::get_content( $content['parent_id'] );

                // sub contents
                $childs = Content::get_childs($content_id, LANG_ID, null, null, $only_published = false);
                for ($i = 0; $i < count($childs); $i++)
                    $this->content_delete($childs[$i]['content_id'], LANG_ID);

                // If there aren't any other content with same content_id I delete all the linked files
                if (Content::file_count($content_id))
                    $this->_file_delete_by_content_id($content_id);

                // delete the content
                Content::content_delete($content_id);
                
                if( $content['parent_id'] > 0 )
                    // Path of the parent
                    echo json_encode( array("success"=>true,"path"=>$content_parent['path']) );
                else
                    echo json_encode( array("success"=>true,"path"=>""));
            }
            
        }
        
        
        /**
        * Get path
        */
        function _content_get_path($content_id, $lang_id, $parent_path = null) {

            if ($content = Content::get_content_and_type($content_id, $lang_id)) {

                if (!$parent_path && ($content['parent_id'] > 0)) {
                    $parent_path = Content::get_content($content['parent_id']);
                    $parent_path = $parent_path["path"];
                }

                // home page Path is /
                elseif ($content['position'] == 0 && $content['parent_id'] == 0)
                    return null;

                $path = $content['path_type'];

                preg_match_all("/\{(.*?)\}/", $path, $match);
                $key = $match[0];
                $value = $match[1];
                for ($i = 0; $i < count($value); $i++) {
                    switch ($value[$i]) {
                        case 'title': $v = $content['title'];
                            break;
                        case 'content_id': $v = $content_id;
                            break;
                        case 'content_id':$v = $content['content_id'];
                            break;
                        case 'y': $v = date("Y", $content['date']);
                            break;
                        case 'm': $v = date("m", $content['date']);
                            break;
                        case 'd': $v = date("d", $content['date']);
                            break;
                        default:
                            trigger_error("<b>Path type wrong: value {$value[$i]} not recognized! Value allowed: {title}, {id}, {content_id}, {y}, {m}, {d}</b>", E_USER_ERROR);
                            exit;
                    }
                    $special_chars = array('$', '&', '+', ',', '/', ':', ';', '=', '?', '@', '<', '>', '#', '%', '{', '}', '|', '\\', '^', '~', ']', '[', '`');
                    $v = str_replace($special_chars, '_', trim($v));
                    $v = str_replace(" ", "-", $v);
                    $v = str_replace('"', " ", $v);
                    $path = str_replace($key[$i], $v, $path);
                }
                if (db::get_row("SELECT * FROM " . DB_PREFIX . "content WHERE path=? AND content_id!=?", array($path, $content_id)))
                    $path .= "-" . $content_id;
                $path .= "/";

                if ($parent_path) {

                    // If path short == 1
                    // I get the first node of the parent path.
                    // You can set the short path for avoid the problem of duplicated contents, for example
                    // when a product has is in more than one category, so it could be accessible as
                    // products/memory/usb-key-1  and  products/key/usb-key-1
                    if ($content['path_short']) {
                        $p = explode("/", $parent_path);
                        $parent_path = $p[0] . "/";
                    }
                    $path = $parent_path . $path;
                }

                return $path;
            }
            else
                return false;
        }


        /**
        * Set path
        */
        function _content_set_path($content_id, $lang_id, $parent_path = null) {
            if ( ( $path = $this->_content_get_path($content_id, $lang_id, $parent_path) ) !== false ) {

                db::query(
                          "UPDATE " . DB_PREFIX . "content 
                           SET path=:path 
                           WHERE content_id=:content_id AND lang_id=:lang_id", 
                           array(":path"=>$path, ":content_id"=>$content_id, ":lang_id"=>$lang_id ) 
                         );

                $content_list = Content::get_childs($content_id);
                for ($i = 0; $i < count($content_list); $i++)
                    $this->_content_set_path($content_list[$i]['content_id'], $lang_id, $path);
            
                return $path;
            }
            
        }

        /**
        * Set path of all languages of contents
        */
        function _content_set_path_all_languages($content_id) {
            if ($langs = db::get_all("SELECT lang_id FROM " . DB_PREFIX . "content WHERE content_id=?", array($content_id), "lang_id", "lang_id"))
                foreach ($langs as $lang_id)
                    $this->_content_set_path($content_id, $lang_id);
        }

        
        //-------------------------------------------------------------
        //
        //                          Files
        //
        //-------------------------------------------------------------

        // delete file by content id    
        function _file_delete_by_content_id($content_id) {

            // lista dei file
            $file_list = db::get_all("SELECT * 
                                      FROM " . DB_PREFIX . "file 
                                      WHERE rel_id=? AND module='content'", array($content_id)
            );

            for ($i = 0; $i < count($file_list); $i++)
            // cancello i file
                Content::file_delete($file_list[$i]['file_id']);
        }
        
        //-------------------------------------------------------------
        //
        //                          Theme
        //
        //-------------------------------------------------------------


        /**
         * Available themes 
         */
        function themes_list() {
            $theme_list = db::get_all("SELECT * FROM " . DB_PREFIX . "theme ORDER BY date DESC");
            echo json_encode($theme_list);
        }



        

        //-------------------------------------------------------------
        //
        //                          Layout
        //
        //-------------------------------------------------------------


        
        /**
         * Available layouts 
         */
        function pages_list() {
            $pages_list = db::get_all("SELECT * FROM " . DB_PREFIX . "layout WHERE published=1 ORDER BY position");
            echo json_encode($pages_list);
        }

        

        /**
         * Get the HTML of the selected layout
         */
        function get_layout_html($theme, $layout_id) {
            $layout = Content::get_layout($layout_id);
            $template = $layout['template'];
            echo file_get_contents(THEMES_DIR . $theme . "/layout." . $template . ".html");
        }

        

        /**
         * Set the layout for this content
         * @param int $content_id
         * @param int $layout_id 
         */
        function set_layout($content_id, $layout_id) {
            db::query("UPDATE " . DB_PREFIX . "content SET layout_id=? WHERE content_id=?", array( $layout_id, $content_id ) );
            Content::clean_cache($content_id);
        }

    }

    // -- end