<?php

    class CommentAjaxModule extends Module {

        function add($id) {

            $content = Content::get_content($id);

            // can publish
            if ($content && $content['comment_enabled']) {

                // check last comment
                if ($last_comment = Content::get_last_comment($id)) {

                }

                $comment = get_post("comment");
                $comment = strip_tags($comment, "<b><i>");
                $comment = str_replace("\n", "<br>", $comment);
                $comment = str_replace("\r", "", $comment);

                $name = User::get_user_field("name");

                // add comment
                DB::insert(DB_PREFIX . "content_comment", array("content_id" => $content['content_id'],
                    "lang_id" => LANG_ID,
                    "user_id" => User::get_user_id(),
                    "name" => $name,
                    "comment" => $comment,
                    "date" => time(),
                    "ip" => IP,
                    "published" => true));

                $comment_id = db::get_last_id();
                $comment_row = Content::get_comment($comment_id);

                // get total comment
                $ncomment = Content::get_n_comment($id);

                // set the number of comment
                DB::query("UPDATE " . DB_PREFIX . "content SET ncomment = ? WHERE content_id = ? AND lang_id=?", array($ncomment, $id, LANG_ID));


                // alert users with email
                // response
                echo json_encode($comment_row);
            }
        }

    }

    // -- end