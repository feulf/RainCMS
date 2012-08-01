<?php

    class BlogBlock extends Block {

        function draw() {

            $view = new View();
            $view->assign($this->get_content());
            $view->assign("childs", $this->get_childs());
            $view->draw($this->get_template());
        }

    }

    // -- end