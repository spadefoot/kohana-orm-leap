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
 * This class provides a way to access the scheme for a MS SQL database.
 *
 * @package Leap
 * @category MS SQL
 * @version 2013-01-05
 *
 * @abstract
 */
abstract class Base_DB_MsSQL_Schema extends DB_Schema {

	/**
	 * This function returns a result set that contains an array of all fields in
	 * the specified database table/view.
	 *
	 * @access public
	 * @override
	 * @param string $table                 the table/view to evaluated
	 * @param string $like                  a like constraint on the query
	 * @return array                        an array of fields within the specified
	 *                                      table
	 */
	public function fields($table, $like = '') {
		/*
		if (is_string($like)) {
			$results = $this->query(Database::SELECT,'SELECT * FROM INFORMATION_SCHEMA.Columns WHERE TABLE_NAME LIKE '.$this->quote($table), FALSE);
		}
		else {
			$results = $this->query(Database::SELECT,'SELECT * FROM INFORMATION_SCHEMA.Columns WHERE TABLE_NAME = '.$this->quote($table), FALSE);
		}

		$result = array();
		foreach ($results as $row) {
			list($type, $length) = $this->_parse_type($row['DATA_TYPE']);

			$column = $this->data_type($type);

			$column['column_name']      = $row['COLUMN_NAME'];
			$column['column_default']   = $row['COLUMN_DEFAULT'];
			$column['data_type']        = $type;
			$column['is_nullable']      = ($row['IS_NULLABLE'] == 'YES');
			$column['ordinal_position'] = $row['ORDINAL_POSITION'];

			if ($row['CHARACTER_MAXIMUM_LENGTH']) {
				$column['character_maximum_length'] = $row['CHARACTER_MAXIMUM_LENGTH'];
			}

			$result[$row['COLUMN_NAME']] = $column;
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
	 * @see http://www.alberton.info/sql_server_meta_info.html
	 * @see http://stackoverflow.com/questions/765867/list-of-all-index-index-columns-in-sql-server-db
	 */
	public function indexes($table, $like = '') {
		$builder = DB_SQL::select($this->source)
			->column('t1.NAME', 'schema')
			->column('t0.NAME', 'table')
			->column('t2.NAME', 'index')
			->column('t4.NAME', 'column')
			->column('t3.KEY_ORDINAL', 'seq_index')
			->column('t2.IS_PRIMARY_KEY', 'primary')
			->column('t2.IS_UNIQUE', 'unique')
			->from('SYS.TABLES', 't0')
			->join(DB_SQL_JoinType::_LEFT_, 'SYS.SCHEMAS', 't1')
			->on('t1.SCHEMA_ID', DB_SQL_Operator::_EQUAL_TO_, 't0.SCHEMA_ID')
			->join(DB_SQL_JoinType::_LEFT_, 'SYS.INDEXES', 't2')
			->on('t2.OBJECT_ID', DB_SQL_Operator::_EQUAL_TO_, 't0.OBJECT_ID')
			->join(DB_SQL_JoinType::_LEFT_, 'SYS.INDEX_COLUMNS', 't3')
			->on('t3.OBJECT_ID', DB_SQL_Operator::_EQUAL_TO_, 't0.OBJECT_ID')
			->on('t3.INDEX_ID', DB_SQL_Operator::_EQUAL_TO_, 't2.INDEX_ID')
			->join(DB_SQL_JoinType::_LEFT_, 'SYS.COLUMNS', 't4')
			->on('t4.OBJECT_ID', DB_SQL_Operator::_EQUAL_TO_, 't0.OBJECT_ID')
			->on('t4.COLUMN_ID', DB_SQL_Operator::_EQUAL_TO_, 't3.COLUMN_ID')
			->where('t0.NAME', DB_SQL_Operator::_EQUAL_TO_, $table)
			->where('t2.IS_DISABLED', DB_SQL_Operator::_EQUAL_TO_, 0)
			->order_by(DB_SQL::expr('UPPER([t1].[NAME])'))
			->order_by(DB_SQL::expr('UPPER([t0].[NAME])'))
			->order_by(DB_SQL::expr('UPPER([t2].[NAME])'))
			->order_by('t3.KEY_ORDINAL');

		if ( ! empty($like)) {
			$builder->where('t2.NAME', DB_SQL_Operator::_LIKE_, $like);
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
	 * @see http://www.alberton.info/sql_server_meta_info.html
	 */
	public function tables($like = '') {
		$builder = DB_SQL::select($this->source)
			->column('[TABLE_SCHEMA]', 'schema')
			->column('[TABLE_NAME]', 'table')
			->column(DB_SQL::expr("'BASE'"), 'type')
			->from('[INFORMATION_SCHEMA].[TABLES]')
			->where('[TABLE_TYPE]', DB_SQL_Operator::_EQUAL_TO_, 'BASE_TABLE')
			->where(DB_SQL::expr("OBJECTPROPERTY(OBJECT_ID([TABLE_NAME]), 'IsMsShipped')"), DB_SQL_Operator::_EQUAL_TO_, 0)
			->order_by(DB_SQL::expr('UPPER([TABLE_SCHEMA])'))
			->order_by(DB_SQL::expr('UPPER([TABLE_NAME])'));

		if ( ! empty($like)) {
			$builder->where('[TABLE_NAME]', DB_SQL_Operator::_LIKE_, $like);
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
	 * @see http://www.alberton.info/sql_server_meta_info.html
	 * @see http://it.toolbox.com/wiki/index.php/Find_all_the_triggers_in_a_database
	 * @see http://stackoverflow.com/questions/4305691/need-to-list-all-triggers-in-sql-server-database-with-table-name-and-tables-sch
	 */
	public function triggers($table, $like = '') {
		$builder = DB_SQL::select($this->source)
			->column('[t4].[NAME]', 'schema')
			->column('[t1].[NAME]', 'table')
			->column('[t0].[NAME]', 'trigger')
			->column(DB_SQL::expr("CASE WHEN OBJECTPROPERTY([t0].[ID], 'ExecIsInsertTrigger') = 1 THEN 'INSERT' WHEN OBJECTPROPERTY([t0].[ID], 'ExecIsUpdateTrigger') = 1 THEN 'UPDATE' WHEN OBJECTPROPERTY([t0].[ID], 'ExecIsDeleteTrigger') = 1 THEN 'DELETE' END"), 'event')
			->column(DB_SQL::expr("CASE WHEN OBJECTPROPERTY([t0].[ID], 'ExecIsInsteadOfTrigger') = 1 THEN 'INSTEAD OF' ELSE 'AFTER' END"), 'timing')
			->column(DB_SQL::expr("'ROW'"), 'per')
			->column('[t2].[TEXT]', 'action')
			->column(DB_SQL::expr('0'), 'seq_index')
			->column(DB_SQL::expr('NULL'), 'created')
			->from('[SYSOBJECTS]', '[t0]')
			->join(NULL, '[SYSOBJECTS]', '[t1]')
			->on('[t1].[ID]', DB_SQL_Operator::_EQUAL_TO_, '[t0].[PARENT_OBJ]')
			->join(NULL, '[SYSCOMMENTS]', '[t2]')
			->on('[t2].[ID]', DB_SQL_Operator::_EQUAL_TO_, '[t0].[ID]')
			->join(DB_SQL_JoinType::_LEFT_, '[SYS].[TABLES]', '[t3]')
			->on('[t3].[OBJECT_ID]', DB_SQL_Operator::_EQUAL_TO_, '[t0].[PARENT_OBJ]')
			->join(DB_SQL_JoinType::_LEFT_, '[SYS].[SCHEMAS]', '[t4]')
			->on('[t4].[SCHEMA_ID]', DB_SQL_Operator::_EQUAL_TO_, '[t3].[SCHEMA_ID]')
			->where('[t0].[XTYPE]', DB_SQL_Operator::_EQUAL_TO_, 'TR')
			->where('[t1].[NAME]', DB_SQL_Operator::_EQUAL_TO_, $table)
			->where(DB_SQL::expr("CASE WHEN OBJECTPROPERTY([t0].[ID], 'ExecIsTriggerDisabled') = 1 THEN 0 ELSE 1 END"), DB_SQL_Operator::_EQUAL_TO_, 1)
			->order_by(DB_SQL::expr('UPPER([t4].[NAME])'))
			->order_by(DB_SQL::expr('UPPER([t1].[NAME])'))
			->order_by(DB_SQL::expr('UPPER([t0].[NAME])'));

		if ( ! empty($like)) {
			$builder->where('[t0].[NAME]', DB_SQL_Operator::_LIKE_, $like);
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
	 * @see http://www.alberton.info/sql_server_meta_info.html
	 */
	public function views($like = '') {
		$builder = DB_SQL::select($this->source)
			->column('[TABLE_SCHEMA]', 'schema')
			->column('[TABLE_NAME]', 'table')
			->column(DB_SQL::expr("'VIEW'"), 'type')
			->from('[INFORMATION_SCHEMA].[TABLES]')
			->where('[TABLE_TYPE]', DB_SQL_Operator::_EQUAL_TO_, 'VIEW')
			->where(DB_SQL::expr("OBJECTPROPERTY(OBJECT_ID([TABLE_NAME]), 'IsMsShipped')"), DB_SQL_Operator::_EQUAL_TO_, 0)
			->order_by(DB_SQL::expr('UPPER([TABLE_SCHEMA])'))
			->order_by(DB_SQL::expr('UPPER([TABLE_NAME])'));

		if ( ! empty($like)) {
			$builder->where('[TABLE_NAME]', DB_SQL_Operator::_LIKE_, $like);
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
			'nvarchar'  => array('type' => 'string'),
			'ntext'     => array('type' => 'string'),
			'tinyint'   => array('type' => 'int', 'min' => '0', 'max' => '255'),
		);

		if (isset($types[$type])) {
			return $types[$type];
		}

		return parent::data_type($type);
		*/
	}

}
