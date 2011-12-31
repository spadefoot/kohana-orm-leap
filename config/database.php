<?php defined('SYSPATH') or die('No direct access allowed.');

$config = array();

$config['default'] = array(
    'type'          => 'mysql',     // string (e.g. db2, drizzle, firebird, mariadb, mssql, mysql, oracle, postgresql, or sqlite)
    'driver'        => 'standard',  // string (e.g. standard, improved, or pdo)
    'connection'    => array(
        'persistent'    => FALSE,       // boolean
        'hostname'      => 'localhost', // string
        'port'          => '',          // string
        'database'      => '',          // string
        'username'      => 'root',      // string
        'password'      => 'root',      // string
    ),
    'caching'       => FALSE,       // boolean
    'charset'       => 'utf8',      // string
    'profiling'     => FALSE,       // boolean
    'table_prefix'  => '',          // string
);

return $config;
?>