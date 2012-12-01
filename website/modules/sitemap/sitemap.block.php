<?php

        class SitemapBlock extends Block {

        function draw() {

            $view = new View();
            $view->assign($this->get_content());
            $view->assign("sitemap", $this->_site_map_tree());
            $view->draw($this->get_template());
        }

        function _site_map_tree($id = 0, $tab = "", $order_by = "position ASC") {
            $html = "";
            if ($childs = Content::get_childs($id, $lang_id = LANG_ID, $order_by)) {
                $html = "\n" . $tab . "<ul>";
                foreach ($childs as $i => $content) {
                    $type = Content::get_content_type($content['type_id']);
                    $html .= $tab . '<li>' . "\n" .
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
