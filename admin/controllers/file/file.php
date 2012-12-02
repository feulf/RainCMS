<?php

    add_script("file.js", ADMIN_JAVASCRIPT_DIR, ADMIN_JAVASCRIPT_URL);
    add_style("file.css", ADMIN_VIEWS_CSS_DIR, ADMIN_VIEWS_CSS_URL);

    require "file_base.php";

    class FileController extends FileBaseController {

        function index($content_id = null, $file_id = null, $action = null) {

            $html = $this->_content_file_list($content_id, $file_id);

            //---------------------------------------------
            // Draw Control Panel
            //---------------------------------------------
            $view = new View;
            $view->assign("html", $html );
            $view->draw("file/file");

        }
        
        function file_list(){
            $content_id = get_post('content_id');
            $file_id = get_post('file_id');
            echo $this->_content_file_list($content_id, $file_id);
            $this->ajax_mode();
            exit;
        }
        

        //------------------------------------------
        // File functions
        //------------------------------------------
        // draw file list
        function _content_file_list($content_id, $file_id) {

            $allowed_ext = get_setting("image_ext") . "," . get_setting("audio_ext") . "," . get_setting("video_ext") . "," . get_setting("document_ext") . "," . get_setting("archive_ext");

            add_script('ajaxupload.js', ADMIN_JAVASCRIPT_DIR, ADMIN_JAVASCRIPT_URL);

            $javascript = "";
            $javascript .= "var msg_file_delete = '" . get_msg("file_msg_delete") . "', msg_file_editname = '" . get_msg("file_msg_editname") . "', msg_extension_not_allowed = '" . get_msg("file_type_not_allowed") . " $allowed_ext';";
            $javascript .= "var allowed_ext = '$allowed_ext'";

            $javascript_onload = "";
            $javascript_onload .= "file_enable_upload_btn( '" . get_setting("admin_max_file_size_upload") . "' );";

            add_javascript($javascript);
            add_javascript($javascript_onload, $onload = true);

            $order_by = get('forder') ? get('forder') : "position";
            $order = get('forder_by') == "desc" ? "desc" : "asc";

            $file_list = DB::get_all("SELECT *
                                      FROM " . DB_PREFIX . "file f
                                      GROUP BY f.file_id" );
            
            // if I'm calling this from a content
            $cover_enabled = false;
            $cover_id = 0;
            if( $content_id ){
                $cover_id = DB::get_field("SELECT fr.file_id 
                                           FROM ".DB_PREFIX."file f 
                                           JOIN ".DB_PREFIX."file_rel fr ON f.file_id=fr.file_id
                                           WHERE fr.rel_id=:rel_id AND fr.rel_type=:rel_type
                                          ",
                                          array(":rel_id"=>$content_id,":rel_type"=>FILE_COVER));
                
                $content = Content::get_content_and_type($content_id);
                $type_id = $content['type_id'];
                $cover_enabled = DB::get_row("SELECT * FROM ".DB_PREFIX."content_type_field WHERE type_id=:type_id AND name='cover'", array(":type_id"=>$type_id) )?true:false;

            }

            $view = new View;
            $view->assign('file_list', $file_list);
            $view->assign("content_id", $content_id);
            $view->assign("cover_enabled", $cover_enabled );
            $view->assign("cover_id", $cover_id );
            $view->assign("file_id", $file_id);
            $view->assign("cp", "file/upload");
            return $view->draw('content/content.file.list', $to_string = true);
        }

    }

    // -- end
