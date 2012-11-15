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
 * This class builds an SQL insert statement.
 *
 * @package Leap
 * @category SQL
 * @version 2012-10-09
 *
 * @abstract
 */
abstract class Base_DB_SQL_Insert_Builder extends DB_SQL_Builder {

	/**
	 * This variable stores a reference to the compiler class that implements the expression
	 * interface.
	 *
	 * @access protected
	 * @var DB_SQL_Expression_Interface
	 */
	protected $compiler = NULL;

	/**
	 * This variable stores the build data for the SQL statement.
	 *
	 * @access protected
	 * @var array
	 */
	protected $data = NULL;

	/**
	 * This variable stores the name of the SQL dialect being used.
	 *
	 * @access protected
	 * @var string
	 */
	protected $dialect = NULL;

	/**
	 * This constructor instantiates this class using the specified data source.
	 *
	 * @access public
	 * @param DB_DataSource $source             the data source to be used
	 */
	public function __construct(DB_DataSource $source) {
		$this->dialect = $source->dialect;
		$compiler = 'DB_' . $this->dialect . '_Expression';
		$this->compiler = new $compiler($source);
		$this->data = array();
		$this->data['into'] = NULL;
		$this->data['columns'] = array();
		$this->data['rows'] = array();
	}

	/**
	 * This function sets which table will be modified.
	 *
	 * @access public
	 * @param string $table                     the database table to be modified
	 * @return DB_SQL_Insert_Builder            a reference to the current instance
	 */
	public function into($table) {
		$table = $this->compiler->prepare_identifier($table);
		$this->data['into'] = $table;
		return $this;
	}

	/**
	 * This function sets the associated value with the specified column.
	 *
	 * @access public
	 * @param string $column                    the column to be set
	 * @param string $value                     the value to be set
	 * @param integer $row						the index of the row
	 * @return DB_SQL_Insert_Builder            a reference to the current instance
	 */
	public function column($column, $value, $row = 0) {
		$column = $this->compiler->prepare_identifier($column);
		$value = $this->compiler->prepare_value($value);
		$row = $this->compiler->prepare_natural($row);
		$this->data['columns'][$column] = NULL;
		$this->data['rows'][$row][$column] = $value;
		return $this;
	}

	/**
	 * This function sets a row of columns/values pairs.
	 *
	 * @access public
	 * @param array $values						the columns/values pairs to be set
	 * @param integer $row						the index of the row
	 * @return DB_SQL_Insert_Builder			a reference to the current instance
	 */
	public function row(Array $values, $row = 0) {
		foreach ($values as $column => $value) {
			$this->column($column, $value, $row);
		}
		return $this;
	}

}
?>