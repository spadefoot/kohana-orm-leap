<?php defined('SYSPATH') OR die('No direct script access.');

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
 * This class wraps the connection's configurations.
 *
 * @package Leap
 * @category Connection
 * @version 2012-11-29
 *
 * @abstract
 */
abstract class Base_DB_DataSource extends Core_Object {

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
	 * @param mixed $config                          the data source configurations
	 * @throws Throwable_InvalidArgument_Exception      indicates that there is a data type mismatch
	 * @throws Throwable_InvalidProperty_Exception      indicates that the database group is undefined
	 */
	public function __construct($config) {
		if (empty($config)) {
			$id = 'database.default';
			if (($config = Kohana::$config->load($id)) === NULL) {
				throw new Throwable_InvalidProperty_Exception('Message: Unable to load data source. Reason: Database group :id is undefined.', array(':id' => $id));
			}
			$this->init($config, $id);
		}
		else if (is_string($config)) {
			$id = 'database.' . $config;
			if (($config = Kohana::$config->load($id)) === NULL) {
				throw new Throwable_InvalidProperty_Exception('Message: Unable to load data source. Reason: Database group :id is undefined.', array(':id' => $id));
			}
			$this->init($config, $id);
		}
		else if (is_array($config)) {
			$this->init($config);
		}
		else if (is_object($config) AND ($config instanceof DB_DataSource)) {
			$this->settings = $config->settings;
		}
		else {
			throw new Throwable_InvalidArgument_Exception('Message: Unable to load data source. Reason: Data type :type is mismatched.', array(':type' => gettype($config)));
		}
	}

	/**
	 * This function returns the value associated with the specified property.
	 *
	 * @access public
	 * @param string $name                          the name of the property
	 * @return mixed                                the value of the property
	 * @throws Throwable_InvalidProperty_Exception     indicates that the specified property is
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
			break;
			default:
				throw new Throwable_InvalidProperty_Exception('Message: Unable to get the specified property. Reason: Property :key is either inaccessible or undefined.', array(':key' => $name));
			break;
		}
	}

	/**
	 * This function handles the initialization of the data source's settings.
	 *
	 * @access protected
	 * @param array $settings                           the settings to be used
	 * @param string $id                                the data source's id
	 */
	protected function init($settings, $id = NULL) {
		$this->settings = array();

		if ($id === NULL) {
			$this->settings['id'] = (isset($settings['id']))
				? (string) $settings['id']
				: 'unique_id.' . uniqid();
		}
		else {
			$this->settings['id'] = (string) $id;
		}

		$cache = array();
		$cache['enabled'] = (isset($settings['caching'])) ? (bool) $settings['caching'] : FALSE;
		$cache['lifetime'] = Kohana::$cache_life;
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
			$this->settings['dialect'] = 'mysql';
		}

		$this->settings['driver'] = (isset($settings['driver']))
			? (string) $settings['driver']
			: 'standard';

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
			: 'sql'; // e.g. sql, nosql, ldap

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

}
?>