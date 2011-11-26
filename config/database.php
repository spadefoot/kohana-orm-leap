<?php defined('SYSPATH') or die('No direct access allowed.');

$config = array();

$config['default'] = array(
	'type'       => 'mysql',
	'connection' => array(
		/**
		 * The following options are available:
		 *
		 * string   hostname     server hostname, or socket
		 * string   port         port number
		 * string   database     database name
		 * string   username     database username
		 * string   password     database password
		 * boolean  persistent   use persistent connections?
		 */
		'hostname'      => 'localhost',
		//'port'          => '',
		'database'      => 'kohana',
		'username'      => FALSE,
		'password'      => FALSE,
		'persistent'    => FALSE,
	),
),

return $config;
?>