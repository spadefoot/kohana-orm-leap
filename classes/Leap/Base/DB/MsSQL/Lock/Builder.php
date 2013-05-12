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
 * This class builds a MS SQL lock statement.
 *
 * @package Leap
 * @category MS SQL
 * @version 2013-01-13
 *
 * @see http://msdn.microsoft.com/en-us/library/ms187373.aspx
 * @see http://stackoverflow.com/questions/5102152/tablock-vs-tablockx
 * @see http://www.mssqlcity.com/Articles/Adm/SQL70Locks.htm
 * @see http://msdn.microsoft.com/en-us/library/aa213041%28v=sql.80%29.aspx
 * @see http://msdn.microsoft.com/en-us/library/ms173763.aspx
 * @see http://stackoverflow.com/questions/1636173/lock-a-table-in-sql-server
 *
 * @abstract
 */
abstract class Base\DB\MsSQL\Lock\Builder extends DB\SQL\Lock\Builder {

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
		$sql = "SELECT * FROM {$table} WITH (";
		$modes = array();
		if ($hints !== NULL) {
			foreach ($hints as $hint) {
				if (preg_match('/^FORCESCAN|HOLDLOCK|NOLOCK|NOWAIT|PAGLOCK|READCOMMITTED|READCOMMITTEDLOCK|READPAST|READUNCOMMITTED|REPEATABLEREAD|ROWLOCK|SERIALIZABLE|TABLOCK|TABLOCKX|UPDLOCK|XLOCK$/i', $hint)) {
					$modes[] = strtoupper($hint);
				}
				else if (preg_match('/^(INDEX|FORCESEEK).+$/i', $hint)) {
					$modes[] = DB\SQL::expr($hint);
				}
			}
		}
		if (empty($modes)) {
			$modes[] = 'TABLOCKX';
		}
		$this->data[$table] = $sql . implode(', ', $modes) . ');';
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
