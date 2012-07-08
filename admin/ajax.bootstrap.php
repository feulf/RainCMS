<?php

    // include the Loader class
    require_once LIBRARY_DIR . "RainLoaderAdmin.php";

    // start the loader
    $loader = new RainLoaderAdmin();

    RainLoaderAdmin::configure("controller_extension", AJAX_CONTROLLER_EXTENSION);
    RainLoaderAdmin::configure("controller_class_name", AJAX_CONTROLLER_CLASS_NAME);

    // enable the ajax mode
    $loader->ajax_mode();

    $loader->init_session();
    $loader->init_db();
    $loader->init_settings();
    $loader->init_language();
    $loader->auth_user();
    $loader->auto_load_controller();

    // -- end