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
 * This class builds an Oracle update statement.
 *
 * @package Leap
 * @category Oracle
 * @version 2012-01-28
 *
 * @see http://download.oracle.com/docs/cd/B19306_01/server.102/b14200/statements_10007.htm#i2067715
 * @see http://psoug.org/reference/update.html
 *
 * @abstract
 */
abstract class Base_DB_Oracle_Update_Builder extends DB_SQL_Update_Builder {

	/**
	 * This function returns the SQL statement.
	 *
	 * @access public
	 * @param boolean $terminated           whether to add a semi-colon to the end
	 *                                      of the statement
	 * @return string                       the SQL statement
	 *
	 * @see http://www.oracle.com/technetwork/issue-archive/2006/06-sep/o56asktom-086197.html
	 * @see http://geekswithblogs.net/WillSmith/archive/2008/06/18/oracle-update-with-join-again.aspx
	 */
	public function statement($terminated = TRUE) {
		if ( ! empty($this->data['order_by']) OR ($this->data['limit'] > 0) OR ($this->data['offset'] > 0)) {
			$sql = "SELECT * FROM {$this->data['table']}";

			if ( ! empty($this->data['where'])) {
				$append = FALSE;
				$sql .= ' WHERE ';
				foreach ($this->data['where'] as $where) {
					if ($append AND ($where[1] != DB_SQL_Builder::_CLOSING_PARENTHESIS_)) {
						$sql .= " {$where[0]} ";
					}
					$sql .= $where[1];
					$append = ($where[1] != DB_SQL_Builder::_OPENING_PARENTHESIS_);
				}
			}

			if ( ! empty($this->data['order_by'])) {
				$sql .= ' ORDER BY ' . implode(', ', $this->data['order_by']);
			}

			if (($this->data['limit'] > 0) AND ($this->data['offset'] > 0)) {
				$max_row_to_fetch = $this->data['offset'] + ($this->data['limit'] - 1);
				$min_row_to_fetch = $this->data['offset'];
				$sql = "SELECT * FROM (SELECT \"t0\".*, ROWNUM AS \"rn\" FROM ({$sql}) \"t0\" WHERE ROWNUM <= {$max_row_to_fetch}) WHERE \"rn\" >= {$min_row_to_fetch}";
			}
			else if ($this->data['limit'] > 0) {
				$sql = "SELECT * FROM ({$sql}) WHERE ROWNUM <= {$this->data['limit']}";
			}
			else if ($this->data['offset'] > 0) {
				$sql = "SELECT * FROM ({$sql}) WHERE ROWNUM >= {$this->data['offset']}";
			}

			$sql = "UPDATE ({$sql})";

			if ( ! empty($this->data['column'])) {
				$sql .= ' SET ' . implode(', ', array_values($this->data['column']));
			}
		}
		else {
			$sql = "UPDATE {$this->data['table']}";

			if ( ! empty($this->data['column'])) {
				$sql .= ' SET ' . implode(', ', array_values($this->data['column']));
			}

			if ( ! empty($this->data['where'])) {
				$append = FALSE;
				$sql .= ' WHERE ';
				foreach ($this->data['where'] as $where) {
					if ($append AND ($where[1] != DB_SQL_Builder::_CLOSING_PARENTHESIS_)) {
						$sql .= " {$where[0]} ";
					}
					$sql .= $where[1];
					$append = ($where[1] != DB_SQL_Builder::_OPENING_PARENTHESIS_);
				}
			}
		}

		if ($terminated) {
			$sql .= ';';
		}

		return $sql;
	}

}
?>