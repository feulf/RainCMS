<?php

    // development database (default)
    $db['dev']['driver'] = 'mysql';
    $db['dev']['hostname'] = 'localhost';
    $db['dev']['username'] = 'root';
    $db['dev']['password'] = 'root';
    $db['dev']['database'] = 'rain';

    // production database (live website)
    $db['www.raincms.com']['driver'] = '';
    $db['www.raincms.com']['hostname'] = '';
    $db['www.raincms.com']['username'] = '';
    $db['www.raincms.com']['password'] = '';
    $db['www.raincms.com']['database'] = '';

    if (!defined("DB_PREFIX"))
        define("DB_PREFIX", "");
    if (isset($_SERVER['SERVER_NAME']) && array_key_exists($_SERVER['SERVER_NAME'], $db)) {
        define("DEFAULT_CONNECTION_NAME", $_SERVER['SERVER_NAME']);
    } else {
        define("DEFAULT_CONNECTION_NAME", "dev");
    }

    // -- end