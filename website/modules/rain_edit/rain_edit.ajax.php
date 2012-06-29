<?php

    class Rain_EditAjaxModule extends Module {

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

        function block_list() { 

            $content_id = get_post('content_id');
            $content = Content::get_content($content_id);
            $page_id = $content['layout_id'];
            $type_id = $content['type_id'];

            $block_list = Content::get_block_list($page_id, $type_id, $content_id);

            $load_area_disabled = array();

            foreach ($block_list as $block) {
                if ($block['load_area'] == 'disabled') {
                    $load_area_disabled[] = $block;
                }
            }

            echo json_encode($load_area_disabled);
        }

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

        function themes_list() {
            $theme_list = db::get_all("SELECT * FROM " . DB_PREFIX . "theme ORDER BY date DESC");
            echo json_encode($theme_list);
        }

        function pages_list() {
            $pages_list = db::get_all("SELECT * FROM " . DB_PREFIX . "layout WHERE published=1 ORDER BY position");
            echo json_encode($pages_list);
        }

        //
        function block_content_new($layout_id = 1, $load_area = "right", $title = "text", $content = "text here") {
            $content_id = 1 + db::get_field("SELECT id FROM " . DB_PREFIX . "content ORDER BY content_id DESC LIMIT 1");
            db::insert(DB_PREFIX . "content", array("content_id" => $content_id, "title" => "test", "content" => $content, "lang_id" => LANG_ID, "type_id" => TYPE_ID_CONTENT));
            db::insert(DB_PREFIX . "block", array('load_area' => $load_area, 'content_id' => $content_id, "module" => "content", "template" => "content", "global" => 1));
            dump(db::get_row("SELECT * FROM ".DB_PREFIX."block ORDER BY layout_id DESC LIMIT 1"));
            
            Content::clean_cache($content_id);
        }

        function block_delete($block_id) {
            $block = Content::get_block($block_id);
            $content_id = $block['content_id'];
            $content = Content::get_content($content_id);
            db::query( "DELETE FROM ".DB_PREFIX."block WHERE block_id=?", array($block_id) );
            
            Content::clean_cache($content_id);
        }
        
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

        function get_layout_html($theme, $layout_id) {
            $layout = Content::get_layout($layout_id);
            $template = $layout['template'];
            echo file_get_contents(THEMES_DIR . $theme . "/layout." . $template . ".html");
        }

        function set_layout($content_id, $layout_id) {
            db::query("UPDATE " . DB_PREFIX . "content SET layout_id=? WHERE content_id=?", array( $layout_id, $content_id ) );
            Content::clean_cache($content_id);
        }

    }

    // -- end