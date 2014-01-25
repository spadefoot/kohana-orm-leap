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
 * This class provides a way to access the scheme for a Oracle database.
 *
 * @package Leap
 * @category Oracle
 * @version 2013-02-02
 *
 * @abstract
 */
abstract class Base\DB\Oracle\Schema extends DB\Schema {

	/**
	 * This function returns an associated array which describes the properties
	 * for the specified SQL data type.
	 *
	 * @access public
	 * @override
	 * @param string $type                   the SQL data type
	 * @return array                         an associated array which describes the properties
	 *                                       for the specified data type
	 *
	 * @license http://kohanaframework.org/license
	 */
	public function data_type($type) {
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

		$type = strtoupper($type);
		$type = str_replace(' zerofill', '', $type);

		if (isset($types[$type])) {
			return $types[$type];
		}

		return parent::data_type($type);
		*/
	}

	/**
	 * This function returns a result set of fields for the specified table.
	 *
	 * +---------------+---------------+------------------------------------------------------------+
	 * | field         | data type     | description                                                |
	 * +---------------+---------------+------------------------------------------------------------+
	 * | schema        | string        | The name of the schema that contains the table.            |
	 * | table         | string        | The name of the table.                                     |
	 * | column        | string        | The name of the column.                                    |
	 * | type          | string        | The data type of the column.                               |
	 * | max_length    | integer       | The max length, max digits, or precision of the column.    |
	 * | max_decimals  | integer       | The max decimals or scale of the column.                   |
	 * | attributes    | string        | Any additional attributes associated with the column.      |
	 * | seq_index     | integer       | The sequence index of the column.                          |
	 * | nullable      | boolean       | Indicates whether the column can contain a NULL value.     |
	 * | default       | mixed         | The default value of the column.                           |
	 * +---------------+---------------+------------------------------------------------------------+
	 *
	 * @access public
	 * @override
	 * @param string $table                 the table to evaluated
	 * @param string $like                  a like constraint on the query
	 * @return DB\ResultSet                 an array of fields within the specified
	 *                                      table
	 *
	 * @see http://docs.oracle.com/cd/B19306_01/server.102/b14237/statviews_2094.htm
	 * @see http://stackoverflow.com/questions/205736/oracle-get-list-of-all-tables
	 */
	public function fields($table, $like = '') {
		/*
		SELECT
			TABLE_NAME,
			COLUMN_NAME
		FROM
			ALL_TAB_COLUMNS
		WHERE
			COLUMN_NAME LIKE '%PATTERN%';
		*/
		/*
		SELECT table_name, column_name
		FROM cols
		WHERE table_name LIKE 'EST%'
		AND column_name LIKE '%CALLREF%';
		*/
		/*
		$table = $this->precompiler->prepare_identifier($table);

		$sql = 'SHOW FULL COLUMNS FROM ' . $table;

		if ( ! empty($like)) {
			$like = $this->precompiler->prepare_value($like);
			$sql .= ' LIKE ' . $like;
		}

		$sql .= ';';

		$connection = DB\Connection\Pool::instance()->get_connection($this->data_source);
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
	 * @return DB\ResultSet                 a result set of indexes for the specified
	 *                                      table
	 *
	 * @see http://www.techonthenet.com/oracle/questions/find_pkeys.php
	 * @see http://docs.oracle.com/cd/B19306_01/server.102/b14237/statviews_1064.htm#i1577532
	 * @see http://docs.oracle.com/cd/B19306_01/server.102/b14237/statviews_1069.htm
	 * @see http://docs.oracle.com/cd/B19306_01/server.102/b14237/statviews_1037.htm
	 * @see http://www.razorsql.com/articles/oracle_system_queries.html
	 * @see http://forums.oracle.com/forums/thread.jspa?threadID=424532
	 * @see http://stackoverflow.com/questions/765867/list-of-all-index-index-columns-in-sql-server-db
	 * @see http://viralpatel.net/blogs/understanding-primary-keypk-constraint-in-oracle/
	 * @see http://www.techonthenet.com/oracle/questions/find_pkeys.php
	 */
	public function indexes($table, $like = '') {
		$builder = DB\SQL::select($this->data_source)
			->column('t0.TABLE_OWNER', 'schema')
			->column('t0.TABLE_NAME', 'table')
			->column('t0.INDEX_NAME', 'index')
			->column('t0.COLUMN_NAME', 'column')
			->column('t0.COLUMN_POSITION', 'seq_index')
			->column(DB\SQL::expr("CASE \"t0\".\"DESCEND\" WHEN 'Y' THEN 'DESC' ELSE 'ASC' END"), 'ordering')
			->column(DB\SQL::expr("CASE \"t2\".\"CONSTRAINT_TYPE\" WHEN 'P' THEN 1 WHEN 'U' THEN 1 ELSE 0 END"), 'unique')
			->column(DB\SQL::expr("CASE \"t2\".\"CONSTRAINT_TYPE\" WHEN 'P' THEN 1 ELSE 0 END"), 'primary')
			->from('SYS.ALL_IND_COLUMNS', 't0')
			//->join(DB\SQL\JoinType::_LEFT_, 'SYS.ALL_INDEXES', 't1')
			//->on('t1.OWNER', DB\SQL\Operator::_EQUAL_TO_, 't0.INDEX_OWNER')
			//->on('t1.INDEX_NAME', DB\SQL\Operator::_EQUAL_TO_, 't0.INDEX_NAME')
			->join(DB\SQL\JoinType::_LEFT_, 'SYS.ALL_CONSTRAINTS', 't2')
			->on('t2.INDEX_OWNER', DB\SQL\Operator::_EQUAL_TO_, 't0.INDEX_OWNER')
			->on('t2.INDEX_NAME', DB\SQL\Operator::_EQUAL_TO_, 't0.INDEX_NAME')
			->where('t0.TABLE_NAME', DB\SQL\Operator::_EQUAL_TO_, $table)
			//->where('t1.STATUS', DB\SQL\Operator::_EQUAL_TO_, 'VALID')
			->where('t2.STATUS', DB\SQL\Operator::_EQUAL_TO_, 'ENABLED')
			->order_by(DB\SQL::expr('UPPER("t0"."TABLE_OWNER")'))
			->order_by(DB\SQL::expr('UPPER("t0"."TABLE_NAME")'))
			->order_by(DB\SQL::expr('UPPER("t0"."INDEX_NAME")'))
			->order_by('t0.COLUMN_POSITION');

		if ( ! empty($like)) {
			$builder->where('t0.INDEX_NAME', DB\SQL\Operator::_LIKE_, $like);
		}

		return $builder->query();
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
	 * @return DB\ResultSet                 a result set of database tables
	 *
	 * @see http://infolab.stanford.edu/~ullman/fcdb/oracle/or-nonstandard.html
	 * @see http://stackoverflow.com/questions/205736/oracle-get-list-of-all-tables
	 * @see http://www.razorsql.com/articles/oracle_system_queries.html
	 */
	public function tables($like = '') {
		$builder = DB\SQL::select($this->data_source)
			->column('OWNER', 'schema')
			->column('TABLE_NAME', 'table')
			->column(DB\SQL::expr("'BASE'"), 'type')
			->from('SYS.ALL_TABLES')
			->order_by(DB\SQL::expr('UPPER("OWNER")'))
			->order_by(DB\SQL::expr('UPPER("TABLE_NAME")'));

		if ( ! empty($like)) {
			$builder->where('TABLE_NAME', DB\SQL\Operator::_LIKE_, $like);
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
	 * @return DB\ResultSet                 a result set of triggers for the specified
	 *                                      table
	 *
	 * @see http://docs.oracle.com/cd/B19306_01/server.102/b14237/statviews_2107.htm
	 * @see http://docs.oracle.com/cd/B19306_01/server.102/b14200/statements_7004.htm#i2235611
	 * @see http://www.razorsql.com/articles/oracle_system_queries.html
	 */
	public function triggers($table, $like = '') {
		$builder = DB\SQL::select($this->data_source)
			->column('OWNER', 'schema')
			->column('TABLE_NAME', 'table')
			->column('TRIGGER_NAME', 'trigger')
			->column('TRIGGERING_EVENT', 'event')
			->column(DB\SQL::expr("CASE UPPER(\"TRIGGER_TYPE\") WHEN 'BEFORE EACH ROW' THEN 'BEFORE' WHEN 'BEFORE STATEMENT' THEN 'BEFORE' WHEN 'BEFORE EVENT' THEN 'BEFORE' WHEN 'AFTER EACH ROW' THEN 'AFTER' WHEN 'AFTER STATEMENT' THEN 'AFTER' WHEN 'AFTER EVENT' THEN 'AFTER' ELSE NULL END"), 'timing')
			->column(DB\SQL::expr("CASE UPPER(\"TRIGGER_TYPE\") WHEN 'BEFORE STATEMENT' THEN 'STATEMENT' WHEN 'AFTER STATEMENT' THEN 'STATEMENT' WHEN 'BEFORE EVENT' THEN 'EVENT' WHEN 'AFTER EVENT' THEN 'EVENT' ELSE 'ROW' END"), 'per')
			->column('TRIGGER_BODY', 'action')
			->column(DB\SQL::expr('0'), 'seq_index')
			->column(DB\SQL::expr('NULL'), 'created')
			->from('SYS.ALL_TRIGGERS')
			->where('TABLE_NAME', DB\SQL\Operator::_EQUAL_TO_, $table)
			->where('STATUS', DB\SQL\Operator::_EQUAL_TO_, 'ENABLED')
			->order_by(DB\SQL::expr('UPPER("OWNER")'))
			->order_by(DB\SQL::expr('UPPER("TABLE_NAME")'))
			->order_by(DB\SQL::expr('UPPER("TRIGGER_NAME")'));

		if ( ! empty($like)) {
			$builder->where('TRIGGER_NAME', DB\SQL\Operator::_LIKE_, $like);
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
	 * @return DB\ResultSet                 a result set of database views
	 * 
	 * @see http://infolab.stanford.edu/~ullman/fcdb/oracle/or-nonstandard.html
	 * @see http://www.razorsql.com/articles/oracle_system_queries.html
	 */
	public function views($like = '') {
		$builder = DB\SQL::select($this->data_source)
			->column('OWNER', 'schema')
			->column('VIEW_NAME', 'table')
			->column(DB\SQL::expr("'VIEW'"), 'type')
			->from('SYS.ALL_VIEWS')
			->order_by(DB\SQL::expr('UPPER("OWNER")'))
			->order_by(DB\SQL::expr('UPPER("VIEW_NAME")'));

		if ( ! empty($like)) {
			$builder->where('VIEW_NAME', DB\SQL\Operator::_LIKE_, $like);
		}

		return $builder->query();
	}

}
