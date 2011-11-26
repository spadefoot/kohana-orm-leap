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
 * This class manages the caching of database connections.
 *
 * @package Leap
 * @category Connection
 * @version 2011-06-20
 *
 * @see http://stackoverflow.com/questions/1353822/how-to-implement-database-connection-pool-in-php
 * @see http://www.webdevelopersjournal.com/columns/connection_pool.html
 * @see http://sourcemaking.com/design_patterns/object_pool
 *
 * @abstract
 */
class DB_Connection_Pool extends Base_DB_Connection_Pool { }
?>