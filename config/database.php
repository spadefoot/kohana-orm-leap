<?php

$config = array();

$config['default'] = array(
	'type'          => 'SQL',       // string (e.g. LDAP, NoSQL, or SQL)
	'dialect'       => 'MySQL',     // string (e.g. DB2, Drizzle, Firebird, MariaDB, MsSQL, MySQL, Oracle, PostgreSQL, or SQLite)
	'driver'        => 'Standard',  // string (e.g. Improved, PDO, Standard, or TCP)
	'connection'    => array(
		'persistent'    => FALSE,       // boolean
		'hostname'      => 'localhost', // string
		'port'          => '',          // string
		'database'      => '',          // string
		'username'      => 'root',      // string
		'password'      => 'root',      // string
		'role'          => '',          // string
	),
	'caching'       => FALSE,       // boolean
	'charset'       => 'utf8',      // string
);

return $config;
