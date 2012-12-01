<?php

    class HomeModule extends Module {

        function draw() {

            $this->assign($this->get_content());
            $this->assign("file_list", $this->get_file_list());
            $this->set_layout("layout.home");

        }

    }

    // -- end