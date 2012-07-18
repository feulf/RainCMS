<?php

    require "content_base.php";

    class ContentAjaxController extends ContentBaseController {

        function tree($action = null) {
            switch ($action) {
                case 'update':
                    echo $this->_draw_site_tree(get('sel_id'), null, get('content_id'));
                    break;
                case 'close':
                    echo $this->_close_site_tree(get('content_id'));
                    break;
            }
        }

        function sort($parent_id = null) {

            $sortable = get_post("sortable");
            $sortable = explode("&", str_replace("i[]=", "", $sortable));
            
            for ($i = 0, $n=count($sortable); $i < $n; $i++) {
                db::query("UPDATE ".DB_PREFIX."content_rel
                           SET position=:position
                           WHERE content_id=:content_id AND rel_id=:parent_id AND rel_type='parent'",
                           array(":position" => $i, ":content_id" => $sortable[$i], ":parent_id" => $parent_id) 
                         );
            }

        }

        /**
        * Content new
        */
        function content_new($parent_id) {

            $type_id = get_post('type_id');
            $title = get_post('title');


            // USER HAS ACCESS TO CREATE?
            // if( !subcontent_access( $parent_id ) )
            //    return drawMsg( _ACCESS_DENIED_, WARNING, $close=true );
            // GET THE PARENT
            if (!$parent_id)
                $parent_id = 0;
            elseif (!($parent = Content::get_content($parent_id)))
                return drawMsg('content not found', WARNING, $close = true);

            // GET THE TYPE
            if (!$type = Content::get_content_type($type_id))
                return drawMsg('content type uknown', WARNING, $close = true);

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

            $template = array_shift($template_list);
            if (null === $template)
                $template = "";

            // CONTENT TEMPLATE
            // GET POSITION
            $position = Content::get_content_last_position($parent_id) + 1;

            // GET LAYOUT
            $layout_id = $parent_id > 0 ? $parent['layout_id'] : LAYOUT_ID_GENERIC;

            // PUBLISHED
            $published = true;

            // LANG_ID
            $lang_id = LANG_ID;


            // GET ID AND CONTENT_ID
            $content_id = Content::get_last_content_id() + 1;

            // SITEMAPS VARS
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
                "template" => $template, "published" => $published, "last_edit_time" => TIME,
                // google sitemap
                "changefreq" => $changefreq, "priority" => $priority
                    )
            );

            DB::insert(DB_PREFIX . "content_rel", array("content_id" => $content_id, "rel_id" => $parent_id, "rel_type" => "parent", "position" => $position ) );

            $this->_content_set_path($content_id, $lang_id, $parent_path = null);

            header("location: " . ADMIN_FILE_URL . "Content/edit/$content_id/");
            die;
        }

        /**
        * Save the contents
        */
        function save($content_id) {

            $layout_id = post('layout_id');
            $date = post('date');
            $template = post('template');
            $read_access = post('read_access');
            $changefreq = post('changefreq');
            $priority = post('priority');
            $start_date = post('start_date');
            $end_date = post('end_date');
            $menu_id = post('menu_id');

            // get content
            if (!$content_row = Content::get_content($content_id))
                return drawMsg(_CONTENT_NOT_FOUND_, WARNING, $close = true);

            // get type
            $type = Content::get_content_type($content_row['type_id']);
            $type_id = $content_row['type_id'];

            // set parent id and content_id
            $parent_id = $content_row['parent_id'];
            $content_id = $content_row['content_id'];

            /*
            // check access
            if( !content_access( $content_id ) )
            return drawMsg( _ACCESS_DENIED_, ERROR, $close=true );
            */

            // set date
            $date = isset($date) && $date != "" ? strtotime($date) : time();

            // create link
            if (( $linked_copy = post('linked_copy') ) && ( in_array(1, $linked_copy) ))
                foreach ($linked_copy as $linked_copy_id => $enabled) {
                    if (!$enabled)
                        db::query("DELETE 
                                   FROM " . DB_PREFIX . "content 
                                   WHERE parent_id=? AND content_id=?", array($linked_copy_id, $content_id));

                    elseif ($enabled && !db::get_row("SELECT * 
                                                                FROM " . DB_PREFIX . "content
                                                                WHERE parent_id=? AND content_id=?
                                                                LIMIT 1;", array($linked_copy_id, $content_id)))
                        content_create_link($content_id, $linked_copy_id);
                }


            // set google sitemaps
            if (User::is_super_admin()) {
                $changefreq = $changefreq >= -1 && $changefreq <= 6 ? $changefreq : $type['changefreq'];
                $priority = $priority >= 0.1 && $priority <= 1.0 ? $priority : $type['priority'];
            } else {
                $changefreq = $content_row['changefreq'];
                $priority = $content_row['priority'];
            }

            /*
            // delete event date table
            DB::query( "DELETE FROM ".DB_PREFIX."content_event_date
                        WHERE content_id=?", 
                        array($content_id) );
            */
            // check if there're new events
            if( $start_date ){

                foreach( $start_date as $i => $date ){

                    $start_time = mktime( 0, 0, 0, substr($date,0,2), substr($date,3,2), substr($date,6,4) );
                    $end_time = $start_time;
                    if( isset($end_date[$i]) ){
                        $date = $end_date[$i];
                        $end_time = mktime( 0, 0, 0, substr($date,0,2), substr($date,3,2), substr($date,6,4) );
                    }
                    
                    /*
                    DB::query("INSERT INTO ".DB_PREFIX."content_event_date
                                        (content_id, start_time, end_time)
                               VALUES   (:content_id, :start_time, :end_time )",
                               array( ":content_id"=>$content_id, ":start_time"=>$start_time, ":end_time"=>$end_time )
                            );
                     * 
                     */
                }
                
            }

            // get content in all languages
            $langs = db::get_all("SELECT * FROM " . DB_PREFIX . "content WHERE content_id=?", array($content_id), "lang_id");


            // get multilanguage fields
            $multilanguage_field_list = db::get_all("SELECT * 
                                                                    FROM " . DB_PREFIX . "content_type_field
                                                                    WHERE type_id = ? AND published=1 AND multilanguage=1
                                                                    ORDER BY position", array($content_row['type_id']));


            // GET ALL WORD FIELD TO REMOVE DEATH IMAGE
            $word_field = "";
            if ($multilanguage_field_list)
                foreach ($multilanguage_field_list as $field) {
                    if ($field['field_type'] == 'word')
                        foreach ($langs as $lang_id => $lang)
                            $word_field .= get($lang_id . "_" . $field['name']);
                }

            // REMOVE EMBED IMAGE NOT IN CONTENT ANY MORE
            $this->_deleteDeathImageFromContent($word_field, $content_id);

            // GET ALL WORD FIELD TO REMOVE DEATH IMAGE
            // SET ALL LANGUAGES
            foreach ($langs as $lang_id => $content_lang) {

                // IF THERE IS NO TITLE I DON'T CREATE ANY CONTENT
                if ($title = post($lang_id . '_title')) {

                    $published = post($lang_id . '_published');

                    // SET LANGUAGE FIELD QUERY
                    $query_field = "title=:title, published=:published";
                    $prepared_values[":title"] = $title;
                    $prepared_values[":published"] = $published;
                    
                    if ($multilanguage_field_list){
                        foreach ($multilanguage_field_list as $field){
                            $query_field .= "," . $field['name'] . "=:" . $field['name'];
                            $prepared_values[ ":" . $field['name'] ] = post($lang_id . "_" . $field['name']);
                        }
                    }

                    // TAGS
                    if ($type['tags_enabled']){
                        $query_field .= ",tags=:tags";
                        $prepared_values[":tags"] = post( $lang_id . "_tags");
                    }
                    
                    // CREATE NEW CONTENT IF THE LANGUAGE DOESN'T EXSISTS
                    if (!Content::get_content($content_id, $lang_id))
                        DB::insert( DB_PREFIX."content", array("content_id"=>$content_id,"lang_id"=>$lang_id) );

                    // UPDATE CONTENT MULTILANGUAGES

                    db::query("UPDATE " . DB_PREFIX . "content
                               SET $query_field
                               WHERE content_id=:content_id AND lang_id=:lang_id",
                               $prepared_values + array(":content_id"=>$content_id, ":lang_id"=>$lang_id )
                             );

                    // UPDATE CONTENT MULTILANGUAGES
                    // UPDATE LINK
                    //if( $title != $content_lang['title'] ) //if the title changed, also the link will change
                    $this->_content_set_path($content_id, $lang_id, $parent_path = null);
                }
            }
            // SET ALL LANGUAGES
            // UPDATE LANGUAGE FIELDS
            // SET ALL COMMON FIELDS QUERY
            $field_list = db::get_all("SELECT * 
                                                        FROM " . DB_PREFIX . "content_type_field 
                                                        WHERE type_id = ? AND published=1 AND multilanguage = 0 
                                                        ORDER BY position", array($content_row['type_id']));
            $query_field = "";
            if ($field_list)
                foreach ($field_list as $field)
                    if ($field['name'] != 'cover' && $field['name'] != 'date')
                        $query_field .= "," . $field['name'] . "='" . post($field['name']) . "'" . "\n";
            // SET ALL COMMON FIELDS QUERY
            // UPDATE CONTENT SETTINGS

            db::query("UPDATE " . DB_PREFIX . "content
                                SET layout_id       = :layout_id,
                                    type_id        = :type_id,
                                    menu_id        = :menu_id, 
                                    date            = :date,
                                    template        = :template,
                                    read_access     = :read_access,
                                    last_edit_time  = UNIX_TIMESTAMP(),
                                    changefreq      = :changefreq,
                                    priority        = :priority
                                    $query_field
                                WHERE content_id='$content_id'", array(":layout_id" => $layout_id, ":type_id" => $type_id, ":menu_id" => $menu_id, ":date" => $date, ":template" => $template, ":read_access" => $read_access, ":changefreq" => $changefreq, ":priority" => $priority)
            );


            // UPDATE CONTENT SETTINGS

            $content_row = Content::get_content($content_id);
            $title = $content_row['title'];
            $link = $content_row['link'];

            $script = "<script type=\"text/javascript\">" . "\n" .
                    "update_tree(0,'$content_id');" . "\n" .
                    "$('#content_title').html('$title')" . "\n" .
                    "$('#content_preview').attr('href','$link')" . "\n" .
                    '</script>' . "\n";
            

            Content::clean_cache( $content_id );

            echo $script . draw_msg('<a href="' . $link . '">' . get_msg("content_success_msg_edit") . '</a>', SUCCESS, $close = true, $autoClose = 10);
        }

        /**
        * delete content
        */
        function delete($content_id) {

            // sub contents
            $childs = Content::get_childs($content_id, LANG_ID, null, null, $only_published = false);
            for ($i = 0; $i < count($childs); $i++)
                $this->delete($childs[$i]['content_id'], LANG_ID);

            // If there aren't any other content with same content_id I delete all the linked files
            if (Content::file_count($content_id))
                $this->_file_delete_by_content_id($content_id);

            // delete the content
            Content::content_delete($content_id);
        }

        /**
        * File Upload
        */
        function upload_image_content() {
            $content_id = get_post('content_id');
            echo $this->_upload_image_content($content_id);
        }

        function cover_upload() {
            $content_id = get_post('content_id');
            echo $this->_cover_upload($content_id);
        }

        function cover_delete() {
            $content_id = get_post('content_id');
            echo $this->_cover_delete($content_id);
        }

        function file_upload($content_id) {
            echo $this->_file_upload($content_id);
        }

        function file_delete($file_id) {
            Content::file_delete($file_id);
        }

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

        /**
        * File Upload
        */
        function _file_upload($content_id) {



            // get the upload_id, when the updload is complete this function return the upload_id, so the javascript function knows which element to update.
            $upload_id = get_post("upload_id");


            // get the content
            $content = Content::get_content($content_id);
            $type_id = $content['type_id'];
            $type = Content::get_content_type($type_id);


            /*
            if( !content_access( $content_id ) )
            return json_encode( array( 'status'=>ERROR, 'msg'=>get_msg("access_denied"), 'upload_id'=>$upload_id) );
            * 
            */
            if (isset($_FILES['file'])) {

                $name = $_FILES['file']['name'];
                $size = $_FILES['file']['size'];
                $error = $_FILES['file']['error'];

                $ext = strtolower(file_ext($name));
                $thumb_src = $thumbnail_filepath = $width = $height = null;

                if ($error == UPLOAD_ERR_FORM_SIZE)
                    return json_encode(array('status' => ERROR, 'msg' => get_msg("file_exceed_size"), 'upload_id' => $upload_id));

                if (!$file_type_id = $this->_file_get_type($name))
                    return json_encode(array('status' => ERROR, 'msg' => get_msg("file_type_uknown"), 'upload_id' => $upload_id));

                if (!$type['file_enabled'] or
                        ( $file_type_id == IMAGE && !$type['image_enabled'] ) or
                        ( $file_type_id == AUDIO && !$type['audio_enabled'] ) or
                        ( $file_type_id == VIDEO && !$type['video_enabled'] ) or
                        ( $file_type_id == DOCUMENT && !$type['document_enabled'] ) or
                        ( $file_type_id == ARCHIVE && !$type['archive_enabled'] )
                ) {
                    return json_encode(array('status' => ERROR, 'msg' => get_msg("file_type_not_allowed"), 'upload_id' => $upload_id));
                }

                switch ($file_type_id) {

                    case IMAGE:
                        if ($file_info = upload_image('file', THUMB_PREFIX)) {

                            $filepath = $file_info["filepath"];
                            $thumbnail_filepath = $file_info["thumbnail_filepath"];
                            $thumb_src = UPLOADS_DIR . $thumbnail_filepath;

                            list($width, $height) = getimagesize(UPLOADS_DIR . $filepath);
                        }
                        else
                            return json_encode(array('status' => ERROR, 'msg' => get_msg("upload_error"), 'upload_id' => $upload_id));

                        break;

                    case VIDEO:
                    case AUDIO:
                    case DOCUMENT:
                    case ARCHIVE:

                        if ($file_info = upload_file('file')) {
                            $filepath = $file_info['filepath'];
                            $thumbnail_filepath = $width = $height = "";
                            list( $thumb_src ) = glob(ADMIN_VIEWS_IMAGES_DIR . "file_ext/{$ext}.*");
                            if (!$thumb_src)
                                $thumb_src = ADMIN_VIEWS_IMAGES_DIR . "image_not_found.gif";
                        }
                        else
                            return json_encode(array('status' => ERROR, 'msg' => get_msg("upload_error"), 'upload_id' => $upload_id));

                        break;
                }

                // get the file position
                $position = db::get_field("SELECT position+1 AS position FROM " . DB_PREFIX . "file WHERE rel_id=? AND module='content' ORDER BY position DESC LIMIT 1", array($content_id), "position");

                // update last edit
                db::query("UPDATE " . DB_PREFIX . "content
                        SET last_edit_time=UNIX_TIMESTAMP() 
                        WHERE content_id=?", array($content_id));

                db::query("INSERT INTO " . DB_PREFIX . "file 
                           ( rel_id, module, name, filepath, ext, thumb, type_id, status, size, width, height, last_edit_time ) 
                           VALUES ( :content_id, 'content', :name, :filepath, :ext, :thumbnail_filepath, :file_type_id, :status, :size, :width, :height, UNIX_TIMESTAMP() )", 
                           array(":content_id" => $content_id, ":name" => $name, ":filepath" => $filepath, ":ext" => $ext, ":thumbnail_filepath" => $thumbnail_filepath, ":file_type_id" => $file_type_id, ":status" => FILE_LIST, ":size" => $size, ":width" => $width, ":height" => $height));

                $file_id = db::get_last_id();

                // update the size total used space
                db::query("UPDATE " . DB_PREFIX . "setting SET value=value+? WHERE setting='space_used'", array($size));

                // insert image
                return json_encode(array('status' => SUCCESS, 'msg' => get_msg("upload_success"), 'upload_id' => $upload_id, 'file_id' => $file_id, 'thumb_src' => $thumb_src, 'filepath' => $filepath));
            }
            else
                return json_encode(array('status' => ERROR, 'msg' => get_msg("upload_error"), 'upload_id' => $upload_id));
        }

        function file_sort($content_id = null) {

            $sortable = get_post("sortable");
            $sortable = explode("&", str_replace("f[]=", "", $sortable));

            for ($i = 0; $i < count($sortable); $i++)
                db::query("UPDATE " . DB_PREFIX . "file SET position=:position WHERE file_id=:file_id AND rel_id=:content_id AND module='content' LIMIT 1;", array(":position" => $i, ":file_id" => $sortable[$i], ":content_id" => $content_id));
        }

        /**
        * Get the file type
        */
        function _file_get_type($file) {
            $ext = file_ext($file);
            if (preg_match("/$ext/i", get_setting("image_ext")))
                return IMAGE;
            if (preg_match("/$ext/i", get_setting("audio_ext")))
                return AUDIO;
            if (preg_match("/$ext/i", get_setting("video_ext")))
                return VIDEO;
            if (preg_match("/$ext/i", get_setting("document_ext")))
                return DOCUMENT;
            if (preg_match("/$ext/i", get_setting("archive_ext")))
                return ARCHIVE;
        }

        function _upload_image_content($content_id) {

            $w = 100;
            $h = 100;

            $content = Content::get_content($content_id);

            if (isset($_FILES['file']) && $file_info = upload_image('file', $thumb_prefix = "t_", $w, $h, false)) {

                $name = $file_info['name'];
                $size = $file_info['size'];
                $error = $file_info['error'];
                $ext = file_ext($name);
                $filepath = $file_info["filepath"];
                $thumbnail_filepath = $file_info["thumbnail_filepath"];
                $file_type_id = IMAGE;

                DB::query("INSERT INTO " . DB_PREFIX . "file 
                        ( rel_id, module, name, filepath, thumb, type_id, status, size, last_edit_time )
                        VALUES ( :content_id, 'content', :name, :filepath, :thumbnail_filepath, :file_type_id, :status, :size, UNIX_TIMESTAMP() )", 
                        array(":content_id" => $content_id, ":name" => $name, ":filepath" => $filepath, ":thumbnail_filepath" => $thumbnail_filepath, ":file_type_id" => $file_type_id, ":status" => FILE_EMBED, ":size" => $size)
                );

                $file_id = DB::get_last_id();

                return json_encode(array('result' => true, 'file_id' => $file_id, 'filepath' => $filepath, 'dir' => UPLOADS_URL));
            }
        }

        /**
        * Content Cover Upload
        */
        function _cover_upload($content_id) {

            // check if user has access
            //if( !content_access( $content_id ) )
            //    return false;

            if ($content = Content::get_content($content_id)) {
                $content_id = $content['content_id'];
                $type_id = $content['type_id'];


                // SET PARAMETER width, height, square of cover and cover thumb
                $field = DB::get_field("SELECT param FROM " . DB_PREFIX . "content_type_field WHERE name='cover' AND type_id=?", array($type_id));
                parse_str($field, $p);

                $w = isset($p['w']) ? $p['w'] : null;    // image width
                $h = isset($p['h']) ? $p['h'] : null;    // image height
                $s = isset($p['s']) ? $p['s'] : false;   // image is square
                //cover thumb
                $tw = isset($p['tw']) ? $p['tw'] : null;
                $th = isset($p['th']) ? $p['th'] : null;
                $ts = isset($p['ts']) ? $p['ts'] : false;

                if ($file_info = upload_image('cover', THUMB_PREFIX, $tw, $th, $ts)) {

                    $name = $file_info["name"];
                    $size = $file_info["size"];
                    $upload_path = $file_info["upload_path"];
                    $filename = $file_info["filename"];
                    $filepath = $file_info["filepath"];
                    $thumbnail_filename = $file_info["thumbnail_filename"];
                    $thumbnail_filepath = $file_info["thumbnail_filepath"];
                    
                    if (image_resize(UPLOADS_DIR . $filepath, UPLOADS_DIR . $filepath, $w, $h, $s)) {

                        $thumb = THUMB_PREFIX . $filepath;
                        $file_type_id = IMAGE;
                        list($width, $height) = getimagesize(UPLOADS_DIR . $filepath);

                        DB::query("INSERT INTO " . DB_PREFIX . "file 
                                ( rel_id, module, name, filepath, thumb, type_id, status, size, width, height, last_edit_time )
                                VALUES ( :content_id, 'content', :name, :filepath, :thumbnail_filepath, :file_type_id, :status, :size, :width, :height, UNIX_TIMESTAMP() )", 
                                array(":content_id" => $content_id, ":name" => $name, ":filepath" => $filepath, ":thumbnail_filepath" => $thumbnail_filepath, ":file_type_id" => $file_type_id, ":status" => FILE_COVER, ":size" => $size, ":width"=>$width, ":height"=>$height )
                        );

                        
                        $file_id = DB::get_last_id();

                        // update the size total used space
                        DB::query("UPDATE " . DB_PREFIX . "setting SET value=value+? WHERE setting='space_used'", array($size) );

                        return json_encode(array('status' => true, 'thumb_src' => UPLOADS_URL . $thumb, 'src' => UPLOADS_URL . $filepath));
                    }
                    else
                        return json_encode(array('status' => false, 'msg' => 'Resize images error'));
                }
                else
                    return json_encode(array('status' => false, 'msg' => 'Upload images error'));
            }
        }

        /**
        * Content cover delete
        *
        */
        function _cover_delete($content_id) {

            // check if user has access
            //if( !content_access( $content_id ) )
            //    return false;
            if ($content = Content::get_content($content_id)) {
                $content_id = $content['content_id'];
                if ($cover = DB::get_row("SELECT file_id
                                                FROM " . DB_PREFIX . "file
                                                WHERE rel_id=? AND module='content' AND status=?
                                                LIMIT 1;", array($content_id, FILE_COVER)
                ))
                    Content::file_delete($cover['file_id']);

                DB::query("UPDATE " . DB_PREFIX . "content
                                SET cover = ''
                                WHERE content_id=?", array($content_id)
                );
                return true;
            }
        }
        

    }

    // -- end