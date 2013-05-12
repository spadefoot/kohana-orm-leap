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
 * This class builds a MS SQL update statement.
 *
 * @package Leap
 * @category MS SQL
 * @version 2012-12-05
 *
 * @see http://msdn.microsoft.com/en-us/library/aa260662%28v=sql.80%29.aspx
 *
 * @abstract
 */
abstract class Base\DB\MsSQL\Update\Builder extends DB\SQL\Update\Builder {

	/**
	 * This function returns the SQL statement.
	 *
	 * @access public
	 * @override
	 * @param boolean $terminated           whether to add a semi-colon to the end
	 *                                      of the statement
	 * @return string                       the SQL statement
	 *
	 * @see http://stackoverflow.com/questions/655010/how-to-update-and-order-by-using-ms-sql
	 */
	public function statement($terminated = TRUE) {
		$alias = ($this->data['table'] == 't0') ? 't1' : 't0';

		$sql = "WITH {$alias} AS (";

		$sql .= 'SELECT';

		if ($this->data['limit'] > 0) {
			$sql .= " TOP {$this->data['limit']}";
		}

		$sql .= " * FROM {$this->data['table']}";

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

		//if ($this->data['offset'] > 0) {
		//    $sql .= " OFFSET {$this->data['offset']}";
		//}

		$sql .= ") UPDATE {$alias}";

		if ( ! empty($this->data['column'])) {
			$sql .= ' SET ' . implode(', ', array_values($this->data['column']));
		}

		if ($terminated) {
			$sql .= ';';
		}

		return $sql;
	}

}
