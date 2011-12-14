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
 * @version 2011-12-13
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
			if ( ! isset($this->settings['id'])) {
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
	 * This function returns the value associated with the specified property.
	 *
	 * @access public
	 * @param string $name                          the name of the property
	 * @return mixed                                the value of the property
	 * @throws Kohana_InvalidProperty_Exception     indicates that the specified property is
	 *                                              either inaccessible or undefined
	 */
	public function __get($name) {
        switch ($name) {
            case 'id':
                return $this->settings['id'];
            break;
            case 'cache':
                // TODO make this modifiable
                $cache = array();
                $cache['enabled'] = (isset($this->settings['caching'])) ? (bool) $this->settings['caching'] : FALSE;
                $cache['lifetime'] = Kohana::$cache_life;
                $cache['force'] = FALSE;
                return (object) $cache;
            break;
            case 'charset':
                return (isset($this->settings['charset'])) ? $this->settings['charset'] : 'utf8';
            break;
            case 'dialect':
            case 'type':
                return (isset($this->settings['type'])) ? $this->settings['type'] : 'mysql';
            break;
            case 'driver':
		        return (isset($this->settings['driver'])) ? $this->settings['driver'] : 'standard';
		    break;
            case 'database':
            case 'password':
            case 'port':
            case 'username':
		        return (isset($this->settings['connection'][$name])) ? $this->settings['connection'][$name] : '';
		    break;
            case 'host':
		        return (isset($this->settings['connection']['hostname'])) ? $this->settings['connection']['hostname'] : 'localhost';
		    break;
            default:
                throw new Kohana_InvalidProperty_Exception('Message: Unable to get the specified property. Reason: Property :key is either inaccessible or undefined.', array(':key' => $name));
            break;
        }
    }

	/**
	 * This function determines whether the connection is persistent.
	 *
	 * @access public
	 * @return boolean                              whether the connection is persistent
	 */
	public function is_persistent() {
		if (isset($this->settings['persistent'])) {
			return (bool) $this->settings['persistent'];
		}
		return FALSE;
	}

}
?>