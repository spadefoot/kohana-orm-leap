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
 * This class builds an SQL select statement.
 *
 * @package Leap
 * @category SQL
 * @version 2011-12-12
 *
 * @abstract
 */
abstract class Base_DB_SQL_Select_Builder extends DB_SQL_Builder {

	/**
	 * This variable stores the name of the SQL dialect being used.
	 *
	 * @access protected
	 * @var string
	 */
	protected $dialect = NULL;

	/**
	 * This variable stores a reference to the helper class that implements the expression
	 * interface.
	 *
	 * @access protected
	 * @var DB_SQL_Expression_Interface
	 */
	protected $helper = NULL;

	/**
	 * This variable stores the build data for the SQL statement.
	 *
	 * @access protected
	 * @var array
	 */
	protected $data = NULL;

	/**
	 * This constructor instantiates this class using the specified data source.
	 *
	 * @access public
	 * @param DB_DataSource $source             the data source to be used
	 * @param array $columns                    the columns to be selected
	 */
	public function __construct(DB_DataSource $source, Array $columns = array()) {
		$this->dialect = $source->dialect;
		$helper = 'DB_' . $this->dialect . '_Expression';
		$this->helper = new $helper($source);
		$this->data = array();
		$this->data['distinct'] = FALSE;
		$this->data['column'] = array();
		$this->data['from'] = NULL;
		$this->data['join'] = array();
		$this->data['where'] = array();
		$this->data['group_by'] = array();
		$this->data['having'] = array();
		$this->data['order_by'] = array();
		$this->data['limit'] = 0;
		$this->data['offset'] = 0;
		$this->data['combine'] = array();
		foreach ($columns as $column) {
			$this->column($column);
		}
	}

	/**
	 * This function sets whether to constrain the SQL statement to only distinct records.
	 *
	 * @access public
	 * @param boolean $distinct                 whether to constrain the SQL statement to only
	 *                                          distinct records
	 * @return DB_SQL_Select_Builder            a reference to the current instance
	 */
	public function distinct($distinct = TRUE) {
		$distinct = $this->helper->prepare_boolean($distinct);
		$this->data['distinct'] = $distinct;
		return $this;
	}

	/**
	 * This function explicits sets the specified column to be selected.
	 *
	 * @access public
	 * @param string $column                    the column to be selected
	 * @param string $alias                     the alias to used for the specified table
	 * @return DB_SQL_Select_Builder            a reference to the current instance
	 */
	public function column($column, $alias = NULL) {
		$column = $this->helper->prepare_identifier($column);
		if ( ! is_null($alias)) {
			$alias = $this->helper->prepare_alias($alias);
			$column = "{$column} AS {$alias}";
		}
		$this->data['column'][] = $column;
		return $this;
	}

	/**
	 * This function sets the table that will be accessed.
	 *
	 * @access public
	 * @param string $table                     the table to be accessed
	 * @param string $alias                     the alias to used for the specified table
	 * @return DB_SQL_Select_Builder            a reference to the current instance
	 */
	public function from($table, $alias = NULL) {
		$table = $this->helper->prepare_identifier($table);
		if ( ! is_null($alias)) {
			$alias = $this->helper->prepare_alias($alias);
			$table = "{$table} AS {$alias}";
		}
		$this->data['from'] = $table;
		return $this;
	}

	/**
	 * This function joins a table.
	 *
	 * @access public
	 * @param string $type                      the type of join
	 * @param string $table                     the table to be joined
	 * @param string $alias                     the alias to used for the specified table
	 * @return DB_SQL_Select_Builder            a reference to the current instance
	 */
	public function join($type, $table, $alias = NULL) {
		$table = 'JOIN ' . $this->helper->prepare_identifier($table);
		if ( ! is_null($type)) {
			$type = $this->helper->prepare_join($type);
			$table = "{$type} {$table}";
		}
		if ( ! is_null($alias)) {
			$alias = $this->helper->prepare_alias($alias);
			$table = "{$table} {$alias}";
		}
		$this->data['join'][] = array($table, array(), array());
		return $this;
	}

