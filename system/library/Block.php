<?php

    /**
    * Controller class
    */
    class Block extends Module{

        function __construct($init_params){
            parent::__construct($init_params);
            $this->settings = Content::get_block_settings( $this->get_block_id() );
        }
        
        function get_block_id(){
            return isset($this->content["block_id"])?$this->content["block_id"]:null;
        }
        
        function get_template(){
            return $this->content['template_index'] . "block." . $this->content['template'];
        }
        
        function get_setting($key){
            return isset($this->settings[$key])?$this->settings[$key]:null;
        }
        


    }

    // -- end