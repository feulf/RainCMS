<?php

    /**
    * Controller class
    */
    class Module {

        protected $loader;
        protected $content;
        protected $selected;

        // init Module parameters
        function __construct($init_params) {
            $this->loader = $init_params['loader'];
            $this->selected = $init_params['selected'];
            $this->content = isset($init_params['content']) ? $init_params['content'] : array();
        }
        
        function load_library($library) {
            $obj = strtolower($library);
            require_once LIBRARY_DIR . ucfirst(strtolower($library)) . ".php";
            $this->$obj = new $library;
        }

        function get($key = null) {
            return $this->selected ? get($key) : null;
        }

        function post($key = null) {
            return $this->selected ? post($key) : null;
        }

        function get_post($key = null) {
            return $this->selected ? get_post($key) : null;
        }

        function get_content() {
            return $this->content;
        }

        function get_content_id() {
            return $this->content['content_id'];
        }

        function get_type() {
            return $this->content['type'];
        }

        function get_type_id() {
            return $this->content['type_id'];
        }

        function get_template() {
            return $this->content['template_index'] . $this->content['template'];
        }

        function get_childs($limit=null) {
            return Content::get_childs($this->content['content_id'], LANG_ID, $this->content['order_by'], $limit );
        }

        function get_tree($level = 2, $content_id = null) {
            if (!$content_id)
                $content_id = $this->content['content_id'];
            return Content::get_tree($content_id, $level);
        }

        function get_next_content() {
            return Content::get_next_content($this->content['parent_id'], $this->content['position']);
        }

        function get_prev_content() {
            return Content::get_prev_content($this->content['parent_id'], $this->content['position']);
        }

        function get_parent() {
            if ($this->content['parent_id'] > 0)
                return Content::get_content($this->content['parent_id']);
        }

        function get_file_list() {
            return Content::get_file_list($this->content['content_id'], "content");
        }

        function get_comment_list() {
            return Content::get_comment_list($this->content['content_id']);
        }

        function get_path() {
            return $this->content['path'];
        }

        function draw_path() {
            return Content::draw_path();
        }

        function get_content_path() {
            return Content::get_path($this->content['content_id']);
        }

        function page_not_found($msg = null) {
            $this->loader->_page_not_found($msg);
        }

        function filter_before() {

        }

        function filter_after() {

        }

    }

    // -- end