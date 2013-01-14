<?php

    Layout::addScript("content.js", ADMIN_JAVASCRIPT_DIR, ADMIN_JAVASCRIPT_URL);
    Layout::addScript("file.js", ADMIN_JAVASCRIPT_DIR, ADMIN_JAVASCRIPT_URL);
    Layout::addStyle("content.css", ADMIN_VIEWS_CSS_DIR, ADMIN_VIEWS_CSS_URL);

    require "content_base.php";

    class ContentController extends ContentBaseController {

        function index($content_id = null, $file_id = null, $action = null) {

            $sel_id = get('sel_id');

            Layout::addJavascript("var content_id='$content_id';");

            if (!isset($_SESSION['content_tree']))
                $_SESSION['content_tree'] = Array();

            // get content
            if (!$content_id) {
                $content_id = 0;
                $title = get_msg("content_root");
                $html = $this->_content_list($content_id, $sel_id, get('order_by'), get('order'));
                $tools = '<a href="' . URL . '" class="tooltip" title="' . get_msg("content_button_view_caption") . '"><img src="' . VIEWS_DIR . 'aimg/preview.gif" alt="' . get("content_button_view") . '" /></a>';
            } elseif ($content_row = Content::get_content($content_id)) {

                $title = $content_row['title'];

                Content::get_path($content_id);

                //Content::add_path( get_msg("content"), ADMIN_FILE_URL . "/Content/" );
                // get the type
                $type_id = $content_row['type_id'];
                $type = Content::get_content_type($type_id);
                $child = Content::get_content_type_childs($type_id); // check if this content can has child

                // check access
                /*
                $ca = content_access( $content_id );
                $sa = subcontent_access( $content_id );
                */

                // set tab
                $this->load_library("Tab");

                $n_tab = 0;
                if ($action == 'content_list' && $child)
                    $this->tab->sel_tab("content_list");
                else
                    $this->tab->sel_tab("content_edit");

                // SET CONTROL PANEL
                $this->tab->add_tab("content_edit", $this->_content_edit($content_id), "content_button_content", "content_button_content_caption");

                // files
                if ($type['file_enabled'])
                    $this->tab->add_tab("file_edit", $this->_content_file_list($content_id, $file_id), "content_button_file", "content_button_file_caption");

                // childs
                if ($child)
                    $this->tab->add_tab("content_list", $this->_content_list($content_id), "content_button_content_list", "content_button_content_list_caption");

                $html = $this->tab->draw($to_string = true);

                // define tools
                $tools = '<a href="' . URL . $content_row['link'] . '" class="tooltip" title="' . get_msg("content_button_view_caption") . '"><img src="' . URL . VIEWS_DIR . 'aimg/preview.gif" alt="' . get("content_button_view") . '" /></a>';

                // level up
                $tools .= '<a href="' . ADMIN_FILE_URL . 'Content/content_list/' . $content_row['parent_id'] . '/?sel_id=' . $content_id . '#content_' . $content_id . '" class="tooltip" title="' . get_msg("content_button_up_level_caption") . '"><img src="' . URL . VIEWS_DIR . 'aimg/up.gif" alt="' . get_msg("content_button_up_level") . '" /></a>';

                // check if there's any unique content already created
                if (!$type['unique'] || !Content::get_content_by_type($type_id, $lang_id = LANG_ID, $only_published = false))
                // copy content
                    $tools .= '<a href="javascript:content_copy(' . $content_id . ',\'' . get_msg("content_msg_action_new") . '\' )" class="tooltip" title="' . get_msg("content_button_copy_caption") . '"><img src="' . URL . VIEWS_DIR . 'aimg/same.gif" alt="' . get_msg("content_button_copy") . '" /></a>';

                $tools .= '<a href="javascript:content_deleteAndRefresh(' . $content_id . ',' . $content_row['parent_id'] . ',\'' . get_msg("content_msg_action_delete_confirm") . '\',\'' . get_msg("delete_success") . '\');" class="tooltip" title="' . get_msg("content_button_delete_caption") . '"><img src="' . URL . VIEWS_DIR . 'aimg/del.gif" alt="' . get_msg("content_button_delete") . '" /></a>';
            }

            //---------------------------------------------
            // Draw Control Panel
            //---------------------------------------------
            $view = new View;
            $view->assign("path", Content::draw_path());
            $view->assign("title", $title);
            $view->assign("tools", $tools);
            $view->assign("html", $html);
            $view->assign("content_id", $content_id);
            $view->assign("file_id", $file_id);
            $view->assign("site_tree", $this->_draw_tree($content_id));
            $view->draw("content/content");

        }

        /**
        * draw the edit panel
        */
        function edit($content_id = null, $file_id = null) {
            $this->index($content_id, $file_id, $action = "edit");
        }

        /**
        * draw the list panel
        */
        function content_list($content_id = null, $file_id = null) {
            $this->index($content_id, $file_id, $action = "content_list");
        }

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

                
                // get the Content
                if ($multilanguage_field_list){
                    foreach ($multilanguage_field_list as $i => $field) {
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

                        if( $field_type == "word" ){
                            $lang_icon = ( count($langs) > 1 ? "<a href=\"index.php?id=$content_id&lang_id=$lang_id\" class=\"tooltip\" title=\"" . get_msg("content_form_lang") . "\">" . $lang . " <img src=" . LANG_DIR . "$lang_id/$lang_id.gif></a>" : null );
                            //$this->form->open_table("content_form_table", $lang_icon, "table_elastic");
                            $this->form->add_item("word", $lang_id . "_content", "content_form_content", "content_form_content_field", $content_row_lang['content'], LANG_ID == $lang_id ? "required" : null, $param_array);
                            $this->form->add_space();
                            unset( $multilanguage_field_list[$i] );
                            break;
                        }

                    }
                }
                
                
                $lang_icon = ( count($langs) > 1 ? "<a href=\"index.php?id=$content_id&lang_id=$lang_id\" class=\"tooltip\" title=\"" . get_msg("content_form_lang") . "\">" . $lang . " <img src=" . LANG_DIR . "$lang_id/$lang_id.gif></a>" : null );
                $this->form->open_table("content_form_table", $lang_icon, "table");
                $this->form->add_item("text", $lang_id . "_title", "content_form_title", "content_form_title_field", $content_row_lang['title'], LANG_ID == $lang_id ? "required" : null, $param_array);

                if ($multilanguage_field_list){
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
                $options = DB::get_all("SELECT c.content_id, c.title
                                        FROM " . DB_PREFIX . "content c
                                        JOIN " . DB_PREFIX . "content_rel r ON c.content_id = r.rel_id AND r.rel_type='parent'
                                        WHERE type_id IN
                                        ( SELECT parent_id
                                          FROM " . DB_PREFIX . "content_type_tree AS t
                                          WHERE type_id = ?
                                        )
                                        ORDER BY r.position;", 
                                        array($type['type_id']), 
                                        "content_id", "title"
                                       );

                // LIST OF CONTENTS WHERE THIS CONTENT IS COPIED
                $value = DB::get_all("SELECT r.rel_id AS parent_id, c.content_id
                                      FROM " . DB_PREFIX . "content c
                                      JOIN " . DB_PREFIX . "content_rel r ON c.content_id = r.rel_id AND r.rel_type='parent'
                                      WHERE c.content_id = ?", 
                                      array($content_row['content_id']), 
                                      "parent_id", "content_id"
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
                            $cover = DB::get_row( "SELECT filepath AS cover, thumb AS cover_thumbnail 
                                                     FROM ".DB_PREFIX."file_rel fr
                                                     JOIN ".DB_PREFIX."file f ON f.file_id=fr.file_id
                                                     WHERE fr.rel_id=:rel_id AND rel_type=:rel_type",
                                                     array( ":rel_id"=>$content_id, ":rel_type"=>FILE_COVER )
                                                  );
                            
                            $param_array += array( 'content_id' => $content_id, 'cover'=>$cover['cover'], 'cover_thumbnail'=>$cover['cover_thumbnail'] );

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
        * Content List
        */
        function _content_list($content_id = 0, $sel_id = 0) {

            // GET CONTENT
            if (!$content_id) {
                $content_id = 0;
                $content_row = array('content_id' => 0);
                $type = array('type_id' => 0, 'order_by' => null );
            } elseif ($content_row = Content::get_content($content_id)){
                $type = Content::get_content_type( $content_row['type_id'] );
            }
            else
                return draw_msg(_CONTENT_NOT_FOUND_, WARNING, $close = true);

            $order = $type['order_by'];

            // 5 minutes
            $time = TIME - ( 5 * MINUTE );
            // get page 0
            $content_row_list = Content::get_childs_and_type($content_id, LANG_ID, $time, $order );

            // check if the content is sortable
            if ( null==$type['order_by'] OR preg_match('/position/', $type['order_by'] ) ){
                Layout::addJavascript("content_sortable('{$content_id}');", $onload = true);
                $sortable = true;
            }
            else
                $sortable = false;

            // get children list of type
            $child_type = array();
            $type_id = $type['type_id'] > 0 ? $type['type_id'] : 0;
            if ($ct = Content::get_content_type_childs($type_id))
                foreach ($ct as $t) {

                    $child_type_id = $t['type_id'];
                    $child_type_name = $t['type'];
                    $child_is_unique = $t['unique'];

                    // Check if there is any unique type already inserted in the list
                    if (!$child_is_unique || !Content::get_content_by_type($child_type_id, $lang_id = LANG_ID, $only_published = false)) {
                        $child_type[$child_type_id] = get_msg("type_" . str_replace(" ", "_", $child_type_name));
                    }
                }



            // draw the list
            $view = new View;
            $view->assign($content_row);
            $view->assign("content_list", $content_row_list);
            $view->assign("sel_id", $sel_id);  // show selected content
            $view->assign("sortable", $sortable);  // content is sortable
            $view->assign("child_type", $child_type);
            return $view->draw("content/content.list", $toString = true);
        }

        //------------------------------------------
        // File functions
        //------------------------------------------
        // draw file list
        function _content_file_list($content_id, $file_id) {

            $content_id = $content_id > 0 ? $content_id : 0;

            if (!$content_id or !($content_row = Content::get_content($content_id) ))
                return draw_msg(_CONTENT_NOT_FOUND_, WARNING);

            /*
            if( !content_access( $content_id ) )
            return draw_msg( _ACCESS_DENIED_, WARNING );
            */

            $type_id = $content_row['type_id'];
            $type = Content::get_content_type($type_id);

            $cover_enabled = DB::get_row("SELECT * FROM ".DB_PREFIX."content_type_field WHERE type_id=:type_id AND name='cover'", array(":type_id"=>$type_id) )?true:false;
            

            $allowed_ext = ( $type['image_enabled'] ? get_setting("image_ext") . "," : null ) . ( $type['audio_enabled'] ? get_setting("audio_ext") . "," : null ) . ( $type['video_enabled'] ? get_setting("video_ext") . "," : null ) . ( $type['document_enabled'] ? get_setting("document_ext") . "," : null ) . ( $type['archive_enabled'] ? get_setting("archive_ext") . "," : null );

            Layout::addScript('ajaxupload.js', ADMIN_JAVASCRIPT_DIR, ADMIN_JAVASCRIPT_URL);

            $javascript = "";
            $javascript .= "var msg_file_delete = '" . get_msg("file_msg_delete") . "', msg_file_editname = '" . get_msg("file_msg_editname") . "', msg_extension_not_allowed = '" . get_msg("file_type_not_allowed") . " $allowed_ext';";
            $javascript .= "var allowed_ext = '$allowed_ext'";

            $javascript_onload = "";
            $javascript_onload .= "file_sortable( '$content_id' );";
            $javascript_onload .= "file_enable_upload_btn( '$content_id', '" . get_setting("admin_max_file_size_upload") . "' );";

            Layout::addJavascript($javascript);
            Layout::addJavascript($javascript_onload, $onload = true);

            $order_by = get('forder') ? get('forder') : "position";
            $order = get('forder_by') == "desc" ? "desc" : "asc";

            $file_list=DB::get_all("SELECT *
                                    FROM " . DB_PREFIX . "file_rel fr
                                    JOIN " . DB_PREFIX . "file f ON fr.file_id=f.file_id
                                    WHERE rel_id=:rel_id
                                    GROUP BY f.file_id
                                    ORDER BY fr.position",
                                    array(":rel_id"=>$content_id)
            );
            
            $cover_id = DB::get_field("SELECT fr.file_id 
                                       FROM ".DB_PREFIX."file f 
                                       JOIN ".DB_PREFIX."file_rel fr ON f.file_id=fr.file_id
                                       WHERE fr.rel_id=:rel_id AND fr.rel_type=:rel_type
                                      ",
                                      array(":rel_id"=>$content_id,":rel_type"=>FILE_COVER));

            $view = new View;
            $view->assign('file_list', $file_list);
            $view->assign($content_row);
            $view->assign("cover_enabled", $cover_enabled );
            $view->assign("cover_id", $cover_id );
            $view->assign("content_id", $content_id);
            $view->assign("file_id", $file_id);
            $view->assign("cp", "file/upload");
            return $view->draw('content/content.file.list', $to_string = true);
        }

    }

    // -- end
