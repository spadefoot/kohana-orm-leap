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
 * This class provides a way to access the scheme for a Oracle database.
 *
 * @package Leap
 * @category Oracle
 * @version 2012-08-21
 *
 * @abstract
 */
abstract class Base_DB_Oracle_Schema extends DB_Schema {

	/**
	 * This function returns a result set that contains an array of all fields in
	 * the specified database table/view.
	 *
	 * @access public
	 * @param string $table					the table/view to evaluated
	 * @param string $like                  a like constraint on the query
	 * @return DB_ResultSet         		an array of fields within the specified
	 * 										table
	 *
	 * @see http://stackoverflow.com/questions/205736/oracle-get-list-of-all-tables
	 */
	public function fields($table, $like = '') {
		/*
		SELECT table_name, column_name
		FROM cols
		WHERE table_name LIKE 'EST%'
		AND column_name LIKE '%CALLREF%';
		*/
		/*
		$table = $this->compiler->prepare_identifier($table);

		$sql = 'SHOW FULL COLUMNS FROM ' . $table;

		if ( ! empty($like)) {
			$like = $this->compiler->prepare_value($like);
			$sql .= ' LIKE ' . $like;
		}

		$sql .= ';';

		$connection = DB_Connection_Pool::instance()->get_connection($this->source);
		$records = $connection->query($sql);

		$fields = array();

		foreach ($records as $record) {
			list($type, $length) = $this->parse_type($record['Type']);

			$field = $this->data_type($type);

			$field['name'] = $record['Field'];
			$field['type'] = $type;
			$field['primary_key'] = ($record['Key'] == 'PRI');
			if ($field['primary_key']) {
				$field['attributes']['auto_incremented'] = ($record['Extra'] == 'auto_increment');
			}
			$field['nullable'] = ($record['Null'] == 'YES');
			$field['default'] = $record['Default'];

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

			$fields[$record['Field']] = $field;
		}

		return $fields;
		*/
	}

	/**
	 * This function returns a result set that contains an array of all indexes from
	 * the specified table.
	 *
	 * @access public
	 * @param string $table					the table/view to evaluated
	 * @return DB_ResultSet 				an array of indexes from the specified
	 * 										table
	 *
	 * @see http://www.razorsql.com/articles/oracle_system_queries.html
	 * @see http://forums.oracle.com/forums/thread.jspa?threadID=424532
	 */
	public function indexes($table) {
		/*
		$sql = "SELECT INDEX_NAME, TABLE_NAME, TABLE_OWNER FROM SYS.ALL_INDEXES ORDER BY TABLE_OWNER, TABLE_NAME, INDEX_NAME;";

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
	 * @return DB_ResultSet 				an array of tables within the database
	 *
	 * @see http://infolab.stanford.edu/~ullman/fcdb/oracle/or-nonstandard.html
	 * @see http://stackoverflow.com/questions/205736/oracle-get-list-of-all-tables
	 */
	public function tables($like = '') {
		/*
		$builder = DB_SQL::select($this->source)
			->column('table_name', 'name')
			//->from('dba_tables')
			//->from('all_tables')
			->from('user_tables')
			->order_by(DB_SQL::expr('LOWER("table_name")'));

		if ( ! empty($like)) {
			$builder->where('table_name', DB_SQL_Operator::_LIKE_, $like);
		}

		$results = $builder->query();

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
	 * 
	 * @see http://infolab.stanford.edu/~ullman/fcdb/oracle/or-nonstandard.html
	 */
	public function views($like = '') {
		/*
		$builder = DB_SQL::select($this->source)
			->column('view_name', 'name')
			//->from('dba_views')
			//->from('all_views')
			->from('user_views')
			->order_by(DB_SQL::expr('LOWER("view_name")'));

		if ( ! empty($like)) {
			$builder->where('view_name', DB_SQL_Operator::_LIKE_, $like);
		}

		$results = $builder->query();

		return $results;
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
		/*
		static $types = array(
			'blob'                      => array('type' => 'string', 'binary' => TRUE, 'character_maximum_length' => '65535'),
			'bool'                      => array('type' => 'bool'),
			'bigint unsigned'           => array('type' => 'int', 'min' => '0', 'max' => '18446744073709551615'),
			'datetime'                  => array('type' => 'string'),
			'decimal unsigned'          => array('type' => 'float', 'exact' => TRUE, 'min' => '0'),
			'double'                    => array('type' => 'float'),
			'double precision unsigned' => array('type' => 'float', 'min' => '0'),
			'double unsigned'           => array('type' => 'float', 'min' => '0'),
			'enum'                      => array('type' => 'string'),
			'fixed'                     => array('type' => 'float', 'exact' => TRUE),
			'fixed unsigned'            => array('type' => 'float', 'exact' => TRUE, 'min' => '0'),
			'float unsigned'            => array('type' => 'float', 'min' => '0'),
			'int unsigned'              => array('type' => 'int', 'min' => '0', 'max' => '4294967295'),
			'integer unsigned'          => array('type' => 'int', 'min' => '0', 'max' => '4294967295'),
			'longblob'                  => array('type' => 'string', 'binary' => TRUE, 'character_maximum_length' => '4294967295'),
			'longtext'                  => array('type' => 'string', 'character_maximum_length' => '4294967295'),
			'mediumblob'                => array('type' => 'string', 'binary' => TRUE, 'character_maximum_length' => '16777215'),
			'mediumint'                 => array('type' => 'int', 'min' => '-8388608', 'max' => '8388607'),
			'mediumint unsigned'        => array('type' => 'int', 'min' => '0', 'max' => '16777215'),
			'mediumtext'                => array('type' => 'string', 'character_maximum_length' => '16777215'),
			'national varchar'          => array('type' => 'string'),
			'numeric unsigned'          => array('type' => 'float', 'exact' => TRUE, 'min' => '0'),
			'nvarchar'                  => array('type' => 'string'),
			'point'                     => array('type' => 'string', 'binary' => TRUE),
			'real unsigned'             => array('type' => 'float', 'min' => '0'),
			'set'                       => array('type' => 'string'),
			'smallint unsigned'         => array('type' => 'int', 'min' => '0', 'max' => '65535'),
			'text'                      => array('type' => 'string', 'character_maximum_length' => '65535'),
			'tinyblob'                  => array('type' => 'string', 'binary' => TRUE, 'character_maximum_length' => '255'),
			'tinyint'                   => array('type' => 'int', 'min' => '-128', 'max' => '127'),
			'tinyint unsigned'          => array('type' => 'int', 'min' => '0', 'max' => '255'),
			'tinytext'                  => array('type' => 'string', 'character_maximum_length' => '255'),
			'year'                      => array('type' => 'string'),
		);

		$type = str_replace(' zerofill', '', $type);

		if (isset($types[$type])) {
			return $types[$type];
		}

		return parent::data_type($type);
		*/
	}

}
?>