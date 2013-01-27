<?php defined('SYSPATH') OR die('No direct script access.');

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
 * This class provides a way to access the scheme for a Drizzle database.
 *
 * @package Leap
 * @category Drizzle
 * @version 2013-01-05
 *
 * @abstract
 */
abstract class Base_DB_Drizzle_Schema extends DB_Schema {

	/**
	 * This function returns a result set that contains an array of all fields in
	 * the specified database table/view.
	 *
	 * @access public
	 * @override
	 * @param string $table                 the table/view to evaluated
	 * @param string $like                  a like constraint on the query
	 * @return DB_ResultSet                 an array of fields within the specified
	 *                                      table
	 */
	public function fields($table, $like = '') {
		/*
		$sql = 'SHOW FULL COLUMNS FROM ' . $this->precompiler->prepare_identifier($table);

		if ( ! empty($like)) {
			$like = $this->precompiler->prepare_value($like);
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
	 * This function returns a result set of indexes for the specified table.
	 *
	 * +---------------+---------------+------------------------------------------------------------+
	 * | field         | data type     | description                                                |
	 * +---------------+---------------+------------------------------------------------------------+
	 * | schema        | string        | The name of the schema that contains the table.            |
	 * | table         | string        | The name of the table.                                     |
	 * | index         | string        | The name of the index.                                     |
	 * | column        | string        | The name of the column.                                    |
	 * | seq_index     | integer       | The sequence index of the index.                           |
	 * | ordering      | string        | The ordering of the index.                                 |
	 * | unique        | boolean       | Indicates whether index on column is unique.               |
	 * | primary       | boolean       | Indicates whether index on column is a primary key.        |
	 * +---------------+---------------+------------------------------------------------------------+
	 *
	 * @access public
	 * @override
	 * @param string $table                 the table to evaluated
	 * @param string $like                  a like constraint on the query
	 * @return DB_ResultSet                 a result set of indexes for the specified
	 *                                      table
	 *
	 * @see http://dev.mysql.com/doc/refman/5.6/en/show-index.html
	 */
	public function indexes($table, $like = '') {
		$connection = DB_Connection_Pool::instance()->get_connection($this->source);

		$schema = $this->precompiler->prepare_identifier($this->source->database);
		$table = $this->precompiler->prepare_identifier($table);

		$sql = "SHOW INDEXES FROM {$table} FROM {$schema}";

		if ( ! empty($like)) {
			$sql .= " WHERE 'Key_name' LIKE " . $this->precompiler->prepare_value($like);
		}

		$sql .= ';';

		$reader = $connection->reader($sql);

		$records = array();

		while ($reader->read()) {
			$record = $reader->row('array');
			$buffer = array(
				'schema' => $this->source->database,
				'table' => $record['Table'],
				'index' => $record['Key_name'],
				'column' => $record['Column_name'],
				'seq_index' => $record['Seq_in_index'],
				'ordering' => ($record['Collation'] == 'A') ? 'ASC' : NULL,
				'unique' => ($record['Non_unique'] == '0'),
				'primary' => ($record['Key_name'] == 'PRIMARY'),
			);
			$records[] = $buffer;
		}

		$reader->free();

		$results = new DB_ResultSet($records);

		return $results;
	}

	/**
	 * This function returns a result set of database tables.
	 *
	 * +---------------+---------------+------------------------------------------------------------+
	 * | field         | data type     | description                                                |
	 * +---------------+---------------+------------------------------------------------------------+
	 * | schema        | string        | The name of the schema that contains the table.            |
	 * | table         | string        | The name of the table.                                     |
	 * | type          | string        | The type of table.                                         |
	 * +---------------+---------------+------------------------------------------------------------+
	 *
	 * @access public
	 * @override
	 * @param string $like                  a like constraint on the query
	 * @return DB_ResultSet                 a result set of database tables
	 *
	 * @see http://www.geeksww.com/tutorials/database_management_systems/mysql/tips_and_tricks/mysql_query_to_find_all_views_in_a_database.php
	 */
	public function tables($like = '') {
		$builder = DB_SQL::select($this->source)
			->column('TABLE_SCHEMA', 'schema')
			->column('TABLE_NAME', 'table')
			->column(DB_SQL::expr("'BASE'"), 'type')
			->from('INFORMATION_SCHEMA.TABLES')
			//->where('TABLE_SCHEMA', DB_SQL_Operator::_EQUAL_TO_, $this->source->database)
			->where(DB_SQL::expr('UPPER(`TABLE_TYPE`)'), DB_SQL_Operator::_EQUAL_TO_, 'BASE_TABLE')
			->order_by(DB_SQL::expr('UPPER(`TABLE_SCHEMA`)'))
			->order_by(DB_SQL::expr('UPPER(`TABLE_NAME`)'));

		if ( ! empty($like)) {
			$builder->where('TABLE_NAME', DB_SQL_Operator::_LIKE_, $like);
		}

		return $builder->query();
	}

	/**
	 * This function returns a result set of triggers for the specified table.
	 *
	 * +---------------+---------------+------------------------------------------------------------+
	 * | field         | data type     | description                                                |
	 * +---------------+---------------+------------------------------------------------------------+
	 * | schema        | string        | The name of the schema that contains the table.            |
	 * | table         | string        | The name of the table to which the trigger is defined on.  |
	 * | trigger       | string        | The name of the trigger.                                   |
	 * | event         | string        | 'INSERT', 'DELETE', or 'UPDATE'                            |
	 * | timing        | string        | 'BEFORE', 'AFTER', or 'INSTEAD OF'                         |
	 * | per           | string        | 'ROW', 'STATEMENT', or 'EVENT'                             |
	 * | action        | string        | The action that will be triggered.                         |
	 * | seq_index     | integer       | The sequence index of the trigger.                         |
	 * | created       | date/time     | The date/time of when the trigger was created.             |
	 * +---------------+---------------+------------------------------------------------------------+
	 *
	 * @access public
	 * @override
	 * @param string $table                 the table to evaluated
	 * @param string $like                  a like constraint on the query
	 * @return DB_ResultSet                 a result set of triggers for the specified
	 *                                      table
	 *
	 * @see http://dev.mysql.com/doc/refman/5.6/en/triggers-table.html
	 * @see http://dev.mysql.com/doc/refman/5.6/en/show-triggers.html
	 */
	public function triggers($table, $like = '') {
		$builder = DB_SQL::select($this->source)
			->column('EVENT_OBJECT_SCHEMA', 'schema')
			->column('EVENT_OBJECT_TABLE', 'table')
			->column('TRIGGER_NAME', 'trigger')
			->column('EVENT_MANIPULATION', 'event')
			->column('ACTION_TIMING', 'timing')
			->column('ACTION_ORIENTATION', 'per')
			->column('ACTION_STATEMENT', 'action')
			->column('ACTION_ORDER', 'seq_index')
			->column('CREATED', 'created')
			->from('INFORMATION_SCHEMA.TRIGGERS')
			//->where('EVENT_OBJECT_SCHEMA', DB_SQL_Operator::_EQUAL_TO_, $this->source->database)
			->where(DB_SQL::expr('UPPER(`EVENT_OBJECT_TABLE`)'), DB_SQL_Operator::_EQUAL_TO_, $table)
			->order_by(DB_SQL::expr('UPPER(`EVENT_OBJECT_SCHEMA`)'))
			->order_by(DB_SQL::expr('UPPER(`EVENT_OBJECT_TABLE`)'))
			->order_by(DB_SQL::expr('UPPER(`TRIGGER_NAME`)'))
			->order_by('ACTION_ORDER');

		if ( ! empty($like)) {
			$builder->where('TRIGGER_NAME', DB_SQL_Operator::_LIKE_, $like);
		}

		return $builder->query();
	}

	/**
	 * This function returns a result set of database views.
	 *
	 * +---------------+---------------+------------------------------------------------------------+
	 * | field         | data type     | description                                                |
	 * +---------------+---------------+------------------------------------------------------------+
	 * | schema        | string        | The name of the schema that contains the table.            |
	 * | table         | string        | The name of the table.                                     |
	 * | type          | string        | The type of table.                                         |
	 * +---------------+---------------+------------------------------------------------------------+
	 *
	 * @access public
	 * @override
	 * @param string $like                  a like constraint on the query
	 * @return DB_ResultSet                 a result set of database views
	 *
	 * @see http://www.geeksww.com/tutorials/database_management_systems/mysql/tips_and_tricks/mysql_query_to_find_all_views_in_a_database.php
	 */
	public function views($like = '') {
		$builder = DB_SQL::select($this->source)
			->column('TABLE_SCHEMA', 'schema')
			->column('TABLE_NAME', 'table')
			->column(DB_SQL::expr("'VIEW'"), 'type')
			->from('INFORMATION_SCHEMA.TABLES')
			//->where('TABLE_SCHEMA', DB_SQL_Operator::_EQUAL_TO_, $this->source->database)
			->where(DB_SQL::expr('UPPER(`TABLE_TYPE`)'), DB_SQL_Operator::_EQUAL_TO_, 'VIEW')
			->order_by(DB_SQL::expr('UPPER(`TABLE_SCHEMA`)'))
			->order_by(DB_SQL::expr('UPPER(`TABLE_NAME`)'));

		if ( ! empty($like)) {
			$builder->where('TABLE_NAME', DB_SQL_Operator::_LIKE_, $like);
		}

		return $builder->query();
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This function returns an associated array which describes the properties
	 * for the specified SQL data type.
	 *
	 * @access protected
	 * @override
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
