<?php

    //init language
    load_lang('user');
    load_lang('admin.user');

    // add javascript
    add_script("jquery.min.js", JQUERY_DIR, JQUERY_URL);
    add_script("user.js", ADMIN_JAVASCRIPT_DIR, ADMIN_JAVASCRIPT_URL);

    // add style
    add_style("user.css", ADMIN_CSS_DIR, ADMIN_CSS_URL);


    //include base controller
    require_once "user_base_controller.php";

    class UserAjaxController extends UserBaseController {

        // init theme and view class
        function filter_before() {
            $this->loader->init_theme();
        }

        function account() {
            echo parent::account();
        }

        function account_save() {

            $user_id = post('user_id');
            $name = post('name');
            $email = post('email');
            $lang_id = post('lang_id');
            $sex = post('sex');
            $birth_date = post('birth_date');
            $firstname = post('firstname');
            $lastname = post('lastname');
            $address = post('address');
            $zip = post('zip');
            $city = post('city');
            $prov = post('prov');
            $state = post('state');
            $country = post('country');
            $tel = post('tel');
            $mobile = post('mobile');
            $web = post('web');
            $company = post('company');
            $mailing_list = post('mailing_list');
            $group = post('group');
            $msg = post('msg');
            $status = post('status');
            $permission_list = post('permission');

            $is_registered = ( $status == USER_BANNED || $status == USER_REGISTERED || $status == USER_ADMIN || $status == USER_SUPER_ADMIN ) ? 1 : 0;
            if (( $user_id > 0 ) && ( $user = user::get_user($user_id) )) {

                $user_level = Array(USER_SUPER_ADMIN => get_msg('user,super_admin'), USER_ADMIN => get_msg('user,admin'), USER_REGISTERED => get_msg('user,registered'), USER_UNREGISTERED => get_msg('user,unregistered'), USER_BANNED => get_msg('user,banned'), USER_REFUSED => get_msg('user,refused'));
                $status = key_exists($status, $user_level) ? $status : USER_CONTACT;
                DB::update(DB_PREFIX."user", array("name" => $name,
                                                   "email" => $email,
                                                   "lang_id" => $lang_id,
                                                   "sex" => $sex,
                                                   "birth_date" => $birth_date,
                                                   "firstname" => $firstname,
                                                   "lastname" => $lastname,
                                                   "address" => $address,
                                                   "zip" => $zip,
                                                   "city" => $city,
                                                   "prov" => $prov,
                                                   "country" => $country,
                                                   "state" => $state,
                                                   "tel" => $tel,
                                                   "mobile" => $mobile,
                                                   "web" => $web,
                                                   "company" => $company,
                                                   "mailing_list" => $mailing_list,
                                                   "status" => $status,
                                                   "is_registered" => $is_registered ), 
                                                   "user_id='$user_id'" );

            } else {
                
                DB::insert(DB_PREFIX."user", array("name" => $name,
                                                   "email" => $email,
                                                   "lang_id" => $lang_id,
                                                   "sex" => $sex,
                                                   "birth_date" => $birth_date,
                                                   "firstname" => $firstname,
                                                   "lastname" => $lastname,
                                                   "address" => $address,
                                                   "zip" => $zip,
                                                   "city" => $city,
                                                   "prov" => $prov,
                                                   "country" => $country,
                                                   "state" => $state,
                                                   "tel" => $tel,
                                                   "mobile" => $mobile,
                                                   "web" => $web,
                                                   "company" => $company,
                                                   "mailing_list" => $mailing_list,
                                                   "status" => $status,
                                                   "is_registered" => $is_registered ) ); 
                
                $user_id = db::get_last_id();
                $user = user::get_user($user_id);
            }

            if ($permission_list) {
                foreach ($permission_list as $i => $permission) {
                    $content_id = isset($permission['content_id']) ? $permission['content_id'] : 0;
                    $content_access = isset($permission['content_access']) ? 1 : 0;
                    $subcontent_access = isset($permission['subcontent_access']) ? 1 : 0;
                    $permission_id = isset($permission['permission_id']) ? $permission['permission_id'] : 0;

                    if (isset($permission['delete']))
                        db::query("DELETE FROM " . DB_PREFIX . "content_permission WHERE permission_id=?", array($permission_id));
                    elseif ($permission_id > 0){

                        db::query("UPDATE " . DB_PREFIX . "content_permission 
                                    SET content_id = :content_id,
                                        content_access = :content_access,
                                        subcontent_access = :subcontent_access
                                        WHERE permission_id = :permission_id", 
                                    array( ":content_id"=> $content_id, ":content_access"=>$content_access, ":subcontent_access"=>$subcontent_access, ":permission_id"=> $permission['permission_id'] )
                                );
                    }
                    else{

                        db::query("INSERT INTO " . DB_PREFIX . "content_permission 
                                    ( content_id, content_access, subcontent_access, user_id ) 
                                    VALUES ( :content_id, :content_access, :subcontent_access, :user_id )",
                                    array(":content_id"=>$content_id, ":content_access"=>$content_access, ":subcontent_access"=>$subcontent_access, ":user_id"=>$user_id) );
                    }
                }
            }

        }

        function group_save( $group_id ) {

            $name = post('name');
            $permission_list = post('permission');

            $this->load_library("group");
            if (( $group_id > 0 ) && ( $group = Group::get_group($group_id) )) {

                db::query("UPDATE " . DB_PREFIX . "usergroup SET name = :name WHERE group_id = :group_id", array(":name"=>$name, ":group_id"=>$group_id ) );

                if ( $permission_list ) {
                    foreach ($permission_list as $i => $permission) {
                        $content_id = isset($permission['content_id']) ? $permission['content_id'] : 0;
                        $content_access = isset($permission['content_access']) ? 1 : 0;
                        $subcontent_access = isset($permission['subcontent_access']) ? 1 : 0;
                        $permission_id = isset($permission['permission_id']) ? $permission['permission_id'] : 0;

                        if (isset($permission['delete'])){

                            db::query("DELETE FROM " . DB_PREFIX . "content_permission WHERE permission_id=?", array($permission_id) );

                        }
                        elseif ($permission_id > 0){

                            db::query("UPDATE " . DB_PREFIX . "content_permission 
                                       SET content_id = :content_id,
                                           content_access = :content_access,
                                           subcontent_access = :subcontent_access
                                           WHERE permission_id = :permission_id", 
                                       array( ":content_id"=> $content_id, ":content_access"=>$content_access, ":subcontent_access"=>$subcontent_access, ":permission_id"=> $permission['permission_id'] )
                                    );

                        }
                        else{

                            db::query("INSERT INTO " . DB_PREFIX . "content_permission 
                                       ( content_id, content_access, subcontent_access, group_id ) 
                                       VALUES ( :content_id, :content_access, :subcontent_access, :group_id )",
                                       array(":content_id"=>$content_id, ":content_access"=>$content_access, ":subcontent_access"=>$subcontent_access, ":group_id"=>$group_id) );

                        }
                    }
                }
            }
        }

        function group_new() {
            if ($group_name = get('group_name')) {
                db::query("INSERT INTO " . DB_PREFIX . "usergroup ( name ) VALUES ( ? )", array($group_name));
                echo $group_id = db::getLastId();
            }
        }

        function group_add_user() {

            $group_id = get("group_id");
            $user_id = get("user_id");

            if (is_numeric($user_id))
                db::query("INSERT IGNORE INTO " . DB_PREFIX . "usergroup_user (group_id,user_id) VALUES ( ?, ? );", array($group_id, $user_id));
            elseif ($user_list_id = explode(",", $user_id))
                foreach ($user_list_id as $i => $user_id)
                    if (is_numeric($user_id))
                        db::query("INSERT IGNORE INTO " . DB_PREFIX . "usergroup_user (group_id,user_id) VALUES ( ?, ? );", array($group_id, $user_id));
        }

        function group_del_user() {

            $group_id = get_post("group_id");
            $user_id = get_post("user_id");

            if (is_numeric($user_id))
                db::query("DELETE FROM " . DB_PREFIX . "usergroup_user WHERE group_id=? AND user_id=?", array($group_id, $user_id));
            elseif ($user_list_id = explode(",", $user_id))
                foreach ($user_list_id as $i => $user_id)
                    if (is_numeric($user_id))
                        db::query("DELETE FROM " . DB_PREFIX . "usergroup_user WHERE group_id=? AND user_id=?", array($group_id, $user_id));
        }

        function group_view( $group_id = null ){

            if( $group_id != 'all' && $group_id != 'registered' && $group_id != 'online' ){
                $permission_list = db::get_all( "SELECT ".DB_PREFIX."content_permission.*, ".DB_PREFIX."content.title  
                                                 FROM ".DB_PREFIX."content_permission
                                                 INNER JOIN ".DB_PREFIX."content ON ".DB_PREFIX."content_permission.content_id = ".DB_PREFIX."content.content_id
                                                 WHERE ".DB_PREFIX."content_permission.group_id = $group_id
                                                 GROUP BY ".DB_PREFIX."content.content_id
                                                ");
            }
            else
                $permission_list = "";


            // load the Group library
            $this->load_library("Group");

            $group = Group::get_group( $group_id );
            $content_list = Content::get_childs( 0 );
            
            $tpl = new View();
            $tpl->assign( $group );
            $tpl->assign( "content_list", $content_list );
            $tpl->assign( "permission_list", $permission_list );
            $tpl->draw( 'user/group.view' );
            
        }
        
        function group_edit( $group_id = null ){
            if( $group_id != 'all' && $group_id != 'registered' && $group_id != 'online' ){
                $content_list = Content::get_childs( 0 );
                $permission_list = db::get_all( "SELECT ".DB_PREFIX."content_permission.*, ".DB_PREFIX."content.title 
                                                        FROM ".DB_PREFIX."content_permission
                                                        INNER JOIN ".DB_PREFIX."content ON ".DB_PREFIX."content_permission.content_id = ".DB_PREFIX."content.content_id
                                                        WHERE ".DB_PREFIX."content_permission.group_id = $group_id
                                                        GROUP BY ".DB_PREFIX."content.content_id
                                                        ");
            }
            else
                $permission_list = $content_list = "";

            // load the Group library
            $this->load_library("Group");

            $tpl = new View();
            $tpl->assign( Group::get_group( $group_id ) );
            $tpl->assign( "group_id", $group_id);
            $tpl->assign( "content_list", $content_list );
            $tpl->assign( "permission_list", $permission_list );
            $tpl->draw( 'user/group.edit' );
        }


        function account_delete() {

            $user_id = post("user_id");
            if (is_numeric($user_id)) {
                db::query("DELETE FROM " . DB_PREFIX . "user WHERE user_id=?", array($user_id));
                db::query("DELETE FROM " . DB_PREFIX . "usergroup_user WHERE user_id=?", array($user_id));
            } elseif ($user_list_id = explode(",", $user_id)){
                foreach ($user_list_id as $i => $user_id){
                    if (is_numeric($user_id)) {
                        db::query("DELETE FROM " . DB_PREFIX . "user WHERE user_id=?", array($user_id));
                        db::query("DELETE FROM " . DB_PREFIX . "usergroup_user WHERE user_id=?", array($user_id));
                    }
                }
            }
        }

    }

    // -- end