<?php

    class BlogModule extends Module {

        function draw() {
            $this->assign($this->get_content());
            $this->assign("file_list", $this->get_file_list());
            $this->assign("content_list", $this->get_childs());
            $this->assign("comment_list", $this->get_comment_list());
            $this->set_layout("layout.blog");
        }
        
        function filter($path,$params){
            
            $content = $this->auto_complete($path);
            if(sizeof($content)==1){
                header('location: ' . URL . $content[0]['path'] );
            }
            else{
                
            }
            
            
        }

    }

    // -- end