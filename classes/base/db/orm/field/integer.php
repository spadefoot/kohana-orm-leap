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
 * This class represents an "integer" field in a database table.
 *
 * @package Leap
 * @category ORM
 * @version 2012-03-05
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
	 */
	public function __construct(DB_ORM_Model $model, Array $metadata = array()) {
		parent::__construct($model, 'integer');

		if (isset($metadata['max_length'])) {
			$this->metadata['max_length'] = (int) $metadata['max_length']; // the maximum length of the integer
		}

		$this->metadata['unsigned'] = (isset($metadata['unsigned'])) ? (bool) $metadata['unsigned'] : FALSE;

		// smallint/tinyint: -2^15 (-32,768) through 2^15 - 1 (32,767)
		$this->metadata['range']['lower_bound'] = ($this->metadata['unsigned']) ? 0 : -2147483648;
		$this->metadata['range']['upper_bound'] = 2147483647;

		if (isset($metadata['range'])) {
			$this->metadata['range']['lower_bound'] = max( (int)  $metadata['range'][0], $this->metadata['range']['lower_bound']);
			$this->metadata['range']['upper_bound'] = min( (int)  $metadata['range'][1], $this->metadata['range']['upper_bound']);
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
			$default = max(0, $this->metadata['range']['lower_bound']);
			$this->metadata['default'] = $default;
			$this->value = $default;
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
			if (isset($this->metadata['max_length'])) {
				$strval = strval($value);
				if (strlen($strval) > $this->metadata['max_length']) {
					return FALSE;
				}
			}
			if (($value < $this->metadata['range']['lower_bound']) || ($value > $this->metadata['range']['upper_bound'])) {
				return FALSE;
			}
		}
		return parent::validate($value);
	}

}
?>