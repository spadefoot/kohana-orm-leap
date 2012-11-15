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
 * This class represents a "number" adaptor for a handling UOM conversions.
 *
 * @package Leap
 * @category ORM
 * @version 2012-11-14
 *
 * @abstract
 */
abstract class Base_DB_ORM_Field_Adaptor_UOM  extends DB_ORM_Field_Adaptor {

	/**
	 * This constructor initializes the class.
	 *
	 * @access public
	 * @param DB_ORM_Model $model                   a reference to the implementing model
	 * @param array $metadata                       the adaptor's metadata
	 * @throws Throwable_Exception                     indicates that error occurred when loading
	 *                                              a configuration
	 * @throws Throwable_InvalidArgument_Exception     indicates that an invalid field name
	 *                                              was specified
	 */
	public function __construct(DB_ORM_Model $model, Array $metadata = array()) {
		parent::__construct($model, $metadata['field']);

		$this->metadata['units'] = array();

		$group = strtolower('uom.' . $metadata['measurement'] . '.' . $metadata['units'][0]);

		if (($unit = Kohana::$config->load($group)) === NULL) {
			throw new Throwable_Exception('Message: Unable to load configuration. Reason: Configuration group :group is undefined.', array(':group' => $group));
		}

		$this->metadata['units'][0] = $unit; // field's unit

		$group = strtolower('uom.' . $metadata['measurement'] . '.' . $metadata['units'][1]);

		if (($unit = Kohana::$config->load($group)) === NULL) {
			throw new Throwable_Exception('Message: Unable to load configuration. Reason: Configuration group :group is undefined.', array(':group' => $group));
		}

		$this->metadata['units'][1] = $unit; // adaptor's unit
	}

	/**
	 * This function returns the value associated with the specified property.
	 *
	 * @access public
	 * @override
	 * @param string $key                           the name of the property
	 * @return mixed                                the value of the property
	 * @throws Throwable_InvalidProperty_Exception     indicates that the specified property is
	 *                                              either inaccessible or undefined
	 */
	public function __get($key) {
		switch ($key) {
			case 'value':
				$value = $this->model->{$this->metadata['field']};
				if (($value !== NULL) AND ! ($value instanceof DB_SQL_Expression)) {
					$value = static::convert($value, $this->metadata['units'][0], $this->metadata['units'][1]);
				}
				return $value;
			break;
			default:
				if (isset($this->metadata[$key])) { return $this->metadata[$key]; }
			break;
		}
		throw new Throwable_InvalidProperty_Exception('Message: Unable to get the specified property. Reason: Property :key is either inaccessible or undefined.', array(':key' => $key));
	}

	/**
	 * This function sets the value for the specified key.
	 *
	 * @access public
	 * @override
	 * @param string $key                           the name of the property
	 * @param mixed $value                          the value of the property
	 * @throws Throwable_InvalidProperty_Exception     indicates that the specified property is
	 *                                              either inaccessible or undefined
	 */
	public function __set($key, $value) {
		switch ($key) {
			case 'value':
				if ($value !== NULL) {
					$value = static::convert($value, $this->metadata['units'][1], $this->metadata['units'][0]);
				}
				$this->model->{$this->metadata['field']} = $value;
			break;
			default:
				throw new Throwable_InvalidProperty_Exception('Message: Unable to set the specified property. Reason: Property :key is either inaccessible or undefined.', array(':key' => $key, ':value' => $value));
			break;
		}
	}

	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This function converts a value's units.
	 *
	 * @access protected
	 * @static
	 * @param double $value                     the value to converted
	 * @param string $units0                    the value's starting units
	 * @param string $units1                    the value's ending units
	 * @return double                           the new value
	 */
	protected static function convert($value, $units0, $units1) {
		return ($value * static::parse($units0)) / static::parse($units1);
	}

	/**
	 * This function parses a mathematical expression to evaluate it.
	 *
	 * @access protected
	 * @static
	 * @param string $expr                      the expression to be evaluated
	 * @return double                           the value
	 *
	 * @see http://de.php.net/eval
	 */
	protected static function parse($expr) {
		$expr = preg_replace("/[^0-9+\-.*\/()%]/", "", $expr);
		$expr = preg_replace("/([+-])([0-9]{1})(%)/", "*(1\$1.0\$2)", $expr);
		$expr = preg_replace("/([+-])([0-9]+)(%)/", "*(1\$1.\$2)", $expr);
		$expr = preg_replace("/([0-9]+)(%)/", ".\$1", $expr);
		if ($expr == "") {
			$value = 0;
		}
		else {
			eval("\$value=" . $expr . ";");
		}
		return $value;
	}

}
?>