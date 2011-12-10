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
 * This interfaces enforces that an SQLTranslator will provide functions for certain database.
 *
 * @package Leap
 * @category SQL
 * @version 2011-06-07
 *
 * @see http://en.wikibooks.org/wiki/Category:SQL_Dialects_Reference
 * @see http://en.wikibooks.org/wiki/SQL_Dialects_Reference/Data_structure_definition/Delimited_identifiers
 * @see http://en.wikibooks.org/wiki/SQL_Dialects_Reference/Functions_and_expressions/Math_functions/Aggregate_functions
 * @see http://en.wikibooks.org/wiki/SQL_Dialects_Reference/Functions_and_expressions/Math_functions/Numeric_functions
 * @see http://en.wikibooks.org/wiki/SQL_Dialects_Reference/Functions_and_expressions/Math_functions/Trigonometric_functions
 * @see http://en.wikibooks.org/wiki/SQL_Dialects_Reference/Functions_and_expressions/Misc_expressions
 * @see http://en.wikibooks.org/wiki/SQL_Dialects_Reference/Functions_and_expressions/String_functions
 * @see http://troels.arvin.dk/db/rdbms/
 * @see http://ferruh.mavituna.com/sql-injection-cheatsheet-oku/
 */
interface Base_DB_SQL_Translator_Interface {

	/**
	* This function returns an equivalent Firebird SQL statement.
	*
	* @access public
	* @return string                                the Firebird SQL statement
	*/
	public function fbsql();

	/**
	* This function returns an equivalent MS SQL statement.
	*
	* @access public
	* @return string                                the MS SQL statement
	*/
	public function mssql();

	/**
	* This function returns an equivalent MySQL statement.
	*
	* @access public
	* @return string                                the MySQL statement
	*/
	public function mysql();

}
?>