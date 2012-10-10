<?php

    // To add a cover
    function form_cover($name, $value, $parameters, $validation, $cp) {
        
        add_script("cover.js", $script_dir = LIBRARY_DIR . "Form/plugins/cover/" );
        
        $content_id = $parameters['content_id'];
        $cover = $parameters['cover'];
        $cover_thumbnail = $parameters['cover_thumbnail'];
        $cover_html = '<a href="' . ( UPLOADS_DIR . $cover ) . '" rel="lightbox"><img src="' . ( UPLOADS_URL . $cover_thumbnail ) . '" alt="cover"/></a> <img src="' . ADMIN_VIEWS_IMAGES_URL . 'del.gif" style="cursor:hand" onclick="cover_delete(\'' . $content_id . '\', \'' . $script_dir . '\');" alt="delete"/>';
        $iframe_html = '<iframe src="' . $script_dir . 'upload.php?content_id=' . $content_id . '" style="height:30px;width:120px;border:0px;overflow:hidden;"></iframe>';
        
        return '<div id="cover">' . ( $cover ? $cover_html : $iframe_html ) . '</div>';
    }

    // -- end