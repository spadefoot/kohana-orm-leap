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

/**
 * This class builds a MariaDB lock statement.
 *
 * @package Leap
 * @category MariaDB
 * @version 2013-01-12
 *
 * @see http://dev.mysql.com/doc/refman/5.6/en/lock-tables.html
 *
 * @abstract
 */
abstract class Base\DB\MariaDB\Lock\Builder extends DB\SQL\Lock\Builder {

	/**
	 * This function acquires the required locks.
	 *
	 * @access public
	 * @override
	 * @return DB\SQL\Lock\Builder                     a reference to the current instance
	 */
	public function acquire() {
		$this->connection->execute('LOCK TABLES ' . implode(',', $this->data) . ';');
		return $this;
	}

	/**
	 * This function adds a lock definition.
	 *
	 * @access public
	 * @override
	 * @param string $table                            the table to be locked
	 * @param array $hints                             the hints to be applied
	 * @return DB\SQL\Lock\Builder                     a reference to the current instance
	 */
	public function add($table, Array $hints = NULL) {
		$modes = array();
		if ($hints !== NULL) {
			foreach ($hints as $hint) {
				if (preg_match('/^((LOW_PRIORITY )?WRITE)|(READ( LOCAL)?)$/i', $hint)) {
					$modes[] = strtoupper($hint);
				}
			}
		}
		if (empty($modes)) {
			$modes[] = 'WRITE';
		}
		$this->data[] = $this->precompiler->prepare_identifier($table) . ' ' . implode('|', $modes);
		return $this;
	}

	/**
	 * This function releases all acquired locks.
	 *
	 * @access public
	 * @override
	 * @param string $method                           the method to be used to release
	 *                                                 the lock(s)
	 * @return DB\SQL\Lock\Builder                     a reference to the current instance
	 */
	public function release($method = '') {
		$this->connection->execute('UNLOCK TABLES;');
		return $this;
	}

}
