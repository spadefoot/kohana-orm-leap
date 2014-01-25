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
 * This class provides a set of functions for preparing PostgreSQL expressions.
 *
 * @package Leap
 * @category PostgreSQL
 * @version 2013-01-28
 *
 * @abstract
 */
abstract class Base\DB\PostgreSQL\Precompiler extends DB\SQL\Precompiler {

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
	 * @override
	 * @param string $expr                          the expression to be prepared
	 * @return string                               the prepared expression
	 * @throws Throwable\InvalidArgument\Exception  indicates a data type mismatch
	 */
	public function prepare_alias($expr) {
		if ( ! is_string($expr)) {
			throw new Throwable\InvalidArgument\Exception('Message: Invalid alias token specified. Reason: Token must be a string.', array(':expr' => $expr));
		}
		return static::_OPENING_QUOTE_CHARACTER_ . trim(preg_replace('/[^a-z0-9$_ ]/i', '', $expr)) . static::_CLOSING_QUOTE_CHARACTER_;
	}

	/**
	 * This function prepares the specified expression as an identifier column.
	 *
	 * @access public
	 * @override
	 * @param string $expr                          the expression to be prepared
	 * @return string                               the prepared expression
	 * @throws Throwable\InvalidArgument\Exception  indicates a data type mismatch
	 *
	 * @see http://en.wikibooks.org/wiki/SQL_Dialects_Reference/Data_structure_definition/Delimited_identifiers
	 */
	public function prepare_identifier($expr) {
		if ($expr instanceof DB\PostgreSQL\Select\Builder) {
			return DB\SQL\Builder::_OPENING_PARENTHESIS_ . $expr->statement(FALSE) . DB\SQL\Builder::_CLOSING_PARENTHESIS_;
		}
		else if ($expr instanceof DB\SQL\Expression) {
			return $expr->value($this);
		}
		else if (class_exists('\\Database\\Expression') AND ($expr instanceof \Database\Expression)) {
			return $expr->value();
		}
		else if ( ! is_string($expr)) {
			throw new Throwable\InvalidArgument\Exception('Message: Invalid identifier expression specified. Reason: Token must be a string.', array(':expr' => $expr));
		}
		else if (preg_match('/^SELECT.*$/i', $expr)) {
			$expr = rtrim($expr, "; \t\n\r\0\x0B");
			return DB\SQL\Builder::_OPENING_PARENTHESIS_ . $expr . DB\SQL\Builder::_CLOSING_PARENTHESIS_;
		}
		$parts = explode('.', $expr);
		foreach ($parts as &$part) {
			$part = static::_OPENING_QUOTE_CHARACTER_ . trim(preg_replace('/[^a-z0-9$_ ]/i', '', $part)) . static::_CLOSING_QUOTE_CHARACTER_;
		}
		$expr = implode('.', $parts);
		return $expr;
	}

	/**
	 * This function prepares the specified expression as a join type.
	 *
	 * @access public
	 * @override
	 * @param string $expr                          the expression to be prepared
	 * @return string                               the prepared expression
	 * @throws Throwable\InvalidArgument\Exception  indicates a data type mismatch
	 *
	 * @see http://www.postgresql.org/docs/8.2/static/queries-table-expressions.html
	 */
	public function prepare_join($expr) {
		if (is_string($expr)) {
			$expr = strtoupper($expr);
			switch ($expr) {
				case DB\SQL\JoinType::_CROSS_:
				case DB\SQL\JoinType::_INNER_:
				case DB\SQL\JoinType::_LEFT_:
				case DB\SQL\JoinType::_LEFT_OUTER_:
				case DB\SQL\JoinType::_RIGHT_:
				case DB\SQL\JoinType::_RIGHT_OUTER_:
				case DB\SQL\JoinType::_FULL_:
				case DB\SQL\JoinType::_FULL_OUTER_:
				case DB\SQL\JoinType::_NATURAL_:
				case DB\SQL\JoinType::_NATURAL_INNER_:
				case DB\SQL\JoinType::_NATURAL_LEFT_:
				case DB\SQL\JoinType::_NATURAL_LEFT_OUTER_:
				case DB\SQL\JoinType::_NATURAL_RIGHT_:
				case DB\SQL\JoinType::_NATURAL_RIGHT_OUTER_:
				case DB\SQL\JoinType::_NATURAL_FULL_:
				case DB\SQL\JoinType::_NATURAL_FULL_OUTER_:
					return $expr;
				break;
			}
		}
		throw new Throwable\InvalidArgument\Exception('Message: Invalid join type token specified. Reason: Token must exist in the enumerated set.', array(':expr' => $expr));
	}

