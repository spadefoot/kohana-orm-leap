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
 * This class builds a PostgreSQL lock statement.
 *
 * @package Leap
 * @category PostgreSQL
 * @version 2013-01-13
 *
 * @see http://www.postgresql.org/docs/9.2/static/sql-lock.html
 *
 * @abstract
 */
abstract class Base\DB\PostgreSQL\Lock\Builder extends DB\SQL\Lock\Builder {

	/**
	 * This function acquires the required locks.
	 *
	 * @access public
	 * @override
	 * @return DB\SQL\Lock\Builder                     a reference to the current instance
	 */
	public function acquire() {
		$this->connection->begin_transaction();
		foreach ($this->data as $sql) {
			$this->connection->execute($sql);
		}
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
		$table = $this->precompiler->prepare_identifier($table);
		$sql = "LOCK TABLE {$table} IN ";
		$mode = 'EXCLUSIVE';
		$wait = '';
		if ($hints !== NULL) {
			foreach ($hints as $hint) {
				if (preg_match('/^(EXCLUSIVE)|((ACCESS|ROW) (SHARE|EXCLUSIVE))|(SHARE( (UPDATE|ROW) EXCLUSIVE)?)$/i', $hint)) {
					$mode = strtoupper($hint);
				}
				else if (preg_match('/^NOWAIT$/i', $hint)) {
					$wait = ' NOWAIT';
				}
			}
		}
		$this->data[$table] = $sql . $mode . ' MODE' . $wait . ';';
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
		switch (strtoupper($method)) {
			case 'ROLLBACK':
				$this->connection->rollback();
			break;
			case 'COMMIT':
			default:
				$this->connection->commit();
			break;
		}
		return $this;
	}

}
