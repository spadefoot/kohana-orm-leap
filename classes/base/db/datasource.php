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
 * This class wraps the connection's configurations.
 *
 * @package Leap
 * @category Connection
 * @version 2011-12-11
 *
 * @abstract
 */
abstract class Base_DB_DataSource extends Kohana_Object {

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
	 * @throws Kohana_InvalidArgument_Exception      indicates that there is a data type mismatch
	 * @throws Kohana_InvalidProperty_Exception      indicates that the connection string is invalid
	 */
	public function __construct($config) {
		if (empty($config)) {
			$id = 'database.default';
			$this->settings = Kohana::$config->load($id);
			$this->settings['id'] = $id;
		}
		else if (is_string($config)) {
			$id = 'database.' . $config;
			$this->settings = Kohana::$config->load($id);
			if ($this->settings === NULL) {
				throw new Kohana_InvalidProperty_Exception('Message: Unable to load data source. Reason: Database group :group is undefined.', array(':group' => $config));
			}
			$this->settings['id'] = $id;
		}
		else if (is_array($config)) {
			$this->settings = $config;
			if (!isset($this->settings['id'])) {
				$this->settings['id'] = 'unique_id.' . uniqid();
			}
		}
		else if (is_object($config) && ($config instanceof DB_DataSource)) {
			$this->settings = $config->settings;
		}
		else {
			throw new Kohana_InvalidArgument_Exception('Message: Unable to load data source. Reason: Data type :type is mismatched.', array(':type' => gettype($config)));
		}
	}

	/**
	 * This function returns the database.
	 *
	 * @access public
	 * @return string            					the database
	 */
	public function get_database() {
		return (isset($this->settings['connection']['database'])) ? $this->settings['connection']['database'] : '';
	}

	/**
	 * This function returns the database driver to be used.
	 *
	 * @access public
	 * @return string                               the database driver to be used
	 */
	public function get_driver() {
		return (isset($this->settings['driver'])) ? $this->settings['driver'] : 'std';
	}

	/**
	 * This function returns the host server.
	 *
	 * @access public
	 * @return string            					the host server
	 */
	public function get_host_server() {
		return (isset($this->settings['connection']['hostname'])) ? $this->settings['connection']['hostname'] : 'localhost';
	}

	/**
	 * This function returns the name of the data source configuration group.
	 *
	 * @access public
	 * @return string								the name of the data source configuration
	 * 												group
	 */
	public function get_id() {
		return $this->settings['id'];
	}

	/**
	 * This function returns the password.
	 *
	 * @access public
	 * @return string            					the password
	 */
	public function get_password() {
		return (isset($this->settings['connection']['password'])) ? $this->settings['connection']['password'] : '';
	}

	/**
	 * This function returns the port.
	 *
	 * @access public
	 * @return string            					the port
	 */
	public function get_port() {
		return (isset($this->settings['connection']['port'])) ? $this->settings['connection']['port'] : '';
	}

	/**
	 * This function returns the resource/database type.
	 *
	 * @access public
	 * @return string            					the resource/database type
	 */
	public function get_resource_type() {
		return (isset($this->settings['type'])) ? strtolower($this->settings['type']) : 'mysql';
	}

	/**
	 * This function returns the username.
	 *
	 * @access public
	 * @return string            					the username
	 */
	public function get_username() {
		return (isset($this->settings['connection']['username'])) ? $this->settings['connection']['username'] : '';
	}

	/**
	 * This function determines whether the connection is persistent.
	 *
	 * @access public
	 * @return boolean                              whether the connection is persistent
	 */
	public function is_persistent() {
		if (isset($this->settings['persistent'])) {
			return (boolean)$this->settings['persistent'];
		}
		return FALSE;
	}

}
?>