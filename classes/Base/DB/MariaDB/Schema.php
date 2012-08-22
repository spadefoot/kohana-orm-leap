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
 * This class provides a way to access the scheme for a MariaDB database.
 *
 * @package Leap
 * @category MariaDB
 * @version 2012-08-21
 *
 * @abstract
 */
abstract class Base_DB_MariaDB_Schema extends DB_Schema {

	/**
	 * This function returns a result set that contains an array of all fields in
	 * the specified database table/view.
	 *
	 * @access public
	 * @param string $table					the table/view to evaluated
	 * @param string $like                  a like constraint on the query
	 * @return DB_ResultSet 				an array of fields within the specified
	 * 										table
	 */
	public function fields($table, $like = '') {
		/*
		$sql = 'SHOW FULL COLUMNS FROM ' . $this->compiler->prepare_identifier($table);

		if ( ! empty($like)) {
			$like = $this->compiler->prepare_value($like);
			$sql .= ' LIKE ' . $like;
		}

		$sql .= ';';

		$connection = DB_Connection_Pool::instance()->get_connection($this->source);
		$records = $connection->query($sql)->as_array();

		$fields = array();
		$ordinal_position = 0;

		foreach ($records as $record) {
			$field = $record['Field'];

			$fields[$field]['table_name'] = $table;
			$fields[$field]['field_name'] = $record['Field'];

			$type = $this->parse_type($record['Type']);

			$actual_type = $type[0];

			switch ($actual_type) {
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
			}

			$fields[$field]['actual_type'] = $actual_type; // database's data type
			$fields[$field]['type'] = $type[0]; // PHP's data type

			$fields[$field]['maximum_length'] = $type[1];
			$fields[$field]['decimal_digits'] = $type[2];

			$fields[$field]['attributes'] = $type[3];

			$fields[$field]['nullable'] = ($record['Null'] == 'YES');

			$default_value = $record['Default'];
			if ($default_value != 'NULL') {
				switch ($type[0]) {
					case 'boolean':
						settype($default_value, 'boolean');
					break;
					case 'bit':
					case 'integer':
						settype($default_value, 'integer');
					break;
					case 'decimal':
					case 'double':
						settype($default_value, 'double');
					break;
					case 'binary':
					case 'blob':
					case 'date':
					case 'datetime':
					case 'string':
					case 'text':
					case 'time':
						settype($default_value, 'string');
					break;
				}
				$fields[$field]['default_value'] = $default_value;
			}
			else {
				$fields[$field]['default_value'] = NULL;
			}

			$fields[$field]['ordinal_position'] = $ordinal_position; // TODO fix ordinal position
			$ordinal_position++;
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
	 */
	public function indexes($table) {
		/*
		$table = $this->compiler->prepare_identifier($table);

		$sql = 'SHOW INDEX FROM ' . $table . ';';

		$connection = DB_Connection_Pool::instance()->get_connection($this->source);
		$records = $connection->query($sql)->as_array();

		$buffer = array();
		$i = 0;

		foreach ($records as $record) {
			$buffer[$i]['table_name'] = $record['Table'];
			$buffer[$i]['field_name'] = $record['Column_name'];
			$buffer[$i]['index_name'] = $record['Key_name'];
			$buffer[$i]['sequence'] = (int) $record['Seq_in_index'];
			$buffer[$i]['is_primary_key'] = ($record['Key_name'] == 'PRIMARY');
			$buffer[$i]['is_unique'] = ($record['Non_unique'] == '0');
			$i++;
		}

		return new DB_ResultSet($buffer, $i, 'array');
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
	 * @see http://www.geeksww.com/tutorials/database_management_systems/mysql/tips_and_tricks/mysql_query_to_find_all_views_in_a_database.php
	 */
	public function tables($like = '') {
		/*
		$builder = DB_SQL::select($this->source)
			->column('TABLE_NAME', 'table_name')
			->from('information_schema.TABLES')
			->where('TABLE_SCHEMA', DB_SQL_Operator::_LIKE_, $this->source->database)
			->where('TABLE_TYPE', DB_SQL_Operator::_LIKE_, 'BASE_TABLE')
			->order_by(DB_SQL::expr('UPPER(`TABLE_NAME`)'));

		if ( ! empty($like)) {
			$builder->where('TABLE_NAME', DB_SQL_Operator::_LIKE_, $like);
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
	 * @return DB_ResultSet 				an array of views within the database
	 *
	 * @see http://www.geeksww.com/tutorials/database_management_systems/mysql/tips_and_tricks/mysql_query_to_find_all_views_in_a_database.php
	 */
	public function views($like = '') {
		/*
		$builder = DB_SQL::select($this->source)
			->column('TABLE_NAME', 'table_name')
			->from('information_schema.TABLES')
			->where('TABLE_SCHEMA', DB_SQL_Operator::_LIKE_, $this->source->database)
			->where('TABLE_TYPE', DB_SQL_Operator::_LIKE_, 'VIEW')
			->order_by(DB_SQL::expr('UPPER(`TABLE_NAME`)'));

		if ( ! empty($like)) {
			$builder->where('TABLE_NAME', DB_SQL_Operator::_LIKE_, $like);
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
		case 'blob':
			$type[0] = 'string';
			$type[2] = '65535';
		break;
		case 'bool':
			$type[0] = 'boolean';
		break;
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

	/**
	 * This function extracts a field's data type information.
	 *
	 * @access public
	 * @static
	 * @param string $type                  the data type to be parsed
	 * @return array                        an array with the field's type information
	 *
	 * @see http://kohanaframework.org/3.1/guide/api/Database#_parse_type
	 */
	protected static function parse_type($type) {
		/*
		$open = strpos($type, '(');

		if ($open === FALSE) {
			return array($type, 0, 0);
		}

		$close = strpos($type, ')', $open);

		$args = preg_split('/,/', substr($type, $open + 1, $close - 1 - $open));

		$info = array();
		$info[0] = substr($type, 0, $open) . substr($type, $close + 1); // actual type
		$info[1] = (isset($args[0])) ? $args[0] : 0; // maximum length
		$info[2] = (isset($args[1])) ? $args[1] : 0; // decimal digits
		$info[3] = array(); // attributes

		return $info;
		*/
	}

}
?>