<?php

/**
 * Copyright © 2011–2013 Spadefoot Team.
 *
 * Unless otherwise noted, LEAP is licensed under the Apache License,
 * Version 2.0 (the "License"); you may not use this file except in
 * compliance with the License. You may obtain a copy of the License
 * at:
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Leap\Base\DB\Connection {

	/**
	 * This class manages the caching of database connections.
	 *
	 * @package Leap
	 * @category Connection
	 * @version 2013-03-19
	 *
	 * @see http://stackoverflow.com/questions/1353822/how-to-implement-database-connection-pool-in-php
	 * @see http://www.webdevelopersjournal.com/columns/connection_pool.html
	 * @see http://sourcemaking.com/design_patterns/object_pool
	 * @see http://www.snaq.net/java/DBPool/
	 * @see http://www.koders.com/java/fid4840DD8CBE361AA355537C8C9332D92F226F19C1.aspx?s=Q
	 *
	 * @abstract
	 */
	abstract class Pool extends Core\Object implements \Countable {

		/**
		 * This variable stores the lookup table.
		 *
		 * @access protected
		 * @var array
		 */
		protected $lookup = array();

		/**
		 * This variable stores the pooled connections.
		 *
		 * @access protected
		 * @var array
		 */
		protected $pool = array();

		/**
		 * This variable stores the settings for the connection pool.
		 *
		 * @access protected
		 * @var array
		 */
		protected $settings = array();

		/**
		 * This function prevents the class from being cloned.
		 *
		 * @access protected
		 * @override
		 */
		protected function __clone() {}

		/**
		 * This constructor creates an instance of this class.
		 *
		 * @access protected
		 */
		protected function __construct() {
			$this->settings['max_size'] = PHP_INT_MAX; // the maximum number of connections that may be held in the pool
		}

		/**
		 * This function returns the value associated with the specified property.
		 *
		 * @access public
		 * @override
		 * @param string $key                           the name of the property
		 * @return mixed                                the value of the property
		 * @throws Throwable\InvalidProperty\Exception  indicates that the specified property is
		 *                                              either inaccessible or undefined
		 */
		public function __get($key) {
			switch ($key) {
				case 'max_size':
					return $this->settings[$key];
				default:
					throw new Throwable\InvalidProperty\Exception('Message: Unable to get the specified property. Reason: Property :key is either inaccessible or undefined.', array(':key' => $key));
				break;
			}
		}

		/**
		 * This function sets the value for the specified key.
		 *
		 * @access public
		 * @override
		 * @param string $key                           the name of the property
		 * @param mixed $value                          the value of the property
		 * @throws Throwable\InvalidProperty\Exception  indicates that the specified property is
		 *                                              either inaccessible or undefined
		 */
		public function __set($key, $value) {
			switch ($key) {
				case 'max_size':
					$this->settings[$key] = abs( (int) $value);
				break;
				default:
					throw new Throwable\InvalidProperty\Exception('Message: Unable to set the specified property. Reason: Property :key is either inaccessible or undefined.', array(':key' => $key));
				break;
			}
		}

		/**
		 * This function adds an existing connection to the connection pool.
		 *
		 * @access public
		 * @param DB\Connection\Driver $connection      the connection to be added
		 * @return boolean                              whether the connection was added
		 * @throws Throwable\Database\Exception         indicates that no new connections
		 *                                              can be added
		 */
		public function add_connection(DB\Connection\Driver $connection) {
			if ($connection !== NULL) {
				$connection_id = $connection->__hashCode();
				if ( ! isset($this->lookup[$connection_id])) {
					if ($this->count() >= $this->settings['max_size']) {
						throw new Throwable\Database\Exception('Message: Failed to add connection. Reason: Exceeded maximum number of connections that may be held in the pool.', array(':source' => $connection->data_source->id));
					}
					$data_source_id = $connection->data_source->id;
					$this->pool[$data_source_id][$connection_id] = $connection;
					$this->lookup[$connection_id] = $data_source_id;
				}
				return TRUE;
			}
			return FALSE;
		}

		/**
		 * This function returns the number of connections in the connection pool.
		 *
		 * @access public
		 * @override
		 * @return integer                              the number of connections in the
		 *                                              connection pool
		 */
		public function count() {
			return count($this->lookup);
		}

		/**
		 * This function returns the appropriate connection from the pool. When there are
		 * multiple connections created from the same data source, the last opened connection
		 * will be returned when $new is set to "FALSE."
		 *
		 * @access public
		 * @param mixed $config                         the data source configurations
		 * @param boolean $new                          whether to create a new connection
		 * @return DB\Connection\Driver                 the appropriate connection
		 * @throws Throwable\Database\Exception         indicates that no new connections
		 *                                              can be added
		 */
		public function get_connection($config = 'default', $new = FALSE) {
			$data_source = DB\DataSource::instance($config);
			if (isset($this->pool[$data_source->id]) AND ! empty($this->pool[$data_source->id])) {
				if ($new) {
					foreach ($this->pool[$data_source->id] as $connection) {
						if ( ! $connection->is_connected()) {
							$connection->open();
							return $connection;
						}
					}
				}
				else {
					$connection = end($this->pool[$data_source->id]);
					do {
						if ($connection->is_connected()) {
							reset($this->pool[$data_source->id]);
							return $connection;
						}
					}
					while ($connection = prev($this->pool[$data_source->id]));
					$connection = end($this->pool[$data_source->id]);
					reset($this->pool[$data_source->id]);
					$connection->open();
					return $connection;
				}
			}
			if ($this->count() >= $this->settings['max_size']) {
				throw new Throwable\Database\Exception('Message: Failed to create new connection. Reason: Exceeded maximum number of connections that may be held in the pool.', array(':source' => $data_source, ':new' => $new));
			}
			$connection = DB\Connection\Driver::factory($data_source);
			$connection->open();
			$connection_id = $connection->__hashCode();
			$this->pool[$data_source->id][$connection_id] = $connection;
			$this->lookup[$connection_id] = $data_source->id;
			return $connection;
		}

		/**
		 * This function releases the specified connection within the connection pool.  The
		 * connection will then be allowed to close via its destructor when completely unset.
		 *
		 * @access public
		 * @param DB\Connection\Driver $connection      the connection to be released
		 */
		public function release(DB\Connection\Driver $connection) {
			if ($connection !== NULL) {
				$connection_id = $connection->__hashCode();
				if (isset($this->lookup[$connection_id])) {
					$data_source_id = $this->lookup[$connection_id];
					unset($this->pool[$data_source_id][$connection_id]);
					unset($this->lookup[$connection_id]);
				}
			}
		}

		//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

		/**
		 * This variable stores a singleton instance of this class.
		 *
		 * @access protected
		 * @static
		 * @var DB\Connection\Pool
		 */
		protected static $instance = NULL;

		/**
		 * This function is automatically called at the time of shutdown to release all
		 * connections within the connection pool.
		 *
		 * @access public
		 * @static
		 */
		public static function autorelease() {
			$instance = static::instance();
			$instance->lookup = array();
			$instance->pool = array();
		}

		/**
		 * This function returns a singleton instance of this class.
		 *
		 * @access public
		 * @static
		 * @return DB\Connection\Pool               	a singleton instance of this class
		 */
		public static function instance() {
			if (static::$instance === NULL) {
				register_shutdown_function(array('\\Leap\\DB\\Connection\\Pool', 'autorelease'));
				static::$instance = new DB\Connection\Pool();
			}
			return static::$instance;
		}

	}

}