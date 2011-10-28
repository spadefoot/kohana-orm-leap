<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Copyright 2011 Spadefoot
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

/**
 * Specifies the driver configurations for the Mailer class.
 *
 * @package Leap
 * @category Config
 * @version 2011-06-20
 */
$config = array();

$config['driver'] = array(
    //'db2' => 'pdo',
    'db2' => 'std',
    //'firebird' => 'pdo',
    'firebird' => 'std',
    //'mssql' => 'pdo',
    'mssql' => 'std',
    //'mysql' => 'pdo',
    'mysql' => 'std',
    //'oracle' => 'pdo',
    'oracle' => 'std',
    //'postgresql' => 'pdo',
    'postgresql' => 'std',
    //'sqlite'=> 'pdo',
    'sqlite'=> 'std',
);

return $config;
?>