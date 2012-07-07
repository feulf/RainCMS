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

        function set_theme($theme_id) {
            if ($directory = db::get_field("SELECT directory FROM " . DB_PREFIX . "theme WHERE theme_id=?", array($theme_id), "directory"))
                db::query("UPDATE " . DB_PREFIX . "setting SET value=? WHERE setting='theme' LIMIT 1", array($directory));
        }

    }

    // -- end