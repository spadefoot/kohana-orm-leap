<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Copyright © 2011–2013 Spadefoot Team.
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
 * This class provides a way to access the scheme for a PostgreSQL database.
 *
 * @package Leap
 * @category PostgreSQL
 * @version 2013-01-05
 *
 * @abstract
 */
abstract class Base_DB_PostgreSQL_Schema extends DB_Schema {

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
	 *
	 * @see http://www.linuxscrew.com/2009/07/03/postgresql-show-tables-show-databases-show-columns/
	 */
	public function fields($table, $like = '') {
		/*
		$this->_connection or $this->connect();

		$sql = 'SELECT column_name, column_default, is_nullable, data_type, character_maximum_length, numeric_precision, numeric_scale, datetime_precision'
			.' FROM information_schema.columns'
			.' WHERE table_schema = '.$this->quote($this->schema()).' AND table_name = '.$this->quote($table);

		if (is_string($like))
		{
			$sql .= ' AND column_name LIKE '.$this->quote($like);
		}

		$sql .= ' ORDER BY ordinal_position';

		$result = array();

		foreach ($this->query(Database::SELECT, $sql, FALSE) as $column)
		{
			$column = array_merge($this->datatype($column['data_type']), $column);

			$column['is_nullable'] = ($column['is_nullable'] === 'YES');

			$result[$column['column_name']] = $column;
		}

		return $result;
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
	 * | index         | string        | The name of the index.          .                          |
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
	 * @see http://www.alberton.info/postgresql_meta_info.html
	 * @see http://stackoverflow.com/questions/2204058/show-which-columns-an-index-is-on-in-postgresql
	 * @see http://code.activestate.com/recipes/576557/
	 * @see http://www.postgresql.org/docs/current/static/catalog-pg-index.html
	 */
	public function indexes($table, $like = '') {
		$builder = DB_SQL::select($this->source)
			->column('t4.schname', 'schema')
			->column('t0.relname', 'table')
			->column('t2.relname', 'index')
			->column('t3.attname', 'column')
			->column('t3.attnum', 'seq_index')
			->column(DB_SQL::expr('NULL'), 'ordering')
			->column(DB_SQL::expr("CASE \"t1\".\"indisunique\" WHEN 't' THEN 1 ELSE 0 END"), 'unique')
			->column(DB_SQL::expr("CASE \"t1\".\"indisprimary\" WHEN 't' THEN 1 ELSE 0 END"), 'primary')
			->from('pg_class', 't0')
			->join(DB_SQL_JoinType::_LEFT_, 'pg_index', 't1')
			->on('t1.indrelid', DB_SQL_Operator::_EQUAL_TO_, 't0.oid')
			->join(DB_SQL_JoinType::_LEFT_, 'pg_class', 't2')
			->on('t2.oid', DB_SQL_Operator::_EQUAL_TO_, 't1.indexrelid')
			->join(DB_SQL_JoinType::_LEFT_, 'pg_attribute', 't3')
			->on('t3.attrelid', DB_SQL_Operator::_EQUAL_TO_, 't0.oid')
			->on('t3.attnum', DB_SQL_Operator::_EQUAL_TO_, DB_SQL::expr('ANY("t1"."indkey")'))			
			->join(DB_SQL_JoinType::_LEFT_, DB_SQL::expr('(SELECT "t5"."table_schema" AS "schname", "t5"."table_schema" AS "relname" FROM "information_schema"."tables" AS "t5" WHERE "t5"."table_type" = \'BASE TABLE\' AND "t5"."table_schema" NOT IN (\'pg_catalog\', \'information_schema\'))'), 't4')
			->on('t4.relname', DB_SQL_Operator::_EQUAL_TO_, 't0.relname')
			->where('t0.relkind', DB_SQL_Operator::_EQUAL_TO_, 'r')
			->where('t0.relname', DB_SQL_Operator::_EQUAL_TO_, $table)
			->order_by(DB_SQL::expr('UPPER("t4"."schname")'))
			->order_by(DB_SQL::expr('UPPER("t0"."relname")'))
			->order_by(DB_SQL::expr('UPPER("t2"."relname")'))
			->order_by('t3.attnum');

		if ( ! empty($like)) {
			$builder->where('t2.relname', DB_SQL_Operator::_LIKE_, $like);
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
	 * | type          | string        | The type of table.              .                          |
	 * +---------------+---------------+------------------------------------------------------------+
	 *
	 * @access public
	 * @override
	 * @param string $like                  a like constraint on the query
	 * @return DB_ResultSet                 a result set of database tables
	 *
	 * @see http://www.alberton.info/postgresql_meta_info.html
	 * @see http://www.linuxscrew.com/2009/07/03/postgresql-show-tables-show-databases-show-columns/
	 * @see http://www.polak.ro/postgresql-select-tables-names.html
	 */
	public function tables($like = '') {
		$builder = DB_SQL::select($this->source)
			->column('table_schema', 'schema')
			->column('table_name', 'table')
			->column(DB_SQL::expr("'BASE'"), 'type')
			->from('information_schema.tables')
			->where('table_type', DB_SQL_Operator::_EQUAL_TO_, 'BASE TABLE')
			->where('table_schema', DB_SQL_Operator::_NOT_IN_, array('pg_catalog', 'information_schema'))
			->order_by(DB_SQL::expr('UPPER("table_schema")'))
			->order_by(DB_SQL::expr('UPPER("table_name")'));

		if ( ! empty($like)) {
			$builder->where('table_name', DB_SQL_Operator::_LIKE_, $like);
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
	 * @see http://www.postgresql.org/docs/8.1/static/infoschema-triggers.html
	 */
	public function triggers($table, $like = '') {
		$builder = DB_SQL::select($this->source)
			->column('event_object_schema', 'schema')
			->column('event_object_table', 'table')
			->column('trigger_name', 'trigger')
			->column('event_manipulation', 'event')
			->column('condition_timing', 'timing')
			->column('action_orientation', 'per')
			->column('action_statement', 'action')
			->column('action_order', 'seq_index')
			->column(DB_SQL::expr('NULL'), 'created')
			->from('information_schema.triggers')
			->where('event_object_schema', DB_SQL_Operator::_NOT_IN_, array('pg_catalog', 'information_schema'))
			->where('event_object_table', '!~', '^pg_')3
			->where(DB_SQL::expr('UPPER("event_object_table")'), DB_SQL_Operator::_EQUAL_TO_, $table)
			->order_by(DB_SQL::expr('UPPER("event_object_schema")'))
			->order_by(DB_SQL::expr('UPPER("event_object_table")'))
			->order_by(DB_SQL::expr('UPPER("trigger_name")'))
			->order_by('action_order');

		if ( ! empty($like)) {
			$builder->where('trigger_name', DB_SQL_Operator::_LIKE_, $like);
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
	 * | type          | string        | The type of table.              .                          |
	 * +---------------+---------------+------------------------------------------------------------+
	 *
	 * @access public
	 * @override
	 * @param string $like                  a like constraint on the query
	 * @return DB_ResultSet                 a result set of database views
	 *
	 * @see http://www.alberton.info/postgresql_meta_info.html
	 * @see http://www.linuxscrew.com/2009/07/03/postgresql-show-tables-show-databases-show-columns/
	 * @see http://www.polak.ro/postgresql-select-tables-names.html
	 */
	public function views($like = '') {
		$builder = DB_SQL::select($this->source)
			->column('table_schema', 'schema')
			->column('table_name', 'table')
			->column(DB_SQL::expr("'VIEW'"), 'type')
			->from('information_schema.tables')
			->where('table_type', DB_SQL_Operator::_EQUAL_TO_, 'VIEW')
			->where('table_schema', DB_SQL_Operator::_NOT_IN_, array('pg_catalog', 'information_schema'))
			->where('table_name', '!~', '^pg_')
			->order_by(DB_SQL::expr('UPPER("table_schema")'))
			->order_by(DB_SQL::expr('UPPER("table_name")'));

		if ( ! empty($like)) {
			$builder->where('table_name', DB_SQL_Operator::_LIKE_, $like);
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
		static $types = array(
			// PostgreSQL >= 7.4
			'box'       => array('type' => 'string'),
			'bytea'     => array('type' => 'string', 'binary' => TRUE),
			'cidr'      => array('type' => 'string'),
			'circle'    => array('type' => 'string'),
			'inet'      => array('type' => 'string'),
			'int2'      => array('type' => 'int', 'min' => '-32768', 'max' => '32767'),
			'int4'      => array('type' => 'int', 'min' => '-2147483648', 'max' => '2147483647'),
			'int8'      => array('type' => 'int', 'min' => '-9223372036854775808', 'max' => '9223372036854775807'),
			'line'      => array('type' => 'string'),
			'lseg'      => array('type' => 'string'),
			'macaddr'   => array('type' => 'string'),
			'money'     => array('type' => 'float', 'exact' => TRUE, 'min' => '-92233720368547758.08', 'max' => '92233720368547758.07'),
			'path'      => array('type' => 'string'),
			'polygon'   => array('type' => 'string'),
			'point'     => array('type' => 'string'),
			'text'      => array('type' => 'string'),

			// PostgreSQL >= 8.3
			'tsquery'   => array('type' => 'string'),
			'tsvector'  => array('type' => 'string'),
			'uuid'      => array('type' => 'string'),
			'xml'       => array('type' => 'string'),
		);

		if (isset($types[$type])) {
			return $types[$type];
		}

		return parent::data_type($type);
		*/
	}

}
