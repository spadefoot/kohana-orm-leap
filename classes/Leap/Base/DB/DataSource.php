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

namespace Leap\Base\DB {

	/**
	 * This class wraps the connection's configurations.
	 *
	 * @package Leap
	 * @category Connection
	 * @version 2013-02-03
	 *
	 * @abstract
	 */
	abstract class DataSource extends Core\Object {

		/**
		 * This constant represents a master instance of a database.
		 *
		 * @access public
		 * @const integer
		 */
		const MASTER_INSTANCE = 0;

		/**
		 * This constant represents a slave instance of a database.
		 *
		 * @access public
		 * @const integer
		 */
		const SLAVE_INSTANCE = 1;

		/**
		 * This variable stores the settings for the data source.
		 *
		 * @access protected
		 * @var array
		 */
		protected $settings;

		/**
		 * This function loads the configurations.
		 *
		 * @access public
		 * @param mixed $config                         the data source configurations
		 * @throws Throwable\InvalidArgument\Exception  indicates a data type mismatch
		 * @throws Throwable\InvalidProperty\Exception  indicates that the database group is undefined
		 */
		public function __construct($config) {
			if (empty($config)) {
				$id = 'database.default';
				if (($config = static::config($id)) === NULL) {
					throw new Throwable\InvalidProperty\Exception('Message: Unable to load data source. Reason: Database group :id is undefined.', array(':id' => $id));
				}
				$this->init($config, $id);
			}
			else if (is_string($config)) {
				$id = 'database.' . $config;
				if (($config = static::config($id)) === NULL) {
					throw new Throwable\InvalidProperty\Exception('Message: Unable to load data source. Reason: Database group :id is undefined.', array(':id' => $id));
				}
				$this->init($config, $id);
			}
			else if (is_array($config)) {
				$this->init($config);
			}
			else if (is_object($config) AND ($config instanceof DB\DataSource)) {
				$this->settings = $config->settings;
			}
			else {
				throw new Throwable\InvalidArgument\Exception('Message: Unable to load data source. Reason: Data type :type is mismatched.', array(':type' => gettype($config)));
			}
		}

		/**
		 * This function returns the value associated with the specified property.
		 *
		 * @access public
		 * @override
		 * @param string $name                          the name of the property
		 * @return mixed                                the value of the property
		 * @throws Throwable\InvalidProperty\Exception  indicates that the specified property is
		 *                                              either inaccessible or undefined
		 */
		public function __get($name) {
			switch ($name) {
				case 'cache':
				case 'charset':
				case 'database':
				case 'dialect':
				case 'driver':
				case 'host':
				case 'id':
				case 'password':
				case 'port':
				case 'type':
				case 'username':
				case 'role':
					return $this->settings[$name];
				default:
					throw new Throwable\InvalidProperty\Exception('Message: Unable to get the specified property. Reason: Property :key is either inaccessible or undefined.', array(':key' => $name));
			}
		}

		/**
		 * This function determines whether a specific property has been set.
		 *
		 * @access public
		 * @override
		 * @param string $name                          the name of the property
		 * @return boolean                              indicates whether the specified property
		 *                                              has been set
		 */
		public function __isset($name) {
			if (isset($this->settings[$name]) AND ($name != 'persistent')) {
				return (FALSE === empty($this->settings[$name]));
			}
			return FALSE;
		}

		/**
		 * This function handles the initialization of the data source's settings.
		 *
		 * @access protected
		 * @param array $settings                       the settings to be used
		 * @param string $id                            the data source's id
		 */
		protected function init($settings, $id = NULL) {
			$this->settings = array();

			if ($id === NULL) {
				// TODO Verify that config id does not already exist in the "database.php" config file.
				$this->settings['id'] = (isset($settings['id']))
					? (string) $settings['id']
					: 'unique_id.' . uniqid();
			}
			else {
				$this->settings['id'] = (string) $id;
			}

			$cache = array();
			$cache['enabled'] = (isset($settings['caching'])) ? (bool) $settings['caching'] : FALSE;
			$cache['lifetime'] = (class_exists('\\Kohana')) ? \Kohana::$cache_life : 60;
			$cache['force'] = FALSE;
			$this->settings['cache'] = (object) $cache;

			$this->settings['charset'] = (isset($settings['charset']))
				? (string) $settings['charset'] // e.g. utf8
				: '';

			$this->settings['database'] = (isset($settings['connection']['database']))
				? (string) $settings['connection']['database']
				: '';

			if (isset($settings['dialect'])) {
				$this->settings['dialect'] = (string) $settings['dialect'];
			}
			else if (isset($settings['type'])) { // deprecated
				$this->settings['dialect'] = (string) $settings['type'];
			}
			else {
				$this->settings['dialect'] = 'MySQL';
			}

			$this->settings['driver'] = (isset($settings['driver']))
				? (string) $settings['driver']
				: 'Standard';

			$this->settings['host'] = (isset($settings['connection']['hostname']))
				? (string) $settings['connection']['hostname']
				: '';

			$this->settings['persistent'] = (isset($settings['connection']['persistent']))
				? (bool) $settings['connection']['persistent']
				: FALSE;

			$this->settings['password'] = (isset($settings['connection']['password']))
				? (string) $settings['connection']['password']
				: '';

			$this->settings['port'] = (isset($settings['connection']['port']))
				? (string) $settings['connection']['port']
				: '';

			$this->settings['type'] = (isset($settings['type']))
				? (string) $settings['type']
				: 'SQL'; // e.g. SQL, NoSQL, LDAP

			$this->settings['username'] = (isset($settings['connection']['username']))
				? (string) $settings['connection']['username']
				: '';

			$this->settings['role'] = (isset($settings['connection']['role']))
				? (string) $settings['connection']['role']
				: '';
		}

		/**
		 * This function determines whether the connection is persistent.
		 *
		 * @access public
		 * @return boolean                              whether the connection is persistent
		 */
		public function is_persistent() {
			return $this->settings['persistent'];
		}

		////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

		/**
		 * This variable stores an array of singleton instances of this class.
		 *
		 * @access protected
		 * @static
		 * @var array
		 */
		protected static $instances = array();

		/**
		 * This function returns configurations settings for the specified path.
		 *
		 * @access public
		 * @static
		 * @param string $path                          the path to be used
		 * @return mixed                                the configuration settings for the
		 *                                              specified path
		 */
		public static function config($path) {
			return \Kohana::$config->load($path);
		}

		/**
		 * This function returns a singleton instance of this class.
		 *
		 * @access public
		 * @static
		 * @param mixed $config                         the data source configurations
		 * @return DB\DataSource                        a singleton instance of this class
		 */
		public static function instance($config = 'default') {
			if (is_string($config)) {
				if ( ! isset(static::$instances[$config])) {
					static::$instances[$config] = new DB\DataSource($config);
				}
				return static::$instances[$config];
			}
			else if (is_object($config) AND ($config instanceof DB\DataSource)) {
				$id = $config->id;
				if ( ! isset(static::$instances[$id])) {
					static::$instances[$id] = $config;
				}
				return $config;
			}
			else if (is_array($config) AND isset($config['id'])) {
				$id = $config['id'];
				if ( ! isset(static::$instances[$id])) {
					static::$instances[$id] = new DB\DataSource($config);
				}
				return static::$instances[$id];
			}
			else {
				$data_source = new DB\DataSource($config);
				static::$instances[$data_source->id] = $data_source;
				return $data_source;
			}
		}

	}

}