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
 * This class represents a field in a database table.
 *
 * @package Leap
 * @category ORM
 * @version 2012-11-14
 *
 * @abstract
 *
 * @see http://www.firebirdsql.org/manual/migration-mssql-data-types.html
 * @see http://msdn.microsoft.com/en-us/library/aa258271%28v=sql.80%29.aspx
 * @see http://kimbriggs.com/computers/computer-notes/mysql-notes/mysql-data-types-50.file
 */
abstract class Base_DB_ORM_Field extends Core_Object {

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
		$this->metadata['control'] = 'auto';
		$this->metadata['default'] = NULL;
		$this->metadata['modified'] = FALSE;
		$this->metadata['nullable'] = TRUE;
		$this->metadata['savable'] = TRUE;
		$this->metadata['type'] = $type;
		$this->value = NULL;
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
			case 'value':
				return $this->value;
			break;
			default:
				if (array_key_exists($key, $this->metadata)) {
					return $this->metadata[$key];
				}
			break;
		}
		throw new Throwable_InvalidProperty_Exception('Message: Unable to get the specified property. Reason: Property :key is either inaccessible or undefined.', array(':key' => $key));
	}

	/**
	 * This function sets the value for the specified key.
	 *
	 * @access public
	 * @param string $key                           the name of the property
	 * @param mixed $value                          the value of the property
	 * @throws Throwable_Validation_Exception             indicates that the specified value does
	 *                                              not validate
	 * @throws Throwable_InvalidProperty_Exception     indicates that the specified property is
	 *                                              either inaccessible or undefined
	 */
	public function __set($key, $value) {
		switch ($key) {
			case 'value':
				if ( ! ($value instanceof DB_SQL_Expression)) {
					if ($value !== NULL) {
						settype($value, $this->metadata['type']);
						if ( ! $this->validate($value)) {
							throw new Throwable_Validation_Exception('Message: Unable to set the specified property. Reason: Value :value failed to pass validation constraints.', array(':value' => $value));
						}
					}
					else if ( ! $this->metadata['nullable']) {
						$value = $this->metadata['default'];
					}
				}
				if (isset($this->metadata['callback']) AND ! $this->model->{$this->metadata['callback']}($value)) {
					throw new Throwable_Validation_Exception('Message: Unable to set the specified property. Reason: Value :value failed to pass validation constraints.', array(':value' => $value));
				}
				$this->metadata['modified'] = TRUE;
				$this->value = $value;
			break;
			case 'modified':
				$this->metadata['modified'] = (bool) $value;
			break;
			default:
				throw new Throwable_InvalidProperty_Exception('Message: Unable to set the specified property. Reason: Property :key is either inaccessible or undefined.', array(':key' => $key, ':value' => $value));
			break;
		}
	}

	/**
	 * This function generates an HTML form control using the field's metadata.
	 *
	 * @access public
	 * @param string $name                          the name of the field
	 * @param array $attributes                     the HTML form tag's attributes
	 * @return string                               the HTML form control
	 */
	public function control($name, Array $attributes) {
		if ( ! $this->metadata['savable'] AND ($this->metadata['control'] != 'label')) {
			$attributes['disabled'] = 'disabled';
			//$attributes['readonly'] = 'readonly';
		}
		switch ($this->metadata['control']) {
			case 'auto':
				if (isset($this->metadata['enum'])) {
					return Form::select($name, $this->metadata['enum'], $this->value, $attributes);
				}
				return Form::input($name, $this->value, $attributes);
			case 'button':
				return Form::button($name, $this->value, $attributes);
			case 'checkbox':
				return Form::checkbox($name, 1, $this->value, $attributes);
			case 'file':
				return Form::file($name, $attributes);
			case 'hidden':
				return Form::hidden($name, $this->value, $attributes);
			case 'image':
				return Form::image($name, $this->value, $attributes);
			case 'label':
				return Form::label($name, $this->value, $attributes);
			case 'password':
				return Form::password($name, '', $attributes); // Note: Don't set password for security reasons
			case 'select':
				return Form::select($name, $this->metadata['enum'], $this->value, $attributes);
			case 'submit':
				return Form::submit($name, $this->value, $attributes);
			case 'textarea':
				return Form::textarea($name, $this->value, $attributes);
			case 'text':
				return Form::input($name, $this->value, $attributes);
			default:
				throw new Throwable_Exception('Message: Unable to create HTML form control. Reason: Invalid type of HTML form control.', array(':control' => $this->metadata['control'], ':field' => $name));
			break;
		}
	}

	/**
	 * This function generates an HTML form control using the field's metadata.
	 *
	 * @access public
	 * @param string $name                          the name of the field/alias
	 * @param array $attributes                     the HTML form tag's attributes
	 * @return string                               the HTML form label
	 */
	public function label($name, Array $attributes) {
		$text = (isset($this->metadata['label']))
			? $this->metadata['label']
			: $name;
		return Form::label($name, $text, $attributes);
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

	/**
	 * This function validates the specified value against any constraints.
	 *
	 * @access protected
	 * @param mixed $value                          the value to be validated
	 * @return boolean                              whether the specified value validates
	 */
	protected function validate($value) {
		if (isset($this->metadata['enum']) AND ! in_array($value, $this->metadata['enum'])) {
			return FALSE;
		}
		return TRUE;
	}

}
?>