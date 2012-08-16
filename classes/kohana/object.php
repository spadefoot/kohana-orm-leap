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
 * This class acts as the base class for any Kohana object.
 *
 * @package Leap
 * @category Object
 * @version 2012-08-16
 *
 * @abstract
 */
abstract class Kohana_Object {

	/**
	 * This function returns the hash code for the object.
	 *
	 * @access public
	 * @return string					the hash code for the object
	 */
	public function __hashCode() {
		return spl_object_hash($this);
	}

}
?>