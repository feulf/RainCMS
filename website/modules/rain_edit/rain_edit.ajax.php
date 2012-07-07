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
        function content_wysiwyg_update( $content_id ) {

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
        function content_type_childs( $content_id = null ){

            load_lang( "admin.generic" );

            // get all the content type for the website
            $site_type_childs = array();
            $content_type_childs = Content::get_content_type_childs( 0 );
            if ( $content_type_childs ){
                foreach ($content_type_childs as $type) {
                    // Check if there is any unique type already inserted in the list
                    if ( !$type['unique'] OR !Content::get_content_by_type($type['type_id'], $lang_id = LANG_ID, $only_published = false) ) {
                        $type['type'] = get_msg( "type_" . $type['type'] );
                        $site_type_childs[] = $type;
                    }
                }
            }
            $selected_content_type_childs = array();
            $title = "";

            // If we are into a node I load the content type I can create inside it
            if( $content_id ){

                $content = Content::get_content($content_id);
                $title = $content["title"];

                // get the type childs for this node
                $selected_content_type_childs = Content::get_content_type_childs($content['type_id']);

            }
            
            echo json_encode( array("parent_name"=>$title,"type_childs"=>$site_type_childs, "selected_type_childs"=> $selected_content_type_childs ) );
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
            if( $parent_id == ROOT_ID )
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
        
        
        function content_edit($content_id){
            load_lang("admin.content");
            echo $this->_content_edit($content_id);
        }
        /**
         * Content edit
         */
        function _content_edit($content_id) {

            // GET CONTENT
            if (!$content_id or !$content_row = Content::get_content($content_id))
                return draw_msg("content_not_found", WARNING);

            // CHECK ACCESS
            //if( !content_access( $content_id ) )
            //        return draw_msg( "access_denied", WARNING );
            // GET TYPE
            $type = Content::get_content_type($type_id = $content_row['type_id']);
            $mod = get_msg("{$type['lang_index']},MODULE,NAME") ? get_msg("{$type['lang_index']},MODULE,NAME") : $type['type'];


            // INIT CONTROL PANEL
            $this->load_library("form");
            $this->form->init_form(URL . "admin.ajax.php/content/save/$content_id/", "post");

            // INIT LANG
            $installed_lang = DB::get_all( "SELECT * FROM ".DB_PREFIX."language ORDER BY position" );
            
            
            foreach ($installed_lang as $lang)
                $langs[$lang["lang_id"]] = $lang["language"];
            if (!load_lang("admin." . $type["type"]))
                $type["lang_index"] = "content";

            // GET FIELD
            $multilanguage_field_list = Content::get_content_type_fields($type_id, $only_published = true, $multilanguage = true);

            // CREATE MULTILANGUAGE FIELD
            foreach ($langs as $lang_id => $lang) {

                // GET CONTENT
                $content_row_lang = Content::get_content($content_id, $lang_id);

                // PARAMS
                $param_array = array("content_id" => $content_id, "content_id" => $content_row['content_id'], "css" => URL . THEMES_DIR . get_setting('theme') . '/css/style.css');

                // CONTROL PANEL
                $lang_icon = ( count($langs) > 1 ? "<a href=\"index.php?id=$content_id&lang_id=$lang_id\" class=\"tooltip\" title=\"" . get_msg("content_form_lang") . "\">" . $lang . " <img src=" . LANG_DIR . "$lang_id/$lang_id.gif></a>" : null );
                $this->form->open_table("content_form_table", $lang_icon, "table");
                $this->form->add_item("text", $lang_id . "_title", "content_form_title", "content_form_title_field", $content_row_lang['title'], LANG_ID == $lang_id ? "required" : null, $param_array);

                if ($multilanguage_field_list)
                    foreach ($multilanguage_field_list as $field) {

                        //type_id, field_name, field_type, validation, command, param, layout, position, published
                        extract($field);

                        // PARAMS
                        $param_array = array("content_id" => $content_id, "content_id" => $content_row['content_id'], "css" => URL . THEMES_DIR . get_setting('theme') . '/css/style.css');
                        parse_str($param, $field_param_array);
                        $param_array += $field_param_array;


                        $input_layout = $layout ? $layout : "layout";
                        $title = $type['lang_index'] . "_form_" . $name;
                        $description = $type['lang_index'] . "_form_" . $name . "_field";
                        $input_name = $lang_id . "_" . $name;
                        $value = $content_row_lang[$name];

                        $validation = LANG_ID == $lang_id ? $validation : null;

                        switch ($field_type) {
                            case 'date':
                                $value = $value ? time_format($value, DATE_FORMAT_SIMPLE) : null;
                                $param_array += array('dateFormat' => SDATE_FORMAT);
                                break;
                        }

                        $this->form->add_item($field_type, $input_name, $title, $description, $value, $validation, $param_array, $input_layout);
                    }


                if ($type['tags_enabled'])
                    $this->form->add_item("text", $lang_id . "_tags", "content_form_tags", "content_form_tags_field", $content_row_lang['tags'], null, $param_array);
                $this->form->add_item("yes", $lang_id . "_published", "content_form_published", "content_form_published_field", $content_row_lang['published'], null, $param_array);   // pubblica si/no
                $this->form->close_table();
            }
            // !CREATE MULTILANGUAGE FIELD
            // CONTENT INFO
            $this->form->open_table("content_form_table_info");


            // Check if this content allow copy.
            // Copy are usefull for example for products that can be added to more than one categories
            // LINKED COPY
            if ($type['linked_copy']) {


                // LIST OF CONTENTS WHERE THIS CONTENT CAN BE COPIED
                DB::get_all("SELECT id, title
                                                            FROM " . DB_PREFIX . "content AS c
                                                            WHERE type_id IN
                                                            ( SELECT parent_id
                                                            FROM " . DB_PREFIX . "content_type_tree AS t
                                                            WHERE type_id = ? )
                                                            ORDER BY c.position;", array($type['type_id']), "content_id", "title"
                );

                // LIST OF CONTENTS WHERE THIS CONTENT IS COPIED
                $value = DB::get_all("SELECT parent_id, id
                                                        FROM " . DB_PREFIX . "content
                                                        WHERE content_id = ?", array($content_row['content_id']), "parent_id", "content_id"
                );

                $this->form->add_item("linked_copy", "linked_copy", "{$type['lang_index']}_form_linked_copy", "{$type['lang_index']}_form_linked_copy_field", $value, null, array('options' => $options, 'content_id' => $content_row['parent_id']), "row");
            }
            // LINKED COPY
            // FIELD
            $field_list = Content::get_content_type_fields($type_id, $only_published = true, $multilanguage = false);

            if ($field_list) {

                foreach ($field_list as $field) {

                    //type_id, name, field_name, field_type, validation, command, param, layout, position, published
                    extract($field);

                    // PARAMS
                    $param_array = array("content_id" => $content_id, "css" => URL . THEMES_DIR . get_setting('theme') . 'css/style.tinymce.css');
                    parse_str($param, $field_param_array);
                    $param_array += $field_param_array;


                    $input_layout = $layout ? $layout : "layout";
                    $title = $type['lang_index'] . "_form_" . $name;
                    $description = $type['lang_index'] . "_form_" . $name . "_field";
                    $input_name = $name;
                    $value = $content_row[ $name ];

                    switch ($field_type) {
                        case 'date':
                            $value = $value ? time_format($value, DATE_FORMAT_SIMPLE) : null;
                            $param_array += array('dateFormat' => SDATE_FORMAT);
                            break;
                        case 'word':
                            $param_array += array('css' => URL . VIEWS_DIR . 'aimg/style.tinymce.css', "content_id" => $content_row['content_id'], "module" => "content");
                            break;
                        case 'cover':
                            $param_array += array('content_id' => $content_id, 'cover'=>$content_row['cover'], 'cover_thumbnail'=>$content_row['cover_thumbnail']);
                    }

                    $this->form->add_item($field_type, $input_name, $title, $description, $value, $validation, $param_array, $input_layout);
                }
            }
            // FIELD
            // PAGE LAYOUT

            if ((count($layout_list = Content::get_layout_list()) > 1) && User::is_super_admin()) {

                foreach ($layout_list as $content_id => $layout_row)
                    $layout_list_temp[$content_id] = $layout_row['name'];
                $layout_list = $layout_list_temp;
                $this->form->add_item("select", "layout_id", "content_form_layout", "content_form_layout_field", $content_row['layout_id'], "required", array('options' => $layout_list));
            } elseif (isset($layout_list[$content_row['layout_id']]))
                $this->form->add_hidden("layout_id", $content_row['layout_id']);
            else
                $this->form->add_hidden("layout_id", LAYOUT_ID_GENERIC);
            // PAGE LAYOUT
            // CONTENT TEMPLATE
            $template_index = THEMES_DIR . get_setting('theme') . "/" . ( $type['template_index'] ? $type['template_index'] : null );
            $template_list_temp = glob($template_index . "*");
            $strlen_index = strlen($template_index);

            for ($i = 0, $template_list = array(); $i < count($template_list_temp); $i++) {
                $l = file_name(substr($template_list_temp[$i], $strlen_index));
                // i get all layout that has not "block." in the name
                if (!preg_match('#^block\.#', $l) && !is_dir($template_list_temp[$i]))
                    $template_list[$l] = $l;
            }

            if (count($template_list) > 1)
                $this->form->add_item("select", "template", "content_form_template", "content_form_template_field", $content_row['template'], null, array('options' => $template_list));
            else
                $this->form->add_hidden("template", array_shift($template_list));

            // CONTENT TEMPLATE
            // MENU
            if ($content_row['parent_id'] == 0)
                $this->form->add_item("select", "menu_id", "content_form_menu", "content_form_menu_field", $content_row['menu_id'], null, array('options' => array(NO => get_msg("no"), PRINCIPAL_MENU_ID => get_msg("principal_menu"), SECONDARY_MENU_ID => get_msg("secondary_menu"))));
            // MENU
            // ACCESS
            //$this->form->add_item( "select", "read_access", _CONTENT_form_READ_ACCESS_ , get_msg('CONTENT,CP,READ_ACCESS,FIELD,') ,$content_row['read_access'], null, array('options'=> $read_access = array( USER_UNREGISTERED => _USER_ALL_, USER_REGISTERED => _USER_REGISTERED_, USER_ADMIN => _USER_ADMIN_ ) ) );
            // ACCESS
            // SITEMAPS
            if (User::is_super_admin()) {
                global $changefreq;
                foreach ($changefreq as $k => &$v) {
                    if ($k == $type['changefreq'])
                        $changefreq_array[$k] = $v . " &nbsp;&nbsp; (" . get_msg("default") . ")";
                    else
                        $changefreq_array[$k] = $v;
                }
                $this->form->add_item("select", "changefreq", "content_form_changefreq", "content_form_changefreq_field", $content_row['changefreq'], null, array('options' => $changefreq_array));

                for ($i = 0.1; $i < 1.0; $i+= 0.1)
                    if ("$i" == $type['priority'])
                        $priority["$i"] = $i . " &nbsp;&nbsp; (" . get_msg("default") . ")";
                    else
                        $priority["$i"] = $i;

                $this->form->add_item("select", "priority", "content_form_priority", "content_form_priority_field", $content_row['priority'], null, array('options' => $priority));
            }
            // SITEMAPS

            $this->form->add_button("save");

            $this->form->close_table();
            // !CONTENT INFO

            return $this->form->draw($ajax = true, $string = true);
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