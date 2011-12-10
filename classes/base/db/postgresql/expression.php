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
 * This class provides a set of functions for preparing a PostgreSQL expression.
 *
 * @package Leap
 * @category PostgreSQL
 * @version 2011-12-04
 *
 * @abstract
 */
abstract class Base_DB_PostgreSQL_Expression implements DB_SQL_Expression_Interface {

	/**
	 * This constant represents an opening identifier quote character.
	 *
	 * @access public
	 * @static
	 * @const string
	 */
	const _OPENING_QUOTE_CHARACTER_ = '"';

	/**
	 * This constant represents a closing identifier quote character.
	 *
	 * @access public
	 * @static
	 * @const string
	 */
	const _CLOSING_QUOTE_CHARACTER_ = '"';

	/**
	* This function prepares the specified expression as an alias.
	*
	* @access public
	* @param string $expr                       the expression string to be prepared
	* @return string                            the prepared expression
	* @throws Kohana_InvalidArgument_Exception  indicates that there is a data type mismatch
	*/
	public function prepare_alias($expr) {
		if (!is_string($expr)) {
			throw new Kohana_InvalidArgument_Exception('Message: Invalid alias token specified. Reason: Token must be a string.', array(':expr' => $expr));
		}
		return self::_OPENING_QUOTE_CHARACTER_ . trim(preg_replace('/[^a-z0-9$_ ]/i', '', $expr)) . self::_CLOSING_QUOTE_CHARACTER_;
	}

	/**
	* This function prepares the specified expression as a boolean.
	*
	* @access public
	* @param string $expr                       the expression string to be prepared
	* @return string                            the prepared expression
	*/
	public function prepare_boolean($expr) {
		return (boolean)$expr;
	}

	/**
	* This function prepares the specified expression as a connector.
	*
	* @access public
	* @param string $expr                       the expression string to be prepared
	* @return string                            the prepared expression
	*/
	public function prepare_connector($expr) {
		if (is_string($expr)) {
			$expr = strtoupper($expr);
			switch ($expr) {
				case DB_SQL_Connector::_AND_:
				case DB_SQL_Connector::_OR_:
					return $expr;
				break;
			}
		}
		throw new Kohana_InvalidArgument_Exception('Message: Invalid connector token specified. Reason: Token must exist in the enumerated set.', array(':expr' => $expr));
	}

	/**
	* This function prepares the specified expression as an identifier column.
	*
	* @access public
	* @param string $expr                       the expression string to be prepared
	* @return string                            the prepared expression
	*
	* @see http://en.wikibooks.org/wiki/SQL_Dialects_Reference/Data_structure_definition/Delimited_identifiers
	*/
	public function prepare_identifier($expr) {
		if ($expr instanceof DB_PostgreSQL_Select_Builder) {
			return '(' . $expr->statement(FALSE) . ')';
		}
		else if (($expr instanceof Database_Expression) || ($expr instanceof DB_SQL_Expression)) {
			return $expr->value();
		}
		else if (!is_string($expr)) {
			throw new Kohana_InvalidArgument_Exception('Message: Invalid identifier expression specified. Reason: Token must be a string.', array(':expr' => $expr));
		}
		else if (preg_match('/^SELECT.*$/i', $expr)) {
			if ($expr[count($expr - 1)] == ';') {
				$expr = substr($expr, 0, -1);
			}
			return "({$expr})";
		}
		$parts = explode('.', $expr);
		foreach ($parts as &$part) {
			$part = self::_OPENING_QUOTE_CHARACTER_ . trim(preg_replace('/[^a-z0-9$_ ]/i', '', $part)) . self::_CLOSING_QUOTE_CHARACTER_;
		}
		$expr = implode('.', $parts);
		return $expr;
	}

	/**
	* This function prepares the specified expression as a join type.
	*
	* @access public
	* @param string $expr                       the expression string to be prepared
	* @return string                            the prepared expression
	*
	* @see http://www.postgresql.org/docs/8.2/static/queries-table-expressions.html
	*/
	public function prepare_join($expr) {
		if (is_string($expr)) {
			$expr = strtoupper($expr);
			switch ($expr) {
				case DB_SQL_JoinType::_CROSS_:
				case DB_SQL_JoinType::_INNER_:
				case DB_SQL_JoinType::_LEFT_:
				case DB_SQL_JoinType::_LEFT_OUTER_:
				case DB_SQL_JoinType::_RIGHT_:
				case DB_SQL_JoinType::_RIGHT_OUTER_:
				case DB_SQL_JoinType::_FULL_:
				case DB_SQL_JoinType::_FULL_OUTER_:
				case DB_SQL_JoinType::_NATURAL_:
				case DB_SQL_JoinType::_NATURAL_INNER_:
				case DB_SQL_JoinType::_NATURAL_LEFT_:
				case DB_SQL_JoinType::_NATURAL_LEFT_OUTER_:
				case DB_SQL_JoinType::_NATURAL_RIGHT_:
				case DB_SQL_JoinType::_NATURAL_RIGHT_OUTER_:
				case DB_SQL_JoinType::_NATURAL_FULL_:
				case DB_SQL_JoinType::_NATURAL_FULL_OUTER_:
					return $expr;
				break;
			}
		}
		throw new Kohana_InvalidArgument_Exception('Message: Invalid join type token specified. Reason: Token must exist in the enumerated set.', array(':expr' => $expr));
	}

