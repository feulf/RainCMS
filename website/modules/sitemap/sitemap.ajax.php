<?php

    class SitemapAjaxModule extends Module {

        function draw() {
            $html = "<?xml version='1.0' encoding='UTF-8'?>" . "\n" .
                    '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"' . "\n" .
                    '    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"' . "\n" .
                    '    xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9' . "\n" .
                    '    http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">' . "\n" .
                    $this->_site_map_tree() .
                    '</urlset>';
            echo $html;
        }

        protected function _site_map_tree($id = 0, $priority = 1) {

            if ($id == 0 || Content::get_content($id)) {
                $type_id = isset($content) ? $content['type_id'] : 1;
                $type = Content::get_content_type($type_id);
                $order = $type['order_by'] ? " ORDER BY {$type['order_by']}" : null;

                $content_list = DB::get_all("SELECT *, CONCAT( '" . URL . "',path ) AS link
                                                FROM " . DB_PREFIX . "content
                                                WHERE published >= 1 AND parent_id=? AND changefreq >= 0
                                                $order", array($id)
                );

                if ($priority == 0)
                    $priority = 0.1;

                $html = '';
                if ($content_list)
                    foreach ($content_list as &$content) {

                        $html .= '    <url>' . "\n" .
                                '        <loc>' . $content['link'] . '</loc>' . "\n" .
                                '        <lastmod>' . time_format($content['last_edit_time'], '%Y-%m-%d', false) . '</lastmod>' . "\n" .
                                '        <changefreq>' . $this->_get_change_freq($content['changefreq']) . '</changefreq>' . "\n" .
                                '        <priority>' . $priority . '</priority>' . "\n" .
                                '    </url>' . "\n" .
                                $this->_site_map_tree($content['content_id'], $priority - 0.1);
                    }
                return $html;
            }
        }

        protected function _get_change_freq($freq) {
            global $changefreq;
            return $changefreq[$freq];
        }

    }

    // -- end