<?php

    load_lang("admin.configure");
    Layout::addScript("conf.js", ADMIN_JAVASCRIPT_DIR, ADMIN_JAVASCRIPT_URL);
    Layout::addStyle("conf.css", ADMIN_VIEWS_CSS_DIR, ADMIN_VIEWS_CSS_URL);

    class DashboardController extends Controller {

        function index() {

            $html = "";
            //$html = $this->_account_info();
            $html .= $this->_space_info();
            $html .= $this->_analytics_info();
            echo $html;
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
                $this->analytics->__init();
                return $this->analytics->draw_stats();
            }
        }

    }

    // -- end