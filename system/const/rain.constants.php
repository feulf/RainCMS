<?php

    define("RAINCMS_VERSION", "Rain Framework Alpha");

    //-------------------------------------------------------------
    //
    //                          Module Constants
    //
    //-------------------------------------------------------------

    define("MODULE_EXTENSION", ".php");
    define("MODULE_CLASS_NAME", "Module");
    define("AJAX_MODULE_EXTENSION", ".ajax.php");
    define("AJAX_MODULE_CLASS_NAME", "AjaxModule");
    define("BLOCK_EXTENSION", ".block.php");
    define("BLOCK_CLASS_NAME", "Block");



    //-------------------------------------------------------------
    //
    //                          User Constants
    //
    //-------------------------------------------------------------

    define("REGISTRATION_CONFIRM_FREE", 0);
    define("REGISTRATION_CONFIRM_EMAIL", 1);
    define("REGISTRATION_CONFIRM_ADMIN", 2);

    
    
    //-------------------------------------------------------------
    //
    //                          Cache Control
    //
    //-------------------------------------------------------------
    define( "ONUPDATE", -1 );
    define( "NOCACHE", 0 );

    

    //-------------------------------------------------------------
    //
    //                          Constants
    //
    //-------------------------------------------------------------
    //change frequency sitemaps
    global $changefreq;
    $changefreq = Array(
        -1 => "not in sitemaps", // this content will not added to the sitemaps
        0 => "always",
        1 => "hourly",
        2 => "daily",
        3 => "weekly",
        4 => "monthly",
        5 => "yearly",
        6 => "never",
    );

    //-------------------------------------------------------------
    //
    //                          Rain CMS
    //
    //-------------------------------------------------------------


    define("ROOT_ID", 0);                    // default id of root
    define("MODULE_DEFAULT", "content");        // default module
    define("MODULE_ACTION_DEFAULT", "index");    // default module
    define("LAYOUT_ID_GENERIC", 1);            // default layout_id
    define("LAYOUT_ID_NOT_FOUND", 2);            // layout_id of content not found
    define("LOAD_AREA_DEFAULT", "center");   // name of default load_area in a page
    define("CONTENT_PATH_SEPARATOR", " / ");  // define the path separator symbol
    // menu
    define("ADMIN_MENU_ID", 1);               // menu_id of admin panel menu
    define("PRINCIPAL_MENU_ID", 2);           // principal menu
    define("SECONDARY_MENU_ID", 3);           // secondary menu
    // type_id of principal content_type
    define("TYPE_ID_CONTENT", 1);
    define("TYPE_ID_SITE_MAP", 2);
    define("TYPE_ID_LOGIN", 3);
    define("TYPE_ID_REGISTRATION", 4);
    define("TYPE_ID_SEARCH", 5);
    define("TYPE_ID_CONTACT", 6);


    //-------------------------------------------------------------
    //
    //                          File Constants
    //
    //-------------------------------------------------------------

    define("THUMB_PREFIX", "t_");

    //file type
    global $file_type;
    $file_type = array(1 => "image",
        2 => "audio",
        3 => "video",
        4 => "document",
        5 => "archive");

    // File type
    define("IMAGE", 1);
    define("AUDIO", 2);
    define("VIDEO", 3);
    define("DOCUMENT", 4);
    define("ARCHIVE", 5);

    // File location
    define("FILE_LIST", 0);        // linked to the list of content
    define("FILE_EMBED", 1);        // embed into the content
    define("FILE_COVER", 2);       // cover of content
    define("FILE_BLOCK", 3);       // is a block
    define("FILE_ATTACHMENT", 4); // email attachment
    define("FILE_IMG_EMAIL", 5);  // img into email


    // -- end