	/**
	 * This function sets an "on" constraint for the last join specified.
	 *
	 * @access public
	 * @param string $column0                   the column to be constrained on
	 * @param string $operator                  the operator to be used
	 * @param string $column1                   the constraint column
	 * @return DB_SQL_Select_Builder            a reference to the current instance
	 * @throws Kohana_SQL_Exception             indicates an invalid SQL build instruction
	 */
	public function on($column0, $operator, $column1) {
		if ( ! empty($this->data['join'])) {
			$index = count($this->data['join']) - 1;
			$condition = $this->data['join'][$index][2];
			if ( ! empty($condition)) {
				throw new Kohana_SQL_Exception('Message: Invalid build instruction. Reason: Must not declare two different types of constraints on a JOIN statement.', array(':column0' => $column0, ':operator' => $operator, ':column1:' => $column1));
			}
			$column0 = $this->helper->prepare_identifier($column0);
			$operator = $this->helper->prepare_operator('COMPARISON', $operator);
			$column1 = $this->helper->prepare_identifier($column1);
			$this->data['join'][$index][1][] = "{$column0} {$operator} {$column1}";
		}
		else {
			throw new Kohana_SQL_Exception('Message: Invalid build instruction. Reason: Must declare a JOIN clause before declaring an "on" constraint.', array(':column0' => $column0, ':operator' => $operator, ':column1:' => $column1));
		}
		return $this;
	}

	/**
	 * This function sets a "using" constraint for the last join specified.
	 *
	 * @access public
	 * @param string $column                    the column to be constrained
	 * @return DB_SQL_Select_Builder            a reference to the current instance
	 * @throws Kohana_SQL_Exception             indicates an invalid SQL build instruction
	 */
	public function using($column) {
		if ( ! empty($this->data['join'])) {
			$index = count($this->data['join']) - 1;
			$condition = $this->data['join'][$index][1];
			if ( ! empty($condition)) {
				throw new Kohana_SQL_Exception('Message: Invalid SQL build instruction. Reason: Must not declare two different types of constraints on a JOIN statement.', array(':column' => $column));
			}
			$column = $this->helper->prepare_identifier($column);
			$this->data['join'][$index][2][] = $column;
		}
		else {
			throw new Kohana_SQL_Exception('Message: Invalid SQL build instruction. Reason: Must declare a JOIN clause before declaring a "using" constraint.', array(':column' => $column));
		}
		return $this;
	}

	/**
	 * This function either opens or closes a "where" group.
	 *
	 * @access public
	 * @param string $parenthesis               the parenthesis to be used
	 * @param string $connector                 the connector to be used
	 * @return DB_SQL_Select_Builder            a reference to the current instance
	 */
	public function where_block($parenthesis, $connector = 'AND') {
		$parenthesis = $this->helper->prepare_parenthesis($parenthesis);
		$connector = $this->helper->prepare_connector($connector);
		$this->data['where'][] = array($connector, $parenthesis);
		return $this;
	}

