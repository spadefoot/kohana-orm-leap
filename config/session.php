<?php defined('SYSPATH') or die('No direct script access');

$config = array();

$config['leap'] = array(
    'group' => 'default',
    'lifetime' => 43200,
    'name' => 'session_leap',
    'table' => 'Session',
);

return $config;
?>