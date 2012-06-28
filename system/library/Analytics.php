<?php

    require_once "analytics_api.php";

    class Analytics {

        protected static $api, $analytics_id, $analytics_profile_id, $api_auth;

        function __init() {

            if (get_setting('google_login') && get_setting('google_password')) {

                self::$api = new analytics_api();
                if (isset($_SESSION['analytics_auth']))
                    self::$api_auth = self::$api->auth = $_SESSION['analytics_auth'];
                elseif (self::$api->login(get_setting('google_login'), get_setting('google_password')))
                    self::$api_auth = $_SESSION['analytics_auth'] = self::$api->auth;

                if (isset($_SESSION['analytics_id'])) {
                    self::$analytics_id = $_SESSION['analytics_id'];
                    self::$analytics_profile_id = $_SESSION['analytics_profile_id'];
                } else {
                    $site = get_setting('website_domain');
                    self::$api->load_accounts();
                    self::$analytics_id = $_SESSION['analytics_id'] = self::$api->accounts[$site]['tableId'];
                    self::$analytics_profile_id = $_SESSION['analytics_profile_id'] = self::$api->accounts[$site]['profileId'];
                }
            }
        }

        function get_stats($dimensions = null, $metrics = null, $sort = null, $start_date = null, $end_date = null, $max_results = 10, $start_index = 1, $filters = null) {
            if (!self::$analytics_id)
                self::__init();
            return self::$api->data(self::$analytics_id, $dimensions, $metrics, $sort, $start_date, $end_date, $max_results, $start_index, $filters);
        }

        function draw_stats($dimensions = null, $metrics = null, $sort = null, $start_date = null, $end_date = null, $max_results = 100, $start_index = 1, $filters = null, $page = null) {

            if (file_exists($file = CACHE_DIR . $page . "_analytics.html") && ( time() - ( $ft = filemtime($file) ) ) < (60 * get_setting('google_analytics_refresh_time')))
                return file_get_contents($file);
            else {
                $dimensions = 'ga:date';
                $metrics = 'ga:visits'; //,ga:uniquePageviews,ga:bounces,ga:entrances,ga:exits,ga:newVisits,ga:timeOnPage';
                $sort = 'ga:date';
                $start_date = date("Y-m-d", time() - ( 32 * DAY ));
                $end_date = date("Y-m-d");
                $data = self::get_stats($dimensions, $metrics, $sort, $start_date, $end_date, $max_results, $start_index, $filters);

                foreach ($data as $key => $value) {
                    $time = mktime(0, 0, 0, substr($key, 4, 2), substr($key, 6, 2), substr($key, 0, 4));
                    $stats[$time] = $value;
                }

                $summary = self::get_stats_summary($start_date, $end_date, $filters);

                $tpl = new View();
                $tpl->assign("analytics_id", self::$analytics_id);
                $tpl->assign("analytics_profile_id", self::$analytics_profile_id);
                $tpl->assign("stats", $stats);
                $tpl->assign("summary", $summary);
                $tpl->assign("last_refresh", time());
                $html = $tpl->draw("conf/info_stats", true);

                file_put_contents($file, $html);

                return $html;
            }
        }

        function get_stats_summary($start_date, $end_date, $filters) {
            if (!self::$analytics_id)
                self::__init();

            return $data = self::$api->get_summary(self::$analytics_id, $start_date, $end_date, $filters);
        }

        function draw_page_stats($page) {
            $sort = $dimensions = 'ga:date';
            $metrics = 'ga:pageviews'; //,ga:uniquePageviews,ga:bounces,ga:entrances,ga:exits,ga:newVisits,ga:timeOnPage';
            $start_date = date("Y-m-d", time() - ( 32 * DAY ));
            $end_date = date("Y-m-d");
            $filters = new analytics_filters("ga:pagePath", "==", $page);
            return self::draw_stats($dimensions, $metrics, $sort, $start_date, $end_date, $max_results = 100, $start_index = 1, $filters, $page);
        }

    }

    // -- end