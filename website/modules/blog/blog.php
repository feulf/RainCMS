<?php

    class BlogModule extends Module {

        function draw() {

            $view = new View();
            $view->assign($this->get_content());
            $view->assign("file_list", $this->get_file_list());
            $view->assign("content_list", $this->get_childs());
            $view->assign("comment_list", $this->get_comment_list());
            $view->draw($this->get_template());
        }

    }

    // -- end