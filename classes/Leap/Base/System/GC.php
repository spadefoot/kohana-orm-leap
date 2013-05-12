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

namespace Leap\Base\System {

	use \Leap\Core;

	/**
	 * This class manages garbage collection.
	 *
	 * @package Leap
	 * @category GC
	 * @version 2013-02-10
	 *
	 * @see http://msdn.microsoft.com/en-us/library/system.gc.aspx
	 *
	 * @abstract
	 */
	abstract class GC extends Core\Object {

		/**
		 * This function forces garbage collector to start immediately.
		 *
		 * @access public
		 * @static
		 *
		 * @see http://www.php.net/manual/en/features.gc.php
		 * @see http://www.php.net/manual/en/features.gc.refcounting-basics.php
		 * @see http://www.php.net/manual/en/features.gc.collecting-cycles.php
		 * @see http://www.php.net/manual/en/function.gc-collect-cycles.php
		 */
		public static function collect() {
			if (function_exists('gc_collect_cycles')) {
				gc_enable();
				if (gc_enabled()) {
					gc_collect_cycles();
					gc_disable();
				}
			}
		}

		/**
		 * This function returns the reference count for the specified object.
		 *
		 * @access public
		 * @static
		 * @param mixed $object                             the object to be evaluated
		 * @return integer                                  the reference count for the specified
		 *                                                  object
		 *
		 *
		 * @see http://us3.php.net/manual/en/language.references.php
		 * @see http://stackoverflow.com/questions/3764686/get-the-reference-count-of-an-object-in-php
		 */
		public static function ref_count($object) {
			ob_start();
				debug_zval_dump($object);
				$contents = ob_get_contents();
			ob_end_clean();

			$matches = array();

			preg_match('/refcount\(([0-9]+)/', $contents, $matches);

			$ref_count = (isset($matches[1]))
				? (int) $matches[1] - 3  // this function added 3 references
				: 0;

			return $ref_count;
		}

	}

}