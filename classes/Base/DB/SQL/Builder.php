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
 * This class provides the base functionality for an SQL statement.
 *
 * @package Leap
 * @category SQL
 * @version 2012-11-14
 *
 * @abstract
 */
abstract class Base_DB_SQL_Builder extends Core_Object implements DB_SQL_Statement {

	/**
	 * This constant represents an opening parenthesis.
	 *
	 * @access public
	 * @var string
	 */
	const _OPENING_PARENTHESIS_ = '(';

	/**
	 * This constant represents a closing parenthesis.
	 *
	 * @access public
	 * @var string
	 */
	const _CLOSING_PARENTHESIS_ = ')';

	/**
	 * This function returns the raw SQL statement.
	 *
	 * @access public
	 * @return string                               the raw SQL statement
	 */
	public function __toString() {
		return $this->statement(TRUE);
	}

	/**
	 * This function returns a new instance of the calling class.
	 *
	 * @access public
	 * @param DB_DataSource $source             the data source to be used
	 * @return DB_SQL_Builder                   a new instance of the calling class
	 */
	public static function factory(DB_DataSource $source) {
		$class = get_called_class();
		return new $class($source);
	}

}
?>