	/**
	 * This function adds a "where" constraint.
	 *
	 * @access public
	 * @param string $column                    the column to be constrained
	 * @param string $operator                  the operator to be used
	 * @param string $value                     the value the column is constrained with
	 * @param string $connector                 the connector to be used
	 * @return DB_SQL_Select_Builder            a reference to the current instance
	 * @throws Kohana_SQL_Exception             indicates an invalid SQL build instruction
	 */
	public function where($column, $operator, $value, $connector = 'AND') {
		$operator = $this->helper->prepare_operator('COMPARISON', $operator);
		if (($operator == DB_SQL_Operator::_BETWEEN_) || ($operator == DB_SQL_Operator::_NOT_BETWEEN_)) {
			if ( ! is_array($value)) {
				throw new Kohana_SQL_Exception('Message: Invalid SQL build instruction. Reason: Operator requires the value to be declared as an array.', array(':column' => $column, ':operator' => $operator, ':value' => $value, ':connector' => $connector));
			}
			$column = $this->helper->prepare_identifier($column);
			$value0 = $this->helper->prepare_value($value[0]);
			$value1 = $this->helper->prepare_value($value[1]);
			$connector = $this->helper->prepare_connector($connector);
			$this->data['where'][] = array($connector, "{$column} {$operator} {$value0} AND {$value1}");
		}
		else {
			if ((($operator == DB_SQL_Operator::_IN_) || ($operator == DB_SQL_Operator::_NOT_IN_)) && !is_array($value)) {
				throw new Kohana_SQL_Exception('Message: Invalid SQL build instruction. Reason: Operator requires the value to be declared as an array.', array(':column' => $column, ':operator' => $operator, ':value' => $value, ':connector' => $connector));
			}
			if (is_null($value)) {
				switch ($operator) {
					case DB_SQL_Operator::_EQUAL_TO_:
						$operator = DB_SQL_Operator::_IS_;
					break;
					case DB_SQL_Operator::_NOT_EQUIVALENT_:
						$operator = DB_SQL_Operator::_IS_NOT_;
					break;
				}
			}
			$column = $this->helper->prepare_identifier($column);
			$value = $this->helper->prepare_value($value);
			$connector = $this->helper->prepare_connector($connector);
			$this->data['where'][] = array($connector, "{$column} {$operator} {$value}");
		}
		return $this;
	}

	/**
	 * This function adds a "group by" clause.
	 *
	 * @access public
	 * @param string $column                    the column(s) to be grouped
	 * @return DB_SQL_Select_Builder            a reference to the current instance
	 */
	public function group_by($column) {
		$fields = (is_array($column)) ? $column : array($column);
		foreach ($fields as $field) {
			$identifier = $this->helper->prepare_identifier($field);
			$this->data['group_by'][] = $identifier;
		}
		return $this;
	}

	/**
	 * This function either opens or closes a "having" group.
	 *
	 * @access public
	 * @param string $parenthesis               the parenthesis to be used
	 * @param string $connector                 the connector to be used
	 * @return DB_SQL_Select_Builder            a reference to the current instance
	 * @throws Kohana_SQL_Exception             indicates an invalid SQL build instruction
	 */
	public function having_block($parenthesis, $connector = 'AND') {
		if (empty($this->data['group_by'])) {
			throw new Kohana_SQL_Exception('Message: Invalid SQL build instruction. Reason: Must declare a GROUP BY clause before declaring a "having" constraint.', array(':parenthesis' => $parenthesis, ':connector' => $connector));
		}
		$parenthesis = $this->helper->prepare_parenthesis($parenthesis);
		$connector = $this->helper->prepare_connector($connector);
		$this->data['having'][] = array($connector, $parenthesis);
		return $this;
	}

