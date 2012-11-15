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
 * This class builds an SQL select statement.
 *
 * @package Leap
 * @category ORM
 * @version 2012-11-14
 *
 * @abstract
 */
abstract class Base_DB_ORM_Select_Proxy  extends Core_Object implements DB_SQL_Statement {

	/**
	 * This variable stores an instance of the SQL builder class.
	 *
	 * @access protected
	 * @var DB_SQL_Select_Builder
	 */
	protected $builder = NULL;

	/**
	 * This variable stores an instance of the ORM builder extension class.
	 *
	 * @access protected
	 * @var DB_ORM_Builder
	 */
	protected $extension = NULL;

	/**
	 * This variable stores the model's name.
	 *
	 * @access protected
	 * @var string
	 */
	protected $model = NULL;

	/**
	 * This variable stores a reference to the data source.
	 *
	 * @access protected
	 * @var DB_DataSource
	 */
	protected $source = NULL;

	/**
	 * This variable stores the name of the model's table.
	 *
	 * @access protected
	 * @var string
	 */
	protected $table = NULL;

	/**
	 * This constructor instantiates this class using the specified model's name.
	 *
	 * @access public
	 * @param string $model                         the model's name
	 * @param array $columns                        the columns to be selected
	 */
	public function __construct($model, Array $columns = array()) {
		$name = $model;
		$model = DB_ORM_Model::model_name($name);
		$this->source = new DB_DataSource($model::data_source());
		$builder = 'DB_' . $this->source->dialect . '_Select_Builder';
		$this->table = $model::table();
		$this->builder = new $builder($this->source, $columns);
		if (empty($columns)) {
			$this->builder->all("{$this->table}.*");
		}
		$this->builder->from($this->table);
		$extension = DB_ORM_Model::builder_name($name);
		if (class_exists($extension)) {
			$this->extension = new $extension($this->builder);
		}
		$this->model = $model;
	}

	/**
	 * This function attempts to call an otherwise inaccessible function on the model's
	 * builder extension.
	 *
	 * @access public
	 * @param string $function                      the name of the called function
	 * @param array $arguments                      an array with the parameters passed
	 * @return mixed                                the result of the called function
	 * @throws Throwable_UnimplementedMethod_Exception indicates that the called function is
	 *                                              inaccessible
	 */
	public function __call($function, $arguments) {
		if ($this->extension !== NULL) {
			if (method_exists($this->extension, $function)) {
				$result = call_user_func_array(array($this->extension, $function), $arguments);
				if ($result instanceof DB_ORM_Builder) {
					return $this;
				}
				return $result;
			}
		}
		throw new Throwable_UnimplementedMethod_Exception('Message: Call to undefined member function. Reason: Function :function has not been defined in class :class.', array(':class' => get_class($this->extension), ':function' => $function, ':arguments' => $arguments));
	}

	/**
	 * This function sets whether to constrain the SQL statement to only distinct records.
	 *
	 * @access public
	 * @param boolean $distinct                     whether to constrain the SQL statement to only
	 *                                              distinct records
	 * @return DB_ORM_Select_Proxy                  a reference to the current instance
	 */
	public function distinct($distinct = TRUE) {
		$this->builder->distinct($distinct);
		return $this;
	}

	/**
	 * This function sets the wildcard to be used.
	 *
	 * @access public
	 * @param string $wildcard              the wildcard to be used
	 * @return DB_ORM_Select_Proxy          a reference to the current instance
	 */
	public function all($wildcard = '*') {
		$this->builder->all("{$this->table}.*");
		return $this;
	}

	/**
	 * This function explicits sets the specified column to be selected.
	 *
	 * @access public
	 * @param string $column                        the column to be selected
	 * @param string $alias                         the alias to be used for the specified column
	 * @return DB_ORM_Select_Proxy                  a reference to the current instance
	 */
	public function column($column, $alias = NULL) {
		$this->builder->column($column, $alias);
		return $this;
	}

	/**
	 * This function joins a table.
	 *
	 * @access public
	 * @param string $type                          the type of join
	 * @param string $table                         the table to be joined
	 * @param string $alias                         the alias to be used for the specified table
	 * @return DB_ORM_Select_Proxy                  a reference to the current instance
	 */
	public function join($type, $table, $alias = NULL) {
		$this->builder->join($type, $table, $alias);
		return $this;
	}

	/**
	 * This function sets an "on" constraint for the last join specified.
	 *
	 * @access public
	 * @param string $column0                       the column to be constrained on
	 * @param string $operator                      the operator to be used
	 * @param string $column1                       the constraint column
	 * @return DB_ORM_Select_Proxy                  a reference to the current instance
	 * @throws Throwable_SQL_Exception                 indicates an invalid SQL build instruction
	 */
	public function on($column0, $operator, $column1) {
		$this->builder->on($column0, $operator, $column1);
		return $this;
	}

	/**
	 * This function sets a "using" constraint for the last join specified.
	 *
	 * @access public
	 * @param string $column                        the column to be constrained
	 * @return DB_ORM_Select_Proxy                  a reference to the current instance
	 */
	public function using($column) {
		$this->builder->using($column);
		return $this;
	}

