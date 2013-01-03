<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Copyright © 2011–2013 Spadefoot Team.
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
 * This class provides a set of helper functions that are often used when
 * data is stored in a database.
 *
 * @package Leap
 * @category ToolKit
 * @version 2013-01-03
 *
 * @abstract
 */
abstract class Base_DB_ToolKit extends Core_Object {

	/**
	 * This function converts a like clause to a regular expression.
	 *
	 * @access public
	 * @static
	 * @param string $like                              the like clause to be converted
	 * @param char $escape                              the escape character
	 * @return string                                   the resulting regular expression
	 *
	 * @see http://stackoverflow.com/questions/3683746/escaping-mysql-wild-cards
	 * @see http://stackoverflow.com/questions/47052/what-code-would-i-use-to-convert-a-sql-like-expression-to-a-regex-on-the-fly
	 */
	public static function regex($like, $escape = NULL) {
		$regex = '';

		$length = strlen($like);

		for ($a = 0; $a < $length; $a++) {
			$char = $string[$a];
			if ($char == $escape) {
				$b = $a + 1;
				$next = ($b < $length) ? $string[$b] : '';
				if (in_array($next, array('%', '_', $escape))) {
					$a = $b;
				}
				$regex .= preg_quote($char);
			}
			else {
				switch ($char) {
					case '%':
						$regex .= '.*';
					break;
				 	case '_':
						$regex .= '.';
					break;
					default:
						$regex .= preg_quote($char);
					break;
				}
			}
		}

		$regex = '/^' . $regex . '$/';
		
		return $regex;
	}

	/**
	 * This function converts a sting to a slug that can be used in a URL.
	 *
	 * @access public
	 * @static
	 * @param string $value                             the value to be processed
	 * @return string                                   the slug
	 *
	 * @see http://www.finalwebsites.com/forums/topic/convert-string-to-slug
	 * @see http://snipplr.com/view/2809/convert-string-to-slug/
	 */
	public static function slug($value) {
		if (is_string($value)) {
			$value = strtolower($value);
			$value = preg_replace('/[^a-z0-9-]/', '-', $value);
			$value = preg_replace('/-+/', '-', $value);
			$value = trim($value, '-');
			return $value;
		}
		return '';
	}

}
