<?php

    require LIBRARY_DIR . "RainLoaderAdmin.php";

    // init loader
    $loader = new RainLoaderAdmin();

    $loader->init_session();

    $loader->init_db();

    $loader->init_settings();

    $loader->init_language();

    $loader->auth_user();

    $loader->init_theme();

    $loader->load_head();

    if (User::is_admin()) {
        $loader->set_layout('layout.index');
        $loader->auto_load_controller();
        $loader->load_menu();
    } else {
        $loader->set_layout('layout.login');        // set page layout
        $loader->controller("login", "index");
    }

    $loader->load_menu();

    $loader->draw();



    // -- end