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
 * This class builds an SQLite lock statement.
 *
 * @package Leap
 * @category SQLite
 * @version 2013-01-12
 *
 * @abstract
 */
abstract class Base_DB_SQLite_Lock_Builder extends DB_SQL_Lock_Builder {

	/**
	 * This function acquires the required locks.
	 *
	 * @access public
	 * @override
	 * @return DB_SQL_Lock_Builder                     a reference to the current instance
	 */
	public function acquire() {
		$this->connection->begin_transaction();
		return $this;
	}

	/**
	 * This function adds a lock definition.
	 *
	 * @access public
	 * @override
	 * @param string $table                            the table to be locked
	 * @param array $hints                             the hints to be applied
	 * @return DB_SQL_Lock_Builder                     a reference to the current instance
	 */
	public function add($table, Array $hints = NULL) {
		// do nothing
		return $this;
	}

	/**
	 * This function releases all acquired locks.
	 *
	 * @access public
	 * @override
	 * @return DB_SQL_Lock_Builder                     a reference to the current instance
	 */
	public function release() {
		$this->connection->commit();
		return $this;
	}

}
