<?php

    /**
    *  RainFramework
    *  -------------
    *  Realized by Federico Ulfo & maintained by the Rain Team
    *  Distributed under MIT license http://www.opensource.org/licenses/mit-license.php
    */

    /**
    * Load and draw templates
    *
    */
    class User {

        /**
        * User container
        */
        private static $user;

        /**
        * Get the user id of the authenticated user
        * @return type
        */
        static function get_user_id() {
            return isset(static::$user) ? static::$user['user_id'] : 0;
        }

        /**
        * Get all the user info (except the PW)
        * @param type $user_id
        * @return type
        */
        static function get_user($user_id = NULL) {
            if ($user_id)
                return DB::get_row("SELECT *, '' AS password, '' AS salt FROM " . DB_PREFIX . "user WHERE user_id=?", array($user_id));
            else
                return isset(static::$user) ? static::$user : NULL;
        }

        /**
        * Get the user level
        * @param type $user_id
        */
        static function get_user_level($user_id) {

            // get the user info
            $user = static::get_user($user_id);

            // set the user level (Admin, Moderator ...)
            $user['level'] = get_msg(strtolower($GLOBALS['user_level'][$user['status']]));
        }

        /**
        * Refresh the user info, useful if the user change his information
        * @return type
        */
        static function refresh_user_info() {

            static::$user = $_SESSION['user'] = static::get_user();
            static::$user['check'] = $_SESSION['user']['check'] = BASE_NAME;
            static::refresh_localization();
            return static::$user;
        }

        /**
        * Return true if the user is admin
        * @param type $user_id
        * @return type
        */
        static function is_admin($user_id = NULL) {
            return static::get_user_field("status", $user_id) >= USER_ADMIN;
        }

        /**
        * return true if the user is super admin
        * @param int $user_id By default is selected the logged user
        */
        static function is_super_admin($user_id = NULL) {
            return static::get_user_field("status", $user_id) >= USER_SUPER_ADMIN;
        }

        /**
        * Get the selected field of the user
        * @param string $field Selected field
        * @param int $user_id By default is selected the logged user
        */
        static function get_user_field($field, $user_id = NULL) {
            if ($user = static::get_user($user_id)) {
                if (isset($user[$field]))
                    return $user[$field];
                else
                    trigger_error("Field not found: $field");
            }
        }


        /**
        * set the language on the selected user
        */
        static function set_user_lang($lang_id) {
            if ($user_id = static::get_user_id()) {
                DB::query('UPDATE ' . DB_PREFIX . 'user SET lang_id=? WHERE user_id=?', array($lang_id, $user_id));
                $_SESSION['user']['lang_id'] = $lang_id;
            }
        }


        /**
        * Do login, and return the login status
        */
        static function login($login = NULL, $password = NULL, $enable_cookies = false, $logout = NULL, $errorWait = 1) {

            if ($logout)
                return LOGIN_LOGOUT;

            // true if the user is logged
            // In shared server could happen that your login is shared in all website, user.check verify that the login is only on this application
            elseif (!$login && !$password && isset($_SESSION['user']) && isset($_SESSION['user']['check']) && $_SESSION['user']['check'] == BASE_NAME) {
                static::$user = $_SESSION['user'];
                return LOGIN_LOGGED;
            }
            else
                $_SESSION['user'] = NULL;


            //se login e password sono salvate nei cookie
            if (cookie('login') && cookie('password')) {
                $login = cookie('login');
                $salt_and_pw = cookie('password');
            }
            else
                $salt_and_pw = NULL;

            //check if there's login and pw, or salt_pw
            if ($login AND ($password OR $salt_and_pw)) {

                if (!$salt_and_pw)
                    $salt_and_pw = md5(DB::get_field("SELECT salt FROM " . DB_PREFIX . "user WHERE email=?", array($login) ) . $password);

                if ($user = DB::get_row("SELECT * FROM " . DB_PREFIX . "user WHERE email=:login AND password = :salt_and_pw", array(":login"=>$login, ":salt_and_pw"=>$salt_and_pw) ) ) {

                    // create new salt and password
                    if ($password) {
                        $user_id = $user['user_id'];
                        $salt = rand(0, 99999);
                        $md5_password = md5($salt . $password);
                        DB::query("UPDATE " . DB_PREFIX . "user SET password=:md5_password, salt=:salt, activation_code='' WHERE user_id=:user_id", array(":md5_password"=>$md5_password,":salt"=>$salt, "user_id"=>$user_id ) );
                    }

                    if ($enable_cookies) {
                        set_cookie("login", $login, YEAR );
                        set_cookie("password", $salt_and_pw, YEAR );
                    }

                    if ($user['status'] >= USER_ADMIN) {
                        set_cookie("admin_check", 1, DAY);
                    }


                    $user['check'] = $_SESSION['user']['check'] = BASE_NAME;
                    $user['level'] = get_msg(strtolower($GLOBALS['user_level'][$user['status']]));

                    // save user data
                    static::$user = $_SESSION['user'] = $user;

                    //update date and IP
                    DB::query("UPDATE " . DB_PREFIX . "user SET last_ip=:ip, data_login=UNIX_TIMESTAMP() WHERE user_id=:user_id", array(":ip"=>get_ip(), ":user_id"=>$user['user_id']));

                    return LOGIN_DONE;
                } else {

                    // if login is wrong PHP will sleep for $errorWait seconds
                    sleep($errorWait);
                    static::$user = NULL;
                    unset($_SESSION['user']);
                    delete_cookie("login");
                    delete_cookie("password");
                    delete_cookie("admin_check");

                    return LOGIN_ERROR;
                }
            }
            else
                return LOGIN_NOT_LOGGED;
        }

        /**
        * Do logout
        */
        static function logout() {
            if ($user_id = static::get_user_id())
                static::logout_localization();
            static::$user = NULL;
            unset($_SESSION['user']);
            delete_cookie("login");
            delete_cookie("password");
            delete_cookie("admin_check");
        }

        /**
        * Set the User geolocation and path of the page he's visiting
        */
        static function init_localization($link = NULL, $content_id = NULL, $online_time = USER_ONLINE_TIME) {

            $file = basename($_SERVER['SCRIPT_FILENAME']);
            $url = $_SERVER['REQUEST_URI'];
            $user_localization = isset($_SESSION['user_localization']) ? $_SESSION['user_localization'] : NULL;
            $sid = session_id();
            $browser = BROWSER . " " . BROWSER_VERSION;
            $os = BROWSER_OS;
            $ip = IP;

            if (!$user_localization) {
                DB::query("DELETE FROM " . DB_PREFIX . "user_localization WHERE time < ?", array( TIME - HOUR ) );
            }

            $user_localization_id = $user_localization ? $_SESSION['user_localization']['user_localization_id'] : DB::get_field("SELECT user_localization_id FROM " . DB_PREFIX . "user_localization WHERE sid=?",array($sid));

            if ($user_id = static::get_user_id()) {
                $guest_id = 0;
                $name = static::get_user_field("name");
            } else {
                $guest_id = isset($user_localization['guest_id']) ? $user_localization['guest_id'] : ( 1 + DB::get_field("SELECT guest_id FROM " . DB_PREFIX . "user_localization ORDER BY guest_id DESC LIMIT 1;") );
                $name = get_msg('guest') . " " . $guest_id;
            }

            if ($user_localization_id) {
                $user_localization_array = array('ip' => $ip,
                    'sid' => $sid,
                    'user_id' => $user_id,
                    'guest_id' => $guest_id,
                    'name' => $name,
                    'url' => $url,
                    'content_id' => $content_id,
                    'file' => $file,
                    'os' => $os,
                    'browser' => $browser,
                    'time' => TIME);

                DB::update(DB_PREFIX . "user_localization", $user_localization_array, "user_localization_id='$user_localization_id'");
            } else {

                if (!($location = ip_to_location($ip, $assoc = true)))
                    $location = array('CountryCode' => '', 'CountryName' => '', 'RegionCode' => '', 'RegionName' => '', 'City' => '', 'ZipPostalCode' => '', 'Latitude' => '', 'Longitude' => '', 'TimezoneName' => '', 'Gmtooffset' => '');

                $user_localization_array = array('ip' => $ip,
                    'sid' => $sid,
                    'user_id' => $user_id,
                    'guest_id' => $guest_id,
                    'name' => $name,
                    'url' => $url,
                    'content_id' => $content_id,
                    'file' => $file,
                    'os' => $os,
                    'browser' => $browser,
                    'time' => TIME,
                    'time_first_click' => TIME,
                    'country_code' => $location['CountryCode'],
                    'country_name' => $location['CountryName'],
                    'region_code' => $location['RegionCode'],
                    'region_name' => $location['RegionName'],
                    'city_name' => $location['City'],
                    'zip' => $location['ZipPostalCode'],
                    'latitude' => $location['Latitude'],
                    'longitude' => $location['Longitude'],
                    'timezone_name' => $location['TimezoneName'],
                    'gmt_offset' => $location['Gmtooffset']
                );

                DB::insert(DB_PREFIX . "user_localization", $user_localization_array);
                $user_localization_id = DB::get_last_id();
            }

            $_SESSION['user_localization'] = array('user_localization_id' => $user_localization_id, 'content_id' => $content_id, 'guest_id' => $guest_id, 'name' => $name, 'time' => TIME, 'file' => $file, 'user_id' => $user_id, 'os' => $os, 'browser' => $browser);
        }

        /**
        * Refresh all the user info
        */
        static function refresh_localization() {

            if (isset($_SESSION['user_localization'])) {
                DB::query('UPDATE ' . DB_PREFIX . 'user_localization SET time=? WHERE user_localization_id=?', array(TIME, static::get_user_localization_id()));
                $_SESSION['user_localization']['time'] = TIME;
            }
        }

        /**
        * Refresh all the user info
        */
        static function get_user_localization_id() {
            if (isset($_SESSION['user_localization']))
                return $_SESSION['user_localization']['user_localization_id'];
        }

        /**
        * Get the userWhereIs info
        */
        static function get_user_localization($user_localization_id = NULL, $online_time = USER_ONLINE_TIME) {

            if (!$user_localization_id)
                $user_localization_id = static::get_user_localization_id();

            return DB::get_row("SELECT u.*, '' AS password, '', '' AS salt, l.*
                                FROM " . DB_PREFIX . "user_localization l
                                LEFT JOIN " . DB_PREFIX . "user u ON l.user_id = u.user_id
                                WHERE ( :time - time ) < :online_time
                                AND l.user_localization_id = :user_localization_id", 
                                array(":time"=>TIME, ":online_time"=>$online_time, ":user_localization_id"=>$user_localization_id)
            );
        }

        /**
        * Get the userWhereIs info
        */
        static function get_user_localization_by_user_id($user_id, $online_time = USER_ONLINE_TIME) {

            return DB::get_row("SELECT u.*, '' AS password, '', '' AS salt, l.*
                                FROM " . DB_PREFIX . "user_localization l
                                LEFT JOIN " . DB_PREFIX . "user u ON l.user_id = u.user_id
                                WHERE ( :time - time ) < :online_time
                                AND l.user_id = :user_id", 
                                array(":time"=>TIME, ":online_time"=>$online_time, ":user_id"=>$user_id)
            );
        }

        /**
        * Get the list of all user online
        */
        static function get_user_localization_list($content_id = NULL, $yourself = true, $online_time = USER_ONLINE_TIME) {

            $prepared = array(":time"=>TIME, ":online_time"=>$online_time);

            if ($content_id)
                $prepared[":content_id"] = $content_id;
            if ($yourself)
                $prepared[":sid"] = session_id();

            return DB::get_all("SELECT u.*, l.*, IF ( u.user_id > 0, u.name, l.name ) AS name
                               FROM " . DB_PREFIX . "user_localization l
                               LEFT JOIN " . DB_PREFIX . "user u ON l.user_id = u.user_id
                               WHERE ( :time - time ) < :online_time" .
                               ( $content_id != NULL ? "AND l.content_id = :content_id" : NULL ) .
                               ($yourself ? " AND l.sid != :sid" : NULL ), 
                               $prepared
            );
        }

        /**
        * Delete the user where is info
        */
        static function logout_localization() {
            DB::query("DELETE FROM " . DB_PREFIX . "user_localization WHERE user_localization_id= ?", array(static::get_user_localization_id()));
            unset($_SESSION['user_localization']);
        }

    }

    // -- end