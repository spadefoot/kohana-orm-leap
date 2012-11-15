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
 * This class represents a relation in a database table.
 *
 * @package Leap
 * @category ORM
 * @version 2012-11-14
 *
 * @abstract
 */
abstract class Base_DB_ORM_Relation extends Core_Object {

	/**
	 * This variable stores a reference to the implementing model.
	 *
	 * @access protected
	 * @var DB_ORM_Model
	 */
	protected $model;

	/**
	 * This variable stores the relation's metadata.
	 *
	 * @access protected
	 * @var array
	 */
	protected $metadata;

	/**
	 * This variable stores the relation's corresponding model(s).
	 *
	 * @access protected
	 * @var mixed
	 */
	protected $cache;

	/**
	 * This constructor initializes the class.
	 *
	 * @access public
	 * @param DB_ORM_Model $model                   a reference to the implementing model
	 * @param string $type                          the type of relationship
	 */
	public function __construct(DB_ORM_Model $model, $type) {
		$this->model = $model;
		$this->metadata = array();
		$this->metadata['type'] = $type;
		$this->cache = NULL;
	}

	/**
	 * This function returns the value associated with the specified property.
	 *
	 * @access public
	 * @param string $key                           the name of the property
	 * @return mixed                                the value of the property
	 * @throws Throwable_InvalidProperty_Exception     indicates that the specified property is
	 *                                              either inaccessible or undefined
	 */
	public function __get($key) {
		switch ($key) {
			case 'result':
				if ($this->cache === NULL) {
					$this->cache = $this->load();
				}
				return $this->cache;
			break;
			default:
				if (isset($this->metadata[$key])) { return $this->metadata[$key]; }
			break;
		}
		throw new Throwable_InvalidProperty_Exception('Message: Unable to get the specified property. Reason: Property :key is either inaccessible or undefined.', array(':key' => $key));
	}

	/**
	 * This function loads the corresponding model(s).
	 *
	 * @access protected
	 * @abstract
	 * @return mixed								the corresponding model(s)
	 */
	protected abstract function load();

	/**
	 * This function resets the relation's cache to NULL.
	 *
	 * @access public
	 */
	public function reset() {
		$this->cache = NULL;
	}

}
?>