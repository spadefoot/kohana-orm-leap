<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Copyright 2012 Spadefoot
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
 * This class represents a bit-field value.
 *
 * @package Leap
 * @category Data Type
 * @version 2012-11-14
 *
 * @abstract
 */
abstract class Base_BitField extends Core_Object implements Countable {

	/**
	 * This variable stores the maximum size/boundary of the bit-field.
	 *
	 * @access protected
	 * @var integer
	 */	
	protected $boundary;

	/**
	 * This variable stores the bit-field pattern.  The key is the 'field' name and
	 * the 'value' is the number of bits that the field represents.  The pattern starts
	 * from the right-most bit to the left bit boundary.  For example:
	 *
	 *     $pattern = array(
	 *         'A' => 1,
	 *         'B' => 4,
	 *         'C' => 7,
	 *         'D' => 12,
	 *         'E' => 8
	 *     );
	 *
	 *     0000 0000 0000 0000 0000 0000 0000 0000
	 *     EEEE EEEE DDDD DDDD DDDD CCCC CCCB BBBA
	 *
	 * @access protected
	 * @var array
	 */
	protected $pattern;

	/**
	 * This variable stores the bit-field values as a bit-array.
	 *
	 * @access protected
	 * @var array
	 */
	protected $values;

	/**
	 * This constructor initializes the class.
	 *
	 * @access public
	 * @param string $pattern                       the pattern to be used
	 * @param mixed $value                          the value of the field
	 */
	public function __construct(Array $pattern, $value = '0') {
		$this->boundary = (PHP_INT_SIZE == 8) ? 64 : 32;
		$this->pattern = $pattern;
		$this->map($value);
	}

	/**
	 * This function gets the value of the specified field.
	 *
	 * @access public
	 * @param string $field                         the name of the field
	 * @return integer                              the value of the field
	 * @throws Throwable_InvalidProperty_Exception     indicates that the specified property is
	 *                                              either inaccessible or undefined
	 */
	public function __get($field) {
		if ( ! array_key_exists($field, $this->values)) {
			throw new Throwable_InvalidProperty_Exception('Message: Unable to get the specified property. Reason: Property :field is either inaccessible or undefined.', array(':field' => $field));
		}
		return $this->values[$field];
	}

	/**
	 * This function sets the value for the specified field.
	 *
	 * @access public
	 * @param string $field                         the name of the field
	 * @param mixed $value                          the value of the field
	 * @throws Throwable_InvalidProperty_Exception     indicates that the specified property is
	 *                                              either inaccessible or undefined
	 */
	public function __set($field, $value) {
		if ( ! array_key_exists($field, $this->values)) {
			throw new Throwable_InvalidProperty_Exception('Message: Unable to set the specified property. Reason: Property :field is either inaccessible or undefined.', array(':field' => $field, ':value' => $value));
		}
		$this->values[$field] = bindec(static::unpack($value, $this->boundary));
	}

	/**
	 * This function renders the bit-field as a binary string when the object is treated
	 * like a string, e.g. with PHP's echo and print commands.
	 *
	 * @access public
	 * @return string                               the value as a binary string
	 */
	public function __toString() {
		return $this->as_binary();
	}

	/**
	 * This function returns the value as a binary string.
	 *
	 * @access public
	 * @return string                               the value as a binary string
	 */
	public function as_binary($format = '%s') {
		$binary = '';
		foreach ($this->values as $field => $value) {
			$binary = substr(static::unpack($value, $this->boundary), $this->boundary - $this->pattern[$field]) . $binary;
		}
		$binary = str_pad($binary, $this->boundary, '0', STR_PAD_LEFT);
		if ($format != '%s') { // this is done for efficiency
			return sprintf($format, $binary);
		}
		return $binary;
	}

	/**
	 * This function returns the bit-field as a hexadecimal.
	 *
	 * @access public
	 * @param string $format                        the string formatting to be used
	 * @return string                               the value as a hexadecimal
	 */
	public function as_hexcode($format = '%s') {
		$hexcode = dechex(static::pack($this->as_binary()));
		if ($format != '%s') {
			return sprintf($format, $hexcode); // this is done for efficiency
		}
		return $hexcode;
	}

	/**
	 * This function returns the bit-field as an integer.
	 *
	 * @access public
	 * @return integer                              the value as an integer
	 */
	public function as_integer() {
		return static::pack($this->as_binary());
	}

	/**
	 * This function returns the value as a binary string.
	 *
	 * @access public
	 * @param string $format                        the string formatting to be used
	 * @return string                               the value as a binary string
	 */
	public function as_string($format = '%s') {
		return $this->as_binary($format);
	}

	/**
	 * This function returns the size/boundary of the bit-field, which will be either
	 * 32 or 64 bits.
	 *
	 * @access public
	 * @return integer                              the size of the bit-field
	 */
	public function count() {
		return $this->boundary;
	}

	/**
	 * This function returns whether the specified pattern matches the bit-field's
	 * pattern.
	 *
	 * @access public
	 * @return boolean                              whether the pattern matches
	 */
	public function has_pattern(Array $pattern) {
		return ( (string) serialize($pattern) === (string) serialize($this->pattern)); // order matters
	}

	/**
	 * This function maps the specified value using the bit-field pattern.
	 *
	 * @access public
	 * @param mixed $value                          the value to be mapped
	 */
	public function map($value) {
		$this->values = array();
		$binary = static::unpack($value, $this->boundary);
		$start = 0;
		$length = strlen($binary);
		foreach ($this->pattern as $field => $bits) {
			$this->values[$field] = ($start < $length)
				? static::pack(substr($binary, $length - ($start + $bits), min($bits, $this->boundary)))
				: 0;
			$start += $bits;
		}
		if ($start > $this->boundary) {
			throw new Throwable_Exception('Message: Invalid bit-field pattern. Reason: Pattern exceeds the bit boundary of :boundary.', array(':pattern' => $this->pattern, ':boundary' => $this->boundary));
		}
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This function converts a binary string into an integer value.
	 *
	 * @access protected
	 * @static
	 * @param string $binary                        the binary string to be packed
	 * @return integer                              an integer value
	 */
	protected static function pack($binary) {
		return bindec($binary);
	}

	/**
	 * This function converts the specified value to a binary string.
	 *
	 * @access protected
	 * @static
	 * @param mixed $value                          the value to be unpacked
	 * @return string                               a binary string
	 */
	protected static function unpack($value, $boundary) {
		if (is_numeric($value)) {
			return str_pad(decbin($value), $boundary, '0', STR_PAD_LEFT);
		}
		else if (is_string($value)) {
			$binary = (preg_match("/^b'.*'$/i", $value))
				? substr($value, 2, strlen($value) - 3)
				: $value;
			if (preg_match('/^(0|1)*$/', $binary)) {
				$length = strlen($binary);
				if ($length > $boundary) {
					return substr($binary, $length - $boundary, $boundary);
				}
				if ($length < $boundary) {
					return str_pad($binary, $boundary, '0', STR_PAD_LEFT);
				}
				return $binary;
			}
		}
		else if (is_object($value) && ($value instanceof BitField)) {
			return $value->as_binary();
		}
		return str_pad('0', $boundary, '0', STR_PAD_LEFT);
	}

}
?>