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
 * @category ORM
 * @version 2012-08-16
 *
 * @abstract
 */
abstract class Base_DB_ORM_Insert_Proxy extends Kohana_Object implements DB_SQL_Statement {

	/**
	 * This variable stores an instance of the SQL builder class.
	 *
	 * @access protected
	 * @var DB_SQL_Insert_Builder
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
	 * This constructor instantiates this class using the specified model's name.
	 *
	 * @access public
	 * @param string $model                         the model's name
	 */
	public function __construct($model) {
		$name = $model;
		$model = DB_ORM_Model::model_name($name);
		$this->source = new DB_DataSource($model::data_source());
		$builder = 'DB_' . $this->source->dialect . '_Insert_Builder';
		$this->builder = new $builder($this->source);
		$extension = DB_ORM_Model::builder_name($name);
		if (class_exists($extension)) {
			$this->extension = new $extension($this->builder);
		}
		$table = $model::table();
		$this->builder->into($table);
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
	 * @throws Kohana_UnimplementedMethod_Exception indicates that the called function is
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
		throw new Kohana_UnimplementedMethod_Exception('Message: Call to undefined member function. Reason: Function :function has not been defined in class :class.', array(':class' => get_class($this->extension), ':function' => $function, ':arguments' => $arguments));
	}

	/**
	 * This function sets the associated value with the specified column.
	 *
	 * @access public
	 * @param string $column                        the column to be set
	 * @param string $value                         the value to be set
	 * @return DB_SQL_Insert_Builder                a reference to the current instance
	 */
	public function column($column, $value) {
		$this->builder->column($column, $value);
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
	 * This function executes the SQL statement.
	 *
	 * @access public
	 * @param boolean $is_auto_incremented          whether to query for the last insert id
	 * @return integer                              the last insert id
	 */
	public function execute() {
		$is_auto_incremented = $this->model::is_auto_incremented();
		$connection = DB_Connection_Pool::instance()->get_connection($this->source);
		$connection->execute($this->statement());
		$primary_key = ($is_auto_incremented) ? $connection->get_last_insert_id() : 0;
		return $primary_key;
	}

}
?>