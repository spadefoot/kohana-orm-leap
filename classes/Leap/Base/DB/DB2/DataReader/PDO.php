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

namespace Leap\Base\DB\DB2\DataReader {

	/**
	 * This class is used to read data from a DB2 database using the PDO
	 * driver.
	 *
	 * @package Leap
	 * @category DB2
	 * @version 2012-12-04
	 *
	 * @abstract
	 */
	abstract class PDO extends DB\SQL\DataReader\PDO {}

}