<?

$db = array(
    // development database (default)
    'dev' => array(
        'driver' => 'mysql',
        'hostname' => 'localhost',
        'username' => 'root',
        'password' => 'root',
        'database' => 'rain'
    ),
    //production database (live website)
    'raincms.com' => array(
        'driver' => '',
        'hostname' => '',
        'username' => '',
        'password' => '',
        'database' => ''
    )
);

if (isset($_SERVER['SERVER_NAME']) && array_key_exists($_SERVER['SERVER_NAME'], $db)) 
    define("DEFAULT_CONNECTION_NAME", $_SERVER['SERVER_NAME']);
else
    define("DEFAULT_CONNECTION_NAME", "dev");


 if (!defined("DB_PREFIX"))
    define ("DB_PREFIX", "");
// -- end