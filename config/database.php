<?php defined('SYSPATH') OR die('No direct script access.');

$config = array();

$config['default'] = array(
	'type'          => 'sql',       // string (e.g. sql, nosql, or ldap)
	'dialect'       => 'MySQL',     // string (e.g. DB2, Drizzle, Firebird, MariaDB, MsSQL, MySQL, Oracle, PostgreSQL, or SQLite)
	'driver'        => 'Standard',  // string (e.g. Standard, Improved, or PDO)
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
);

return $config;
?>