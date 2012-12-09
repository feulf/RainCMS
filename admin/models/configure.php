<?php

    class ConfigureModel {

        function settings_save($settings) {

            // extract all the settings
            extract($settings);

            if (isset($settings_website_name))
                db::query("UPDATE " . DB_PREFIX . "setting SET value=? WHERE setting='website_name'", array($settings_website_name));
            if (isset($settings_description))
                db::query("UPDATE " . DB_PREFIX . "setting SET value=? WHERE setting='description'", array($settings_description));
            if (isset($settings_website_tel))
                db::query("UPDATE " . DB_PREFIX . "setting SET value=? WHERE setting='website_tel'", array($settings_website_tel));
            if (isset($settings_website_address))
                db::query("UPDATE " . DB_PREFIX . "setting SET value=? WHERE setting='website_address'", array($settings_website_address));
            if (isset($settings_copyright))
                db::query("UPDATE " . DB_PREFIX . "setting SET value=? WHERE setting='copyright'", array($settings_copyright));
            if (isset($settings_website_domain))
                db::query("UPDATE " . DB_PREFIX . "setting SET value=? WHERE setting='website_domain'", array($settings_website_domain));

            if (isset($settings_user_login))
                db::query("UPDATE " . DB_PREFIX . "setting SET value=? WHERE setting='user_login'", array($settings_user_login));
            if (isset($settings_user_register))
                db::query("UPDATE " . DB_PREFIX . "setting SET value=? WHERE setting='user_register'", array($settings_user_register));
            if (isset($settings_registration_confirm))
                db::query("UPDATE " . DB_PREFIX . "setting SET value=? WHERE setting='registration_confirm'", array($settings_registration_confirm));

            if (isset($settings_published))
                db::query("UPDATE " . DB_PREFIX . "setting SET value=? WHERE setting='published'", array($settings_published));
            if (isset($settings_not_published_msg))
                db::query("UPDATE " . DB_PREFIX . "setting SET value=? WHERE setting='not_published_msg'", array($settings_not_published_msg));

            if (User::is_super_admin()) {
                if (isset($settings_google_analytics))
                    db::query("UPDATE " . DB_PREFIX . "setting SET value=? WHERE setting='google_analytics'", array($settings_google_analytics));
                if (isset($settings_google_analytics_code))
                    db::query("UPDATE " . DB_PREFIX . "setting SET value=? WHERE setting='google_analytics_code'", array($settings_google_analytics_code));
                if (isset($settings_google_analytics_refresh_time))
                    db::query("UPDATE " . DB_PREFIX . "setting SET value=? WHERE setting='google_analytics_refresh_time'", array($settings_google_analytics_refresh_time));
                if (isset($settings_google_analytics_website))
                    db::query("UPDATE " . DB_PREFIX . "setting SET value=? WHERE setting='google_analytics_website'", array($settings_google_analytics_website));
                if (isset($settings_google_login))
                    db::query("UPDATE " . DB_PREFIX . "setting SET value=? WHERE setting='google_login'", array($settings_google_login));
                if (isset($settings_google_password))
                    db::query("UPDATE " . DB_PREFIX . "setting SET value=? WHERE setting='google_password'", array($settings_google_password));

                if (isset($settings_email_admin))
                    db::query("UPDATE " . DB_PREFIX . "setting SET value=? WHERE setting='email_admin'", array($settings_email_admin));
                if (isset($settings_email_noreply))
                    db::query("UPDATE " . DB_PREFIX . "setting SET value=? WHERE setting='email_noreply'", array($settings_email_noreply));
                if (isset($settings_email_type))
                    db::query("UPDATE " . DB_PREFIX . "setting SET value=? WHERE setting='email_type'", array($settings_email_type));
                if (isset($settings_smtp_host))
                    db::query("UPDATE " . DB_PREFIX . "setting SET value=? WHERE setting='smtp_host'", array($settings_smtp_host));
                if (isset($settings_smtp_login))
                    db::query("UPDATE " . DB_PREFIX . "setting SET value=? WHERE setting='smtp_login'", array($settings_smtp_login));
                if (isset($settings_smtp_password))
                    db::query("UPDATE " . DB_PREFIX . "setting SET value=? WHERE setting='smtp_password'", array($settings_smtp_password));
                if (isset($settings_email_n_send))
                    db::query("UPDATE " . DB_PREFIX . "setting SET value=? WHERE setting='smtp_n_send'", array($settings_email_n_send));
                if (isset($settings_email_wait))
                    db::query("UPDATE " . DB_PREFIX . "setting SET value=? WHERE setting='smtp_wait'", array($settings_email_wait));
            }

            return true;
        }


        function files_save($settings) {

            // extract all the settings
            extract($settings);
            
            if( User::is_super_admin() ){
                
                if (isset($settings_image_ext))
                    db::query("UPDATE " . DB_PREFIX . "setting SET value=? WHERE setting='image_ext'", array($settings_image_ext));
                if (isset($settings_audio_ext))
                    db::query("UPDATE " . DB_PREFIX . "setting SET value=? WHERE setting='audio_ext'", array($settings_audio_ext));
                if (isset($settings_video_ext))
                    db::query("UPDATE " . DB_PREFIX . "setting SET value=? WHERE setting='video_ext'", array($settings_video_ext));
                if (isset($settings_document_ext))
                    db::query("UPDATE " . DB_PREFIX . "setting SET value=? WHERE setting='document_ext'", array($settings_document_ext));
                if (isset($settings_archive_ext))
                    db::query("UPDATE " . DB_PREFIX . "setting SET value=? WHERE setting='archive_ext'", array($settings_archive_ext));
                if (isset($settings_other_ext))
                    db::query("UPDATE " . DB_PREFIX . "setting SET value=? WHERE setting='other_ext'", array($settings_other_ext));

                
                if (isset($settings_max_file_size_upload))
                    db::query("UPDATE " . DB_PREFIX . "setting SET value=? WHERE setting='max_file_size_upload'", array($settings_max_file_size_upload));
                if (isset($settings_image_quality))
                    db::query("UPDATE " . DB_PREFIX . "setting SET value=? WHERE setting='image_quality'", array($settings_image_quality));
                if (isset($settings_thumbnail_size))
                    db::query("UPDATE " . DB_PREFIX . "setting SET value=? WHERE setting='thumbnail_size'", array($settings_thumbnail_size));
                if (isset($settings_image_size_allowed))
                    db::query("UPDATE " . DB_PREFIX . "setting SET value=? WHERE setting='image_size_allowed'", array($settings_image_size_allowed));
            
                // get the allowed image size and create the config file
                $image_sizes = $settings_thumbnail_size . "," . $settings_image_size_allowed;
                preg_match_all( "#\d*x\d*#", $image_sizes, $matches);
                $image_size_allowed = "'" . implode("','",$matches[0]) . "'";
                
                $image_size_conf_file = CONFIG_DIR . "image_sizes.php";
                $file_content = "<?php \$image_sizes_allowed=array(" . $image_size_allowed . ");";
                file_put_contents( $image_size_conf_file, $file_content );
                
            }
            return true;
        }

        function set_theme($theme_id) {
            if ($directory = db::get_field("SELECT directory FROM " . DB_PREFIX . "theme WHERE theme_id=?", array($theme_id), "directory"))
                db::query("UPDATE " . DB_PREFIX . "setting SET value=? WHERE setting='theme' LIMIT 1", array($directory));
        }

    }

    // -- end