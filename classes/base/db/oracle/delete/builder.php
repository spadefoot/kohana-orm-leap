<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Copyright 2011 Spadefoot
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
 * This class builds an Oracle delete statement.
 *
 * @package Leap
 * @category Oracle
 * @version 2011-06-18
 *
 * @see http://download.oracle.com/docs/cd/B19306_01/server.102/b14200/statements_8005.htm
 * @see http://download.oracle.com/docs/cd/B12037_01/appdev.101/b10807/13_elems014.htm
 *
 * @abstract
 */
abstract class Base_DB_Oracle_Delete_Builder extends DB_SQL_Delete_Builder {

   /**
	 * This constructor instantiates this class.
	 *
	 * @access public
	 */
	public function __construct() {
		parent::__construct('Oracle');
	}

	/**
	 * This function returns the SQL statement.
	 *
	 * @access public
	 * @param boolean $terminated           whether to add a semi-colon to the end
	 *                                      of the statement
	 * @return string                       the SQL statement
	 *
	 * @see http://www.oracle.com/technetwork/issue-archive/2006/06-sep/o56asktom-086197.html
	 */
	public function statement($terminated = TRUE) {
		$sql = "DELETE FROM {$this->data['from']}";

		if (!empty($this->data['where'])) {
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

		if (!empty($this->data['order_by'])) {
			$sql .= ' ORDER BY ' . implode(', ', $this->data['order_by']);
		}

		if ($this->data['limit'] > 0) {
			$sql .= " LIMIT {$this->data['limit']}";
		}

		if ($this->data['offset'] > 0) {
			$sql .= " OFFSET {$this->data['offset']}";
		}

		if ($terminated) {
			$sql .= ';';
		}

		return $sql;
	}

}
?>