	/**
	 * This function prepares the specified expression as a operator.
	 *
	 * @access public
	 * @override
	 * @param string $expr                          the expression to be prepared
	 * @param string $group                         the operator grouping
	 * @return string                               the prepared expression
	 * @throws Throwable\InvalidArgument\Exception  indicates a data type mismatch
	 *
	 * @see http://www.postgresql.org/docs/9.2/static/functions.html
	 * @see http://developer.postgresql.org/pgdocs/postgres/functions.html
	 * @see http://developer.postgresql.org/pgdocs/postgres/functions-comparison.html
	 * @see http://developer.postgresql.org/pgdocs/postgres/functions-matching.html
	 * @see http://www.postgresql.org/docs/8.3/interactive/queries-union.html
	 * @see http://www.postgresql.org/docs/6.5/static/operators1716.htm
	 * @see http://www.postgresql.org/docs/6.5/static/operators1967.htm
	 * @see http://www.postgresql.org/docs/6.5/static/operators2026.htm
	 */
	public function prepare_operator($expr, $group) {
		if (is_string($group) AND is_string($expr)) {
			$group = strtoupper($group);
			$expr = strtoupper($expr);
			if ($group == 'COMPARISON') {
				switch ($expr) {
					case DB\SQL\Operator::_NOT_EQUAL_TO_:
					case DB\SQL\Operator::_NOT_EQUIVALENT_:
						return DB\SQL\Operator::_NOT_EQUIVALENT_;
					case DB\SQL\Operator::_EQUAL_TO_:
					case DB\SQL\Operator::_BETWEEN_:
					case DB\SQL\Operator::_NOT_BETWEEN_:
					case '<<':   // is contained within
					case '<<=':  // is contained within or equals
					case '>>':   // contains
					case '>>=':  // contains or equals
					case DB\SQL\Operator::_LIKE_:
					case '~~':   // like
					case DB\SQL\Operator::_NOT_LIKE_:
					case '!~~':  // not like
					case DB\SQL\Operator::_LESS_THAN_:
					case DB\SQL\Operator::_LESS_THAN_OR_EQUAL_TO_:
					case DB\SQL\Operator::_GREATER_THAN_:
					case DB\SQL\Operator::_GREATER_THAN_OR_EQUAL_TO_:
					case DB\SQL\Operator::_IN_:
					case DB\SQL\Operator::_NOT_IN_:
					case '!!=':  // not in
					case DB\SQL\Operator::_IS_:
					case DB\SQL\Operator::_IS_NOT_:
					case DB\SQL\Operator::_SIMILAR_TO_:
					case DB\SQL\Operator::_NOT_SIMILAR_TO_:
					case '~':   // Match (regex), case sensitive
					case '~*':  // Match (regex), case insensitive
					case '!~':  // Does not match (regex), case sensitive
					case '!~*': // Does not match (regex), case insensitive
					case '#<':  // Interval less than?
					case '#<=': // Interval less than or equal to?
					case '#<>': // Interval not equal?
					case '#=':  // Interval equal?
					case '#>':  // Interval greater than?
					case '#>=': // Interval greater than or equal to?
					case '<#>': // Convert to time interval
					case '<<':  // Interval less than?
					case '|':   // Start of interval
					case '~=':  // Same as
					case '<?>': // Time inside interval?
						return $expr;
					break;
				}
			}
			else if ($group == 'SET') {
				switch ($expr) {
					case DB\SQL\Operator::_EXCEPT_:
					case DB\SQL\Operator::_EXCEPT_ALL_:
					case DB\SQL\Operator::_INTERSECT_:
					case DB\SQL\Operator::_INTERSECT_ALL_:
					case DB\SQL\Operator::_UNION_:
					case DB\SQL\Operator::_UNION_ALL_:
						return $expr;
					break;
				}
			}
		}
		throw new Throwable\InvalidArgument\Exception('Message: Invalid operator token specified. Reason: Token must exist in the enumerated set.', array(':group' => $group, ':expr' => $expr));
	}

	/**
	 * This function prepare the specified expression as a ordering token.
	 *
	 * @access public
	 * @override
	 * @param string $column                        the column to be sorted
	 * @param string $ordering                      the ordering token that signals whether the
	 *                                              column will sorted either in ascending or
	 *                                              descending order
	 * @param string $nulls                         the weight to be given to null values
	 * @return string                               the prepared clause
	 *
	 * @see http://www.postgresql.org/docs/9.0/static/sql-select.html
	 */
	public function prepare_ordering($column, $ordering, $nulls) {
		$column = $this->prepare_identifier($column);
		switch (strtoupper($ordering)) {
			case 'DESC':
				$ordering = 'DESC';
			break;
			case 'ASC':
			default:
				$ordering = 'ASC';
			break;
		}
		$expr = "{$column} {$ordering}";
		switch (strtoupper($nulls)) {
			case 'FIRST':
				$expr .= ' NULLS FIRST';
			break;
			case 'LAST':
				$expr .= ' NULLS LAST';
			break;
		}
		return $expr;
	}

