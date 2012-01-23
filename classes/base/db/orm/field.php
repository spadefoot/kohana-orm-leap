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
 * This class represents a field in a database table.
 *
 * @package Leap
 * @category ORM
 * @version 2012-01-20
 *
 * @abstract
 *
 * @see http://www.firebirdsql.org/manual/migration-mssql-data-types.html
 * @see http://msdn.microsoft.com/en-us/library/aa258271%28v=sql.80%29.aspx
 * @see http://kimbriggs.com/computers/computer-notes/mysql-notes/mysql-data-types-50.file
 */
abstract class Base_DB_ORM_Field extends Kohana_Object {

	/**
	 * This variable stores a reference to the implementing model.
	 *
	 * @access protected
	 * @var DB_ORM_Model
	 */
	protected $model;

	/**
	 * This variable stores the field's metadata.
	 *
	 * @access protected
	 * @var array
	 */
	protected $metadata;

	/**
	 * This variable stores the field's value.
	 *
	 * @access protected
	 * @var mixed
	 */
	protected $value;

	/**
	 * This constructor initializes the class.
	 *
	 * @access public
	 * @param DB_ORM_Model $model                   a reference to the implementing model
	 * @param string $type                          the equivalent PHP data type
	 *
	 * @see http://php.net/manual/en/function.gettype.php
	 */
	public function __construct(DB_ORM_Model $model, $type) {
		$this->model = $model;
		$this->metadata = array();
		$this->metadata['type'] = $type;
		$this->metadata['savable'] = TRUE;
		$this->metadata['modified'] = FALSE;
		$this->metadata['nullable'] = TRUE;
		$this->metadata['default'] = NULL;
		$this->value = NULL;
	}

	/**
	 * This function returns the value associated with the specified property.
	 *
	 * @access public
	 * @param string $key                           the name of the property
	 * @return mixed                                the value of the property
	 * @throws Kohana_InvalidProperty_Exception     indicates that the specified property is
	 *                                              either inaccessible or undefined
	 */
	public function __get($key) {
		switch ($key) {
			case 'value':
				return $this->value;
			break;
			default:
				if (isset($this->metadata[$key])) { return $this->metadata[$key]; }
			break;
		}
		throw new Kohana_InvalidProperty_Exception('Message: Unable to get the specified property. Reason: Property :key is either inaccessible or undefined.', array(':key' => $key));
	}

	/**
	 * This function sets the value for the specified key.
	 *
	 * @access public
	 * @param string $key                           the name of the property
	 * @param mixed $value                          the value of the property
	 * @throws Kohana_InvalidProperty_Exception     indicates that the specified property is
	 *                                              either inaccessible or undefined
	 */
	public function __set($key, $value) {
		switch ($key) {
			case 'value':
				if ( ! is_null($value)) {
					settype($value, $this->metadata['type']);
					$this->validate($value);
					$this->value = $value;
				}
				else {
					$this->value = $this->metadata['default'];
				}
				$this->metadata['modified'] = TRUE;
			break;
			case 'modified':
				$this->metadata['modified'] = (bool) $value;
			break;
			default:
				throw new Kohana_InvalidProperty_Exception('Message: Unable to set the specified property. Reason: Property :key is either inaccessible or undefined.', array(':key' => $key, ':value' => $value));
			break;
		}
	}

	/**
	 * This function validates the specified value against any constraints.
	 *
	 * @access protected
	 * @param mixed $value                          the value to be validated
	 * @return boolean                              whether the specified value validates
	 */
	protected function validate($value) {
		if (isset($this->metadata['enum']) && !in_array($value, $this->metadata['enum'])) {
			return FALSE;
		}
		if (isset($this->metadata['callback']) && call_user_func(array($this->model, $this->metadata['callback']), $value)) {
			return FALSE;
		}
		return TRUE;
	}

	/**
	 * This function resets the field's value.
	 *
	 * @access public
	 */
	public function reset() {
		$this->value = $this->metadata['default'];
		$this->metadata['modified'] = FALSE;
	}

}
?>