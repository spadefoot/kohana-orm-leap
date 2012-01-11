<?php defined('SYSPATH') or die('No direct access');

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
 * This class indicates that an argument does not match with the expected value.
 *
 * @package Exception
 * @version 2011-12-11
 */
class Kohana_InvalidArgument_Exception extends InvalidArgumentException {

	/**
	* This function instantiates the exception with the specified message,
	* variables, and code.
	*
	* @access public
	* @param string $message                    the message
	* @param array $variables                   the variables
	* @param integer $code                      the code
	* @return Kohana_InvalidArgument_Exception  the exception
	*/
	public function __construct($message, array $variables = NULL, $code = 0) {
		// Set the message
		$message = __($message, $variables);

		// Pass the message to the parent
		parent::__construct($message, $code);
	}

	/**
	* This function returns a string for this object.
	*
	* @access public
	* @uses Kohana::exception_text
	* @return string                            the string for this object
	*/
	public function __toString() {
		return Kohana::exception_text($this);
	}

}
?>