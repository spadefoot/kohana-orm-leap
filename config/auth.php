<?php defined("SYSPATH") or die('No direct script access.');

return array(
	'driver' => 'leap',
	'hash_method' => 'sha256',
	'hash_key' => '',
	'salt_pattern' => '',
	'lifetime' => 1209600,
	'session_key' => 'user',
	'users' => array(),
	
	'activation' => TRUE,
	'email_activation' => TRUE,
	'login_with_email' => TRUE,
	'login_with_username' => TRUE,
	'models' => array(
		'role' => 'Role',
		'user' => 'User',
		'token' => 'User_Token',
	),
	'columns' => array(
		'role_name' => 'rName',
		'token' => 'utToken',
		'user_id' => 'uID',
		'user_username' => 'uUsername',
		'user_email' => 'uEmail',
	),
);