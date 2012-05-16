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
 * This class represents a data buffer.
 *
 * @package Leap
 * @category Data Type
 * @version 2012-05-15
 *
 * @see https://developer.apple.com/library/mac/documentation/Cocoa/Reference/Foundation/Classes/NSData_Class/Reference/Reference.html
 *
 * @abstract
 */
abstract class Base_Data extends Kohana_Object implements Countable {

	/**
	 * This constant represents binary data.
	 *
	 * @access public
	 * @const integer
	 */
	const BINARY_DATA = 0;

	/**
	 * This constant represents hexadecimal data.
	 *
	 * @access public
	 * @const integer
	 */
	const HEXADECIMAL_DATA = 1;

	/**
	 * This constant represents string data.
	 *
	 * @access public
	 * @const integer
	 */
	const STRING_DATA = 2;

	/**
	 * This variable stores the data as a hexadecimal.
	 *
	 * @access protected
	 * @var string
	 */
	protected $hexcode;

	/**
	 * This variable stores the length of the data as a byte string.
	 *
	 * @access protected
	 * @var integer
	 */
	protected $length;

	/**
	 * This constructor initializes the class.
	 *
	 * @access public
	 * @param string $data						the data
	 * @param boolean $type						the current type of data
	 */
	public function __construct($data, $type = 0) {
		$this->hexcode = Data::unpack($data, $type);
		$this->length = -1;
	}

	/**
	 * This function returns the data as a hexadecimal.
	 *
	 * @access public
	 * @return string							the data as a hexadecimal
	 */
	public function __toString() {
		return $this->hexcode;
	}

	/**
	 * This function returns the data as a byte string.
	 *
	 * @access public
	 * @return string							the data as a byte string
	 */
	public function as_bytes() {
		return base_convert($this->hexcode, 16, 2);
	}

	/**
	 * This function returns the data as a hexadecimal.
	 *
	 * @access public
	 * @return string							the data as a hexadecimal
	 */
	public function as_hexcode() {
		return $this->hexcode;
	}

	/**
	 * This function returns the data as a string.
	 *
	 * @access public
	 * @return string							the data as a string
	 */
	public function as_string() {
		return Data::pack($this->hexcode);
	}

	/**
	 * This function return the length of the data as a byte string.
	 *
	 * @access public
	 * @return integer							the length of the data as a byte
	 *											string
	 */
	public function count() {
		if ($this->length < 0) {
			$this->length = strlen($this->as_bytes());
		}
		return $this->length;
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This function converts a hexadecimal to a string.
	 *
	 * @access protected
	 * @static
	 * @param string $data						the data to be converted
	 * @return string							a string
	 */
	protected static function pack($hexcode) {
		if (is_string($hexcode)) {
			return pack('H*', $hexcode);
		}
		return '';
	}

	/**
	 * This function converts string to a hexadecimal.
	 *
	 * @access protected
	 * @static
	 * @param string $data						the data to be converted
	 * @param integer $type						the type of data to be converted
	 * @return string							a hexadecimal string
	 */
	protected static function unpack($data, $type) {
		if (is_string($data)) {
			switch ($type) {
				case Data::BINARY_DATA:
					return base_convert($data, 2, 16);
				break;
				case Data::STRING_DATA:
					$hexcode = unpack('H*hex', $data);
					return $hexcode['hex'];
				break;
				case Data::HEXADECIMAL_DATA:
				default:
					return $data;
				break;
			}
		}
		else if (is_object($data) && ($data instanceof Data)) {
			return $data->as_hexcode();
		}
		return '';
	}

}
?>