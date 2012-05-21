<?php defined("SYSPATH") OR die('No direct script access.');

$config = array(
	'driver'                => 'leap',      // string
	'hash_method'           => 'sha256',    // string
	'hash_key'              => '',          // string
	'salt_pattern'          => '',          // string
	'lifetime'              => 1209600,     // integer
	'session_key'           => 'user',      // string
	'users'                 => array(),     // array
	'activation'            => TRUE,        // boolean
	'login_with_email'      => TRUE,        // boolean
	'login_with_username'   => TRUE,        // boolean
	'models'                => array(       // array
		'role'                  => 'Role',          // string
		'user'                  => 'User',          // string
		'token'                 => 'User_Token',    // string
	),
);

return $config;
?>