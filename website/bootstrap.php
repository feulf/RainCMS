<?php

    require LIBRARY_DIR . "RainLoader.php";

    // init loader
    $loader = new RainLoader();
    
    $loader->load_plugins();

    $loader->init_session();

    $loader->init_db();

    $loader->init_settings();

    $loader->init_language();

    $loader->auth_user();

    $loader->init_content();

    $loader->init_theme();

    $loader->load_head();

    $loader->load_module();

    $loader->load_blocks();

    $loader->load_menu();

    $loader->draw();




// -- end