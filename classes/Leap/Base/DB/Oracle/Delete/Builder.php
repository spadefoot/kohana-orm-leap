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
 * This class builds an Oracle delete statement.
 *
 * @package Leap
 * @category Oracle
 * @version 2012-12-05
 *
 * @see http://download.oracle.com/docs/cd/B19306_01/server.102/b14200/statements_8005.htm
 * @see http://download.oracle.com/docs/cd/B12037_01/appdev.101/b10807/13_elems014.htm
 *
 * @abstract
 */
abstract class Base\DB\Oracle\Delete\Builder extends DB\SQL\Delete\Builder {

	/**
	 * This function returns the SQL statement.
	 *
	 * @access public
	 * @override
	 * @param boolean $terminated           whether to add a semi-colon to the end
	 *                                      of the statement
	 * @return string                       the SQL statement
	 *
	 * @see http://www.oracle.com/technetwork/issue-archive/2006/06-sep/o56asktom-086197.html
	 * @see http://docs.oracle.com/cd/B12037_01/appdev.101/b10807/13_elems014.htm
	 */
	public function statement($terminated = TRUE) {
		if ( ! empty($this->data['order_by']) OR ($this->data['limit'] > 0) OR ($this->data['offset'] > 0)) {
			$sql = "SELECT * FROM {$this->data['from']}";

			if ( ! empty($this->data['where'])) {
				$append = FALSE;
				$sql .= ' WHERE ';
				foreach ($this->data['where'] as $where) {
					if ($append AND ($where[1] != DB\SQL\Builder::_CLOSING_PARENTHESIS_)) {
						$sql .= " {$where[0]} ";
					}
					$sql .= $where[1];
					$append = ($where[1] != DB\SQL\Builder::_OPENING_PARENTHESIS_);
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

			$sql = "DELETE FROM ({$sql})";
		}
		else {
			$sql = "DELETE FROM {$this->data['from']}";

			if ( ! empty($this->data['where'])) {
				$append = FALSE;
				$sql .= ' WHERE ';
				foreach ($this->data['where'] as $where) {
					if ($append AND ($where[1] != DB\SQL\Builder::_CLOSING_PARENTHESIS_)) {
						$sql .= " {$where[0]} ";
					}
					$sql .= $where[1];
					$append = ($where[1] != DB\SQL\Builder::_OPENING_PARENTHESIS_);
				}
			}
		}

		if ($terminated) {
			$sql .= ';';
		}

		return $sql;
	}

}
