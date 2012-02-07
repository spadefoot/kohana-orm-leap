<?php defined('SYSPATH') OR die('No direct access allowed.');

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
 * @version 2012-02-05
 *
 * @abstract
 */
abstract class Base_DB_SQL_Insert_Proxy extends Kohana_Object implements DB_SQL_Statement {

	/**
	* This variable stores a reference to the data source.
	*
	* @access protected
	* @var DB_DataSource
	*/
	protected $source;

	/**
	* This variable stores an instance of the SQL statement builder of the preferred SQL
	* language dialect.
	*
	* @access protected
	* @var DB_SQL_Builder
	*/
	protected $builder;

	/**
	* This constructor instantiates this class using the specified data source.
	*
	* @access public
	* @param mixed $config                  the data source configurations
	*/
	public function __construct($config) {
		$this->source = new DB_DataSource($config);
		$builder = 'DB_' . $this->source->dialect . '_Insert_Builder';
		$this->builder = new $builder($this->source);
	}

	/**
	* This function sets which table will be modified.
	*
	* @access public
	* @param string $table                  the database table to be modified
	* @return DB_SQL_Insert_Builder         a reference to the current instance
	*/
	public function into($table) {
		$this->builder->into($table);
		return $this;
	}

	/**
	* This function sets the associated value with the specified column.
	*
	* @access public
	* @param string $column                 the column to be set
	* @param string $value                  the value to be set
	* @return DB_SQL_Insert_Builder         a reference to the current instance
	*/
	public function column($column, $value) {
		$this->builder->column($column, $value);
		return $this;
	}

	/**
	 * This function returns the SQL statement.
	 *
	 * @access public
	 * @param boolean $terminated           whether to add a semi-colon to the end
	 *                                      of the statement
	 * @return string                       the SQL statement
	 */
	public function statement($terminated = TRUE) {
		return $this->builder->statement($terminated);
	}

	/**
	 * This function returns the raw SQL statement.
	 *
	 * @access public
	 * @return string                               the raw SQL statement
	 */
	public function __toString() {
		return $this->builder->statement();
	}

	/**
	 * This function executes the SQL statement via the DAO class.
	 *
	 * @access public
	 * @param boolean $is_auto_incremented  whether to query for the last insert id
	 * @return integer                      the last insert id
	 */
	public function execute() {
		$is_auto_incremented = ((func_num_args() > 0) && (func_get_arg(0) === TRUE));
		$connection = DB_Connection_Pool::instance()->get_connection($this->source);
		$connection->execute($this->statement());
		$primary_key = ($is_auto_incremented) ? $connection->get_last_insert_id() : 0;
		return $primary_key;
	}

}
?>