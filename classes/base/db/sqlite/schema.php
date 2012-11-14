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
 * This class provides a way to access the scheme for a SQLite database.
 *
 * @package Leap
 * @category SQLite
 * @version 2012-02-09
 *
 * @abstract
 */
abstract class Base_DB_SQLite_Schema extends DB_Schema {

	/**
	 * This function returns a result set that contains an array of all fields in
	 * the specified database table/view.
	 *
	 * @access public
	 * @param string $table					the table/view to evaluated
	 * @param string $like                  a like constraint on the query
	 * @return array 						an array of fields within the specified
	 * 										table
	 */
	public function fields($table, $like = '') {
		/*
		$table = $this->compiler->prepare_value($table);
		$regex = $this->like_to_regex($like);

		$sql = "PRAGMA table_info({$table});";

		$connection = DB_Connection_Pool::instance()->get_connection($this->source);
		$records = $connection->query($sql);

		$fields = array();
		foreach ($records as $record) {
			if ( ! empty($regex) OR preg_match($regex, $record['name'])){
				list($type, $length) = $this->parse_type($record['Type']);

				$field = $this->data_type($type);

				$field['name'] = $record['name'];
				$field['type'] = $type;
				$field['primary_key'] = ($record['pk'] == 1);
				if ($field['primary_key']) {
					$field['attributes']['auto_incremented'] = ($record['Extra'] == 'auto_increment');
				}
				$field['nullable'] = ($record['notnull'] == 1);
				$field['default'] = $record['dflt_value'];

				switch ($field['type']) {
					case 'float':
						if (isset($length)) {
							list($field['precision'], $field['scale']) = explode(',', $length);
						}
					break;
					case 'int':
						if (isset($length)) {
							$field['display'] = $length;
						}
					break;
					case 'string':
						switch ($field['data_type']) {
							case 'binary':
							case 'varbinary':
								$field['max_length'] = $length;
							break;
							case 'char':
							case 'varchar':
								$field['max_length'] = $length;
							case 'text':
							case 'tinytext':
							case 'mediumtext':
							case 'longtext':
								$field['collation'] = $record['Collation'];
							break;
							case 'enum':
							case 'set':
								$field['collation'] = $record['Collation'];
								$field['options'] = explode('\',\'', substr($length, 1, -1));
							break;
						}
					break;
				}

				$fields[$record['name']] = $field;
			}
		}

		return $records;
		*/
	}

	/**
	 * This function returns a result set that contains an array of all indexes from
	 * the specified table.
	 *
	 * @access public
	 * @param string $table					the table/view to evaluated
	 * @return array 						an array of indexes from the specified
	 * 										table
	 *
	 * @see http://stackoverflow.com/questions/157392/how-do-i-find-out-if-a-sqlite-index-is-unique-with-sql
	 */
	public function indexes($table) {
		/*
		$sql = "PRAGMA INDEX_LIST('" . $table . "');";

		$connection = DB_Connection_Pool::instance()->get_connection($this->source);
		$results = $connection->query($sql);

		return $results;
		*/
	}

	/**
	 * This function returns a result set that contains an array of all tables within
	 * the database.
	 *
	 * @access public
	 * @param string $like                  a like constraint on the query
	 * @return array 						an array of tables within the database
	 */
	public function tables($like = '') {
		/*
		$sql = "SELECT [tbl_name] AS [name] FROM [sqlite_master] WHERE [type] = 'table' AND [tbl_name] NOT IN ('sqlite_sequence')";

		if ( ! empty($like)) {
			$like = $this->compiler->prepare_value($like);
			$sql .= ' AND [tbl_name] LIKE ' . $like;
		}

		$sql .= ' ORDER BY LOWER([tbl_name])';
		$sql .= ';';

		$connection = DB_Connection_Pool::instance()->get_connection($this->source);
		$results = $connection->query($sql);

		return $results;
		*/
	}

	/**
	 * This function returns a result set that contains an array of all views within
	 * the database.
	 *
	 * @access public
	 * @param string $like                  a like constraint on the query
	 * @return array 						an array of views within the database
	 */
	public function views($like = '') {
		/*
		return array();
		*/
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This function returns an associated array which describes the properties
	 * for the specified SQL data type.
	 *
	 * @access protected
	 * @param string $type                  the SQL data type
	 * @return array                        an associated array which describes the properties
	 *                                      for the specified data type
	 */
	protected function data_type($type) {

	}

	/**
	 * This function converts a like clause to a regular expression.
	 *
	 * @access protected
	 * @param string $like					the like clause to be converted
	 * @return string						the resulting regular expression
	 *
	 * @see http://www.regular-expressions.info/mysql.html
	 */
	protected function like_to_regex($like) {
		/*
		if ( ! empty($like)) {
			$length = strlen($like);
			if (preg_match('/^%.*%$/' , $like)) {
				return '/^.*' . substr($like, 1, $length - 2) . '.*$/';
			}
			else if (preg_match('/^.*%$/', $like)) {
				return '/^' . substr($like, 0, $length - 1) . '.*$/';
			}
			else if (preg_match('/^%.*$/', $like)) {
				return '/^.*' . substr($like, 1, $length - 1) . '$/';
			}
			else {
				return '/^' . $like . '$/';
			}
		}
		return '';
		*/
	}

}
?>