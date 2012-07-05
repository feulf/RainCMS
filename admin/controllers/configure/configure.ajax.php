<?php

    load_lang("admin.configure");

    class ConfigureAjaxController extends Controller {

        function settings($action = null) {

            switch ($action) {
                case 'save':
                    // get all settings
                    $this->_settings_save(get_post(null, null));
                    break;
            }
        }

        function set_theme($theme) {
            $this->load_model("configure");
            $this->configure->set_theme($theme);
        }

        function load_themes() {

            DB::query("DELETE FROM " . DB_PREFIX . "theme");
            if ($theme_list = dir_list(THEMES_DIR)) {

                foreach ($theme_list as $theme) {

                    if ($xml = simplexml_load_file(THEMES_DIR . $theme . "/info.xml")) {
                        $name = $xml->name;
                        $description = $xml->description;
                        $tags = $xml->tags;
                        $colors = $xml->color;
                        $author = $xml->author;
                        $author_email = $xml->author_email;
                        $author_website = $xml->author_website;

                        DB::insert(DB_PREFIX . "theme", array("theme_id" => $theme, "theme" => $name, "description" => $description, "tags" => $tags, "colors" => $colors, "directory" => $theme, "author" => $author, "author_email" => $author_email, "author_website" => $author_website));
                    }
                }
            }
        }

        function _settings_save($settings) {
            $this->load_model("configure");
            if ($this->configure->settings_save($settings))
                echo draw_msg("update_success", SUCCESS, $close = true, $autoClose = 10);
        }

    }

    // -- end
