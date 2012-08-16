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
 * This class builds a SQLite select statement.
 *
 * @package Leap
 * @category SQL
 * @version 2012-08-16
 *
 * @see http://www.sqlite.org/lang_select.html
 *
 * @abstract
 */
abstract class Base_DB_SQLite_Select_Builder extends DB_SQL_Select_Builder {

	/**
	 * This function returns the SQL statement.
	 *
	 * @access public
	 * @param boolean $terminated           whether to add a semi-colon to the end
	 *                                      of the statement
	 * @return string                       the SQL statement
	 */
	public function statement($terminated = TRUE) {
		$sql = 'SELECT ';

		if ($this->data['distinct']) {
			$sql .= 'DISTINCT ';
		}

		$sql .= ( ! empty($this->data['column']))
			? implode(', ', $this->data['column'])
			: $this->data['wildcard'];

		if ($this->data['from'] !== NULL) {
			$sql .= " FROM {$this->data['from']}";
		}

		foreach ($this->data['join'] as $join) {
			$sql .= " {$join[0]}";
			if ( ! empty($join[1])) {
				$sql .= ' ON (' . implode(' AND ', $join[1]) . ')';
			}
			else if ( ! empty($join[2])) {
				$sql .= ' USING ' . implode(', ', $join[2]);
			}
		}

		if ( ! empty($this->data['where'])) {
			$do_append = FALSE;
			$sql .= ' WHERE ';
			foreach ($this->data['where'] as $where) {
				if ($do_append && ($where[1] != DB_SQL_Builder::_CLOSING_PARENTHESIS_)) {
					$sql .= " {$where[0]} ";
				}
				$sql .= $where[1];
				$do_append = ($where[1] != DB_SQL_Builder::_OPENING_PARENTHESIS_);
			}
		}

		if ( ! empty($this->data['group_by'])) {
			$sql .= ' GROUP BY ' . implode(', ', $this->data['group_by']);
		}

		if ( ! empty($this->data['having'])) {
			$do_append = FALSE;
			$sql .= ' HAVING ';
			foreach ($this->data['having'] as $having) {
				if ($do_append && ($having[1] != DB_SQL_Builder::_CLOSING_PARENTHESIS_)) {
					$sql .= " {$having[0]} ";
				}
				$sql .= $having[1];
				$do_append = ($having[1] != DB_SQL_Builder::_OPENING_PARENTHESIS_);
			}
		}

		if ( ! empty($this->data['order_by'])) {
			$sql .= ' ORDER BY ' . implode(', ', $this->data['order_by']);
		}

		if ($this->data['limit'] > 0) {
			$sql .= " LIMIT {$this->data['limit']}";
		}

		if ($this->data['offset'] > 0) {
			$sql .= " OFFSET {$this->data['offset']}";
		}

		foreach ($this->data['combine'] as $combine) {
			$sql .= " {$combine}";
		}

		if ($terminated) {
			$sql .= ';';
		}

		return $sql;
	}

}
?>