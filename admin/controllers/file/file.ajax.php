<?php

    require "file_base.php";

    class FileAjaxController extends FileBaseController {

        function file_upload() {
            echo $this->_file_upload();
        }

        function file_delete($file_id) {
            Content::file_delete($file_id);
        }

        /**
        * File Upload
        */
        function _file_upload() {



            // get the upload_id, when the updload is complete this function return the upload_id, so the javascript function knows which element to update.
            $upload_id = get_post("upload_id");

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

                
                $result = db::query("INSERT INTO " . DB_PREFIX . "file 
                                     ( name, filepath, ext, thumb, type_id, size, width, height, last_edit_time ) 
                                     VALUES ( :name, :filepath, :ext, :thumbnail_filepath, :file_type_id, :size, :width, :height, UNIX_TIMESTAMP() )", 
                                     array(":name" => $name, ":filepath" => $filepath, ":ext" => $ext, ":thumbnail_filepath" => $thumbnail_filepath, ":file_type_id" => $file_type_id, ":size" => $size, ":width" => $width, ":height" => $height) );

                // If I can't upload the file I delete the file
                if( !$result ){
                    unlink( UPLOADS_DIR . $filepath );
                    unlink( UPLOADS_DIR . $thumbnail_filepath );
                    return json_encode(array('status' => ERROR, 'msg' => get_msg("upload_error"), 'upload_id' => $upload_id));
                }

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

    }

    // -- end