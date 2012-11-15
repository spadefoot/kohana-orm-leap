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
 * This class represents a "double" field (i.e. a floating point type) in a database
 * table.
 *
 * @package Leap
 * @category ORM
 * @version 2012-11-14
 *
 * @abstract
 */
abstract class Base_DB_ORM_Field_Double extends DB_ORM_Field {

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
		parent::__construct($model, 'double');

		$max_digits = 1;
		if (isset($metadata['max_decimals'])) {
			$this->metadata['max_decimals'] = abs( (int) $metadata['max_decimals']); // the number of digits that may be after the decimal point
			$max_digits = $this->metadata['max_decimals'] + 1;
		}

		if (isset($metadata['max_digits'])) {
			$this->metadata['max_digits'] = max(abs( (int) $metadata['max_digits']), $max_digits); // the total number of digits that may be stored
		}

		$this->metadata['unsigned'] = (isset($metadata['unsigned'])) ? (bool) $metadata['unsigned'] : FALSE;

		$default = 0.0;
		if (isset($metadata['range'])) { // http://firebirdsql.org/manual/migration-mssql-data-types.html
			$this->metadata['range']['lower_bound'] = (double) $metadata['range'][0]; // float: -1.79E + 308 double: -3.40E + 38
			$default = max($default, $this->metadata['range']['lower_bound']);
			$this->metadata['range']['upper_bound'] = (double) $metadata['range'][1]; // float: 1.79E + 308 double: 3.40E + 38
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
		}
		else if ( ! $this->metadata['nullable'] AND isset($this->metadata['enum'])) {
			$default = $this->metadata['enum'][0];
		}
		else if ($this->metadata['nullable']) {
			$default = (isset($this->metadata['enum']) AND ! in_array(NULL, $this->metadata['enum']))
				? $this->metadata['enum'][0]
				: NULL;
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
			if ($this->metadata['unsigned'] AND ($value < 0.0)) {
				return FALSE;
			}
			else if (isset($this->metadata['range'])) {
				if (($value < $this->metadata['range']['lower_bound']) OR ($value > $this->metadata['range']['upper_bound'])) {
					return FALSE;
				}
			}
			if (isset($this->metadata['max_digits'])) {
				$parts = preg_split('/\./', "{$value}");
				$digits = strlen("{$parts[0]}");
				if (isset($this->metadata['max_decimals']) AND (count($parts) > 1)) {
					$decimals = strlen("{$parts[1]}");
					if ($decimals > $this->metadata['max_decimals']) {
						return FALSE;
					}
					$digits += $decimals;
				}
				if ($digits > $this->metadata['max_digits']) {
					return FALSE;
				}
			}
		}
		return parent::validate($value);
	}

}
?>