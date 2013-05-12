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
 * This class builds an SQL select statement.
 *
 * @package Leap
 * @category SQL
 * @version 2013-01-28
 *
 * @abstract
 */
abstract class Base\DB\SQL\Select\Builder extends DB\SQL\Builder {

	/**
	 * This constructor instantiates this class using the specified data source.
	 *
	 * @access public
	 * @param DB\DataSource $data_source            the data source to be used
	 * @param array $columns                        the columns to be selected
	 */
	public function __construct(DB\DataSource $data_source, Array $columns = array()) {
		$this->dialect = $data_source->dialect;
		$precompiler = '\\Leap\\DB\\' . $this->dialect . '\\Precompiler';
		$this->precompiler = new $precompiler($data_source);
		$this->reset();
		foreach ($columns as $column) {
			$this->column($column);
		}
	}

	/**
	 * This function sets the wildcard to be used.
	 *
	 * @access public
	 * @param string $wildcard                      the wildcard to be used
	 * @return DB\SQL\Select\Builder                a reference to the current instance
	 */
	public function all($wildcard = '*') {
		$this->data['wildcard'] = $this->precompiler->prepare_wildcard($wildcard);
		$this->data['column'] = array();
		return $this;
	}

	/**
	 * This function sets the specified column to be selected.
	 *
	 * @access public
	 * @param string $column                        the column to be selected
	 * @param string $alias                         the alias to be used for the specified column
	 * @return DB\SQL\Select\Builder                a reference to the current instance
	 */
	public function column($column, $alias = NULL) {
		$column = $this->precompiler->prepare_identifier($column);
		if ($alias !== NULL) {
			$alias = $this->precompiler->prepare_alias($alias);
			$column = "{$column} AS {$alias}";
		}
		$this->data['column'][] = $column;
		return $this;
	}

	/**
	 * This function combines another SQL statement using the specified operator.
	 *
	 * @access public
	 * @param string $operator                      the operator to be used to append
	 *                                              the specified SQL statement
	 * @param string $statement                     the SQL statement to be appended
	 * @return DB\SQL\Select\Builder                a reference to the current instance
	 * @throws Throwable\SQL\Exception              indicates an invalid SQL build instruction
	 */
	public function combine($operator, $statement) {
		$builder = '\\Leap\\DB\\' . $this->dialect . '\\Select\\Builder';
		if (is_object($statement) AND ($statement instanceof $builder)) {
			$statement = $statement->statement(FALSE);
		}
		else if ( ! preg_match('/^SELECT.*$/i', $statement)) {
			throw new Throwable\SQL\Exception('Message: Invalid SQL build instruction. Reason: May only combine a SELECT statement.', array(':operator' => $operator, ':statement' => $statement));
		}
		$statement = trim($statement, "; \t\n\r\0\x0B");
		$operator = $this->precompiler->prepare_operator($operator, 'SET');
		$this->data['combine'][] = "{$operator} {$statement}";
		return $this;
	}

	/**
	 * This function will a column to be counted.
	 *
	 * @access public
	 * @param string $column                        the column to be counted
	 * @param string $alias                         the alias to be used for the specified column
	 * @return DB\SQL\Select\Builder                a reference to the current instance
	 */
	public function count($column = '*', $alias = 'count') {
		$column = ( ! empty($column) AND (substr_compare($column, '*', -1, 1) === 0))
			? $this->precompiler->prepare_wildcard($column)
			: $this->precompiler->prepare_identifier($column);
		return $this->column(DB\SQL::expr("COUNT({$column})"), $alias);
	}

	/**
	 * This function sets whether to constrain the SQL statement to only distinct records.
	 *
	 * @access public
	 * @param boolean $distinct                     whether to constrain the SQL statement to only
	 *                                              distinct records
	 * @return DB\SQL\Select\Builder                a reference to the current instance
	 */
	public function distinct($distinct = TRUE) {
		$this->data['distinct'] = $this->precompiler->prepare_boolean($distinct);
		return $this;
	}

