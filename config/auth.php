<?php defined("SYSPATH") OR die('No direct script access.');

$config = array(
	'driver' => 'leap',
	'hash_method' => 'sha256',
	'hash_key' => '',
	'salt_pattern' => '',
	'lifetime' => 1209600,
	'session_key' => 'user',
	'users' => array(),
	
	'activation' => TRUE,
	'login_with_email' => TRUE,
	'login_with_username' => TRUE,
	'models' => array(
		'role' => 'Role',
		'user' => 'User',
		'token' => 'User_Token',
	),
);

return $config;
?>