	/**
	 * This function adds a "having" constraint.
	 *
	 * @access public
	 * @param string $column                    the column to be constrained
	 * @param string $operator                  the operator to be used
	 * @param string $value                     the value the column is constrained with
	 * @param string $connector                 the connector to be used
	 * @return DB_SQL_Select_Builder            a reference to the current instance
	 * @throws Kohana_SQL_Exception             indicates an invalid SQL build instruction
	 */
	public function having($column, $operator, $value, $connector = 'AND') {
		if (empty($this->data['group_by'])) {
			throw new Kohana_SQL_Exception('Message: Invalid SQL build instruction. Reason: Must declare a GROUP BY clause before declaring a "having" constraint.', array(':column' => $column, ':operator' => $operator, ':value' => $value, ':connector' => $connector));
		}
		$operator = $this->helper->prepare_operator('COMPARISON', $operator);
		if (($operator == DB_SQL_Operator::_BETWEEN_) || ($operator == DB_SQL_Operator::_NOT_BETWEEN_)) {
			if ( ! is_array($value)) {
				throw new Kohana_SQL_Exception('Message: Invalid SQL build instruction. Reason: Operator requires the value to be declared as an array.', array(':column' => $column, ':operator' => $operator, ':value' => $value, ':connector' => $connector));
			}
			$column = $this->helper->prepare_identifier($column);
			$value0 = $this->helper->prepare_value($value[0]);
			$value1 = $this->helper->prepare_value($value[1]);
			$connector = $this->helper->prepare_connector($connector);
			$this->data['having'][] = array($connector, "{$column} {$operator} {$value0} AND {$value1}");
		}
		else {
			if ((($operator == DB_SQL_Operator::_IN_) || ($operator == DB_SQL_Operator::_NOT_IN_)) && !is_array($value)) {
				throw new Kohana_SQL_Exception('Message: Invalid SQL build instruction. Reason: Operator requires the value to be declared as an array.', array(':column' => $column, ':operator' => $operator, ':value' => $value, ':connector' => $connector));
			}
			if (is_null($value)) {
				switch ($operator) {
					case DB_SQL_Operator::_EQUAL_TO_:
						$operator = DB_SQL_Operator::_IS_;
					break;
					case DB_SQL_Operator::_NOT_EQUIVALENT_:
						$operator = DB_SQL_Operator::_IS_NOT_;
					break;
				}
			}
			$column = $this->helper->prepare_identifier($column);
			$value = $this->helper->prepare_value($value);
			$connector = $this->helper->prepare_connector($connector);
			$this->data['having'][] = array($connector, "{$column} {$operator} {$value}");
		}
		return $this;
	}

	/**
	 * This function sorts a column either ascending or descending order.
	 *
	 * @access public
	 * @param string $column                    the column to be sorted
	 * @param boolean $descending               whether to sort in descending order
	 * @return DB_SQL_Select_Builder            a reference to the current instance
	 */
	public function order_by($column, $descending = FALSE, $nulls = 'DEFAULT') {
		$column = $this->helper->prepare_identifier($column);
		$descending = $this->helper->prepare_boolean($descending);
		$this->data['order_by'][] = "{$column} " . (($descending) ? 'DESC' : 'ASC');
		return $this;
	}

	/**
	 * This function sets a "limit" constraint on the statement.
	 *
	 * @access public
	 * @param integer $limit                    the "limit" constraint
	 * @return DB_SQL_Select_Builder            a reference to the current instance
	 */
	public function limit($limit) {
		$limit = $this->helper->prepare_natural($limit);
		$this->data['limit'] = $limit;
		return $this;
	}

	/**
	 * This function sets an "offset" constraint on the statement.
	 *
	 * @access public
	 * @param integer $offset                   the "offset" constraint
	 * @return DB_SQL_Select_Builder            a reference to the current instance
	 */
	public function offset($offset) {
		$offset = $this->helper->prepare_natural($offset);
		$this->data['offset'] = $offset;
		return $this;
	}

	/**
	 * This function combines another SQL statement using the specified operator.
	 *
	 * @access public
	 * @param string $operator                  the operator to be used to append
	 *                                          the specified SQL statement
	 * @param string $statement                 the SQL statement to be appended
	 * @return DB_SQL_Select_Builder            a reference to the current instance
	 * @throws Kohana_SQL_Exception             indicates an invalid SQL build instruction
	 */
	public function combine($operator, $statement) {
		$select_builder = 'DB_' . $this->dialect . '_Select_Builder';
		if (is_object($statement) && ($statement instanceof $select_builder)) {
			$statement = $statement->statement(FALSE);
		}
		else if ( ! preg_match('/^SELECT.*$/i', $statement)) {
			throw new Kohana_SQL_Exception('Message: Invalid SQL build instruction. Reason: May only combine a SELECT statement.', array(':operator' => $operator, ':statement' => $statement));
		}
		else if ($statement[count($statement - 1)] == ';') {
			$statement = substr($statement, 0, -1);
		}
		$operator = $this->helper->prepare_operator('SET', $operator);
		$this->data['combine'][] = "{$operator} {$statement}";
		return $this;
	}

}
?>