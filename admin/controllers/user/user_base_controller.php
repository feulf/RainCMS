<?php

    class UserBaseController extends Controller {

        //show user list
        function account_list($group_id = null, $user_id = null, $usearch = null, $limit = null, $order_by = null, $order = null) {

            switch ($group_id) {
                case 'online':
                    $account_list = User::get_user_localization_list();
                    break;

                default:

                    $where = $usearch ? "WHERE (" . DB_PREFIX . "user.name LIKE '%$usearch%' OR " . DB_PREFIX . "user.firstname LIKE '%$usearch%' OR " . DB_PREFIX . "user.lastname LIKE '%$usearch%' )" : null;

                    if ($group_id == 'is_registered')
                        $where = $where ? $where . " AND is_registered=1" : " WHERE is_registered=1";
                    elseif (is_numeric($group_id) && $group_id > 0)
                        $where = $where ? $where . " AND group_id='$group_id'" : " WHERE group_id='$group_id'";

                    $limit = null;
                    $order_by = $order_by ? "ORDER BY " . $order_by : "ORDER BY " . DB_PREFIX . "user.name";
                    $order = $order == "DESC" ? "DESC" : "ASC";

                    $account_list = db::get_all("SELECT " . DB_PREFIX . "user_localization.* , " . DB_PREFIX . "user.*, IF (" . DB_PREFIX . "user.user_id > 0, " . DB_PREFIX . "user.name, " . DB_PREFIX . "user_localization.name ) AS name
                                                FROM " . DB_PREFIX . "user
                                                LEFT JOIN " . DB_PREFIX . "usergroup_user ON " . DB_PREFIX . "usergroup_user.user_id = " . DB_PREFIX . "user.user_id
                                                LEFT JOIN " . DB_PREFIX . "user_localization ON ( " . DB_PREFIX . "user_localization.user_id = " . DB_PREFIX . "user.user_id AND ( " . TIME . " - " . DB_PREFIX . "user_localization.time ) < " . USER_ONLINE_TIME . " )
                                                $where
                                                GROUP BY " . DB_PREFIX . "user.user_id
                                                $order_by $order
                                                $limit
                                                ");
            }


            $tpl = new View;
            $tpl->assign("account_list", $account_list);
            $tpl->assign("usearch", $usearch);
            $tpl->assign("group_id", $group_id);
            $tpl->draw("user/account.list");
        }

        function account() {

            $group_id = get('group_id');
            $user_id = get('user_id');
            $user_localization_id = get('user_localization_id');

            if ($user_id == 0 && $user_localization_id > 0) {
                if ($user_localization = User::get_user_localization($user_localization_id)) {
                    $tpl = new View;
                    $tpl->assign("user_localization", $user_localization);
                    $tpl->assign("group_id", $group_id);
                    $tpl->assign("user", null);
                    return $tpl->draw('user/account.view', true);
                }
                else
                    return "<script>openGroup('online');</script>";
            }
            elseif (is_numeric($user_id) && $user_id > 0) {

                if ($user = User::get_user($user_id)) {

                    $user_localization = User::get_user_localization_by_user_id($user_id);

                    global $user_level;
                    $user['level'] = get_msg($user_level[$user['status']]);

                    $permission_list = db::get_all("SELECT p.*, c.title
                                                                FROM " . DB_PREFIX . "content_permission p
                                                                INNER JOIN " . DB_PREFIX . "user u ON p.user_id = u.user_id
                                                                LEFT JOIN " . DB_PREFIX . "content c ON p.permission_id = c.content_id
                                                                WHERE u.user_id = ?
                                                                GROUP BY c.content_id", array($user_id)
                    );

                    $tpl = new View;
                    $tpl->assign("user", $user);
                    $tpl->assign("user_localization", $user_localization);
                    $tpl->assign("permission_list", $permission_list);
                    $tpl->assign("group_id", $group_id);
                    return $tpl->draw('user/account.view', true);
                }
                else
                    return '<div class="padding_10">' . _USER_NOT_FOUND_ . '</div>';
            }
            else {

                $user_list_id = $user_id;
                $where = null;
                $account_list_split = explode(",", $user_id);

                if (count($account_list_split) > 1) {

                    foreach ($account_list_split as $i => $user_id) {
                        $account_list[$user_id] = User::get_user($user_id);
                        $where .= $where ? " OR user_id=$user_id" : "user_id=$user_id";
                    }
                    $tpl = new View;

                    $nuser = count($account_list_split);
                    $group_list = db::get_all("SELECT g.group_id, g.name, count( gu.user_id ) AS nuser
                                                FROM " . DB_PREFIX . "usergroup g
                                                INNER JOIN " . DB_PREFIX . "usergroup_user gu ON g.group_id = gu.group_id
                                                WHERE ( $where )
                                                GROUP BY g.group_id
                                                HAVING nuser = ?", array($nuser)
                    );


                    $tpl->assign("user_list_id", $user_list_id);
                    $tpl->assign("account_list", $account_list);
                    $tpl->assign("group_list", $group_list);
                    $tpl->assign("group_id", $group_id);
                    return $tpl->draw("user/account_list.edit", true);
                }
            }
        }

        function group_view($group_id = null) {

            if ($group_id != 'all' && $group_id != 'registered' && $group_id != 'online') {
                $permission_list = db::get_all("SELECT p.*, c.title
                                                FROM " . DB_PREFIX . "content_permission p
                                                INNER JOIN " . DB_PREFIX . "content c ON p.permission_id = c.content_id
                                                WHERE p.group_id=?
                                                GROUP BY c.content_id", array($group_id)
                );
            }
            else
                $permission_list = "";

            $tpl = new View;
            $this->load_library("Group");
            $tpl->assign(Group::get_group($group_id));
            $tpl->assign("group_id", $group_id);
            $tpl->assign("permission_list", $permission_list);
            return $tpl->draw('user/group.view', true);
        }

        function group_edit($group_id = null) {

            if ($group_id != 'all' && $group_id != 'registered' && $group_id != 'online') {
                $content_list = Content::get_childs(0);
                $permission_list = db::get_all("SELECT p.*, c.title
                                                                FROM " . DB_PREFIX . "content_permission p
                                                                INNER JOIN " . DB_PREFIX . "content c ON p.permission_id = c.content_id
                                                                WHERE p.group_id = ?
                                                                GROUP BY c.content_id", array($group_id)
                );
            }
            else
                $permission_list = $content_list = "";

            $this->load_library("Group");

            $tpl = new View;
            $tpl->assign(Group::get_group($group_id));
            $tpl->assign("group_id", $group_id);
            $tpl->assign("content_list", $content_list);
            $tpl->assign("permission_list", $permission_list);
            return $tpl->draw('user/group.edit', true);
        }

        function account_edit($user_id, $group_id = null) {

            add_script("jquery.validate.min.js", JQUERY_DIR, JQUERY_URL);
            add_script("jquery.form.js", JQUERY_DIR, JQUERY_URL);

            $user_localization = $user = array();
            if (!($user_id > 0))
                $user = array('user_id' => '', 'name' => '', 'email' => '', 'lang_id' => '', 'sex' => '', 'birth_date' => '', 'firstname' => '', 'lastname' => '', 'company' => '', 'address' => '', 'zip' => '', 'city' => '', 'prov' => '', 'country' => '', 'state' => '', 'tel' => '', 'mobile' => '', 'web' => '', 'mailing_list' => '', 'is_registered' => 0, 'status' => 0);
            elseif ($user_id > 0) {
                $user = User::get_user($user_id);
                $user_localization = User::get_user_localization();
            }

            global $user_level;
            $user['level'] = get_msg($user_level[$user['status']]);


            if (isset($user["is_registered"]) && $user['is_registered'])
                $permission_list = db::get_all("SELECT p.*, c.title
                                                FROM " . DB_PREFIX . "content_permission p
                                                INNER JOIN " . DB_PREFIX . "user u ON p.user_id = u.user_id
                                                LEFT JOIN " . DB_PREFIX . "content c ON p.permission_id = c.content_id
                                                WHERE u.user_id = ?
                                                GROUP BY c.content_id
                                                ORDER BY permission_id ASC", array($user_id)
                );
            else
                $permission_list = null;

            $tpl = new View;
            $tpl->assign($user);
            $tpl->assign($user_localization);
            $tpl->assign("user_level", Array(USER_SUPER_ADMIN => get_msg("USER_SUPER_ADMIN"), USER_ADMIN => get_msg("USER_ADMIN"), USER_REGISTERED => get_msg("USER_REGISTERED"), USER_UNREGISTERED => get_msg("USER_UNREGISTERED"), USER_BANNED => get_msg("USER_BANNED"), USER_REFUSED => get_msg("USER_REFUSED")));
            $tpl->assign("content_list", Content::get_childs(0));
            $tpl->assign("permission_list", $permission_list);
            $tpl->assign("group_id", $group_id);
            $tpl->draw("user/account.edit");
        }

        function account_save_pw($user_id) {


            $password = get_post('user_pw');

            if (( $user_id > 0 ) && ( $user = User::get_user($user_id) )) {
                $salt = rand(0, 99999);
                $md5_password = md5($salt . $password);
                db::query("UPDATE " . DB_PREFIX . "user SET password=?, salt=? WHERE user_id=?", array($md5_password, $salt, $user_id));
                echo get_msg('user_cp_password_edit_success');
            }
            else
                echo get_msg('user_cp_password_edit_error');
        }

        function account_group($user_id = null) {

            $this->load_library("Group");
            $group_list = Group::get_group_list();

            if (is_numeric($user_id) && $user_id > 0) {
                if ($in_group_list = db::get_all($q = "SELECT u.group_id, u.name
                                                                    FROM " . DB_PREFIX . "usergroup u
                                                                    JOIN " . DB_PREFIX . "usergroup_user gu ON ( u.group_id = gu.group_id AND user_id = ? )
                                                                    GROUP BY u.group_id
                                                                    ORDER BY u.name", array($user_id), "group_id"
                ))
                    $group_list = array_diff_assoc($group_list, $in_group_list);
            }
            else {

                $user_list_id = $user_id;
                $where = null;
                $user_list_split = explode(",", $user_id);
                foreach ($user_list_split as $i => $user_id) {
                    $user_list[$user_id] = User::get_user($user_id);
                    $where .= $where ? " OR gu.user_id=$user_id" : "gu.user_id=$user_id";
                }
                $nuser = count($user_list_split);

                if ($in_group_list = db::get_all($query = "SELECT u.group_id, name, count( gu.user_id ) AS nuser
                                                                            FROM " . DB_PREFIX . "usergroup u
                                                                            INNER JOIN " . DB_PREFIX . "usergroup_user gu ON u.group_id = gu.group_id
                                                                            WHERE ( $where )
                                                                            GROUP BY group_id
                                                                            HAVING nuser = ?"
                                , array($nuser)
                                , "group_id"))
                    $group_list = array_diff_assoc($group_list, $in_group_list);
            }

            echo json_encode(array('group_list' => $group_list, 'in_group_list' => $in_group_list));
        }

    }

    // -- end