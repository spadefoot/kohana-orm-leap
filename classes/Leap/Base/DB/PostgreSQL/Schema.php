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
 * This class provides a way to access the scheme for a PostgreSQL database.
 *
 * @package Leap
 * @category PostgreSQL
 * @version 2013-02-01
 *
 * @abstract
 */
abstract class Base\DB\PostgreSQL\Schema extends DB\Schema {

	/**
	 * This function returns an associated array of default properties for the specified
	 * SQL data type.
	 *
	 * @access public
	 * @override
	 * @param string $type                   the SQL data type
	 * @return array                         an associated array of default properties
	 *                                       for the specified data type
	 *
	 * @license http://kohanaframework.org/license
	 *
	 * @see http://www.postgresql.org/docs/9.2/static/datatype.html
	 * @see http://www.postgresql.org/docs/9.2/static/infoschema-datatypes.html
	 */
	public function data_type($type) {
		static $types = array(
			// PostgreSQL >= 7.4
			'BOX'                             => array('type' => 'String'), // 32 bytes
			'BYTEA'                           => array('type' => 'Binary', 'varying' => TRUE),
			'CIDR'                            => array('type' => 'String'), // 7 or 19 bytes
			'CIRCLE'                          => array('type' => 'String'), // 24 bytes
			'INET'                            => array('type' => 'String'), // 7 or 19 bytes
			'INT2'                            => array('type' => 'Integer', 'range' => array(-32768, 32767)),
			'INT4'                            => array('type' => 'Integer', 'range' => array(-2147483648, 2147483647)),
			'INT8'                            => array('type' => 'Integer', 'range' => array('-9223372036854775808', '9223372036854775807')),
			'LINE'                            => array('type' => 'String'), // 32 bytes
			'LSEG'                            => array('type' => 'String'), // 32 bytes
			'MACADDR'                         => array('type' => 'String'), // 6 bytes
			'MONEY'                           => array('type' => 'Double', 'range' => array('-92233720368547758.08', '92233720368547758.07')),
			'PATH'                            => array('type' => 'Text'), // 16+16n bytes
			'POLYGON'                         => array('type' => 'Text'), // 40+16n bytes
			'POINT'                           => array('type' => 'String'), // 16 bytes

			// PostgreSQL >= 8.3
			'TSQUERY'                         => array('type' => 'String'),
			'TSVECTOR'                        => array('type' => 'String'),
			'UUID'                            => array('type' => 'String', 'max_length' => 32),
			'XML'                             => array('type' => 'Text'),
			
			// PostgreSQL:Information Schema
			'CARDINAL_NUMBER'                 => array('type' => 'Integer', 'range' => array(0, 2147483647)),
			'CHARACTER_DATA'                  => array('type' => 'Text'),
			'SQL_IDENTIFIER'                  => array('type' => 'String'),
			'TIME_STAMP'                      => array('type' => 'DateTime'),
			'YES_OR_NO'                       => array('type' => 'Boolean'),
			
			// PostgreSQL:MISC
			'BIGSERIAL'                       => array('type' => 'Integer', 'range' => array(1, '9223372036854775807')),
			'DOUBLE PRECISION'                => array('type' => 'Double', 'max_decimals' => 15),
			'JSON'                            => array('type' => 'Text'),
			'REAL'                            => array('type' => 'Double', 'max_decimals' => 6),
			'SERIAL'                          => array('type' => 'Integer', 'range' => array(1, 2147483647)),
			'SMALLSERIAL'                     => array('type' => 'Integer', 'range' => array(1, 32767)),
		);

		$type = strtoupper($type);

		if (isset($types[$type])) {
			return $types[$type];
		}

		return parent::data_type($type);
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
	 * @see http://www.linuxscrew.com/2009/07/03/postgresql-show-tables-show-databases-show-columns/
	 */
	public function fields($table, $like = '') {
		$builder = DB\SQL::select($this->data_source)
			->column('table_schema', 'schema')
			->column('table_name', 'table')
			->column('column_name', 'column')
			->column('data_type', 'type')
			->column(DB\SQL::expr('GREATEST(character_maximum_length, numeric_precision, datetime_precision)'), 'max_length')
			->column(DB\SQL::expr('COALESCE(numeric_scale, 0)'), 'max_decimals')
			->column(DB\SQL::expr("''"), 'attributes')
			->column('max_length', 'seq_index')
			->column(DB\SQL::expr("CASE WHEN is_nullable = 'YES' THEN 1 ELSE 0 END"), 'nullable')
			->column('column_default', 'default')
			->from('information_schema.columns')
			->where('table_name', DB\SQL\Operator::_EQUAL_TO_, $table)
			->order_by('ordinal_position');
		
		if ( ! empty($like)) {
			$builder->where('column_name', DB\SQL\Operator::_LIKE_, $like);
		}

		return $builder->query();
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
	 * @see http://www.alberton.info/postgresql_meta_info.html
	 * @see http://stackoverflow.com/questions/2204058/show-which-columns-an-index-is-on-in-postgresql
	 * @see http://code.activestate.com/recipes/576557/
	 * @see http://www.postgresql.org/docs/current/static/catalog-pg-index.html
	 */
	public function indexes($table, $like = '') {
		$builder = DB\SQL::select($this->data_source)
			->column('t4.schname', 'schema')
			->column('t0.relname', 'table')
			->column('t2.relname', 'index')
			->column('t3.attname', 'column')
			->column('t3.attnum', 'seq_index')
			->column(DB\SQL::expr('NULL'), 'ordering')
			->column(DB\SQL::expr("CASE \"t1\".\"indisunique\" WHEN 't' THEN 1 ELSE 0 END"), 'unique')
			->column(DB\SQL::expr("CASE \"t1\".\"indisprimary\" WHEN 't' THEN 1 ELSE 0 END"), 'primary')
			->from('pg_class', 't0')
			->join(DB\SQL\JoinType::_LEFT_, 'pg_index', 't1')
			->on('t1.indrelid', DB\SQL\Operator::_EQUAL_TO_, 't0.oid')
			->join(DB\SQL\JoinType::_LEFT_, 'pg_class', 't2')
			->on('t2.oid', DB\SQL\Operator::_EQUAL_TO_, 't1.indexrelid')
			->join(DB\SQL\JoinType::_LEFT_, 'pg_attribute', 't3')
			->on('t3.attrelid', DB\SQL\Operator::_EQUAL_TO_, 't0.oid')
			->on('t3.attnum', DB\SQL\Operator::_EQUAL_TO_, DB\SQL::expr('ANY("t1"."indkey")'))			
			->join(DB\SQL\JoinType::_LEFT_, DB\SQL::expr('(SELECT "t5"."table_schema" AS "schname", "t5"."table_schema" AS "relname" FROM "information_schema"."tables" AS "t5" WHERE "t5"."table_type" = \'BASE TABLE\' AND "t5"."table_schema" NOT IN (\'pg_catalog\', \'information_schema\'))'), 't4')
			->on('t4.relname', DB\SQL\Operator::_EQUAL_TO_, 't0.relname')
			->where('t0.relkind', DB\SQL\Operator::_EQUAL_TO_, 'r')
			->where('t0.relname', DB\SQL\Operator::_EQUAL_TO_, $table)
			->order_by(DB\SQL::expr('UPPER("t4"."schname")'))
			->order_by(DB\SQL::expr('UPPER("t0"."relname")'))
			->order_by(DB\SQL::expr('UPPER("t2"."relname")'))
			->order_by('t3.attnum');

		if ( ! empty($like)) {
			$builder->where('t2.relname', DB\SQL\Operator::_LIKE_, $like);
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
	 * @see http://www.alberton.info/postgresql_meta_info.html
	 * @see http://www.linuxscrew.com/2009/07/03/postgresql-show-tables-show-databases-show-columns/
	 * @see http://www.polak.ro/postgresql-select-tables-names.html
	 */
	public function tables($like = '') {
		$builder = DB\SQL::select($this->data_source)
			->column('table_schema', 'schema')
			->column('table_name', 'table')
			->column(DB\SQL::expr("'BASE'"), 'type')
			->from('information_schema.tables')
			->where('table_type', DB\SQL\Operator::_EQUAL_TO_, 'BASE TABLE')
			->where('table_schema', DB\SQL\Operator::_NOT_IN_, array('pg_catalog', 'information_schema'))
			->order_by(DB\SQL::expr('UPPER("table_schema")'))
			->order_by(DB\SQL::expr('UPPER("table_name")'));

		if ( ! empty($like)) {
			$builder->where('table_name', DB\SQL\Operator::_LIKE_, $like);
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
	 * @see http://www.postgresql.org/docs/8.1/static/infoschema-triggers.html
	 */
	public function triggers($table, $like = '') {
		$builder = DB\SQL::select($this->data_source)
			->column('event_object_schema', 'schema')
			->column('event_object_table', 'table')
			->column('trigger_name', 'trigger')
			->column('event_manipulation', 'event')
			->column('condition_timing', 'timing')
			->column('action_orientation', 'per')
			->column('action_statement', 'action')
			->column('action_order', 'seq_index')
			->column(DB\SQL::expr('NULL'), 'created')
			->from('information_schema.triggers')
			->where('event_object_schema', DB\SQL\Operator::_NOT_IN_, array('pg_catalog', 'information_schema'))
			->where('event_object_table', '!~', '^pg_')
			->where(DB\SQL::expr('UPPER("event_object_table")'), DB\SQL\Operator::_EQUAL_TO_, $table)
			->order_by(DB\SQL::expr('UPPER("event_object_schema")'))
			->order_by(DB\SQL::expr('UPPER("event_object_table")'))
			->order_by(DB\SQL::expr('UPPER("trigger_name")'))
			->order_by('action_order');

		if ( ! empty($like)) {
			$builder->where('trigger_name', DB\SQL\Operator::_LIKE_, $like);
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
	 * @see http://www.alberton.info/postgresql_meta_info.html
	 * @see http://www.linuxscrew.com/2009/07/03/postgresql-show-tables-show-databases-show-columns/
	 * @see http://www.polak.ro/postgresql-select-tables-names.html
	 */
	public function views($like = '') {
		$builder = DB\SQL::select($this->data_source)
			->column('table_schema', 'schema')
			->column('table_name', 'table')
			->column(DB\SQL::expr("'VIEW'"), 'type')
			->from('information_schema.tables')
			->where('table_type', DB\SQL\Operator::_EQUAL_TO_, 'VIEW')
			->where('table_schema', DB\SQL\Operator::_NOT_IN_, array('pg_catalog', 'information_schema'))
			->where('table_name', '!~', '^pg_')
			->order_by(DB\SQL::expr('UPPER("table_schema")'))
			->order_by(DB\SQL::expr('UPPER("table_name")'));

		if ( ! empty($like)) {
			$builder->where('table_name', DB\SQL\Operator::_LIKE_, $like);
		}

		return $builder->query();
	}

}
