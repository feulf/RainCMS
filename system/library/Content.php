<?php

    class Content {

        //-------------------------------------------------------------
        //
        //                          Content
        //
        //-------------------------------------------------------------

        protected static    $content_type_list = array(),
                            $layout_list = array(),
                            $path = array(),
                            $path_selected = array();

        /**
        * Get a Content
        */
        static function get_content( $content_id, $lang_id = LANG_ID) {
            return DB::get_row("SELECT c.*, f.filepath AS cover, f.thumb AS cover_thumbnail, .r.rel_id AS parent_id, c.path AS link, p.template AS layout, t.*
                                FROM " . DB_PREFIX . "content c
                                JOIN " . DB_PREFIX . "content_rel r ON c.content_id=r.content_id AND r.rel_type='parent'
                                JOIN " . DB_PREFIX . "layout p ON p.layout_id = c.layout_id
                                JOIN " . DB_PREFIX . "content_type t ON t.type_id = c.type_id
                                LEFT JOIN ".DB_PREFIX."file_rel fr ON fr.rel_id=c.content_id AND fr.rel_type=:rel_type
                                LEFT JOIN ".DB_PREFIX."file f ON f.file_id=fr.file_id
                                WHERE c.content_id=:content_id AND c.lang_id=:lang_id", 
                                array(":rel_type"=>FILE_COVER, ":content_id" => $content_id, ":lang_id" => $lang_id)
            );
        }

        /**
        * Get the childs of a node
        */
        static function get_childs($content_id, $lang_id = LANG_ID, $order = null, $limit = 0, $only_published = true) {

            return DB::get_all("SELECT c.*, f.filepath AS cover, f.thumb AS cover_thumbnail, c.path AS link
                                FROM " . DB_PREFIX . "content c
                                JOIN " . DB_PREFIX . "content_rel r ON c.content_id=r.content_id AND r.rel_type='parent' AND r.rel_id=:parent_id
                                LEFT JOIN ".DB_PREFIX."file_rel fr ON fr.rel_id=c.content_id AND fr.rel_type=:rel_type
                                LEFT JOIN ".DB_PREFIX."file f ON fr.file_id=f.file_id
                                WHERE c.lang_id=:lang_id" .
                                ( $only_published ? " AND c.published=1 " : null ) .
                                ( $order ? " ORDER BY $order" : " ORDER BY r.position " ) .
                                ( $limit ? " LIMIT $limit" : null ), 
                                array(":parent_id" => $content_id, ":rel_type"=>FILE_COVER, ":lang_id" => $lang_id)
            );
        }

        /**
        * Get the site map starting from a node $content_id and going in deep for n $level
        */
        static function get_tree($content_id, $level, $lang_id = LANG_ID, $order = null, $limit = 0, $only_published = true) {
            $childs = self::get_childs($content_id, $lang_id, $order, $limit, $only_published);
            if ($level > 1 && $childs)
                foreach ($childs as $i => $child)
                    $childs[$i]['childs'] = self::get_tree($childs[$i]['content_id'], $level - 1, $lang_id, $order, $limit, $only_published);
            return $childs;
        }

        /**
        * Get the next content
        */
        static function get_next_content($parent_id, $position, $lang_id = LANG_ID, $only_published = true) {
            return DB::get_row("SELECT c.*, c.path AS link, f.filepath AS cover, f.thumb AS cover_thumbnail, r.position
                                FROM " . DB_PREFIX . "content c
                                JOIN " . DB_PREFIX . "content_rel r ON c.content_id=r.content_id AND r.rel_type='parent' AND r.rel_id=:parent_id
                                LEFT JOIN ".DB_PREFIX."file_rel fr ON fr.rel_id=c.content_id AND fr.rel_type=:rel_type
                                LEFT JOIN ".DB_PREFIX."file f ON f.file_id = fr.file_id
                                WHERE c.lang_id=:lang_id AND r.position>:position" .
                                ( $only_published ? " AND c.published=1 " : null ) .
                                " LIMIT 1", 
                                array(":parent_id" => $parent_id, ":rel_type"=>FILE_COVER, ":lang_id" => $lang_id, ":position" => $position )
            );
        }

        /**
        * Get the previous content
        */
        static function get_prev_content($parent_id, $position, $lang_id = LANG_ID, $only_published = true) {
            return DB::get_row("SELECT c.*, c.path AS link, f.filepath AS cover, f.thumb AS cover_thumbnail, r.position
                                FROM " . DB_PREFIX . "content c
                                JOIN " . DB_PREFIX . "content_rel r ON c.content_id=r.content_id AND r.rel_type='parent' AND r.rel_id=:parent_id
                                LEFT JOIN ".DB_PREFIX."file_rel fr ON fr.rel_id=c.content_id AND fr.rel_type=:rel_type
                                LEFT JOIN ".DB_PREFIX."file f ON f.file_id = fr.file_id
                                WHERE c.lang_id=:lang_id AND r.position<:position" .
                                ( $only_published ? " AND c.published=1" : null ) .
                                " LIMIT 1", 
                                array(":parent_id" => $parent_id, ":rel_type"=>FILE_COVER, ":lang_id" => $lang_id, ":position" => $position )
            );
        }

        /**
        * Get the content by the path
        */
        static function get_content_by_path($path = "", $lang_id = LANG_ID, $only_published = true) {
            return DB::get_row("SELECT c.*, f.filepath AS cover, f.thumb AS cover_thumbnail, c.path AS link, r.rel_id AS parent_id, r.position, p.template AS layout, t.*
                                FROM " . DB_PREFIX . "content c
                                JOIN " . DB_PREFIX . "content_rel r ON c.content_id=r.content_id AND r.rel_type='parent'
                                JOIN " . DB_PREFIX . "layout p ON p.layout_id = c.layout_id
                                JOIN " . DB_PREFIX . "content_type t ON t.type_id = c.type_id
                                LEFT JOIN ".DB_PREFIX."file_rel fr ON fr.rel_id=c.content_id AND fr.rel_type=:rel_type
                                LEFT JOIN ".DB_PREFIX."file f ON f.file_id=fr.file_id
                                WHERE c.path=:path AND lang_id=:lang_id" .
                                ( $only_published ? " AND c.published=1" : null ) .
                                " LIMIT 1", 
                                array( ":rel_type"=>FILE_COVER, ":path" => $path, ":lang_id" => $lang_id )
            );
        }

        /**
        * Get the content by the path
        */
        static function get_content_by_path_autocomplete($path = "", $lang_id = LANG_ID, $only_published = true) {
            $path = $path . "%";
            return DB::get_all("SELECT c.*, f.filepath AS cover, f.thumb AS cover_thumbnail, c.path AS link, r.rel_id AS parent_id, r.position, p.template AS layout, t.*
                                FROM " . DB_PREFIX . "content c
                                JOIN " . DB_PREFIX . "content_rel r ON c.content_id=r.content_id AND r.rel_type='parent'
                                JOIN " . DB_PREFIX . "layout p ON p.layout_id = c.layout_id
                                JOIN " . DB_PREFIX . "content_type t ON t.type_id = c.type_id
                                LEFT JOIN ".DB_PREFIX."file_rel fr ON fr.rel_id=c.content_id AND fr.rel_type=:rel_type
                                LEFT JOIN ".DB_PREFIX."file f ON f.file_id=fr.file_id
                                WHERE c.path LIKE :path AND lang_id=:lang_id" .
                                ( $only_published ? " AND c.published=1" : null )
                                , 
                                array( ":rel_type"=>FILE_COVER, ":path" => $path, ":lang_id" => $lang_id )
            );
        }

        /**
        * Get content by the type
        */
        static function get_content_by_type($type_id, $lang_id = LANG_ID, $only_published = true) {
            return DB::get_row("SELECT c.*, c.path AS link, f.filepath AS cover, f.thumb AS cover_thumbnail, r.rel_id AS parent_id, r.position, p.template AS layout, t.*
                                FROM " . DB_PREFIX . "content c
                                JOIN " . DB_PREFIX . "content_rel r ON c.content_id=r.content_id AND r.rel_type='parent'
                                JOIN " . DB_PREFIX . "layout p ON p.layout_id = c.layout_id
                                JOIN " . DB_PREFIX . "content_type t ON t.type_id = c.type_id
                                LEFT JOIN ".DB_PREFIX."file_rel fr ON fr.rel_id=c.content_id AND fr.rel_type=:rel_type
                                LEFT JOIN ".DB_PREFIX."file f ON f.file_id=fr.file_id
                                WHERE t.type_id=:type_id AND lang_id=:lang_id" .
                                ( $only_published ? " AND c.published=1" : null ) .
                                " LIMIT 1", 
                                array( ":rel_type"=>FILE_COVER, ":type_id" => $type_id, ":lang_id" => $lang_id)
            );
        }

        /**
        * Get a content by the module
        */
        static function get_content_by_module($module, $lang_id = LANG_ID, $only_published = true) {
            return DB::get_row("SELECT c.*, c.path AS link, f.filepath AS cover, f.thumb AS cover_thumbnail, r.rel_id AS parent_id, r.position, p.template AS layout, t.*
                                FROM " . DB_PREFIX . "content c
                                JOIN " . DB_PREFIX . "content_rel r ON c.content_id=r.content_id AND r.rel_type='parent'
                                JOIN " . DB_PREFIX . "layout p ON p.layout_id = c.layout_id
                                JOIN " . DB_PREFIX . "content_type t ON t.type_id = c.type_id
                                LEFT JOIN ".DB_PREFIX."file_rel f ON fr.rel_id=c.content_id AND fr.rel_type=:rel_type
                                LEFT JOIN ".DB_PREFIX."file f ON f.file_id=fr.file_id
                                WHERE t.module=:module AND lang_id=:lang_id" .
                                ( $only_published ? " AND c.published=1" : null ) .
                                " LIMIT 1", 
                                array(":rel_type"=>FILE_COVER, ":module" => $module, ":lang_id" => $lang_id));
        }

        /**
        * Get the content and the type of the content
        */
        static function get_content_and_type($content_id, $lang_id = LANG_ID) {

            return DB::get_row("SELECT c.*, r.rel_id AS parent_id, f.filepath AS cover, f.thumb AS cover_thumbnail, r.rel_id AS parent_id, r.position, t.*
                                FROM " . DB_PREFIX . "content c
                                JOIN " . DB_PREFIX . "content_rel r ON c.content_id=r.content_id AND r.rel_type='parent'
                                JOIN " . DB_PREFIX . "content_type t ON c.type_id=t.type_id 
                                LEFT JOIN ".DB_PREFIX."file_rel fr ON fr.rel_id=c.content_id AND fr.rel_type=:rel_type
                                LEFT JOIN ".DB_PREFIX."file f ON f.file_id=fr.file_id
                                WHERE c.content_id=:content_id AND c.lang_id=:lang_id", 
                                array(":rel_type"=>FILE_COVER, ":content_id" => $content_id, ":lang_id" => $lang_id));
        }

        /**
        * Get the child of a content and the type associated to each child
        */
        static function get_childs_and_type($content_id, $lang_id = LANG_ID, $time = null, $order = null) {
            
            return DB::get_all("SELECT t.*, c.*, f.filepath AS cover, f.thumb AS cover_thumbnail, r.position, count(l.user_localization_id) AS n_user
                                FROM " . DB_PREFIX . "content c
                                JOIN " . DB_PREFIX . "content_rel r ON c.content_id=r.content_id AND r.rel_type='parent' AND r.rel_id=:parent_id
                                JOIN " . DB_PREFIX . "content_type t ON c.type_id = t.type_id
                                LEFT JOIN " . DB_PREFIX . "user_localization l ON l.content_id = c.content_id AND l.time >= :time
                                LEFT JOIN ".DB_PREFIX."file_rel fr ON fr.rel_id=c.content_id AND fr.rel_type=:rel_type
                                LEFT JOIN ".DB_PREFIX."file f ON f.file_id=fr.file_id
                                WHERE c.lang_id=:lang_id
                                GROUP BY c.content_id" .
                                ( $order ? " ORDER BY $order" : " ORDER BY r.position ASC" ), 
                                array( ":parent_id"=>$content_id, ":time"=>$time, ":rel_type"=>FILE_COVER, ":lang_id"=>$lang_id )
            );
        }

        static function content_delete($content_id) {

            if( $content_list = Content::get_childs($content_id) ){
                foreach( $content_list as $content )
                    Content::content_delete($content['content_id'] );
            }
            
            Content::file_delete_by_content_id( $content_id );
            
            //delete content_rel
            db::query("DELETE FROM " . DB_PREFIX . "content_rel WHERE content_id=? OR rel_id=?", array($content_id, $content_id));

            //delete content
            db::query("DELETE FROM " . DB_PREFIX . "content WHERE content_id=?", array($content_id));
            
            //delete content event
            //db::query("DELETE FROM " . DB_PREFIX . "content_event_date WHERE content_id=?", array($content_id));
            
            Content::clean_cache( $content_id );
        }
        
        
        // clean the cache, when you update a content_id
        static function clean_cache( $content_id ){

            $content = Content::get_content( $content_id );
            $path = $content['path'];
            $path = str_replace( "../", "", $path ); // remove upper level for security reason
            
            $cache_filepath = CACHE_DIR . "html/" . basename( URL ) . "/" . $path;
            dir_del( $cache_filepath );
            
            $block_list = DB::get_all( "SELECT * 
                                        FROM ".DB_PREFIX."block 
                                        WHERE content_id=?", 
                                        array($content_id) 
                                     );
            if( $block_list ){
                foreach( $block_list as $block ){
                    $in_content_id = $block['in_content_id'];
                    if( $in_content_id ){
                        $content = Content::get_content( $in_content_id );
                        $path = $content['path'];
                        $path = str_replace( "../", "", $path ); // remove upper level for security reason
                        dir_del( CACHE_DIR . "html/" . $path );
                    }
                }
            }

        }
        
        
        function get_content_date_list( $content_id ){
            return db::get_all( "SELECT * 
                                 FROM " . DB_PREFIX . "content_event_date 
                                 WHERE content_id=? 
                                 ORDER BY start_time ASC", 
                                 array($content_id) 
                              );
        }

        //-------------------------------------------------------------
        //
        //                     Content Path
        //
        //-------------------------------------------------------------
        // get the path (or breadcrumbs) of this content
        static function get_path($content_id, $lang_id = LANG_ID) {

            if (self::$path)
                return self::$path;

            $sel_id = $content_id;
            do {
                $path[] = $content = self::get_content($sel_id, $lang_id);
            } while (($sel_id = $content['parent_id']) > 0);

            return self::$path = array_reverse($path);
        }

        // add one voice to the path
        static function add_path($title, $link) {
            self::$path_selected[] = array('title' => $title, 'link' => $link);
        }

        // draw the path
        static function draw_path() {
            $n = self::$path_selected ? count(self::$path) : count(self::$path) - 1;
            $html = "";
            for ($i = 0; $i < $n; $i++)
                $html .= ( $i > 0 ? CONTENT_PATH_SEPARATOR : null ) . '<a href="' . self::$path[$i]['link'] . '">' . self::$path[$i]['title'] . '</a>';

            for ($i = 0; $i < count(self::$path_selected) - 1; $i++)
                $html .= CONTENT_PATH_SEPARATOR . '<a href="' . self::$path_selected[$i]['link'] . '">' . self::$path_selected[$i]['title'] . '</a>';

            return $html;
        }

        //-------------------------------------------------------------
        //
        //                     Content Type
        //
        //-------------------------------------------------------------
        // return contentType
        static function get_content_type($type_id = 0) {
            if (!self::$content_type_list) {
                self::$content_type_list = DB::get_all("SELECT * 
                                                        FROM " . DB_PREFIX . "content_type 
                                                        WHERE published=1", array(), "type_id" );
            }
            return isset( self::$content_type_list[$type_id] ) ? self::$content_type_list[$type_id] : null;
        }

        // return content type child
        static function get_content_type_childs($type_id = 0) {
            return DB::get_all("SELECT *
                                FROM " . DB_PREFIX . "content_type_tree tt
                                INNER JOIN " . DB_PREFIX . "content_type t ON tt.type_id = t.type_id
                                WHERE parent_id=:type_id", array(":type_id" => $type_id)
            );
        }

        // return content type child
        static function get_content_type_fields($type_id = 0, $only_published = true, $multilanguage = true) {

            if (null === $multilanguage)
                $multilanguage_query = null;
            else
                $multilanguage_query = $multilanguage ? " AND multilanguage=1" : " AND multilanguage=0";

            return DB::get_all("SELECT *
                                FROM " . DB_PREFIX . "content_type_field
                                WHERE type_id=:type_id" .
                            ( $only_published ? " AND published=1" : null ) .
                            $multilanguage_query .
                            " ORDER BY position", array(":type_id" => $type_id)
            );
        }

        static function get_content_type_field( $type_id, $field ){
            return DB::get_row( "SELECT * 
                                 FROM ".DB_PREFIX."content_type_field f
                                 WHERE type_id=:type_id AND name=:field
                                 ",
                                 array(":type_id"=>$type_id, ":field"=>$field)
                              );
        }

        static function get_content_type_tree( $type_id, $parent_id ){
            return DB::get_row( "SELECT * 
                                 FROM ".DB_PREFIX."content_type_tree t
                                 WHERE type_id=:type_id AND parent_id=:parent_id
                                 ",
                                 array(":type_id"=>$type_id, ":parent_id"=>$parent_id)
                              );
        }

        static function get_content_last_position($parent_id) {
            return db::get_field("SELECT r.position
                                FROM " . DB_PREFIX . "content c
                                JOIN content_rel r ON c.content_id=r.content_id AND r.rel_type='parent'
                                WHERE r.rel_id=? 
                                ORDER BY r.position DESC 
                                LIMIT 1", array($parent_id)
            );
        }

        static function get_last_content_id() {
            return db::get_field("SELECT content_id
                                FROM " . DB_PREFIX . "content 
                                ORDER BY content_id DESC 
                                LIMIT 1");
        }

        //-------------------------------------------------------------
        //
        //              Layout Page
        //
        //-------------------------------------------------------------
        // return contentType
        static function get_layout($layout_id = 0) {
            self::get_layout_list();
            return self::$layout_list[$layout_id];
        }

        // get the list of layout
        static function get_layout_list() {
            if (!self::$layout_list)
                self::$layout_list = DB::get_all("SELECT * 
                                                FROM " . DB_PREFIX . "layout 
                                                ORDER BY position", array(), "layout_id"
                );
            return self::$layout_list;
        }

        //-------------------------------------------------------------
        //
        //              Blocks
        //
        //-------------------------------------------------------------
        // return Block
        static function get_block($block_id) {
            return DB::get_row("SELECT *
                                FROM ".DB_PREFIX."block b
                                JOIN ".DB_PREFIX."block_type bt ON b.block_type_id = bt.block_type_id
                                WHERE b.block_id=? 
                                LIMIT 1", array($block_id)
            );
        }

        // return Block List
        static function get_block_list($layout_id = 0, $type_id = 0, $content_id = 0) {
            return DB::get_all("SELECT c.*, c.path AS link, t.*, b.*, bt.module
                                FROM " . DB_PREFIX . "block AS b
                                JOIN " . DB_PREFIX . "block_type bt ON b.block_type_id = bt.block_type_id
                                JOIN " . DB_PREFIX . "content AS c ON b.content_id = c.content_id
                                JOIN " . DB_PREFIX . "content_type AS t ON c.type_id = t.type_id
                                WHERE b.global = 1
                                OR ( :layout_id>0 AND b.layout_id=:layout_id )
                                OR ( :content_id>0 AND b.in_content_id=:content_id )
                                OR ( :type_id>0 AND b.type_id=:type_id )
                                ORDER BY b.load_area, b.position", 
                                array(":layout_id" => $layout_id, ":type_id" => $type_id, ":content_id" => $content_id)
            );
        }

        static function get_block_settings($block_id) {
            return DB::get_all("SELECT * 
                                FROM " . DB_PREFIX . "block_setting 
                                WHERE block_id=?", array($block_id), "setting", "value");
        }
        
        static function get_block_options($block_type_id){
            return DB::get_all("SELECT *
                                FROM ".DB_PREFIX."block_type_option
                                WHERE block_type_id=?
                                ORDER BY position
                               ",
                               array( $block_type_id )
                              );
        }
        
        static function get_block_options_and_values($block_type_id){
            return DB::get_all("SELECT *
                                FROM ".DB_PREFIX."block_type_option bo
                                LEFT JOIN ".DB_PREFIX."block_setting bs ON bo.name=bs.setting
                                WHERE bo.block_type_id=?
                                ORDER BY bo.position
                               ",
                               array( $block_type_id )
                              );
        }
        
        static function get_block_type_list(){
            return DB::get_all("SELECT *
                                FROM ".DB_PREFIX."block_type
                               ");
        }

        //-------------------------------------------------------------
        //
        //        File functions
        //
        //-------------------------------------------------------------
        // get file
        static function get_file($file_id) {
            return DB::get_row("SELECT * 
                                FROM " . DB_PREFIX . "file
                                WHERE file_id = ?", array($file_id)
            );
        }

        static function get_file_list($rel_id, $rel_type = FILE_LIST) {

            $file_list = DB::get_all("SELECT * 
                                      FROM " . DB_PREFIX . "file_rel fr
                                      JOIN " . DB_PREFIX . "file f ON fr.file_id=f.file_id 
                                      WHERE fr.rel_id=:rel_id AND fr.rel_type=:rel_type
                                      ORDER BY position", 
                                      array( ":rel_id"=>$rel_id, ":rel_type"=>$rel_type )
            );
            return $file_list;
        }
        
        static function file_delete_by_content_id( $content_id ){
            $file_list = Content::get_file_list( $content_id );
            foreach( $file_list as $file )
                Content::file_delete( $file['file_id'] );
        }

        // delete file
        static function file_delete($file_id) {
            // search the file
            $file = DB::get_row( "SELECT * FROM ".DB_PREFIX."file WHERE file_id=?", array($file_id) );

            if ( $file ) {
 
                if( $file['rel_type'] == FILE_LIST OR $file['rel_type'] == FILE_CONTENT )
                    Content::clean_cache( $file['rel_id'] );

                //filepath
                $filepath = $file['filepath'];
                $filename = basename( $filepath );
                $path  = substr( $filepath, 0, -strlen( $filename ) );

                //delete row from db
                DB::query("DELETE FROM " . DB_PREFIX . "file WHERE file_id=?", array($file_id));

                // update the size total used space
                DB::query("UPDATE " . DB_PREFIX . "setting SET value=value-? WHERE setting='space_used'", array($file['size']));

                // if the file is not linked to other contents I can delete it
                if (!DB::get_row("SELECT * FROM " . DB_PREFIX . "file WHERE filepath=?", array($filepath))) {
                    if ($files_del = glob(UPLOADS_DIR . $path . "*" . $filename)){
                        foreach ($files_del as $filename_del)
                            unlink($filename_del);
                    }
                }
                return true;
            }
            
        }

        static function file_count($content_id) {
            return DB::get_field("SELECT count(*) as n
                                FROM file
                                WHERE rel_id=?
                                ", array($content_id));
        }

        //-------------------------------------------------------------
        //
        //        Permission functions
        //
        //-------------------------------------------------------------
        // true if user has access to subcontent
        static function content_access($content_id) {

            if (User::is_super_admin())
                return true;

            $access = self::get_access();

            if (( isset($access[0]) && $access[0] | 1 ) || ( isset($access[$content_id]) && $access[$content_id] | 1 ))
                return true;

            for ($i = 0; $i < count($path = self::getPath($content_id)) - 1; $i++)
                if (isset($access[$path[$i]['content_id']]))
                    return true;
        }

        // true if user has access to subcontent
        static function subcontent_access($content_id) {

            if (User::is_super_admin())
                return true;

            $access = self::get_access();

            if (isset($access[0]) && $access[0] | 2 || isset($access[$content_id]) && $access[$content_id] | 2)
                return true;

            for ($i = 0; $i < count($path = self::getPath($content_id)) - 1; $i++)
                if (isset($access[$path[$i]['content_id']]))
                    return true;
        }

        static function get_access() {
            global $access;
            if (!$access) {
                $user_id = User::getUserId();
                $access = DB::get_all("SELECT content_id, (content_access + 2*subcontent_access) AS access
                                            FROM " . DB_PREFIX . "content_permission p
                                            LEFT JOIN " . DB_PREFIX . "usergroup_user g ON ( p.group_id = g.group_id )
                                            WHERE ( g.user_id = ? OR p.user_id = ? )", array($user_id, $user_id), "content_id", "access"
                );
            }
            return $access;
        }

        //-------------------------------------------------------------
        //
        //                     Comment functions
        //
        //-------------------------------------------------------------

        static function get_comment($comment_id) {
            return db::get_row("SELECT cc.*, IF( u.name != '', u.name, cc.name ) AS name
                                    FROM " . DB_PREFIX . "content_comment cc
                                    JOIN " . DB_PREFIX . "content c ON cc.content_id = c.content_id
                                    LEFT JOIN " . DB_PREFIX . "user u ON cc.user_id = u.user_id
                                    WHERE cc.comment_id=?"
                            , array($comment_id));
        }

        static function get_comment_list($content_id, $lang_id = LANG_ID) {
            return db::get_all("SELECT cc.*, IF( u.name != '', u.name, cc.name ) AS name
                                    FROM " . DB_PREFIX . "content_comment cc
                                    JOIN " . DB_PREFIX . "content c ON cc.content_id = c.content_id
                                    LEFT JOIN " . DB_PREFIX . "user u ON cc.user_id = u.user_id
                                    WHERE c.content_id=? AND c.lang_id=?"
                            , array($content_id, $lang_id));
        }

        static function get_last_comment($content_id, $lang_id = LANG_ID) {
            return db::get_row("SELECT cc.*
                                    FROM " . DB_PREFIX . "content_comment cc
                                    JOIN " . DB_PREFIX . "content c ON cc.content_id = c.content_id
                                    WHERE c.content_id=? AND c.lang_id=?
                                    ORDER BY date DESC
                                    LIMIT 1"
                            , array($content_id, $lang_id));
        }

        static function get_n_comment($content_id, $lang_id = LANG_ID) {
            return db::get_field("SELECT count(*) AS n
                                    FROM " . DB_PREFIX . "content_comment cc
                                    JOIN " . DB_PREFIX . "content c ON cc.content_id = c.content_id
                                    WHERE c.content_id=? AND c.lang_id=?
                                    GROUP BY c.content_id"
                            , array($content_id, $lang_id)
                            , "n");
        }

        

        //-------------------------------------------------------------
        //
        //        Module
        //
        //-------------------------------------------------------------

        static function get_module($module) {
            return db::get_row("SELECT *
                                FROM " . DB_PREFIX . "module
                                WHERE module=?"
                            , array($module));
        }

        static function get_module_list() {
            return db::get_all("SELECT *
                                FROM " . DB_PREFIX . "module
                                ORDER BY module",
                                array(),
                                "module"
                              );
        }
        
    }

    // -- end