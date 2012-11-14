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
 * This class provides a way to access the scheme for a MS SQL database.
 *
 * @package Leap
 * @category MS SQL
 * @version 2012-08-21
 *
 * @abstract
 */
abstract class Base_DB_MsSQL_Schema extends DB_Schema {

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
	 * This function returns a result set that contains an array of all indexes from
	 * the specified table.
	 *
	 * @access public
	 * @param string $table					the table/view to evaluated
	 * @return array 						an array of indexes from the specified
	 * 										table
	 *
	 * @see http://stackoverflow.com/questions/765867/list-of-all-index-index-columns-in-sql-server-db
	 */
	public function indexes($table) {
		/*
		$builder = DB_SQL::select($this->source)
			->column('sys.tables.name', 'table_name')
			->column('sys.columns.name', 'field_name')
			->column('sys.indexes.name', 'index_name')
			->column('sys.index_columns.key_ordinal', 'sequence')
			->column('sys.indexes.is_primary_key')
			->column('sys.indexes.is_unique')
			->from('sys.tables')
			->join(DB_SQL_JoinType::_CROSS_, 'sys.indexes')
			->join(DB_SQL_JoinType::_CROSS_, 'sys.index_columns')
			->join(DB_SQL_JoinType::_CROSS_, 'sys.columns')
			->where_block(DB_SQL_Builder::_OPENING_PARENTHESIS_)
			->where('sys.tables.object_id', DB_SQL_Operator::_EQUAL_TO_, DB_SQL::expr('[sys].[indexes].[object_id]'))
			->where('sys.tables.object_id', DB_SQL_Operator::_EQUAL_TO_, DB_SQL::expr('[sys].[index_columns].[object_id]'))
			->where('sys.tables.object_id', DB_SQL_Operator::_EQUAL_TO_, DB_SQL::expr('[sys].[columns].[object_id]'))
			->where('sys.indexes.index_id', DB_SQL_Operator::_EQUAL_TO_, DB_SQL::expr('[sys].[index_columns].[index_id]'))
			->where('sys.index_columns.column_id', DB_SQL_Operator::_EQUAL_TO_, DB_SQL::expr('[sys].[columns].[column_id]'))
			->where_block(DB_SQL_Builder::_CLOSING_PARENTHESIS_)
			->where('sys.tables.name', DB_SQL_Operator::_EQUAL_TO_, DB_SQL::expr("'" . $table . "'")); // TODO prevent SQL insertion attack

		$results = $builder->query();

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
	 *
	 * @see http://www.alberton.info/sql_server_meta_info.html
	 */
	public function tables($like = '') {
		/*
		$builder = DB_SQL::select($this->source)
			->column('TABLE_NAME', 'table_name')
			->from('INFORMATION_SCHEMA.TABLES')
			->where('TABLE_TYPE', DB_SQL_Operator::_EQUAL_TO_, 'BASE TABLE')
			->order_by(DB_SQL::expr('LOWER([TABLE_NAME])'));

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
	 * @return array 						an array of views within the database
	 *
	 * @see http://www.alberton.info/sql_server_meta_info.html
	 */
	public function views($like = '') {
		/*
		$builder = DB_SQL::select($this->source)
			->column('TABLE_NAME', 'table_name')
			->from('INFORMATION_SCHEMA.TABLES')
			->where('TABLE_TYPE', DB_SQL_Operator::_EQUAL_TO_, 'VIEW')
			->order_by(DB_SQL::expr('LOWER([TABLE_NAME])'));

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
?>