	/**
	 * This function sets the table that will be accessed.
	 *
	 * @access public
	 * @param string $table                         the table to be accessed
	 * @param string $alias                         the alias to be used for the specified table
	 * @return DB\SQL\Select\Builder                a reference to the current instance
	 */
	public function from($table, $alias = NULL) {
		$table = $this->precompiler->prepare_identifier($table);
		if ($alias !== NULL) {
			$alias = $this->precompiler->prepare_alias($alias);
			$table = "{$table} AS {$alias}";
		}
		$this->data['from'] = $table;
		return $this;
	}

	/**
	 * This function adds a "group by" clause.
	 *
	 * @access public
	 * @param string $column                        the column(s) to be grouped
	 * @return DB\SQL\Select\Builder                a reference to the current instance
	 */
	public function group_by($column) {
		$fields = (is_array($column)) ? $column : array($column);
		foreach ($fields as $field) {
			$identifier = $this->precompiler->prepare_identifier($field);
			$this->data['group_by'][] = $identifier;
		}
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
	 * @return DB\SQL\Select\Builder                a reference to the current instance
	 * @throws Throwable\SQL\Exception              indicates an invalid SQL build instruction
	 */
	public function having($column, $operator, $value, $connector = 'AND') {
		if (empty($this->data['group_by'])) {
			throw new Throwable\SQL\Exception('Message: Invalid SQL build instruction. Reason: Must declare a GROUP BY clause before declaring a "having" constraint.', array(':column' => $column, ':operator' => $operator, ':value' => $value, ':connector' => $connector));
		}
		$operator = $this->precompiler->prepare_operator($operator, 'COMPARISON');
		if (($operator == DB\SQL\Operator::_BETWEEN_) OR ($operator == DB\SQL\Operator::_NOT_BETWEEN_)) {
			if ( ! is_array($value)) {
				throw new Throwable\SQL\Exception('Message: Invalid SQL build instruction. Reason: Operator requires the value to be declared as an array.', array(':column' => $column, ':operator' => $operator, ':value' => $value, ':connector' => $connector));
			}
			$column = $this->precompiler->prepare_identifier($column);
			$value0 = $this->precompiler->prepare_value($value[0]);
			$value1 = $this->precompiler->prepare_value($value[1]);
			$connector = $this->precompiler->prepare_connector($connector);
			$this->data['having'][] = array($connector, "{$column} {$operator} {$value0} AND {$value1}");
		}
		else {
			if (($operator == DB\SQL\Operator::_IN_ OR $operator == DB\SQL\Operator::_NOT_IN_) AND ! is_array($value)) {
				throw new Throwable\SQL\Exception('Message: Invalid SQL build instruction. Reason: Operator requires the value to be declared as an array.', array(':column' => $column, ':operator' => $operator, ':value' => $value, ':connector' => $connector));
			}
			if ($value === NULL) {
				switch ($operator) {
					case DB\SQL\Operator::_EQUAL_TO_:
						$operator = DB\SQL\Operator::_IS_;
					break;
					case DB\SQL\Operator::_NOT_EQUIVALENT_:
						$operator = DB\SQL\Operator::_IS_NOT_;
					break;
				}
			}
			$column = $this->precompiler->prepare_identifier($column);
			$escape = (in_array($operator, array(DB\SQL\Operator::_LIKE_, DB\SQL\Operator::_NOT_LIKE_)))
				? '\\\\'
				: NULL;
			$value = $this->precompiler->prepare_value($value, $escape);
			$connector = $this->precompiler->prepare_connector($connector);
			$this->data['having'][] = array($connector, "{$column} {$operator} {$value}");
		}
		return $this;
	}

	/**
	 * This function either opens or closes a "having" group.
	 *
	 * @access public
	 * @param string $parenthesis                   the parenthesis to be used
	 * @param string $connector                     the connector to be used
	 * @return DB\SQL\Select\Builder                a reference to the current instance
	 * @throws Throwable\SQL\Exception              indicates an invalid SQL build instruction
	 */
	public function having_block($parenthesis, $connector = 'AND') {
		if (empty($this->data['group_by'])) {
			throw new Throwable\SQL\Exception('Message: Invalid SQL build instruction. Reason: Must declare a GROUP BY clause before declaring a "having" constraint.', array(':parenthesis' => $parenthesis, ':connector' => $connector));
		}
		$parenthesis = $this->precompiler->prepare_parenthesis($parenthesis);
		$connector = $this->precompiler->prepare_connector($connector);
		$this->data['having'][] = array($connector, $parenthesis);
		return $this;
	}

	/**
	 * This function joins a table.
	 *
	 * @access public
	 * @param string $type                          the type of join
	 * @param string $table                         the table to be joined
	 * @param string $alias                         the alias to be used for the specified table
	 * @return DB\SQL\Select\Builder                a reference to the current instance
	 */
	public function join($type, $table, $alias = NULL) {
		$table = 'JOIN ' . $this->precompiler->prepare_identifier($table);
		if ($type !== NULL) {
			$type = $this->precompiler->prepare_join($type);
			$table = "{$type} {$table}";
		}
		if ($alias !== NULL) {
			$alias = $this->precompiler->prepare_alias($alias);
			$table = "{$table} {$alias}";
		}
		$this->data['join'][] = array($table, array(), array());
		return $this;
	}

	/**
	 * This function sets a "limit" constraint on the statement.
	 *
	 * @access public
	 * @param integer $limit                        the "limit" constraint
	 * @return DB\SQL\Select\Builder                a reference to the current instance
	 */
	public function limit($limit) {
		$this->data['limit'] = $this->precompiler->prepare_natural($limit);
		return $this;
	}

	/**
	 * This function sets an "offset" constraint on the statement.
	 *
	 * @access public
	 * @param integer $offset                       the "offset" constraint
	 * @return DB\SQL\Select\Builder                a reference to the current instance
	 */
	public function offset($offset) {
		$this->data['offset'] = $this->precompiler->prepare_natural($offset);
		return $this;
	}

	/**
	 * This function sets an "on" constraint for the last join specified.
	 *
	 * @access public
	 * @param string $column0                       the column to be constrained on
	 * @param string $operator                      the operator to be used
	 * @param string $column1                       the constraint column
	 * @return DB\SQL\Select\Builder                a reference to the current instance
	 * @throws Throwable\SQL\Exception              indicates an invalid SQL build instruction
	 */
	public function on($column0, $operator, $column1) {
		if ( ! empty($this->data['join'])) {
			$index = count($this->data['join']) - 1;
			$condition = $this->data['join'][$index][2];
			if ( ! empty($condition)) {
				throw new Throwable\SQL\Exception('Message: Invalid build instruction. Reason: Must not declare two different types of constraints on a JOIN statement.', array(':column0' => $column0, ':operator' => $operator, ':column1:' => $column1));
			}
			$column0 = $this->precompiler->prepare_identifier($column0);
			$operator = $this->precompiler->prepare_operator($operator, 'COMPARISON');
			$column1 = $this->precompiler->prepare_identifier($column1);
			$this->data['join'][$index][1][] = "{$column0} {$operator} {$column1}";
		}
		else {
			throw new Throwable\SQL\Exception('Message: Invalid build instruction. Reason: Must declare a JOIN clause before declaring an "on" constraint.', array(':column0' => $column0, ':operator' => $operator, ':column1:' => $column1));
		}
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
	 * @return DB\SQL\Select\Builder                a reference to the current instance
	 */
	public function order_by($column, $ordering = 'ASC', $nulls = 'DEFAULT') {
		$this->data['order_by'][] = $this->precompiler->prepare_ordering($column, $ordering, $nulls);
		return $this;
	}

	/**
	 * This function sets both the "offset" constraint and the "limit" constraint on
	 * the statement.
	 *
	 * @access public
	 * @param integer $offset                       the "offset" constraint
	 * @param integer $limit                        the "limit" constraint
	 * @return DB\SQL\Select\Builder                a reference to the current instance
	 */
	public function page($offset, $limit) {
		$this->offset($offset);
		$this->limit($limit);
		return $this;
	}

	/**
	 * This function resets the current builder.
	 *
	 * @access public
	 * @return DB\SQL\Select\Builder                a reference to the current instance
	 */
	public function reset() {
		$this->data = array(
			'column' => array(),
			'combine' => array(),
			'distinct' => FALSE,
			'from' => NULL,
			'group_by' => array(),
			'having' => array(),
			'join' => array(),
			'limit' => 0,
			'offset' => 0,
			'order_by' => array(),
			'where' => array(),
			'wildcard' => '*',
		);
		return $this;
	}

	/**
	 * This function sets a "using" constraint for the last join specified.
	 *
	 * @access public
	 * @param string $column                        the column to be constrained
	 * @return DB\SQL\Select\Builder                a reference to the current instance
	 * @throws Throwable\SQL\Exception              indicates an invalid SQL build instruction
	 */
	public function using($column) {
		if ( ! empty($this->data['join'])) {
			$index = count($this->data['join']) - 1;
			$condition = $this->data['join'][$index][1];
			if ( ! empty($condition)) {
				throw new Throwable\SQL\Exception('Message: Invalid SQL build instruction. Reason: Must not declare two different types of constraints on a JOIN statement.', array(':column' => $column));
			}
			$column = $this->precompiler->prepare_identifier($column);
			$this->data['join'][$index][2][] = $column;
		}
		else {
			throw new Throwable\SQL\Exception('Message: Invalid SQL build instruction. Reason: Must declare a JOIN clause before declaring a "using" constraint.', array(':column' => $column));
		}
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
	 * @return DB\SQL\Select\Builder                a reference to the current instance
	 * @throws Throwable\SQL\Exception              indicates an invalid SQL build instruction
	 */
	public function where($column, $operator, $value, $connector = 'AND') {
		$operator = $this->precompiler->prepare_operator($operator, 'COMPARISON');
		if (($operator == DB\SQL\Operator::_BETWEEN_) OR ($operator == DB\SQL\Operator::_NOT_BETWEEN_)) {
			if ( ! is_array($value)) {
				throw new Throwable\SQL\Exception('Message: Invalid SQL build instruction. Reason: Operator requires the value to be declared as an array.', array(':column' => $column, ':operator' => $operator, ':value' => $value, ':connector' => $connector));
			}
			$column = $this->precompiler->prepare_identifier($column);
			$value0 = $this->precompiler->prepare_value($value[0]);
			$value1 = $this->precompiler->prepare_value($value[1]);
			$connector = $this->precompiler->prepare_connector($connector);
			$this->data['where'][] = array($connector, "{$column} {$operator} {$value0} AND {$value1}");
		}
		else {
			if ((($operator == DB\SQL\Operator::_IN_) OR ($operator == DB\SQL\Operator::_NOT_IN_)) AND ! is_array($value)) {
				throw new Throwable\SQL\Exception('Message: Invalid SQL build instruction. Reason: Operator requires the value to be declared as an array.', array(':column' => $column, ':operator' => $operator, ':value' => $value, ':connector' => $connector));
			}
			if ($value === NULL) {
				switch ($operator) {
					case DB\SQL\Operator::_EQUAL_TO_:
						$operator = DB\SQL\Operator::_IS_;
					break;
					case DB\SQL\Operator::_NOT_EQUIVALENT_:
						$operator = DB\SQL\Operator::_IS_NOT_;
					break;
				}
			}
			$column = $this->precompiler->prepare_identifier($column);
			$escape = (in_array($operator, array(DB\SQL\Operator::_LIKE_, DB\SQL\Operator::_NOT_LIKE_)))
				? '\\\\'
				: NULL;
			$value = $this->precompiler->prepare_value($value, $escape);
			$connector = $this->precompiler->prepare_connector($connector);
			$this->data['where'][] = array($connector, "{$column} {$operator} {$value}");
		}
		return $this;
	}

	/**
	 * This function either opens or closes a "where" group.
	 *
	 * @access public
	 * @param string $parenthesis                   the parenthesis to be used
	 * @param string $connector                     the connector to be used
	 * @return DB\SQL\Select\Builder                a reference to the current instance
	 */
	public function where_block($parenthesis, $connector = 'AND') {
		$parenthesis = $this->precompiler->prepare_parenthesis($parenthesis);
		$connector = $this->precompiler->prepare_connector($connector);
		$this->data['where'][] = array($connector, $parenthesis);
		return $this;
	}

}
