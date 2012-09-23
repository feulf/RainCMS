<?php

    load_lang("admin.content");

    class FileBaseController extends Controller {

        // ------------------------------------------
        //
                //                Tree Methods
        //
                // ------------------------------------------
        // draw tree
        function _draw_tree($sel_id = 0) {

            // initialize the session to know if one node is opened
            if (!isset($_SESSION['content_tree']))
                $_SESSION['content_tree'] = Array();

            $html = '<!-- Content Tree -->' . "\n";

            global $langs;
            if (count($langs) > 1) {
                $html .= '    <select name="lang_id" id="content_lang_select" onchange="window.location=\'' . ADMIN_FILE_URL . 'Content/edit/' . $sel_id . '/?lang_id=\'+this.value" class="tooltip" title="' . get_msg("content_tree_select_lang") . '">';
                foreach ($langs as $lang_id => $language)
                    $html .= '<option value="' . $lang_id . '" ' . ( LANG_ID == $lang_id ? 'selected="selected"' : null ) . '>' . $language . '</option>';
                $html .= '</select>';
            }

            $html .= '<div id="site_tree" class="tree">' . "\n";
            $html .= '    <a href="' . ADMIN_FILE_URL . 'Content/content_list/0/"><img src="' . ADMIN_VIEWS_IMAGES_URL . 'tree/content.gif" title="' . get_msg("content_button_content_list") . '" class="tooltip" alt="-" /></a> <a href="' . ADMIN_FILE_URL . 'Content/" title="' . get_msg("content_button_content_list") . '" class="tooltip' . ($sel_id == 0 ? ' selected' : null) . '">' . get_msg("content_root") . '</a>' . "\n";

            $html .= '    <ul id="tree_0">' . "\n";
            $html .= $this->_draw_site_tree($sel_id) . "\n";
            $html .= '    </ul>' . "\n";
            $html .= '</div>' . "\n";
            $html .= '<!-- /Content Tree -->' . "\n";
            return $html;
        }

        function _draw_site_tree($sel_id = 0, $tab = null, $content_id = 0, $order = null) {

            $html = "";
            
            if (!$order) {
                $content = Content::get_content_and_type($content_id);
                $order = $content['order_by'];
            }
            
            // 5 minutes
            $time = TIME - ( 5 * MINUTE );
            $content_list = Content::get_childs_and_type( $content_id, $lang_id = LANG_ID, $time, $order );

            if ($content_list) {

                // i save the tree node open
                $_SESSION['content_tree'][$content_id] = true;
                for ($i = 0; $i < count($content_list); $i++) {

                    $content = $content_list[$i];
                    //$type = get_content_type( $content['type_id'] );

                    $content_id = $content['content_id'];
                    $title = cut($content['title'], 22);
                    $published = $content['published'];
                    $orderBy = $content['order_by'];
                    $type_icon = $content['icon'];
                    $n_user = $content['n_user'];
                    $hasChilds = DB::get_row("SELECT content_id FROM " . DB_PREFIX . "content_rel WHERE rel_id=:parent_id AND rel_type='parent' LIMIT 1", array(":parent_id" => $content_id)) ? true : false;
                    $showChild = isset($_SESSION['content_tree'][$content_id]) and $hasChilds;
                    $isLast = ( count($content_list) - 1 ) == $i;
                    //$sa             = subcontent_access( $content_id );
                    $sa = 1;
                    $content_is_new = !$content['last_edit_time'];


                    if ($content_is_new)
                        $icon = ' <img src="' . ADMIN_VIEWS_IMAGES_URL . 'new-mini.gif" class="tooltip" title="' . get_msg("content_new") . ' - ' . get_msg("content_new_phrase") . '" alt="' . get_msg("content_new") . '"/>';
                    else
                        $icon = $published ? null : ' <img src="' . ADMIN_VIEWS_IMAGES_URL . 'not_published-mini.gif" class="tooltip" title="' . get_msg("not_published") . ' - ' . get_msg("content_not_published_phrase") . '" alt="' . get_msg("not_published") . '"/>';

                    if ($n_user)
                        $icon .= '<a href="' . ADMIN_FILE_URL . 'User/#group=online" class="tooltip" title="' . $n_user . ' ' . get_msg("content_visits") . ' - ' . get_msg("content_visits_caption") . '"><img src="' . ADMIN_VIEWS_IMAGES_URL . 'user/user.gif" alt="' . get_msg("content_visits") . '"></a><span class="text_light">' . $n_user . '</span>';

                    $html .= "\n";
                    $html .= $tab . '        <li id="li_tree_' . $content_id . '" ' . ( $isLast ? 'class="last"' : null ) . '>' . "\n";
                    $html .= $tab . '            <div ' . ( $hasChilds ? 'class="voice"' : null ) . '>';

                    if ($hasChilds)
                        $html .= '<img src="' . ADMIN_VIEWS_IMAGES_URL . 'tree/' . ( $showChild ? 'opened' : 'closed' ) . '.gif" onclick="open_tree(this,' . $content_id . ',' . $sel_id . ')" alt=""/>';
                    // edit content
                    $html .= '<a href="' . ADMIN_FILE_URL . 'Content/edit/' . $content_id . '/"><img src="' . ADMIN_VIEWS_IMAGES_URL . "tree/" . $type_icon . '" class="tooltip" title="<span class=text_light>'.$content['type'].' &gt; </span>' . get_msg("content_button_content") . '" alt=""/></a> ';
                    // edit list
                    $html .= '<a href="' . ADMIN_FILE_URL . 'Content/' . ( $hasChilds ? 'content_list' : 'edit' ) . '/' . $content_id . '/" class="tooltip' . ($sel_id == $content_id ? ' selected' : null) . ( $sa ? null : ' permission_denied' ) . '" title="<span class=text_light>'.$content['type'].' &gt; </span>' . get_msg("content_button_content_list") . '">';
                    $html .= $title . '</a>' . $icon;
                    $html .= $tab . "            </div>" . "\n";
                    // xhtml 1 validation require to have at least one <li>
                    $html .= $tab . "            <ul id=\"tree_{$content_id}\" class=\"normal\">";
                    if ($showChild)
                        $html .= $this->_draw_site_tree($sel_id, $tab .= "\t", $content_id, $orderBy);
                    else
                        $html .= "<li style=\"display:none;\"></li>";
                    $html .= $tab . "</ul>" . "\n";
                    $html .= $tab . "        </li>" . "\n";
                }
            }

            return $html;
        }

        function _close_site_tree($content_id) {
            unset($_SESSION['content_tree'][$content_id]);
        }

        // ------------------------------------------
        //
                //                Path Methods
        //
                // ------------------------------------------

        /**
        * Set path
        */
        function _content_set_path($content_id, $lang_id, $parent_path = null) {

            if (($path = $this->_content_get_path($content_id, $lang_id, $parent_path)) !== false) {

                db::query("UPDATE " . DB_PREFIX . "content SET 
                                                    path=? 
                                                    WHERE content_id=? AND lang_id=?", array($path, $content_id, $lang_id));

                $content_list = $content->get_childs($content_id);
                for ($i = 0; $i < count($content_list); $i++)
                    $this->_content_set_path($content_list[$i]['content_id'], $lang_id, $path);
            }
        }

        /**
        * Set path of all languages of contents
        */
        function _content_set_path_all_languages($content_id) {
            if ($langs = DB::get_all("SELECT lang_id FROM " . DB_PREFIX . "content WHERE content_id=?", array($content_id), "lang_id", "lang_id"))
                foreach ($langs as $lang_id)
                    $this->_content_set_path($content_id, $lang_id);
        }

        /**
        * Get path
        */
        function _content_get_path($content_id, $lang_id, $parent_path = null) {

            if ($content_row = DB::get_row("SELECT * FROM " . DB_PREFIX . "content AS c INNER JOIN " . DB_PREFIX . "content_type AS t ON c.type_id=t.type_id WHERE lang_id=? AND content_id=?", array($lang_id, $content_id ))) {

                if (!$parent_path && ($content_row['parent_id'] > 0)) {
                    $parent_path_row = $content->get_content($content_row['parent_id']);
                    $parent_path = $parent_path_row['path'];
                }

                // home page Path is /
                elseif ($content_row['position'] == 0 && $content_row['parent_id'] == 0)
                    return null;

                $path = $content_row['path_type'];

                preg_match_all("/\{(.*?)\}/", $path, $match);
                $key = $match[0];
                $value = $match[1];
                for ($i = 0; $i < count($value); $i++) {
                    switch ($value[$i]) {
                        case 'title': $v = $content_row['title'];
                            break;
                        case 'content_id': $v = $content_id;
                            break;
                        case 'content_id':$v = $content_row['content_id'];
                            break;
                        case 'y': $v = date("Y", $content_row['date']);
                            break;
                        case 'm': $v = date("m", $content_row['date']);
                            break;
                        case 'd': $v = date("d", $content_row['date']);
                            break;
                        default:
                            trigger_error("<b>Path type wrong: value {$value[$i]} not recognized! Value allowed: {title}, {id}, {content_id}, {y}, {m}, {d}</b>", E_USER_ERROR);
                            exit;
                    }
                    $special_chars = array('$', '&', '+', ',', '/', ':', ';', '=', '?', '@', '<', '>', '#', '%', '{', '}', '|', '\\', '^', '~', ']', '[', '`');
                    $v = str_replace($special_chars, '_', trim($v));
                    $v = str_replace(" ", "-", $v);
                    $v = str_replace('"', " ", $v);
                    $path = str_replace($key[$i], $v, $path);
                }



                if (DB::get_row("SELECT * FROM " . DB_PREFIX . "content WHERE path=? AND content_id!=?", array($path, $content_id)))
                    $path .= "-" . $content_id;
                $path .= "/";

                if ($parent_path) {

                    // If path short == 1
                    // I get the first node of the parent path.
                    // You can set the short path for avoid the problem of duplicated contents, for example
                    // when a product has is in more than one category, so it could be accessible as
                    // products/memory/usb-key-1  and  products/key/usb-key-1
                    if ($content_row['path_short']) {
                        $p = explode("/", $parent_path);
                        $parent_path = $p[0] . "/";
                    }
                    $path = $parent_path . $path;
                }

                return $path;
            }
            else
                return false;
        }

        /*     * ****************************************************** */

        // File functions

        /*     * ****************************************************** */

        /**
        * Remove the image from mysql and ups dir
        */
        function _deleteDeathImageFromContent($content, $content_id) {

            $content = stripslashes($content);
            preg_match_all('/<img.*?longdesc="\$\:(.*?)".*?\/>/i', $content, $img_content);

            $file_in_content = array();
            for ($i = 0; $i < count($img_content[1]); $i++) {
                if (is_numeric($img_content[1][$i]))
                    $file_in_content[] = $img_content[1][$i];
            }

            $file_id_query = '';
            if ($file_in_content)
                $file_id_query = " AND file_id NOT IN (" . implode(',', $file_in_content) . ")";
            /*
            if( $file_list = DB::get_all( "SELECT *
            FROM ".DB_PREFIX."file
            WHERE rel_id=? AND module='content' AND type_id=? AND status=? $file_id_query",
            array($content_id, IMAGE, FILE_EMBED )
            )
            ){

            for( $i=0; $i<count($file_list); $i++)
            Content::file_delete( $file_list[$i]['file_id'] );
            }
            */
        }

    }

    // -- end