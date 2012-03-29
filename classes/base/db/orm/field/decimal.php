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
 * This class represents a "decimal" field in a database table.
 *
 * @package Leap
 * @category ORM
 * @version 2012-03-05
 *
 * @abstract
 */
abstract class Base_DB_ORM_Field_Decimal extends DB_ORM_Field {

	/**
	 * This constructor initializes the class.
	 *
	 * @access public
	 * @param DB_ORM_Model $model                   a reference to the implementing model
	 * @param array $metadata                       the field's metadata
	 */
	public function __construct(DB_ORM_Model $model, Array $metadata = array()) {
		parent::__construct($model, 'double');

		// Fixed precision and scale numeric data from -10^38 -1 through 10^38 -1.

		$this->metadata['scale'] = (int) $metadata['scale']; // the scale (i.e. the number of digits that can be stored following the decimal point)
		if ($this->metadata['scale'] == 0) {
			$this->metadata['type'] = 'integer';
		}

		$this->metadata['precision'] = (int) $metadata['precision']; // the precision (i.e. the number of significant digits that are stored for values)
		if ($this->metadata['type'] == 'double') {
			$this->metadata['precision'] += 1;
		}

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

		if (isset($metadata['enum'])) {
			$this->metadata['enum'] = (array) $metadata['enum'];
		}

		if (isset($metadata['control'])) {
			$this->metadata['control'] = (string) $metadata['control'];
		}

		if (isset($metadata['label'])) {
			$this->metadata['label'] = (string) $metadata['label'];
		}

		if (isset($metadata['default'])) {
			$default = $metadata['default'];
			if ( ! is_null($default)) {
				settype($default, $this->metadata['type']);
				$this->validate($default);
			}
			$this->metadata['default'] = $default;
			$this->value = $default;
		}
		else if ( ! $this->metadata['nullable']) {
			$default = 0.0;
			settype($default, $this->metadata['type']);
			$this->metadata['default'] = $default;
			$this->value = $default;
		}
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
					$value = number_format( (float) $this->value, $this->metadata['scale']);
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
		if ( ! is_null($value)) {
			if (strlen("{$value}") > $this->metadata['precision']) {
				return FALSE;
			}
		}
		return parent::validate($value);
	}

}
?>