<?php

    // USER

        function address_book( $group_id , $user_id = null, $usearch = null ){
            $nuser_online = db::get_field( "n", "SELECT count(*) AS n FROM ".DB_PREFIX."user_where_is WHERE ( ".TIME." - time ) < " . USER_ONLINE_TIME );

            $tpl = new RainTPL();
            $tpl->assign( "group_list", getGroupList() );
            $tpl->assign( "user_list", user_list( $group_id, $user_id, $usearch ) );
            $tpl->assign( "nuser_online", $nuser_online );
            return $tpl->draw( "user/user", true );
        }    



        //show user list
        function user_list( $group_id, $user_id, $usearch = null, $limit = null, $order_by = null, $order = null ){

            if( $usearch )
                $where = "WHERE (".DB_PREFIX."user.name LIKE '%$usearch%' OR ".DB_PREFIX."user.firstname LIKE '%$usearch%' OR ".DB_PREFIX."user.lastname LIKE '%$usearch%' )";
            else
                $where = "";

            //if( $limit > 0 )
                $limit = "LIMIT 50";

            // select only user in a group
            if( is_numeric( $group_id ) && $group_id > 0)
                $where = $where ? $where . " AND group_id='$group_id'" : " WHERE group_id='$group_id'";
            elseif( $group_id == 'registered' )
                $where = $where ? $where . " AND is_registered=1" : " WHERE is_registered=1";

            if( !$order_by )
                $order_by = "ORDER BY " . DB_PREFIX."user.name";
            else
                $order_by ="ORDER BY " . $order_by;

            $order = $order == "DESC" ? "DESC" : "ASC";




            $user_list = null;
            switch( $group_id ){
                case 'online':
                    $user_list = getUserWhereIsList();
                break;
                default:
                    $user_list = db::get_all( "SELECT ".DB_PREFIX."user_where_is.* , ".DB_PREFIX."user.*, IF (".DB_PREFIX."user.user_id > 0, ".DB_PREFIX."user.name, ".DB_PREFIX."user_where_is.name ) AS name
                            FROM ".DB_PREFIX."user
                            LEFT JOIN ".DB_PREFIX."usergroup_user ON ".DB_PREFIX."usergroup_user.user_id = ".DB_PREFIX."user.user_id
                            LEFT JOIN ".DB_PREFIX."user_where_is ON ( ".DB_PREFIX."user_where_is.user_id = ".DB_PREFIX."user.user_id AND ( ".TIME." - ".DB_PREFIX."user_where_is.time ) < ".USER_ONLINE_TIME." )
                            $where
                            GROUP BY ".DB_PREFIX."user.user_id
                            $order_by $order
                            $limit
                            ");
            }        

            $tpl = new RainTPL();
            $tpl->assign( "user_list", $user_list );
            $tpl->assign( "usearch", $usearch );
            $tpl->assign( "group_id", $group_id );
            return $tpl->draw( "user/user.list", $return_string = true );
        }


        function user_view( $group_id, $user_id, $user_where_is_id ){
            if( $user_id == 0 && $user_where_is_id > 0 ){
                if( $user_where = getUserWhereIsUser($user_where_is_id) ){
                    $tpl = new RainTPL();
                    $tpl->assign( $user_where );
                    $tpl->assign( "group_id", $group_id );
                    return $tpl->draw( 'user/user.view', true );            
                }
                else
                    return "<script>openGroup('online');</script>";
            }
            elseif( is_numeric( $user_id ) && $user_id > 0 ){
                if( $user = db::get_row( "SELECT ".DB_PREFIX."user_where_is.*, ".DB_PREFIX."user.*
                                        FROM ".DB_PREFIX."user_where_is
                                        RIGHT JOIN ".DB_PREFIX."user ON ( ".DB_PREFIX."user_where_is.user_id = ".DB_PREFIX."user.user_id AND ( ".TIME." - time ) < ".USER_ONLINE_TIME." )
                                        WHERE ".DB_PREFIX."user.user_id = $user_id
                                        LIMIT 1") ){

                    global $user_level;
                    $user['level'] = constant( "_" . $user_level[ $user['status'] ] . "_" );

                    $permission_list = db::get_all( "SELECT ".DB_PREFIX."content_permission.*, ".DB_PREFIX."content.title  
                                                            FROM ".DB_PREFIX."content_permission
                                                            INNER JOIN ".DB_PREFIX."user ON ".DB_PREFIX."content_permission.user_id = ".DB_PREFIX."user.user_id
                                                            LEFT JOIN ".DB_PREFIX."content ON ".DB_PREFIX."content_permission.content_id = ".DB_PREFIX."content.content_id
                                                            WHERE ".DB_PREFIX."user.user_id = $user_id
                                                            GROUP BY ".DB_PREFIX."content.content_id
                                                            ");

                    $tpl = new RainTPL();
                    $tpl->assign( $user );
                    $tpl->assign( "permission_list", $permission_list );
                    $tpl->assign( "group_id", $group_id );
                    return $tpl->draw( 'user/user.view', true );            
                }
                else
                    return '<div class="padding_10">' . _USER_NOT_FOUND_ . '</div>';
            }
            else{
                $user_list_id = $user_id;
                $where = null;
                $user_list_split = explode( ",", $user_id );

                if( count($user_list_split) > 1 ){

                    foreach( $user_list_split as $i => $user_id ){
                        $user_list[$user_id] = getUser( $user_id );
                        $where .= $where ? " OR user_id=$user_id" : "user_id=$user_id";
                    }
                    $tpl = new RainTPL();

                    $nuser = count( $user_list_split );
                    $query = "SELECT ".DB_PREFIX."usergroup.group_id, ".DB_PREFIX."usergroup.name, count( ".DB_PREFIX."usergroup_user.user_id ) AS nuser
                            FROM ".DB_PREFIX."usergroup
                            INNER JOIN ".DB_PREFIX."usergroup_user ON ".DB_PREFIX."usergroup.group_id = ".DB_PREFIX."usergroup_user.group_id
                            WHERE ( $where )
                            GROUP BY group_id
                            HAVING nuser = $nuser";

                    $group_list = db::get_all( $query );

                    $tpl->assign( "user_list_id", $user_list_id );
                    $tpl->assign( "user_list", $user_list );
                    $tpl->assign( "group_list", $group_list );
                    $tpl->assign( "group_id", $group_id );
                    return $tpl->draw( "user/user_list.edit", true );
                }
            }
        }



        function user_edit( $group_id, $user_id ){

            if( $user_id == 0 or $user_id == '' )
                $user = array( 'user_id' => '', 'name' => '', 'email' => '', 'lang_id' => '', 'sex' => '', 'birth_date' => '', 'firstname' => '', 'lastname' => '', 'company' => '', 'address' => '', 'zip' => '', 'city' => '', 'prov' => '', 'country' => '', 'state' => '', 'tel' => '', 'mobile' => '', 'web' => '', 'mailing_list' => '', 'is_registered' => 0, 'status' => 0 );
            elseif( $user_id > 0 )
                $user = getUser( $user_id );
            elseif( $user_id < 0 ){
                $user = getUserWhereIsUser( $user_id );
                exit;
            }


            if( isset($user["is_registered"]) && $user['is_registered'] ){
                $permission_list = db::get_all( "SELECT ".DB_PREFIX."content_permission.*, ".DB_PREFIX."content.title  
                                                    FROM ".DB_PREFIX."content_permission
                                                    INNER JOIN ".DB_PREFIX."user ON ".DB_PREFIX."content_permission.user_id = ".DB_PREFIX."user.user_id
                                                    LEFT JOIN ".DB_PREFIX."content ON ".DB_PREFIX."content_permission.content_id = ".DB_PREFIX."content.content_id
                                                    WHERE ".DB_PREFIX."user.user_id = $user_id
                                                    GROUP BY ".DB_PREFIX."content.content_id
                                                    ORDER BY permission_id ASC");
            }
            else
                $permission_list = null;


            $tpl = new RainTPL();
            $tpl->assign( $user );
            $tpl->assign( "user_level", Array( USER_SUPER_ADMIN => _USER_SUPER_ADMIN_, USER_ADMIN => _USER_ADMIN_, USER_REGISTERED => _USER_REGISTERED_, USER_UNREGISTERED => _USER_UNREGISTERED_,  USER_BANNED => _USER_BANNED_, USER_REFUSED => _USER_REFUSED_ ) );
            $tpl->assign( "content_list", get_contentList(0) );
            $tpl->assign( "permission_list", $permission_list );
            $tpl->assign( "group_id", $group_id );
            return $tpl->draw( 'user/user.edit', true );


        }


        function user_save( $user_id, $name, $email, $lang_id, $sex, $birth_date, $firstname, $lastname, $address, $zip, $city, $prov, $state, $country, $tel, $mobile, $web, $company, $mailing_list, $group, $msg, $status, $permission_list ){

            $birth_date = dateToTime($birth_date);

            $is_registered = ( $status == USER_BANNED || $status == USER_REGISTERED || $status == USER_ADMIN || $status == USER_SUPER_ADMIN ) ? 1 : 0;
            if( ( $user_id > 0 ) && ( $user = getUser( $user_id ) ) ){

                    $user_level = Array( USER_SUPER_ADMIN => getMsg('user,super_admin'), USER_ADMIN => getMsg('user,admin'), USER_REGISTERED => getMsg('user,registered'), USER_UNREGISTERED => getMsg('user,unregistered'),  USER_BANNED => getMsg('user,banned'), USER_REFUSED => getMsg('user,refused') );
                    $status = key_exists( $status, $user_level ) ? $status : USER_CONTACT;
                    db::query( "UPDATE ".DB_PREFIX."user SET 
                                                    name = '$name',
                                                    email = '$email',
                                                    lang_id = '$lang_id',
                                                    sex = '$sex',
                                                    birth_date = '$birth_date',
                                                    firstname = '$firstname',
                                                    lastname = '$lastname',
                                                    address = '$address',
                                                    zip = '$zip',
                                                    city = '$city',
                                                    prov = '$prov',
                                                    country = '$country',
                                                    state = '$state',
                                                    tel = '$tel',
                                                    mobile = '$mobile',                        
                                                    web = '$web',
                                                    company = '$company',
                                                    mailing_list = '$mailing_list',
                                                    status = '$status',
                                                    is_registered = '$is_registered'
                                    WHERE user_id = '$user_id'" );
            }
            else{
                db::query( "INSERT INTO ".DB_PREFIX."user ( `name` ,`email` ,`lang_id` ,`sex` ,`birth_date` ,`firstname` ,`lastname` ,`company` ,`address` ,`zip` ,`city` ,`prov` ,`country` ,`state` ,`tel` ,`mobile` ,`web` ,`mailing_list` ,`is_registered` )
                                                            VALUES ( '$name', '$email', '$lang_id', '$sex' , '$birth_date', '$firstname', '$lastname', '$company', '$address', '$zip', '$city', '$prov', '$country', '$state', '$tel', '$m', '$web', '$mailing_list' ,'$is_registered' ); ");
                $user_id = db::getInsertedId();
                $user = getUser( $user_id );
            }

            if( $status == USER_ADMIN ){

                if( $permission_list ){
                    foreach( $permission_list as $i => $permission ){
                        $id = isset( $permission['id'] ) ? $permission['id'] : 0;
                        $content_access = isset( $permission['content_access'] ) ? 1 : 0;
                        $subcontent_access = isset( $permission['subcontent_access'] ) ? 1 : 0;
                        $permission_id = isset( $permission['permission_id'] ) ? $permission['permission_id'] : 0;

                        if( isset( $permission['delete'] ) )
                            db::query( "DELETE FROM ".DB_PREFIX."content_permission WHERE permission_id='$permission_id'" );
                        elseif(  $permission_id > 0 )
                            db::query( "UPDATE ".DB_PREFIX."content_permission SET 
                                                                                    id = $id,
                                                                                    content_access = $content_access,
                                                                                    subcontent_access = $subcontent_access
                                        WHERE permission_id = {$permission['permission_id']}");
                        else
                            db::query( "INSERT INTO ".DB_PREFIX."content_permission ( id, content_access, subcontent_access, user_id ) VALUES
                                                                                    ( '$id', '$content_access', '$subcontent_access', $user_id )" );
                    }
                }
            }

        }

        function user_save_pw( $user_id, $password ){
            if( $user_id > 0 && $user = getUser($user_id) ){
                $salt=rand( 0, 99999 );
                $md5_password = md5( $salt . $password );
                $login = $user['email'];
                db::query( "UPDATE ".DB_PREFIX."user SET password='$md5_password', salt='$salt' WHERE user_id='$user_id'" );
                return _USER_CP_PASSWORD_EDIT_SUCCESS_;
            }
            else 
                return _USER_CP_PASSWORD_EDIT_ERROR_;
        }


        function user_delete( $user_id ){

            if( is_numeric( $user_id ) ){
                db::query( "DELETE FROM ".DB_PREFIX."user WHERE user_id='$user_id'" );
                db::query( "DELETE FROM ".DB_PREFIX."usergroup_user WHERE user_id='$user_id'" );
            }
            elseif( $user_list_id = explode( ",", $user_id ) )
                foreach( $user_list_id as $i => $user_id )
                    if( is_numeric( $user_id ) ){
                        db::query( "DELETE FROM ".DB_PREFIX."user WHERE user_id='$user_id'" );
                        db::query( "DELETE FROM ".DB_PREFIX."usergroup_user WHERE user_id='$user_id'" );
                    }
        }



        function account_ImportPage( $group_id ){
            $tpl = new RainTPL();
            $tpl->assign( "group_list", getGroupList() );
            $tpl->assign( "group_id", $group_id );
            $tpl->draw( "user/account.import");
        }


        function account_import( $group_id, $name ){

            $field = array( 'email', 'name', 'firstname' , 'lastname' , 'company' , 'address' , 'zip' , 'city' , 'prov' , 'country' , 'state' , 'tel' , 'mobile' , 'web', 'lang_id', 'sex', 'birth_date' );

            if( $_FILES ){
                $csv = file_get_contents( $_FILES['file']['tmp_name'] );
                $csv = str_replace( "\n", "\r", $csv );
                $csv = explode( "\r", $csv );

                if( $name && $group_id == 'new' ){
                    $group_id = group_new( $name );    
                    $new=true;
                }
                else{
                    $group = getGroup( $group_id );
                    $name = $group['name'];
                }

                for( $i=0; $i<count($csv);$i++){
                    $data = explode( "\t", $csv[$i] );
                    foreach( $data as $key => $value ){
                        $data[$key] = str_replace( "\\", "\\\\", $value );
                        $data[$key] = str_replace( "'", "\\'", $value );
                    }

                    $field_name = $field_value = "";
                    foreach( $data as $key => $value ){
                        $field_name .= $field_name ? "," . "`{$field[$key]}`" : "`{$field[$key]}`";
                        $field_value .= $field_value ? "," . "'$value'" : "'$value'";
                    }

                    db::query( "INSERT INTO ".DB_PREFIX."user ( $field_name ) VALUES ( $field_value )" );
                    $user_id = db::getInsertedId();
                    if( $group_id > 0 )
                        db::query( "INSERT INTO ".DB_PREFIX."usergroup_user (group_id, user_id    ) VALUES ( $group_id, $user_id )" );
                }


                if( isset( $new ) )
                    return "<div id=\"account_import_script\">.<script>
                        $('#group_list>ul').append( '<li id=\"g_{$group_id}\" onclick=\"openGroup({$group_id})\"><span>{$name}</span></li>' )
                        openGroup( $group_id );
                        $('#account_import_script').remove()
                    </script></div>";
                else
                    return "<div id=\"account_import_script\">.<script>
                        openGroup( $group_id );
                        $('#account_import_script').remove()
                    </script></div>";
            }    
        }



        function addUserInGroup( $group_id, $user_id ){
            if( is_numeric( $user_id ) )
                db::query( "INSERT IGNORE INTO ".DB_PREFIX."usergroup_user (group_id,user_id) VALUES ( $group_id, $user_id );" );
            elseif( $user_list_id = explode( ",", $user_id ) )
                foreach( $user_list_id as $i => $user_id )
                    if( is_numeric( $user_id ) )
                        db::query( "INSERT IGNORE INTO ".DB_PREFIX."usergroup_user (group_id,user_id) VALUES ( $group_id, $user_id );" );

        }

        function delUserFromGroup( $group_id, $user_id ){
            if( is_numeric( $user_id ) )
                db::query( "DELETE FROM ".DB_PREFIX."usergroup_user WHERE group_id=$group_id AND user_id = $user_id" );
            elseif( $user_list_id = explode( ",", $user_id ) )
                foreach( $user_list_id as $i => $user_id )
                    if( is_numeric( $user_id ) )
                        db::query( "DELETE FROM ".DB_PREFIX."usergroup_user WHERE group_id=$group_id AND user_id = $user_id" );
        }




    // GROUP

        function getUserGroup( $user_id ){

            $group_list = getGroupList();

            if( is_numeric ( $user_id ) ){
                if( $in_group_list = db::get_allIndexed( "group_id", "SELECT ".DB_PREFIX."usergroup.group_id, name
                                                FROM ".DB_PREFIX."usergroup
                                                JOIN ".DB_PREFIX."usergroup_user ON ( ".DB_PREFIX."usergroup.group_id = ".DB_PREFIX."usergroup_user.group_id AND user_id = $user_id )
                                                GROUP BY ".DB_PREFIX."usergroup.group_id
                                                ORDER BY name" ) )
                $group_list = array_diff_assoc( $group_list, $in_group_list );
            }
            else{
                $user_list_id = $user_id;
                $where = null;
                $user_list_split = explode( ",", $user_id );
                foreach( $user_list_split as $i => $user_id ){
                    $user_list[$user_id] = getUser( $user_id );
                    $where .= $where ? " OR user_id=$user_id" : "user_id=$user_id";
                }
                $nuser = count( $user_list_split );
                $query = "SELECT ".DB_PREFIX."usergroup.group_id, name, count( ".DB_PREFIX."usergroup_user.user_id ) AS nuser
                        FROM ".DB_PREFIX."usergroup
                        INNER JOIN ".DB_PREFIX."usergroup_user ON ".DB_PREFIX."usergroup.group_id = ".DB_PREFIX."usergroup_user.group_id
                        WHERE ( $where )
                        GROUP BY group_id
                        HAVING nuser = $nuser";

                if( $in_group_list = db::get_allIndexed( "group_id", $query ) )
                    $group_list = array_diff_assoc( $group_list, $in_group_list );
            }
            return json_encode( array('group_list' => $group_list, 'in_group_list' => $in_group_list ) );
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

            $tpl = new RainTPL();
            $tpl->assign( getGroup( $group_id ) );
            $tpl->assign( "group_id", $group_id);
            $tpl->assign( "permission_list", $permission_list );
            return $tpl->draw( 'user/group.view', true );
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

            $tpl = new RainTPL();
            $tpl->assign( getGroup( $group_id ) );
            $tpl->assign( "group_id", $group_id);
            $tpl->assign( "content_list", $content_list );
            $tpl->assign( "permission_list", $permission_list );
            return $tpl->draw( 'user/group.edit', true );
        }

        function group_new( $group_name ){
            db::query( "INSERT INTO ".DB_PREFIX."usergroup ( name ) VALUES ( '$group_name' )" );
            return $group_id = db::getInsertedId();
        }

        function group_save( $group_id, $name, $permission_list ){
            if( ( $group_id > 0 ) && ( $group = getGroup( $group_id ) ) ){

                    db::query( "UPDATE ".DB_PREFIX."usergroup SET 
                                                    name = '$name'
                                    WHERE group_id = '$group_id'" );

                if( $permission_list ){
                    foreach( $permission_list as $i => $permission ){
                        $id = isset( $permission['id'] ) ? $permission['id'] : 0;
                        $content_access = isset( $permission['content_access'] ) ? 1 : 0;
                        $subcontent_access = isset( $permission['subcontent_access'] ) ? 1 : 0;
                        $permission_id = isset( $permission['permission_id'] ) ? $permission['permission_id'] : 0;

                        if( isset( $permission['delete'] ) )
                            db::query( "DELETE FROM ".DB_PREFIX."content_permission WHERE permission_id='$permission_id'" );
                        elseif(  $permission_id > 0 )
                            db::query( "UPDATE ".DB_PREFIX."content_permission SET 
                                                                                    id = $id,
                                                                                    content_access = $content_access,
                                                                                    subcontent_access = $subcontent_access
                                        WHERE permission_id = {$permission['permission_id']}");
                        else
                            db::query( "INSERT INTO ".DB_PREFIX."content_permission ( id, content_access, subcontent_access, group_id ) VALUES
                                                                                    ( '$id', '$content_access', '$subcontent_access', $group_id )" );

                    }
                }
            }
        }



        function group_delete( $group_id, $delete_account = false ){

            if( $delete_account )
                db::query( "DELETE ".DB_PREFIX."user 
                            FROM ".DB_PREFIX."user
                            INNER JOIN ".DB_PREFIX."usergroup_user ON ".DB_PREFIX."user.account_id = ".DB_PREFIX."usergroup_user.account_id  
                            WHERE group_id = '$group_id'" );


            db::query( "DELETE FROM ".DB_PREFIX."usergroup WHERE group_id='$group_id'" );
            db::query( "DELETE FROM ".DB_PREFIX."usergroup_user WHERE group_id='$group_id'" );
        }




    // PERMISSION


        function permission_new( $group_id, $user_id ){


            if( $user_id )
                db::query( "INSERT INTO ".DB_PREFIX."content_permission ( user_id, id ) VALUES ( $user_id, -1 )" );

            elseif( $group_id )

                db::query( "INSERT INTO ".DB_PREFIX."content_permission ( group_id, id ) VALUES ( $group_id, -1 )" );


            return $permission_id = db::getInsertedId();
        }

        function permission_list( $group_id, $user_id ){

            // I get the permission list only for ADMIN or group
            if( ( getUserField( "status", $user_id ) == USER_ADMIN ) || $group_id ){
                $permission_list = db::get_all( "SELECT ".DB_PREFIX."content_permission.*, ".DB_PREFIX."content.title  
                                                    FROM ".DB_PREFIX."content_permission
                                                    INNER JOIN ".DB_PREFIX."user ON ".DB_PREFIX."content_permission.user_id = ".DB_PREFIX."user.user_id
                                                    LEFT JOIN ".DB_PREFIX."content ON ".DB_PREFIX."content_permission.content_id = ".DB_PREFIX."content.content_id
                                                    WHERE ". ( $group_id ? DB_PREFIX."user.group_id = $group_id" : DB_PREFIX."user.user_id = $user_id" ) ."
                                                    GROUP BY ".DB_PREFIX."content.content_id
                                                    ORDER BY permission_id ASC");
                return json_encode( $permission_list );
            }

        }

    // -- end
