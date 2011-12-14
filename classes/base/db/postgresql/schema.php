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
 * This class provides a way to access the scheme for a PostgreSQL database.
 *
 * @package Leap
 * @category PostgreSQL
 * @version 2011-06-23
 *
 * @abstract
 */
abstract class Base_DB_PostgreSQL_Schema extends Kohana_Object {

	/**
	 * This function returns a result set that contains an array of all fields in
	 * the specified database table/view.
	 *
	 * @access public
	 * @abstract
	 * @param string $table					the table/view to evaluated
	 * @param string $type                  a like constraint on the query
	 * @return array 						an array of fields within the specified
	 * 										table
	 *
	 * @see http://www.linuxscrew.com/2009/07/03/postgresql-show-tables-show-databases-show-columns/
	 */
	public function fields($table, $like = '') {
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
	}

	/**
	 * This function returns a result set that contains an array of all indexes from
	 * the specified table.
	 *
	 * @access public
	 * @abstract
	 * @param string $table					the table/view to evaluated
	 * @return array 						an array of indexes from the specified
	 * 										table
	 *
	 * @see http://stackoverflow.com/questions/2204058/show-which-columns-an-index-is-on-in-postgresql
	 * @see http://code.activestate.com/recipes/576557/
	 */
	public function indexes($table) {
		$sql = "select
			t.relname as table_name,
			i.relname as index_name,
			a.attname as column_name
		from
			pg_class t,
			pg_class i,
			pg_index ix,
			pg_attribute a
		where
			t.oid = ix.indrelid
			and i.oid = ix.indexrelid
			and a.attrelid = t.oid
			and a.attnum = ANY(ix.indkey)
			and t.relkind = 'r'
			and t.relname like 'test%'
		order by
			t.relname,
			i.relname;";

		$connection = DB_Connection_Pool::instance()->get_connection($this->data_source);
		$records = $connection->query($sql)->as_array();

		return $records;
	}

	/**
	 * This function returns a result set that contains an array of all tables within
	 * the database.
	 *
	 * @access public
	 * @param string $like                  a like constraint on the query
	 * @return array 						an array of tables within the database
	 *
	 * @see http://www.alberton.info/postgresql_meta_info.html
	 * @see http://www.linuxscrew.com/2009/07/03/postgresql-show-tables-show-databases-show-columns/
	 * @see http://www.polak.ro/postgresql-select-tables-names.html
	 */
	public function tables($like = '') {
		$builder = DB_MsSQL_Select_Builder::factory()
			->column('table_name', 'name')
			->from('information_schema.tables')
			->where('table_type', '=', 'BASE TABLE')
			->where('table_schema', 'NOT IN', array('pg_catalog', 'information_schema'))
			->order_by(DB::expr('LOWER("table_name")'));

		if ( ! empty($like)) {
			$builder->where('table_name', 'LIKE', $like);
		}

		$sql = $builder->statement();

		$connection = DB_Connection_Pool::instance()->get_connection($this->data_source);
		$records = $connection->query($sql)->as_array();

		return $records;
	}

	/**
	 * This function returns a result set that contains an array of all views within
	 * the database.
	 *
	 * @access public
	 * @param string $like                  a like constraint on the query
	 * @return array 						an array of views within the database
	 *
	 * @see http://www.alberton.info/postgresql_meta_info.html
	 * @see http://www.linuxscrew.com/2009/07/03/postgresql-show-tables-show-databases-show-columns/
	 * @see http://www.polak.ro/postgresql-select-tables-names.html
	 */
	public function views($like = '') {
		$builder = DB_MsSQL_Select_Builder::factory()
			->column('table_name', 'name')
			->from('information_schema.tables')
			->where('table_type', '=', 'VIEW')
			->where('table_schema', 'NOT IN', array('pg_catalog', 'information_schema'))
			->where('table_name', 'NOT LIKE', 'pg_%')
			->order_by(DB::expr('LOWER("table_name")'));

		if ( ! empty($like)) {
			$builder->where('table_name', 'LIKE', $like);
		}

		$sql = $builder->statement();

		$connection = DB_Connection_Pool::instance()->get_connection($this->data_source);
		$records = $connection->query($sql)->as_array();

		return $records;
	}

	///////////////////////////////////////////////////////////////HELPERS//////////////////////////////////////////////////////////////

	/**
	 * This function returns an associated array which describes the properties
	 * for the specified SQL data type.
	 *
	 * @access protected
	 * @param string $type                   the SQL data type
	 * @return array                         an associated array which describes the properties
	 *                                       for the specified data type
	 */
	protected function data_type($type) {
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
	}

}
?>