	/**
	* This function prepares the specified expression as a natural number.
	*
	* @access public
	* @param string $expr                       the expression string to be prepared
	* @return string                            the prepared expression
	*/
	public function prepare_natural($expr) {
		settype($expr, 'integer');
		return abs($expr);
	}

	/**
	* This function prepares the specified expression as a operator.
	*
	* @access public
	* @param string $group                      the operator grouping
	* @param string $expr                       the expression string to be prepared
	* @return string                            the prepared expression
	*
	* @see http://developer.postgresql.org/pgdocs/postgres/functions.html
	* @see http://developer.postgresql.org/pgdocs/postgres/functions-comparison.html
	* @see http://developer.postgresql.org/pgdocs/postgres/functions-matching.html
	* @see http://www.postgresql.org/docs/8.3/interactive/queries-union.html
	*/
	public function prepare_operator($group, $expr) {
		if (is_string($group) && is_string($expr)) {
			$group = strtoupper($group);
			$expr = strtoupper($expr);
			if ($group == 'COMPARISON') {
				switch ($expr) {
					case DB_SQL_Operator::_NOT_EQUAL_TO_:
						$expr = DB_SQL_Operator::_NOT_EQUIVALENT_;
					case DB_SQL_Operator::_NOT_EQUIVALENT_:
					case DB_SQL_Operator::_EQUAL_TO_:
					case DB_SQL_Operator::_BETWEEN_:
					case DB_SQL_Operator::_NOT_BETWEEN_:
					case DB_SQL_Operator::_LIKE_:
					case DB_SQL_Operator::_NOT_LIKE_:
					case DB_SQL_Operator::_LESS_THAN_:
					case DB_SQL_Operator::_LESS_THAN_OR_EQUAL_TO_:
					case DB_SQL_Operator::_GREATER_THAN_:
					case DB_SQL_Operator::_GREATER_THAN_OR_EQUAL_TO_:
					case DB_SQL_Operator::_IN_:
					case DB_SQL_Operator::_NOT_IN_:
					case DB_SQL_Operator::_IS_:
					case DB_SQL_Operator::_IS_NOT_:
					case DB_SQL_Operator::_SIMILAR_TO_:
					case DB_SQL_Operator::_NOT_SIMILAR_TO_:
						return $expr;
					break;
				}
			}
			else if ($group == 'SET') {
				switch ($expr) {
					case DB_SQL_Operator::_EXCEPT_:
					case DB_SQL_Operator::_EXCEPT_ALL_:
					case DB_SQL_Operator::_INTERSECT_:
					case DB_SQL_Operator::_INTERSECT_ALL_:
					case DB_SQL_Operator::_UNION_:
					case DB_SQL_Operator::_UNION_ALL_:
						return $expr;
					break;
				}
			}
		}
		throw new Kohana_InvalidArgument_Exception('Message: Invalid operator token specified. Reason: Token must exist in the enumerated set.', array(':group' => $group, ':expr' => $expr));
	}

	/**
	* This function prepares the specified expression as a parenthesis.
	*
	* @access public
	* @param string $expr                       the expression string to be prepared
	* @return string                            the prepared expression
	*/
	public function prepare_parenthesis($expr) {
		if (is_string($expr)) {
			switch ($expr) {
				case DB_SQL_Builder::_OPENING_PARENTHESIS_:
				case DB_SQL_Builder::_CLOSING_PARENTHESIS_:
					return $expr;
				break;
			}
		}
		throw new Kohana_InvalidArgument_Exception('Message: Invalid parenthesis token specified. Reason: Token must exist in the enumerated set.', array(':expr' => $expr));
	}

	/**
	 * This function prepares the specified expression as a value.
	 *
	 * @access public
	 * @param string $expr                       the expression string to be prepared
	 * @return string                            the prepared expression
	 *
	 * @see http://stackoverflow.com/questions/574805/how-to-escape-strings-in-mssql-using-php
	 */
	public function prepare_value($expr) {
		if ($expr === NULL) {
			return 'NULL';
		}
		else if ($expr === TRUE) {
			return "'1'";
		}
		else if ($expr === FALSE) {
			return "'0'";
		}
		else if (is_array($expr)) {
			return '(' . implode(', ', array_map(array($this, __FUNCTION__), $expr)) . ')';
		}
		else if (is_object($expr)) {
			if ($expr instanceof DB_PostgreSQL_Select_Builder) {
				return '(' . $expr->statement(FALSE) . ')';
			}
			else if (($expr instanceof Database_Expression) || ($expr instanceof DB_SQL_Expression)) {
				return $expr->value();
			}
			else {
				return self::prepare_value((string)$expr); // Convert the object to a string
			}
		}
		else if (is_integer($expr)) {
			return (integer)$expr;
		}
		else if (is_double($expr)) {
			return sprintf('%F', $expr);
		}
		else if (is_string($expr) && preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}(\s[0-9]{2}:[0-9]{2}:[0-9]{2})?$/', $expr)) { // is_datetime($expr)
			return "'{$expr}'";
		}
		else if (empty($expr)) {
			return "''";
		}
		else {
			$unpacked = unpack('H*hex', $expr);
			$expr = '0x' . $unpacked['hex'];
			return $expr;
		}
	}

}
?>