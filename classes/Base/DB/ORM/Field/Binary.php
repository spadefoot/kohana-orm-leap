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
 * This class represents a "binary" field in a database table.
 *
 * @package Leap
 * @category ORM
 * @version 2012-11-14
 *
 * @abstract
 */
abstract class Base_DB_ORM_Field_Binary extends DB_ORM_Field {

	/**
	 * This constructor initializes the class.
	 *
	 * @access public
	 * @param DB_ORM_Model $model                   a reference to the implementing model
	 * @param array $metadata                       the field's metadata
	 * @throws Throwable_Validation_Exception             indicates that the specified value does
	 *                                              not validate
	 */
	public function __construct(DB_ORM_Model $model, Array $metadata = array()) {
		parent::__construct($model, 'string');

		$this->metadata['max_length'] = (int) $metadata['max_length'];

		if (isset($metadata['savable'])) {
			$this->metadata['savable'] = (bool) $metadata['savable'];
		}

		if (isset($metadata['nullable'])) {
			$this->metadata['nullable'] = (bool) $metadata['nullable'];
		}

		if (isset($metadata['filter'])) {
			$this->metadata['filter'] = (string) $metadata['filter'];
		}

		if (isset($metadata['callback'])) {
			$this->metadata['callback'] = (string) $metadata['callback'];
		}

		$this->metadata['control'] = 'textarea';

		if (isset($metadata['label'])) {
			$this->metadata['label'] = (string) $metadata['label'];
		}

		if (isset($metadata['default'])) {
			$default = $metadata['default'];
		}
		else if ( ! $this->metadata['nullable']) {
			$default = '';
		}
		else {
			$default = NULL;
		}

		if ( ! ($default instanceof DB_SQL_Expression)) {
			if ($default !== NULL) {
				settype($default, $this->metadata['type']);
			}
			if ( ! $this->validate($default)) {
				throw new Throwable_Validation_Exception('Message: Unable to set default value for field. Reason: Value :value failed to pass validation constraints.', array(':value' => $default));
			}
		}

		$this->metadata['default'] = $default;
		$this->value = $default;
	}

	/**
	 * This function validates the specified value against any constraints.
	 *
	 * @access protected
	 * @override
	 * @param mixed $value                          the value to be validated
	 * @return boolean                              whether the specified value validates
	 */
	protected function validate($value) {
		if ($value !== NULL) {
			if ((strlen($value) > $this->metadata['max_length']) AND ! preg_match('/^(0|1)*$/', $value)) {
				return FALSE;
			}
		}
		return parent::validate($value);
	}

}
?>