<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Copyright 2011 Spadefoot
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
 * This class acts as the base class for any Kohana object.
 *
 * @package System-Ext
 * @category Object
 * @version 2011-11-18
 *
 * @abstract
 */
abstract class Kohana_Object {

	/**
	* This function allows for an inheriting class to determine its child classes.
	*
	* Retro-support of get_called_class()
	* Tested and works in PHP 5.2.4
	*
	* @access public
	* @final
	* @static
	* @param array $backtrace           the generated backtrace
	* @param integer $level             the depth level
	* @return string                    the name of the called class
	*
	* @see http://php.net/manual/en/function.get-called-class.php
	* @see http://www.sol1.com.au/
	*/
	public final static function get_called_class($backtrace = FALSE, $level = 1) {
		if (!$backtrace) {
			$backtrace = debug_backtrace();
		}
		if (!isset($backtrace[$level])) {
			throw new Kohana_Exception('Message: Cannot find called class. Reason: Stack level too deep.', array(':backtrace' => $backtrace, ':level' => $level));
		}
		if (!isset($backtrace[$level]['type'])) {
			throw new Kohana_Exception('Message: Cannot find called class. Reason: Type not set.', array(':backtrace' => $backtrace, ':level' => $level));
		}
		else {
			switch ($backtrace[$level]['type']) {
				case '::':
					try {
						$lines = file($backtrace[$level]['file']);
						$i = 0;
						$callerLine = '';
						do {
							$i++;
							$callerLine = $lines[$backtrace[$level]['line'] - $i] . $callerLine;
						} while (stripos($callerLine, $backtrace[$level]['function']) === FALSE);
						preg_match('/([a-zA-Z0-9\_]+)::' . $backtrace[$level]['function'] . '/', $callerLine, $matches);
						if (!isset($matches[1])) {
							// must be an edge case.
							throw new Kohana_Exception('Message: Cannot find called class.  Reason: Originating method call is obscured.', array(':backtrace' => $backtrace, ':level' => $level));
						}
						switch ($matches[1]) {
							case 'self':
							case 'parent':
								return self::get_called_class($backtrace, $level + 1);
							default:
								return $matches[1];
						}
					}
					catch (ErrorException $ex) {
						throw new Kohana_Exception('Message: Cannot find called class.  Reason: :exception', array(':exception' => $ex->getMessage(), ':backtrace' => $backtrace, ':level' => $level));
					}
				break;
				case '->':
					switch ($backtrace[$level]['function']) {
						case '__get':
							// edge case -> get class of calling object
							if (!is_object($backtrace[$level]['object'])) {
								throw new Kohana_Exception('Message: Cannot find called class.  Reason: Edge case fail. __get called on non object.', array(':backtrace' => $backtrace, ':level' => $level));
							}
							return get_class($backtrace[$level]['object']);
						break;
						default:
							return $backtrace[$level]['class'];
						break;
					}
				default:
					throw new Kohana_Exception('Message: Cannot find called class. Reason: Unknown backtrace method type.', array(':backtrace' => $backtrace, ':level' => $level));
				break;
			}
		}
	}

}
?>