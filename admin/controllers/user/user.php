<?php

    //init language
    load_lang('user');
    load_lang('admin.user');

    // add javascript
    add_script("jquery.validate.min.js", JQUERY_DIR, JQUERY_URL);
    add_script("jquery.form.min.js", JQUERY_DIR, JQUERY_URL);
    add_script('user.js', ADMIN_JAVASCRIPT_DIR, ADMIN_JAVASCRIPT_URL);

    // add style
    add_style('user.css', ADMIN_VIEWS_CSS_DIR, ADMIN_VIEWS_CSS_URL);

    //include base controller
    require_once "user_base_controller.php";

    class UserController extends UserBaseController {

        // init the address book
        function index($group_id = null, $user_id = null, $usearch = null) {

            $this->load_library("Group");

            $user_online = User::get_user_localization_list();
            $nuser_online = count($user_online);

            $tpl = new View;
            $tpl->assign("group_list", Group::get_group_list());
            $tpl->assign("nuser_online", $nuser_online);
            $tpl->draw("user/address_book");
        }

    }

    // -- end