	/**
	 * This function prepares the specified expression as a value.
	 *
	 * @access public
	 * @override
	 * @param string $expr                          the expression to be prepared
	 * @param char $escape                          the escape character
	 * @return string                               the prepared expression
	 *
	 * @see http://www.postgresql.org/docs/7.4/static/datatype-boolean.html
	 */
	public function prepare_value($expr, $escape = NULL) {
		if ($expr === NULL) {
			return 'NULL';
		}
		else if ($expr === TRUE) {
			return "'t'"; // TRUE, 't', 'true', 'y', 'yes', '1'
		}
		else if ($expr === FALSE) {
			return "'f'"; // FALSE, 'f', 'false', 'n', 'no', '0'
		}
		else if (is_array($expr)) {
			$buffer = array();
			foreach ($expr as $value) {
				$buffer[] = $this->prepare_value($value, $escape);
			}
			return DB\SQL\Builder::_OPENING_PARENTHESIS_ . implode(', ', $buffer) . DB\SQL\Builder::_CLOSING_PARENTHESIS_;
		}
		else if (is_object($expr)) {
			if ($expr instanceof DB\PostgreSQL\Select\Builder) {
				return DB\SQL\Builder::_OPENING_PARENTHESIS_ . $expr->statement(FALSE) . DB\SQL\Builder::_CLOSING_PARENTHESIS_;
			}
			else if ($expr instanceof DB\SQL\Expression) {
				return $expr->value($this);
			}
			else if (class_exists('\\Database\\Expression') AND ($expr instanceof \Database\Expression)) {
				return $expr->value();
			}
			else if ($expr instanceof Core\Data\ByteString) {
				return $expr->as_hexcode("x'%s'");
			}
			else if ($expr instanceof Core\Data\BitField) {
				return $expr->as_binary("b'%s'");
			}
			else {
				return static::prepare_value( (string) $expr); // Convert the object to a string
			}
		}
		else if (is_integer($expr)) {
			return (int) $expr;
		}
		else if (is_double($expr)) {
			return sprintf('%F', $expr);
		}
		else if (is_string($expr) AND preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}(\s[0-9]{2}:[0-9]{2}:[0-9]{2})?$/', $expr)) { // is_datetime($expr)
			return "'{$expr}'";
		}
		else if ($expr === '') {
			return "''";
		}
		else {
			return DB\Connection\Pool::instance()->get_connection($this->data_source)->quote($expr, $escape);
		}
	}

	/**
	 * This function prepares the specified expression as a wildcard.
	 *
	 * @access public
	 * @override
	 * @param string $expr                          the expression to be prepared
	 * @return string                               the prepared expression
	 * @throws Throwable\InvalidArgument\Exception  indicates a data type mismatch
	 */
	public function prepare_wildcard($expr) {
		if ( ! is_string($expr)) {
			throw new Throwable\InvalidArgument\Exception('Message: Invalid wildcard token specified. Reason: Token must be a string.', array(':expr' => $expr));
		}
		$parts = explode('.', $expr);
		$count = count($parts);
		for ($i = 0; $i < $count; $i++) {
			$parts[$i] = (trim($parts[$i]) != '*')
				? static::_OPENING_QUOTE_CHARACTER_ . trim(preg_replace('/[^a-z0-9$_ ]/i', '', $parts[$i])) . static::_CLOSING_QUOTE_CHARACTER_
				: '*';
		}
		if (isset($parts[$count - 1]) AND ($parts[$count - 1] != '*')) {
			$parts[] = '*';
		}
		$expr = implode('.', $parts);
		return $expr;
	}

	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This variable stores the compiler's XML config file.
	 *
	 * @access protected
	 * @static
	 * @var Core\Data\Serialization\XML
	 */
	protected static $xml = NULL;

	/**
	 * This function checks whether the specified token is a reserved keyword.
	 *
	 * @access public
	 * @static
	 * @param string $token                         the token to be cross-referenced
	 * @return boolean                              whether the token is a reserved keyword
	 *
	 * @see http://www.postgresql.org/docs/7.3/static/sql-keywords-appendix.html
	 */
	public static function is_keyword($token) {
		if (static::$xml === NULL) {
			static::$xml = Core\Data\Serialization\XML::load('config/sql/postgresql.xml');
		}
		$token = strtoupper($token);
		$nodes = static::$xml->xpath("/sql/dialect[@name='postgresql' and @version='7.3']/keywords[keyword = '{$token}']");
		return ! empty($nodes);
	}

}
