<?php

    if (!defined("URL")) {
        $url = ( isset($_SERVER['HTTPS']) ? "https://" : "http://" ) . $_SERVER["SERVER_NAME"] . dirname($_SERVER['SCRIPT_NAME']);
        if (!preg_match('/.*\/$/', $url))
            $url .= "/";

        define("URL", $url);
        define("DOMAIN", str_replace('www.', '', strtolower($_SERVER['HTTP_HOST'])));
    }


    // Base URLs
    define("SYSTEM_URL", URL); // front end url
    define("APP_URL", URL); // front end url
    
    // public URLs
    define("UPLOADS_URL", URL . UPLOADS_DIR);
    define("JAVASCRIPT_URL", URL . JAVASCRIPT_DIR);
    define("JQUERY_URL", URL . JQUERY_DIR);
    define("CSS_URL", URL . CSS_DIR);
    define("IMAGES_URL", URL . IMAGES_DIR);
    define("LIBRARY_URL", URL . LIBRARY_DIR); // front end url
    
    // File URLs
    define("AJAX_URL", URL . "ajax.php/"); // ajax url
    
    // Admin URLs
    define("ADMIN_URL", URL); // back end url
    define("ADMIN_FILE_URL", ADMIN_URL . "admin/");
    define("ADMIN_AJAX_FILE_URL", ADMIN_URL . "admin.ajax.php/");
    define("ADMIN_VIEWS_URL", ADMIN_URL . ADMIN_VIEWS_DIR);
    define("ADMIN_VIEWS_IMAGES_URL", ADMIN_URL . ADMIN_VIEWS_IMAGES_DIR);
    define("ADMIN_VIEWS_CSS_URL", ADMIN_URL . ADMIN_VIEWS_CSS_DIR);
    define("ADMIN_VIEWS_JS_URL", ADMIN_URL . ADMIN_VIEWS_JS_DIR);
    define("ADMIN_LIBRARY_URL", ADMIN_URL . ADMIN_LIBRARY_DIR);
    define("ADMIN_IMAGES_URL", ADMIN_URL . ADMIN_JAVASCRIPT_DIR);
    define("ADMIN_JAVASCRIPT_URL", ADMIN_URL . ADMIN_JAVASCRIPT_DIR);
    define("ADMIN_CSS_URL", ADMIN_URL . ADMIN_CSS_DIR);

    // -- end