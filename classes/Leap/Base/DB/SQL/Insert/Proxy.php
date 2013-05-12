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
 * This class builds an SQL insert statement.
 *
 * @package Leap
 * @category SQL
 * @version 2013-02-03
 *
 * @abstract
 */
abstract class Base\DB\SQL\Insert\Proxy extends Core\Object implements DB\SQL\Statement {

	/**
	 * This variable stores an instance of the SQL statement builder of the preferred SQL
	 * language dialect.
	 *
	 * @access protected
	 * @var DB\SQL\Builder
	 */
	protected $builder;

	/**
	 * This variable stores a reference to the data source.
	 *
	 * @access protected
	 * @var DB\DataSource
	 */
	protected $data_source;

	/**
	 * This constructor instantiates this class using the specified data source.
	 *
	 * @access public
	 * @param mixed $config                             the data source configurations
	 */
	public function __construct($config) {
		$this->data_source = DB\DataSource::instance($config);
		$builder = '\\Leap\\DB\\' . $this->data_source->dialect . '\\Insert\\Builder';
		$this->builder = new $builder($this->data_source);
	}

	/**
	 * This function returns the raw SQL statement.
	 *
	 * @access public
	 * @override
	 * @return string                                   the raw SQL statement
	 */
	public function __toString() {
		return $this->builder->statement(TRUE);
	}

	/**
	 * This function sets the associated value with the specified column.
	 *
	 * @access public
	 * @param string $column                 	        the column to be set
	 * @param string $value                  	        the value to be set
	 * @param integer $row						        the index of the row
	 * @return DB\SQL\Insert\Proxy           	        a reference to the current instance
	 */
	public function column($column, $value, $row = 0) {
		$this->builder->column($column, $value, $row);
		return $this;
	}

	/**
	 * This function executes the SQL statement via the DAO class.
	 *
	 * @access public
	 * @param boolean $auto_increment		  	        whether to query for the last insert id
	 * @return integer                      	        the last insert id
	 */
	public function execute() {
		$auto_increment = ((func_num_args() > 0) AND (func_get_arg(0) === TRUE));
		$connection = DB\Connection\Pool::instance()->get_connection($this->data_source);
		$connection->execute($this->statement(TRUE));
		$primary_key = ($auto_increment) ? $connection->get_last_insert_id() : 0;
		return $primary_key;
	}

	/**
	 * This function sets which table will be modified.
	 *
	 * @access public
	 * @param string $table                             the database table to be modified
	 * @return DB\SQL\Insert\Proxy           	        a reference to the current instance
	 */
	public function into($table) {
		$this->builder->into($table);
		return $this;
	}

	/**
	 * This function resets the current builder.
	 *
	 * @access public
	 * @return DB\SQL\Insert\Proxy                      a reference to the current instance
	 */
	public function reset() {
		$this->builder->reset();
		return $this;
	}

	/**
	 * This function sets a row of columns/values pairs.
	 *
	 * @access public
	 * @param array $values						        the columns/values pairs to be set
	 * @param integer $row						        the index of the row
	 * @return DB\SQL\Insert\Proxy  			        a reference to the current instance
	 */
	public function row(Array $values, $row = 0) {
		$this->builder->row($values, $row);
		return $this;
	}

	/**
	 * This function returns the SQL statement.
	 *
	 * @access public
	 * @override
	 * @param boolean $terminated           	        whether to add a semi-colon to the end
	 *                                      	        of the statement
	 * @return string                       	        the SQL statement
	 */
	public function statement($terminated = TRUE) {
		return $this->builder->statement($terminated);
	}

}
