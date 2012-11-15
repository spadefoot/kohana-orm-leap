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
 * This class builds an SQL delete statement.
 *
 * @package Leap
 * @category ORM
 * @version 2012-11-14
 *
 * @abstract
 */
abstract class Base_DB_ORM_Delete_Proxy extends Core_Object implements DB_SQL_Statement {

	/**
	 * This variable stores an instance of the SQL builder class.
	 *
	 * @access protected
	 * @var DB_SQL_Delete_Builder
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
	 * This variable stores a reference to the data source.
	 *
	 * @access protected
	 * @var DB_DataSource
	 */
	protected $source = NULL;

	/**
	 * This constructor instantiates this class using the specified model's name.
	 *
	 * @access public
	 * @param string $model                         the model's name
	 */
	public function __construct($model) {
		$name = $model;
		$model = DB_ORM_Model::model_name($name);
		$this->source = new DB_DataSource($model::data_source());
		$builder = 'DB_' . $this->source->dialect . '_Delete_Builder';
		$this->builder = new $builder($this->source);
		$extension = DB_ORM_Model::builder_name($name);
		if (class_exists($extension)) {
			$this->extension = new $extension($this->builder);
		}
		$table = $model::table();
		$this->builder->from($table);
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
	 * This function either opens or closes a "where" group.
	 *
	 * @access public
	 * @param string $parenthesis                   the parenthesis to be used
	 * @param string $connector                     the connector to be used
	 * @return DB_ORM_Delete_Proxy                  a reference to the current instance
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
	 * @return DB_ORM_Delete_Proxy                  a reference to the current instance
	 */
	public function where($column, $operator, $value, $connector = 'AND') {
		$this->builder->where($column, $operator, $value, $connector);
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
	 * @return DB_ORM_Delete_Proxy                  a reference to the current instance
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
	 * @return DB_ORM_Delete_Proxy                  a reference to the current instance
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
	 * @return DB_ORM_Delete_Proxy                  a reference to the current instance
	 */
	public function offset($offset) {
		$this->builder->offset($offset);
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
	 * This function executes the built SQL statement.
	 *
	 * @access public
	 */
	public function execute() {
		$connection = DB_Connection_Pool::instance()->get_connection($this->source);
		$connection->execute($this->statement());
	}

}
?>