<?php

    class BlogAjaxModule extends Module {

        function read_content( $content_id ) {
            $content = Content::get_content( $content_id );
            echo json_encode( $content );
        }

    }

    // -- end