<?php

    load_lang("admin.configure");
    add_script("conf.js", ADMIN_JAVASCRIPT_DIR, ADMIN_JAVASCRIPT_URL);
    add_style("conf.css", ADMIN_VIEWS_CSS_DIR, ADMIN_VIEWS_CSS_URL);

    class ConfigureController extends Controller {

        /**
        * @var Form
        */
        var $form;

        function index($selection = "info", $type_id = null) {

            $html_info = $html_settings = $html_content_types = $html_languages = $html_modules = $html_themes = $html_layout = null;

            switch ($selection) {
                case 'content_types': $html_content_types = $this->_content_types($type_id);
                    break;
                case 'languages': $html_languages = $this->_languages();
                    break;
                case 'modules': $html_modules = $this->_modules();
                    break;
                case 'pages': $html_layout = $this->_layout();
                    break;
                case 'themes': $html_themes = $this->_themes();
                    break;
                default:
                case 'settings':
                    $selection = 'settings';
                    $html_settings = $this->_settings();
                    break;
            }

            $this->load_library("tab");
            $this->tab->add_tab("settings", $html_settings, "conf_button_settings", "", ADMIN_FILE_URL . "Configure/settings/");
            //$this->tab->add_tab("content_types", $html_content_types, "conf_button_content_types", "", ADMIN_FILE_URL . "Configure/content_types/");
            //$this->tab->add_tab("modules", $html_modules, "conf_button_modules", "", ADMIN_FILE_URL . "Configure/modules/");
            //$this->tab->add_tab("languages", $html_languages, "conf_button_languages", "", ADMIN_FILE_URL . "Configure/languages/");
            //$this->tab->add_tab("pages", $html_layout, "conf_button_layout", "", ADMIN_FILE_URL . "Configure/pages/");
            $this->tab->add_tab("themes", $html_themes, "conf_button_themes", "", ADMIN_FILE_URL . "Configure/themes/");
            $this->tab->sel_tab($selection);
            $content_tab = $this->tab->draw($to_string = true);

            $view = new View;
            $view->assign("content", $content_tab);
            $view->draw("conf/configure");
        }

        function info() {
            $this->index('info');
        }

        function settings() {
            $this->index('settings');
        }

        function content_types($type_id = null) {
            $this->index('content_types', $type_id);
        }

        function languages() {
            $this->index('languages');
        }

        function modules() {
            $this->index('modules');
        }

        function pages() {
            $this->index('pages');
        }

        function themes() {
            $this->index('themes');
        }

        protected function _settings() {

            $this->load_library("form");
            $this->form->init_form(URL . "admin.ajax.php/configure/settings/save/");

            $this->form->open_table("conf_button_settings");

            //nome del sito
            $this->form->add_item("text", "settings_website_name", "conf_form_name", null, get_setting('website_name'), "required,maxlength=255");
            $this->form->add_item("text", "settings_description", "conf_form_description", null, get_setting('description'), "maxlength=255");
            $this->form->add_item("text", "settings_website_tel", "conf_form_tel", null, get_setting('website_tel'), "required,maxlength=255");
            $this->form->add_item("text", "settings_website_address", "conf_form_address", null, get_setting('website_address'), "required,maxlength=255");
            $this->form->add_item("text", "settings_copyright", "conf_form_copyright", null, get_setting('copyright'), "maxlength=255", array('mode' => 'simple'));
            $this->form->add_item("text", "settings_website_domain", "conf_form_domain", null, get_setting('website_domain'), "maxlength=255", array('mode' => 'simple'));

            $this->form->add_item("yes", "settings_published", "conf_form_published", null, get_setting('published'), "required,maxlength=255");
            $this->form->add_item("textarea", "settings_not_published_msg", "conf_form_not_published_msg", null, get_setting('not_published_msg'), "maxlength=255", array('height' => 200, 'mode' => 'simple'), "row");
            $this->form->add_button();
            $this->form->close_table();

            $this->form->open_table("conf_form_user");
            $this->form->add_item("yes", "settings_user_login", "conf_form_login", null, get_setting('user_login'));
            $this->form->add_item("yes", "settings_user_register", "conf_form_register", null, get_setting('user_register'));
            $this->form->add_item("select", "settings_registration_confirm", "conf_form_registration_confirm", null, get_setting('registration_confirm'), null, Array("options" => Array(REGISTRATION_CONFIRM_FREE => get_msg("conf_form_registration_confirm_free"), REGISTRATION_CONFIRM_EMAIL => get_msg("conf_form_registration_confirm_email"), REGISTRATION_CONFIRM_ADMIN => get_msg("conf_form_registration_confirm_admin"))));
            $this->form->add_button();
            $this->form->close_table();

            if (User::is_super_admin()) {
                $this->form->open_table("conf_form_table_google");
                $this->form->add_item("text", "settings_google_login", "conf_form_google_login", null, get_setting('google_login'), "required,maxlength=255");
                $this->form->add_item("password", "settings_google_password", "conf_form_google_password", null, get_setting('google_password'), "maxlength=255");
                $this->form->add_item("yes", "settings_google_analytics", "conf_form_google_analytics", null, get_setting('google_analytics'));
                $this->form->add_item("text", "settings_google_analytics_website", "conf_form_google_analytics_website", null, get_setting('google_analytics_website'), "required,maxlength=255");
                $this->form->add_item("text", "settings_google_analytics_code", "conf_form_google_analytics_code", "conf_form_google_analytics_code_field", get_setting('google_analytics_code'));
                $this->form->add_item("text", "settings_google_analytics_refresh_time", "conf_form_google_analytics_refresh_time", null, get_setting('google_analytics_refresh_time'));
                $this->form->add_button();

                $this->form->open_table("conf_form_table_email");
                $this->form->add_item("text", "settings_email_admin", "conf_form_email_admin", null, get_setting('email_admin'), "maxlength=50,required");
                $this->form->add_item("text", "settings_email_noreply", "conf_form_email_noreply", null, get_setting('email_noreply'), "maxlength=50,required");
                $this->form->add_item("select", "settings_email_type", "conf_form_email_type", null, get_setting('email_type'), 'required', array('options' => array('mail' => 'mail', 'smtp' => 'smtp')));
                $this->form->add_item("text", "settings_smtp_host", "conf_form_smtp_host", null, get_setting('smtp_host'), "maxlength=255");
                $this->form->add_item("text", "settings_smtp_login", "conf_form_smtp_login", null, get_setting('smtp_login'), "maxlength=255");
                $this->form->add_item("text", "settings_smtp_password", "conf_form_smtp_password", null, get_setting('smtp_password'));
                $this->form->add_button();
                $this->form->close_table();
            }

            return $this->form->draw($ajax = true, $to_string = true);
        }

        protected function _info() {

            $html = "";
            //$html = $this->_account_info();
            $html .= $this->_space_info();
            $html .= $this->_analytics_info();

            return $html;
        }

        protected function _space_info() {

            $used_space = get_setting('space_used');
            $tot_space = get_setting('space_tot');
            $free_space = $tot_space - $used_space;

            //love easy
            $this->load_library("Charts");
            $data = array(array('used space: ' . byte_format($used_space), $used_space), array('free space: ' . byte_format($free_space), $free_space));
            $this->charts->set_data($data);
            $chart_pie = $this->charts->draw_pie($width = 400, $height = 250);

            $view = new View;
            $view->assign("title", "Memory usage");
            $view->assign("content", $chart_pie);
            return $view->draw("conf/info_space", true);
        }

        protected function _account_info() {
            $view = new View;
            $view->assign(User::get_user());
            return $view->draw("conf/info_account", $to_string = true);
        }

        protected function _analytics_info() {
            if (get_setting('google_analytics')) {
                $this->load_library("Analytics");

                try {
                    $this->analytics->__init();
                    $html = $this->analytics->draw_stats();
                } catch (ErrorException $e) {
                    $html = "Can't load analytics";
                }
            }
        }

        protected function _languages() {

            $language_list = db::get_all("SELECT * FROM " . DB_PREFIX . "language ORDER BY position");

            // content sortable
            add_javascript("lang_sortable();", $onload = true);

            $view = new View;
            $view->assign("language_list", $language_list);
            return $view->draw("conf/languages.list", $to_string = true);
        }

        protected function _layout() {

            add_javascript("layout_sortable();", $onload = true);

            $layout_list = db::get_all("SELECT * FROM " . DB_PREFIX . "layout ORDER BY position DESC");

            $view = new View();
            $view->assign("layout_list", $layout_list);
            return $view->draw("conf/layout.list", $to_string = true);
        }

        protected function _themes() {

            add_javascript("theme_sortable();", $onload = true);

            $theme_list = db::get_all("SELECT * FROM " . DB_PREFIX . "theme ORDER BY date DESC");

            $view = new View();
            $view->assign("theme_user", get_setting('theme_user'));
            $view->assign("theme_list", $theme_list);
            return $view->draw("conf/themes.list", $to_string = true);
        }

        protected function _modules() {

            $module_list = db::get_all("SELECT * FROM " . DB_PREFIX . "module ORDER BY module");

            // content sortable
            add_javascript("module_sortable();", $onload = true);

            $view = new View;
            $view->assign("module_list", $module_list);
            return $view->draw("conf/modules.list", $to_string = true);
        }

        protected function _content_types($type_id = 1) {

            $html = $title = $tools = null;

            if ($type_id > 0) {
                $content_type = Content::get_content_type($type_id);
                $title = $content_type['type'];
                $html = $this->_edit_content_type($type_id);
            }

            $view = new View;
            $view->assign("content_type_tree", $this->_draw_tree($type_id));
            $view->assign("title", $title);
            $view->assign("tools", $tools);
            $view->assign("html", $html);
            return $view->draw("conf/content_types", $to_string = true);
        }

        // draw tree
        function _draw_tree($sel_id = 0) {

            $html = '<div id="site_tree" class="tree">' . "\n";
            $html .= '    <a href="' . ADMIN_FILE_URL . '/Configure/content//"><img src="' . ADMIN_VIEWS_IMAGES_URL . 'tree/content.gif" title="' . get_msg("content_button_content_list") . '" class="tooltip" alt="-" /></a> <a href="' . ADMIN_FILE_URL . '/Configure/contents/1/" title="' . get_msg("content_button_content_list") . '" class="tooltip' . ($sel_id == 0 ? ' selected' : null) . '">' . get_msg("content") . '</a>' . "\n";

            $html .= '    <ul id="tree_0">' . "\n";
            $html .= $this->_draw_site_tree($sel_id) . "\n";
            $html .= '    </ul>' . "\n";
            $html .= '</div>' . "\n";
            $html .= '<!-- /Content Tree -->' . "\n";
            return $html;
        }

        function _draw_site_tree($sel_id = 0, $type_id = 1, $level = 0) {

            if ($level > 2)
                return null;

            $html = "";

            // 5 minutes
            if ($type_list = Content::get_content_type_childs($type_id)) {

                for ($i = 0; $i < count($type_list); $i++) {

                    $type_content = $type_list[$i];
                    $type = $type_content['type'];
                    $type_id = $type_content['type_id'];
                    $title = cut($type, 22);
                    $published = $type_content['published'];
                    $type_icon = $type_content['icon'];
                    $hasChilds = $type_id > 1 ? Content::get_content_type_childs($type_id) : null;
                    $isLast = count($type_list) - 1 == $i;
                    $sa = 1;

                    $html .= '        <li id="li_tree_' . $type_id . '" ' . ( $isLast ? 'class="last"' : null ) . '>' . "\n";
                    $html .= '            <div ' . ( $hasChilds ? 'class="voice"' : null ) . '>';

                    // edit list
                    $html .= '<a href="' . ADMIN_FILE_URL . 'Configure/contents/' . $type_id . '/">';
                    $html .= '<img src="' . ADMIN_VIEWS_IMAGES_URL . "tree/" . $type_icon . '" class="tooltip" title="' . get_msg("content_button_content") . '" alt=""/> ';
                    $html .= '<a href="' . ADMIN_FILE_URL . 'Configure/contents/' . $type_id . '/" class="tooltip' . ($sel_id == $type_id ? ' selected' : null) . ' title="' . get_msg("content_button_content_list") . '">';
                    $html .= $title . '</a>';
                    $html .= "</div>" . "\n";

                    if ($hasChilds && $type_id > 1) {
                        $html .= "            <ul id=\"tree_{$type_id}\" class=\"normal\">";
                        $html .= $this->_draw_site_tree($sel_id, $type_id, $level + 1);
                        $html .= "</ul>" . "\n";
                    }

                    $html .= "        </li>" . "\n";
                }
            }

            return $html;
        }

        function _close_site_tree($id) {
            unset($_SESSION['content_tree'][$id]);
        }

        function _edit_content_type($id) {

            $content_type = Content::get_content_type($id);
            $field_list = Content::get_content_type_fields($id);
            $view = new View;
            $view->assign($content_type);
            $view->assign("field_list", $field_list);
            return $view->draw("conf/content_type", $to_string = true);
        }

    }

    // -- end