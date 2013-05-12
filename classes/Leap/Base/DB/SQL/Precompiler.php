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
 * This class provides a set of functions for preparing SQL expressions.
 * 
 * @package Leap
 * @category SQL
 * @version 2013-01-28
 *
 * @see http://en.wikibooks.org/wiki/SQL_Dialects_Reference
 *
 * @abstract
 */
abstract class Base\DB\SQL\Precompiler extends Core\Object {

	/**
	 * This variable stores a reference to the data source.
	 *
	 * @access protected
	 * @var mixed
	 */
	protected $data_source;

	/**
	 * This function initializes the class with the specified data source.
	 *
	 * @access public
	 * @param mixed $data_source                    the data source to be used
	 */
	public function __construct($data_source) {
		$this->data_source = $data_source;
	}

	/**
	 * This function prepares the specified expression as an alias.
	 *
	 * @access public
	 * @abstract
	 * @param string $expr                          the expression to be prepared
	 * @return string                               the prepared expression
	 * @throws Throwable\InvalidArgument\Exception  indicates a data type mismatch
	 */
	public abstract function prepare_alias($expr);

	/**
	 * This function prepares the specified expression as a boolean.
	 *
	 * @access public
	 * @param mixed $expr                           the expression to be prepared
	 * @return boolean                              the prepared boolean value
	 */
	public function prepare_boolean($expr) {
		return (bool) $expr;
	}

	/**
	 * This function prepares the specified expression as a connector.
	 *
	 * @access public
	 * @param string $expr                          the expression to be prepared
	 * @return string                               the prepared expression
	 * @throws Throwable\InvalidArgument\Exception  indicates a data type mismatch
	 */
	public function prepare_connector($expr) {
		if (is_string($expr)) {
			$expr = strtoupper($expr);
			switch ($expr) {
				case DB\SQL\Connector::_AND_:
				case DB\SQL\Connector::_OR_:
					return $expr;
				break;
			}
		}
		throw new Throwable\InvalidArgument\Exception('Message: Invalid connector token specified. Reason: Token must exist in the enumerated set.', array(':expr' => $expr));
	}

	/**
	 * This function prepares the specified expression as an identifier column.
	 *
	 * @access public
	 * @abstract
	 * @param string $expr                          the expression to be prepared
	 * @return string                               the prepared expression
	 * @throws Throwable\InvalidArgument\Exception  indicates a data type mismatch
	 */
	public abstract function prepare_identifier($expr);

	/**
	 * This function prepares the specified expression as a join type.
	 *
	 * @access public
	 * @abstract
	 * @param string $expr                          the expression to be prepared
	 * @return string                               the prepared expression
	 * @throws Throwable\InvalidArgument\Exception  indicates a data type mismatch
	 */
	public abstract function prepare_join($expr);

	/**
	 * This function prepares the specified expression as a natural number.
	 *
	 * @access public
	 * @param mixed $expr                           the expression to be prepared
	 * @return integer                              the prepared natural
	 */
	public function prepare_natural($expr) {
		return (is_numeric($expr)) ? (int) abs($expr) : 0;
	}

	/**
	 * This function prepares the specified expression as a operator.
	 *
	 * @access public
	 * @abstract
	 * @param string $expr                          the expression to be prepared
	 * @param string $group                         the operator grouping
	 * @return string                               the prepared expression
	 * @throws Throwable\InvalidArgument\Exception  indicates a data type mismatch
	 */
	public abstract function prepare_operator($expr, $group);

	/**
	 * This function prepare the specified expression as a ordering token.
	 *
	 * @access public
	 * @abstract
	 * @param string $column                        the column to be sorted
	 * @param string $ordering                      the ordering token that signals whether the
	 *                                              column will sorted either in ascending or
	 *                                              descending order
	 * @param string $nulls                         the weight to be given to null values
	 * @return string                               the prepared clause
	 */
	public abstract function prepare_ordering($column, $ordering, $nulls);

	/**
	 * This function prepares the specified expression as a parenthesis.
	 *
	 * @access public
	 * @param string $expr                          the expression to be prepared
	 * @return string                               the prepared expression
	 * @throws Throwable\InvalidArgument\Exception  indicates a data type mismatch
	 */
	public function prepare_parenthesis($expr) {
		if (is_string($expr)) {
			switch ($expr) {
				case DB\SQL\Builder::_OPENING_PARENTHESIS_:
				case DB\SQL\Builder::_CLOSING_PARENTHESIS_:
					return $expr;
				break;
			}
		}
		throw new Throwable\InvalidArgument\Exception('Message: Invalid parenthesis token specified. Reason: Token must exist in the enumerated set.', array(':expr' => $expr));
	}

	/**
	 * This function prepares the specified expression as a value.
	 *
	 * @access public
	 * @abstract
	 * @param string $expr                          the expression to be prepared
	 * @param char $escape                          the escape character
	 * @return string                               the prepared expression
	 */
	public abstract function prepare_value($expr, $escape = NULL);

	/**
	 * This function prepares the specified expression as a wildcard.
	 *
	 * @access public
	 * @abstract
	 * @param string $expr                          the expression to be prepared
	 * @return string                               the prepared expression
	 * @throws Throwable\InvalidArgument\Exception  indicates a data type mismatch
	 */
	public abstract function prepare_wildcard($expr);

}