	/**
	 * This function either opens or closes a "where" group.
	 *
	 * @access public
	 * @param string $parenthesis                   the parenthesis to be used
	 * @param string $connector                     the connector to be used
	 * @return DB_ORM_Select_Proxy                  a reference to the current instance
	 */
	public function where_block($parenthesis, $connector = 'AND') {
		$this->builder->where_block($parenthesis, $connector);
		return $this;
	}

	/**
	 * This function adds a "where" constraint.
	 *
	 * @access public
	 * @param string $column                        the column to be constrained
	 * @param string $operator                      the operator to be used
	 * @param string $value                         the value the column is constrained with
	 * @param string $connector                     the connector to be used
	 * @return DB_ORM_Select_Proxy                  a reference to the current instance
	 */
	public function where($column, $operator, $value, $connector = 'AND') {
		$this->builder->where($column, $operator, $value, $connector);
		return $this;
	}

	/**
	 * This function adds a "group by" clause.
	 *
	 * @access public
	 * @param string $column                        the column to be grouped
	 * @return DB_ORM_Select_Proxy                  a reference to the current instance
	 */
	public function group_by($column) {
		$this->builder->group_by($column);
		return $this;
	}

	/**
	 * This function either opens or closes a "having" group.
	 *
	 * @access public
	 * @param string $parenthesis                   the parenthesis to be used
	 * @param string $connector                     the connector to be used
	 * @return DB_ORM_Select_Proxy                  a reference to the current instance
	 */
	public function having_block($parenthesis, $connector = 'AND') {
		$this->builder->having_block($parenthesis, $connector);
		return $this;
	}

	/**
	 * This function adds a "having" constraint.
	 *
	 * @access public
	 * @param string $column                        the column to be constrained
	 * @param string $operator                      the operator to be used
	 * @param string $value                         the value the column is constrained with
	 * @param string $connector                     the connector to be used
	 * @return DB_ORM_Select_Proxy                  a reference to the current instance
	 */
	public function having($column, $operator, $value, $connector = 'AND') {
		$this->builder->having($column, $operator, $value, $connector);
		return $this;
	}

	/**
	 * This function sets how a column will be sorted.
	 *
	 * @access public
	 * @param string $column                        the column to be sorted
	 * @param string $ordering                      the ordering token that signals whether the
	 *                                              column will sorted either in ascending or
	 *                                              descending order
	 * @param string $nulls                         the weight to be given to null values
	 * @return DB_ORM_Select_Proxy                  a reference to the current instance
	 */
	public function order_by($column, $ordering = 'ASC', $nulls = 'DEFAULT') {
		$this->builder->order_by($column, $ordering, $nulls);
		return $this;
	}

	/**
	 * This function sets a "limit" constraint on the statement.
	 *
	 * @access public
	 * @param integer $limit                        the "limit" constraint
	 * @return DB_ORM_Select_Proxy                  a reference to the current instance
	 */
	public function limit($limit) {
		$this->builder->limit($limit);
		return $this;
	}

	/**
	 * This function sets an "offset" constraint on the statement.
	 *
	 * @access public
	 * @param integer $offset                       the "offset" constraint
	 * @return DB_ORM_Select_Proxy                  a reference to the current instance
	 */
	public function offset($offset) {
		$this->builder->offset($offset);
		return $this;
	}

	/**
	 * This function sets both the "offset" constraint and the "limit" constraint on
	 * the statement.
	 *
	 * @access public
	 * @param integer $offset                   the "offset" constraint
	 * @param integer $limit                    the "limit" constraint
	 * @return DB_SQL_Select_Builder            a reference to the current instance
	 */
	public function page($offset, $limit) {
		$this->builder->page($offset, $limit);
		return $this;
	}

	/**
	 * This function combines another SQL statement using the specified operator.
	 *
	 * @access public
	 * @param string $operator                      the operator to be used to append
	 *                                              the specified SQL statement
	 * @param string $statement                     the SQL statement to be appended
	 * @return DB_ORM_Select_Proxy                  a reference to the current instance
	 */
	public function combine($operator, $statement) {
		$this->builder->combine($operator, $statement);
		return $this;
	}

	/**
	 * This function returns the SQL statement.
	 *
	 * @access public
	 * @param boolean $terminated                   whether to add a semi-colon to the end
	 *                                              of the statement
	 * @return string                               the SQL statement
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
		return $this->builder->statement(TRUE);
	}

	/**
	 * This function performs a query using the built SQL statement.
	 *
	 * @access public
	 * @param integer $limit                        the "limit" constraint
	 * @return DB_ResultSet                         the result set
	 */
	public function query($limit = NULL) {
		if ($limit !== NULL) {
			$this->limit($limit);
		}
		$connection = DB_Connection_Pool::instance()->get_connection($this->source);
		$records = $connection->query($this->statement(), $this->model);
		return $records;
	}

}
?>