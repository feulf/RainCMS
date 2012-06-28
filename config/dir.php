<?php

    // Base application directory
    $base_dir = getcwd() . "/";
    chdir($base_dir);
    set_include_path($base_dir);

    // base folder
    define("BASE_DIR", $base_dir);
    define("BASE_NAME", basename($base_dir));

    // Rain folders
    define("SYSTEM_DIR", "system/");
    define("LIBRARY_DIR", "system/library/");
    define("LANGUAGE_DIR", "system/language/");
    define("CONSTANTS_DIR", "system/const/");
    define("LOG_DIR", "system/log/");

    // public folders
    define("PUBLIC_DIR", "public/");
    define("UPLOADS_DIR", "public/uploads/");
    define("JAVASCRIPT_DIR", "public/js/");
    define("JQUERY_DIR", "public/js/jquery/");
    define("CSS_DIR", "public/css/");
    define("IMAGES_DIR", "public/img/");
    define("CONFIG_DIR", "config/");
    define("CACHE_DIR", "cache/");
    define("PUBLIC_CACHE_DIR", "public/cache/");

    // website folders
    define("MODULES_DIR", "website/modules/");
    define("PLUGINS_DIR", "website/plugins/");
    define("THEMES_DIR", "website/themes/");

    // admin folders
    define("MODELS_DIR", "admin/models/");
    define("VIEWS_DIR", "admin/views/");
    define("CONTROLLERS_DIR", "admin/controllers/");

    // admin folders    
    define("ADMIN_DIR", "admin/");
    define("ADMIN_CONTROLLERS_DIR", "admin/controllers/");
    define("ADMIN_MODELS_DIR", "admin/models/");
    define("ADMIN_VIEWS_DIR", "admin/views/");
    define("ADMIN_VIEWS_IMAGES_DIR", "admin/views/aimg/");
    define("ADMIN_VIEWS_CSS_DIR", "admin/views/css/");
    define("ADMIN_VIEWS_JS_DIR", "admin/views/js/");
    define("ADMIN_LIBRARY_DIR", "admin/library/");
    define("ADMIN_JAVASCRIPT_DIR", "admin/library/web/js/");
    define("ADMIN_CSS_DIR", "admin/library/web/css/");

    // -- end