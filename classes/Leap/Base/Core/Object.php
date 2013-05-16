<?php

/**
 * Copyright © 2011–2013 Spadefoot Team.
 *
 * Unless otherwise noted, LEAP is licensed under the Apache License,
 * Version 2.0 (the "License"); you may not use this file except in
 * compliance with the License. You may obtain a copy of the License
 * at:
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Leap\Base\Core {

	/**
	 * This class acts as the base class for any object.
	 *
	 * @package Leap
	 * @category Core
	 * @version 2013-05-12
	 *
	 * @abstract
	 */
	abstract class Object {

		/**
		 * This function returns whether the specified object is equal to the called object.
		 *
		 * @access public
		 * @return boolean                              whether the specified object is equal
		 *                                              to the called object
		 */
		public function __equals($object) {
			return (($object !== NULL) && ($object instanceof Core\Object) && ($object->__hashCode() == $this->__haseCode()));
		}

		/**
		 * This function returns the hash code for the object.
		 *
		 * @access public
		 * @return string                               the hash code for the object
		 */
		public function __hashCode() {
			return spl_object_hash($this);
		}

		/**
		 * This function returns a string that represents the object.
		 *
		 * @access public
		 * @return string                               a string that represents the object
		 */
		public function __toString() {
			return (string) serialize($this);
		}

	}

}