<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Copyright 2011-2012 Spadefoot
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
 * @version 2011-12-09
 *
 * @see http://stackoverflow.com/questions/1353822/how-to-implement-database-connection-pool-in-php
 * @see http://www.webdevelopersjournal.com/columns/connection_pool.html
 * @see http://sourcemaking.com/design_patterns/object_pool
 *
 * @abstract
 */
abstract class Base_DB_Connection_Pool extends Kohana_Object {

	/**
	 * This variable stores a singleton instance of this class.
	 *
	 * @access protected
	 * @static
	 * @var DB_Connection_Pool
	 */
	protected static $instance = NULL;

	/**
	 * This variable stores the connection pool.
	 *
	 * @access protected
	 * @static
	 * @var array
	 */
	protected static $pool = array();

	/**
	* This variable stores the id of the current connection.
	*
	* @access protected
	* @var string
	*/
	protected $id = NULL;

	/**
	 * This constructor creates an instance of this class.
	 *
	 * @access protected
	 */
	protected function __construct() {}

	/**
	 * This function prevents the class from being cloned.
	 *
	 * @access protected
	 */
	protected function __clone() {}

	/**
	 * This function returns the appropriate connection from the pool.
	 *
	 * @access public
	 * @param DB_DataSource $source             the data source configurations
	 * @return DB_Connection			        the appropriate connection
	 */
	public function get_connection($source = 'default') {
		if ( ! (is_object($source) && ($source instanceof DB_DataSource))) {
			$source = new DB_DataSource($source);
		}
		$id = $source->id;
		if ($id != $this->id) {
			if ( ! is_null($this->id)) {
				self::$pool[$this->id]->close();
			}
			if ( ! isset(self::$pool[$id]))	{
				self::$pool[$id] = DB_Connection::factory($source);
			}
			$this->id = $id;
		}
		if ( ! self::$pool[$id]->is_connected()) {
			self::$pool[$id]->open();
		}
		return self::$pool[$id];
	}

	/**
	* This function frees the current connection by closing it.
	*
	* @access public
	*/
	public function release() {
		if ( ! is_null($this->id)) {
			self::$pool[$this->id]->close();
		}
	}

	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This function is automatically called at the time of shutdown and closes any
	 * open connections.
	 *
	 * @access public
	 * @static
	 */
	public static function autorelease() {
		foreach (self::$pool as $connection) {
			if ($connection->is_connected()) {
				$connection->close();
			}
		}
	}

	/**
	 * This function returns a singleton instance of this class.
	 *
	 * @access public
	 * @static
	 * @return DB_Connection_Pool               a singleton instance of this class
	 */
	public static function instance() {
		if (is_null(self::$instance)) {
			register_shutdown_function(array('DB_Connection_Pool', 'autorelease'));
			self::$instance = new DB_Connection_Pool();
		}
		return self::$instance;
	}

}
?>