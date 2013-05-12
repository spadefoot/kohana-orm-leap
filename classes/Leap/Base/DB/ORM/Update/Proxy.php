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
 * This class builds an SQL update statement.
 *
 * @package Leap
 * @category ORM
 * @version 2013-02-03
 *
 * @abstract
 */
abstract class Base\DB\ORM\Update\Proxy extends Core\Object implements DB\SQL\Statement {

	/**
	 * This variable stores an instance of the SQL builder class.
	 *
	 * @access protected
	 * @var DB\SQL\Update\Builder
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
	 * This variable stores an instance of the ORM builder extension class.
	 *
	 * @access protected
	 * @var DB\ORM\Builder
	 */
	protected $extension;

	/**
	 * This function attempts to call an otherwise inaccessible function on the model's
	 * builder extension.
	 *
	 * @access public
	 * @override
	 * @param string $function                          the name of the called function
	 * @param array $arguments                          an array with the parameters passed
	 * @return mixed                                    the result of the called function
	 * @throws Throwable\UnimplementedMethod\Exception  indicates that the called function is
	 *                                                  inaccessible
	 */
	public function __call($function, $arguments) {
		if ($this->extension !== NULL) {
			if (method_exists($this->extension, $function)) {
				$result = call_user_func_array(array($this->extension, $function), $arguments);
				if ($result instanceof DB\ORM\Builder) {
					return $this;
				}
				return $result;
			}
		}
		throw new Throwable\UnimplementedMethod\Exception('Message: Call to undefined member function. Reason: Function :function has not been defined in class :class.', array(':class' => get_class($this->extension), ':function' => $function, ':arguments' => $arguments));
	}

	/**
	 * This constructor instantiates this class using the specified model's name.
	 *
	 * @access public
	 * @param string $model                             the model's name
	 */
	public function __construct($model) {
		$name = $model;
		$model = DB\ORM\Model::model_name($name);
		$this->data_source = DB\DataSource::instance($model::data_source(DB\DataSource::MASTER_INSTANCE));
		$builder = '\\Leap\\DB\\' . $this->data_source->dialect . '\\Update\\Builder';
		$this->builder = new $builder($this->data_source);
		$extension = DB\ORM\Model::builder_name($name);
		if (class_exists($extension)) {
			$this->extension = new $extension($this->builder);
		}
		$table = $model::table();
		$this->builder->table($table);
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
	 * This function executes the built SQL statement.
	 *
	 * @access public
	 */
	public function execute() {
		$connection = DB\Connection\Pool::instance()->get_connection($this->data_source);
		$connection->execute($this->statement());
	}

	/**
	 * This function sets a "limit" constraint on the statement.
	 *
	 * @access public
	 * @param integer $limit                            the "limit" constraint
	 * @return DB\ORM\Update\Proxy                      a reference to the current instance
	 */
	public function limit($limit) {
		$this->builder->limit($limit);
		return $this;
	}

	/**
	 * This function sets an "offset" constraint on the statement.
	 *
	 * @access public
	 * @param integer $offset                           the "offset" constraint
	 * @return DB\ORM\Update\Proxy                      a reference to the current instance
	 */
	public function offset($offset) {
		$this->builder->offset($offset);
		return $this;
	}

	/**
	 * This function sets how a column will be sorted.
	 *
	 * @access public
	 * @param string $column                            the column to be sorted
	 * @param string $ordering                          the ordering token that signals whether the
	 *                                                  column will sorted either in ascending or
	 *                                                  descending order
	 * @param string $nulls                             the weight to be given to null values
	 * @return DB\ORM\Update\Proxy                      a reference to the current instance
	 */
	public function order_by($column, $ordering = 'ASC', $nulls = 'DEFAULT') {
		$this->builder->order_by($column, $ordering, $nulls);
		return $this;
	}

	/**
	 * This function resets the current builder.
	 *
	 * @access public
	 * @return DB\ORM\Update\Proxy                      a reference to the current instance
	 */
	public function reset() {
		$this->builder->reset();
		return $this;
	}

	/**
	 * This function sets the associated value with the specified column.
	 *
	 * @access public
	 * @param string $column                            the column to be set
	 * @param string $value                             the value to be set
	 * @return DB\ORM\Update\Proxy                      a reference to the current instance
	 */
	public function set($column, $value) {
		$this->builder->set($column, $value);
		return $this;
	}

	/**
	 * This function returns the SQL statement.
	 *
	 * @access public
	 * @override
	 * @param boolean $terminated                       whether to add a semi-colon to the end
	 *                                                  of the statement
	 * @return string                                   the SQL statement
	 */
	public function statement($terminated = TRUE) {
		return $this->builder->statement($terminated);
	}

	/**
	 * This function adds a "where" constraint.
	 *
	 * @access public
	 * @param string $column                            the column to be constrained
	 * @param string $operator                          the operator to be used
	 * @param string $value                             the value the column is constrained with
	 * @param string $connector                         the connector to be used
	 * @return DB\ORM\Update\Proxy                      a reference to the current instance
	 */
	public function where($column, $operator, $value, $connector = 'AND') {
		$this->builder->where($column, $operator, $value, $connector);
		return $this;
	}

	/**
	 * This function either opens or closes a "where" group.
	 *
	 * @access public
	 * @param string $parenthesis                       the parenthesis to be used
	 * @param string $connector                         the connector to be used
	 * @return DB\ORM\Update\Proxy                      a reference to the current instance
	 */
	public function where_block($parenthesis, $connector = 'AND') {
		$this->builder->where_block($parenthesis, $connector);
		return $this;
	}

}
