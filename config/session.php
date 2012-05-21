<?php defined('SYSPATH') OR die('No direct script access.');

$config = array();

$config['leap'] = array(
	'group'         => 'default',       // string
	'lifetime'      => 43200,           // integer
	'name'          => 'session_leap',  // string
	'table'         => 'Session',       // string
);

return $config;
?>