<?php

    class SearchModule extends Module {

        function index( $search ) {

            if ($search) {

                $content_list = DB::get_all("SELECT c.*, CONCAT( '" . URL . "',path) AS link,
                                                    f.filepath AS cover, f.thumb AS cover_thumbnail,
                                                    MATCH( c.title, c.subtitle, c.content, c.tags ) AGAINST ( :search ) AS score

                                             FROM " . DB_PREFIX . "content c
                                             JOIN " . DB_PREFIX . "content_type t ON c.type_id = t.type_id
                                             LEFT JOIN ".DB_PREFIX."file_rel fr ON fr.rel_id=c.content_id AND fr.rel_type=".FILE_COVER."
                                             LEFT JOIN ".DB_PREFIX."file f ON f.file_id=fr.file_id
                                             WHERE MATCH ( c.title, c.subtitle, c.content, c.tags ) AGAINST ( :search )
                                             AND c.published = 1 AND t.searchable=1 AND lang_id=:lang_id
                                             GROUP BY content_id
                                             ORDER BY c.type_id, score DESC", 
                                             array(":search" => $search, ":lang_id" => LANG_ID));


                // if no results
                if (!$content_list && strlen($search) >= 5) {

                    $content_list = DB::get_all("SELECT c.*, f.filepath AS cover, f.thumb AS cover_thumbnail, CONCAT( '" . URL . "',path) AS link
                                                        FROM " . DB_PREFIX . "content AS c
                                                        JOIN " . DB_PREFIX . "content_type AS t ON c.type_id = t.type_id
                                                        LEFT JOIN ".DB_PREFIX."file f ON f.rel_id=c.content_id AND f.status=".FILE_COVER."
                                                        WHERE
                                                        c.published = 1 AND t.searchable = 1 AND lang_id=:lang_id
                                                        AND ( content LIKE :search OR title LIKE :search OR tags LIKE :search )
                                                        GROUP BY content_id
                                                        ORDER BY c.type_id DESC", array(":search" => $search, ":lang_id" => LANG_ID));
                }

                if (!$content_list) {

                    $soundex = soundex($search);
                    $soundexPrefix = substr($soundex, 0, 2); // first two characters of soundex
                    $content_list = DB::get_all("SELECT c.*, f.filepath AS cover, f.thumb AS cover_thumbnail, CONCAT( '" . URL . "',path) AS link
                                                        FROM " . DB_PREFIX . "content AS c
                                                        JOIN " . DB_PREFIX . "content_type AS t ON c.type_id = t.type_id
                                                        LEFT JOIN ".DB_PREFIX."file f ON f.rel_id=c.content_id AND f.status=".FILE_COVER."
                                                        WHERE
                                                        c.published = 1 AND t.searchable = 1 AND c.lang_id=:lang_id
                                                        AND ( SOUNDEX(c.title) LIKE :soundexPrefix OR SOUNDEX(c.tags) LIKE :soundexPrefix )
                                                        GROUP BY c.content_id
                                                        ORDER BY c.type_id DESC", array(":soundexPrefix" => "%$soundexPrefix%", ":lang_id" => LANG_ID));
                }
            }
            else
                $content_list = 1;

            $this->assign('search', $search);
            $this->assign($this->get_content());
            $this->assign("content_list", $content_list);
            $this->set_layout("layout.search");
        }
        
        function filter( $action, $params ){
            $search = $action;
            $this->index( $search );
            
        }

    }

    // -- end