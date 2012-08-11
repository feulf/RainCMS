<?php

    /**
    *  RainFramework
    *  -------------
    *  Realized by Federico Ulfo & maintained by the Rain Team
    *  Distributed under MIT license http://www.opensource.org/licenses/mit-license.php
    */

    /**
    * Group
    */
    class Group {

        static function get_group_list() {
            return DB::get_all("SELECT * FROM " . DB_PREFIX . "usergroup ORDER BY name", null, "group_id");
        }

        static function get_group($group_id) {
            return DB::get_row("SELECT * FROM " . DB_PREFIX . "usergroup WHERE group_id=?", array($group_id));
        }

        static function get_user_in_group($group_id, $order_by = "name", $order = "asc", $limit = 0) {
            return DB::get_all("SELECT *
                                FROM " . DB_PREFIX . "usergroup_user g
                                INNER JOIN " . DB_PREFIX . "user u ON g.user_id = u.user_id
                                WHERE g.group_id = ? ORDER BY $order_by $order" . ($limit > 0 ? " LIMIT $limit" : null ), array($group_id)
            );
        }

    }

    // end