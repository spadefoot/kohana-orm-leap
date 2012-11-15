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
 * This class represents an "integer" field in a database table.
 *
 * @package Leap
 * @category ORM
 * @version 2012-11-14
 *
 * @abstract
 */
abstract class Base_DB_ORM_Field_Integer extends DB_ORM_Field {

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
		parent::__construct($model, 'integer');

		if (isset($metadata['max_length'])) {
			$this->metadata['max_length'] = (int) $metadata['max_length']; // the maximum length of the integer
		}

		$this->metadata['unsigned'] = isset($metadata['unsigned']) ? (bool) $metadata['unsigned'] : FALSE;

		if (isset($this->metadata['max_length']) AND ($this->metadata['max_length'] >= 11)) {
			if (PHP_INT_SIZE === 4) {
				$this->metadata['int8fix'] = TRUE;
				$this->metadata['range']['lower_bound'] = $this->metadata['unsigned'] ? '0' : '-9223372036854775808';
				$this->metadata['range']['upper_bound'] = '9223372036854775807';
			}
			else {
				$this->metadata['range']['lower_bound'] = $this->metadata['unsigned'] ? 0 : -9223372036854775808;
				$this->metadata['range']['upper_bound'] = 9223372036854775807;
			}
		}
		else {
			$this->metadata['range']['lower_bound'] = $this->metadata['unsigned'] ? 0 : -2147483648;
			$this->metadata['range']['upper_bound'] = 2147483647;
		}

		if (isset($metadata['range'])) {
			if (isset($this->metadata['int8fix'])) {
				$this->metadata['range']['lower_bound'] = (bccomp(strval($metadata['range'][0]), ($this->metadata['range']['lower_bound']) === 1)) ? strval($metadata['range'][0]) : $this->metadata['range']['lower_bound'];
				$this->metadata['range']['upper_bound'] = (bccomp(strval($metadata['range'][1]), ($this->metadata['range']['lower_bound']) === -1)) ? strval($metadata['range'][0]) : $this->metadata['range']['upper_bound'];
			}
			else {
				$this->metadata['range']['lower_bound'] = max( (int) $metadata['range'][0], $this->metadata['range']['lower_bound']);
				$this->metadata['range']['upper_bound'] = min( (int) $metadata['range'][1], $this->metadata['range']['upper_bound']);
			}
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
		else if ( ! $this->metadata['nullable']) {
			if (isset($this->metadata['enum'])) {
				$default = $this->metadata['enum'][0];
			}
			else if (isset($this->metadata['int8fix'])) {
				$default = (bccomp($this->metadata['range']['lower_bound'], '0') === 1) ? $this->metadata['range']['lower_bound'] : '0';
				if ((bccomp($default, '-2147483648') !== -1) OR (bccomp($default, '2147483647') !== 1)) {
					$default = (int) $default;
				}
			}
			else {
				$default = max(0, $this->metadata['range']['lower_bound']);
			}
		}
		else {
			$default = (isset($this->metadata['enum']) AND ! in_array(NULL, $this->metadata['enum']))
				? $this->metadata['enum'][0]
				: NULL;
		}

		if ( ! ($default instanceof DB_SQL_Expression)) {
			if ($default !== NULL) {
				if ((PHP_INT_SIZE !== 4) OR ! is_string($default) OR ! preg_match('/^-?[0-9]+$/D', $default) OR ((bccomp($default, '-2147483648') !== -1) AND (bccomp($default, '2147483647') !== 1))) {
					settype($default, $this->metadata['type']);
				}
			}
			if ( ! $this->validate($default)) {
				throw new Throwable_Validation_Exception('Message: Unable to set default value for field. Reason: Value :value failed to pass validation constraints.', array(':value' => $default));
			}
		}

		$this->metadata['default'] = $default;
		$this->value = $default;
	}

	/**
	 * This function sets the value for the specified key.
	 *
	 * @access public
	 * @override
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
						if ( ! isset($this->metadata['int8fix']) OR is_int($value) OR ! preg_match('/^-?[0-9]+$/D', (string) $value) OR (bccomp( (string) $value, '-2147483648') !== -1 AND bccomp( (string) $value, '2147483647') !== 1)) {
							settype($value, $this->metadata['type']);
						}
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
	 * This function validates the specified value against any constraints.
	 *
	 * @access protected
	 * @override
	 * @param mixed $value                          the value to be validated
	 * @return boolean                              whether the specified value validates
	 */
	protected function validate($value) {
		if ($value !== NULL) {
			if (isset($this->metadata['max_length']) AND (strlen(strval($value)) > $this->metadata['max_length'])) {
				return FALSE;
			}
			if (isset($this->metadata['int8fix'])) {
				if ( ! preg_match('/^-?[0-9]+$/D', strval($value)) OR (bccomp(strval($value), strval($this->metadata['range']['lower_bound'])) === -1) OR (bccomp(strval($value), strval($this->metadata['range']['upper_bound'])) === 1))
					return FALSE;
			}
			else if (($value < $this->metadata['range']['lower_bound']) OR ($value > $this->metadata['range']['upper_bound'])) {
				return FALSE;
			}
		}
		return parent::validate($value);
	}

}
?>