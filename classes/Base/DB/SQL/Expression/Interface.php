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
 * This interface provides the contract for an expression class.
 * 
 * @package Leap
 * @category SQL
 * @version 2012-02-22
 *
 * @see http://en.wikibooks.org/wiki/SQL_Dialects_Reference
 */
interface Base_DB_SQL_Expression_Interface {

	/**
	 * This function initializes the class with the specified data source.
	 *
	 * @access public
	 * @param mixed $source                     the data source to be used
	 */
	public function __construct($source);

	/**
	 * This function prepares the specified expression as an alias.
	 *
	 * @access public
	 * @param string $expr                      the expression to be prepared
	 * @return string                           the prepared expression
	 * @throws Throwable_InvalidArgument_Exception indicates that there is a data type mismatch
	 */
	public function prepare_alias($expr);

	/**
	 * This function prepares the specified expression as a boolean.
	 *
	 * @access public
	 * @param string $expr                      the expression to be prepared
	 * @return string                           the prepared expression
	 */
	public function prepare_boolean($expr);

	/**
	 * This function prepares the specified expression as a connector.
	 *
	 * @access public
	 * @param string $expr                      the expression to be prepared
	 * @return string                           the prepared expression
	 */
	public function prepare_connector($expr);

	/**
	 * This function prepares the specified expression as an identifier column.
	 *
	 * @access public
	 * @param string $expr                      the expression to be prepared
	 * @return string                           the prepared expression
	 */
	public function prepare_identifier($expr);

	/**
	 * This function prepares the specified expression as a join type.
	 *
	 * @access public
	 * @param string $expr                      the expression to be prepared
	 * @return string                           the prepared expression
	 */
	public function prepare_join($expr);

	/**
	 * This function prepares the specified expression as a natural number.
	 *
	 * @access public
	 * @param string $expr                      the expression to be prepared
	 * @return string                           the prepared expression
	 */
	public function prepare_natural($expr);

	/**
	 * This function prepares the specified expression as a operator.
	 *
	 * @access public
	 * @param string $expr                      the expression to be prepared
	 * @param string $group                     the operator grouping
	 * @return string                           the prepared expression
	 */
	public function prepare_operator($expr, $group);

	/**
	 * This function prepare the specified expression as a ordering token.
	 *
	 * @access public
	 * @param string $column                    the column to be sorted
	 * @param string $ordering                  the ordering token that signals whether the
	 *                                          column will sorted either in ascending or
	 *                                          descending order
	 * @param string $nulls                     the weight to be given to null values
	 * @return string                           the prepared clause
	 */
	public function prepare_ordering($column, $ordering, $nulls);

	/**
	 * This function prepares the specified expression as a parenthesis.
	 *
	 * @access public
	 * @param string $expr                      the expression to be prepared
	 * @return string                           the prepared expression
	 */
	public function prepare_parenthesis($expr);

	/**
	 * This function prepares the specified expression as a value.
	 *
	 * @access public
	 * @param string $expr                      the expression to be prepared
	 * @param char $escape                      the escape character
	 * @return string                           the prepared expression
	 */
	public function prepare_value($expr, $escape = NULL);

	/**
	 * This function prepares the specified expression as a wildcard.
	 *
	 * @access public
	 * @param string $expr                      the expression to be prepared
	 * @return string                           the prepared expression
	 */
	public function prepare_wildcard($expr);

}
?>