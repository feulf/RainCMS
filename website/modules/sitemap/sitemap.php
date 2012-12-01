<?php

    class SitemapModule extends Module {

        function draw($view_as = null) {

            $view = new View();
            $view->assign($this->get_content());
            $view->assign("sitemap", $this->_draw_site_map($view_as));
            $view->draw($this->get_template());
        }

        // Filter the news by date, 2011/12
        function alphabetical() {
            $this->draw("alphabetical");
        }

        // Filter the news by date, 2011/12
        function date() {
            $this->draw("date");
        }

        function _draw_site_map($view_as = 'tree') {

            switch ($view_as) {
                case 'alphabetical':
                    return $this->_site_map_alphabetical();
                    break;
                case 'date':
                    return $this->_site_map_by_date();
                    break;
                default:
                    return $this->_site_map_tree();
            }
        }

        function _site_map_by_date() {
            
            $content_list = DB::get_all("SELECT c.*, f.filepath AS cover, f.thumb AS cover_thumbnail, c.path AS link
                                         FROM " . DB_PREFIX . "content c
                                         JOIN " . DB_PREFIX . "content_rel r ON c.content_id=r.content_id AND r.rel_type='parent' AND r.rel_id=:parent_id
                                         LEFT JOIN ".DB_PREFIX."file f ON f.rel_id=c.content_id AND f.status=".FILE_COVER."
                                         WHERE c.lang_id=:lang_id
                                         AND c.published=1",
                                         array( ":lang_id" => $lang_id )
            );
            

            $html = "<div>";
            for ($i = 0, $old_date = ""; $i < count($content_list); $i++) {
                $content_id = $content_list[$i]['content_id'];
                $title = $content_list[$i]['title'];
                $link = $content_list[$i]['link'];
                $html .= "<div class=\"link\"><a href=\"$link\">{$title}</a></div>" . "\n";
            }
            $html .= "</div>";
            return $html;
        }

        function _site_map_alphabetical() {
            
            $content_list = DB::get_all("SELECT c.*, f.filepath AS cover, f.thumb AS cover_thumbnail, c.path AS link
                                         FROM " . DB_PREFIX . "content c
                                         JOIN " . DB_PREFIX . "content_rel r ON c.content_id=r.content_id AND r.rel_type='parent' AND r.rel_id=:parent_id
                                         LEFT JOIN ".DB_PREFIX."file f ON f.rel_id=c.content_id AND f.status=".FILE_COVER."
                                         WHERE c.lang_id=:lang_id AND c.published=1
                                         ORDER BY c.title",
                                         array( ":lang_id" => $lang_id )
            );

            $html = "";

            for ($i = 0, $old_char = ""; $i < count($content_list); $i++) {

                $char = strtoupper(substr($content_list[$i]['title'], 0, 1));

                if (preg_match("/[^A-Za-z]/", $char))
                    $char = "#";

                $title = $content_list[$i]['title'];
                $link = $content_list[$i]['link'];

                if ($old_char != $char) {
                    if ($i > 0)
                        $html .= "</div>" . "\n";
                    $html .= '<div class="voice">' . "\n";
                    $html .= '    <h2>' . $char . '</h2>' . "\n";
                }
                $old_char = $char;
                $html .= '    <div class="link"><a href="' . $link . '">' . $title . '</a></div>' . "\n";
            }
            $html .= "</div>" . "\n";
            return $html;
        }

        function _site_map_tree($content_id = 0, $tab = "", $order_by = "r.position ASC") {
            $html = "";
            if ($childs = Content::get_childs($content_id, $lang_id = LANG_ID, $order_by)) {
                $html = "\n" . $tab . "<ul>";
                foreach ($childs as $i => $content) {
                    $type = Content::get_content_type($content['type_id']);
                    $html .= $tab . '    <li>' . "\n" .
                            $tab . $tab . '<a href="' . $content['link'] . '">' . $content['title'] . '</a>' .
                            $tab . $tab . $this->_site_map_tree($content['content_id'], $tab . "\t", $type['order_by']) . "\n" .
                            $tab . '</li>' . "\n";
                }
                $html .= $tab . "</ul>" . "\n";
            }

            return $html;
        }

    }

    // -- end