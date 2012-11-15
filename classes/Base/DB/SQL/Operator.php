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
 * This class contains a set of predefined operators.
 *
 * @package Leap
 * @category SQL
 * @version 2012-11-14
 *
 * @see http://www.firebirdsql.org/refdocs/langrefupd21-select.html
 * @see http://nimal.info/blog/2007/intersection-and-set-difference-in-mysql-a-workaround-for-except/
 * @see http://msdn.microsoft.com/en-us/library/ms189499.aspx
 * @see http://www.stanford.edu/dept/itss/docs/oracle/10g/server.101/b10759/operators005.htm
 * @see http://download.oracle.com/docs/cd/B19306_01/server.102/b14200/queries004.htm
 * @see http://www.sqlite.org/lang_select.html
 *
 * @abstract
 */
abstract class Base_DB_SQL_Operator extends Core_Object {

	// Comparison Operators

	const _EQUAL_TO_ = '=';

	const _NOT_EQUIVALENT_ = '<>';

	const _NOT_EQUAL_TO_ = '!=';

	const _BETWEEN_ = 'BETWEEN';

	const _NOT_BETWEEN_ = 'NOT BETWEEN';

	const _LIKE_ = 'LIKE';

	const _NOT_LIKE_ = 'NOT LIKE';

	const _LESS_THAN_ = '<';

	const _LESS_THAN_OR_EQUAL_TO_ = '<=';

	const _GREATER_THAN_ = '>';

	const _GREATER_THAN_OR_EQUAL_TO_ = '>=';

	const _IN_ = 'IN';

	const _NOT_IN_ = 'NOT IN';

	const _IS_ = 'IS';

	const _IS_NOT_ = 'IS NOT';

	const _REGEX_ = 'REGEX'; // supported by MySQL, SQLite (variation)

	const _NOT_REGEX_ = 'NOT REGEX'; // supported by MySQL, SQLite (variation)

	const _GLOB_ = 'GLOB'; // supported by SQLite

	const _NOT_GLOB_ = 'NOT GLOB'; // supported by SQLite

	const _MATCH_ = 'MATCH'; // supported by SQLite

	const _NOT_MATCH_ = 'NOT MATCH'; // supported by SQLite

	const _SIMILAR_TO_ = 'SIMILAR TO'; // supported by PostgreSQL

	const _NOT_SIMILAR_TO_ = 'NOT SIMILAR TO'; // supported by PostgreSQL

	// Set Operators

	const _EXCEPT_ = 'EXCEPT'; // supported by DB2, MS SQL, PostgreSQL, SQLite

	const _EXCEPT_ALL_ = 'EXCEPT ALL'; // supported by PostgreSQL

	const _EXCEPT_DISTINCT_ = 'EXCEPT DISTINCT';

	const _INTERSECT_ = 'INTERSECT'; // supported by DB2, MS SQL, Oracle, PostgreSQL, SQLite

	const _INTERSECT_ALL_ = 'INTERSECT ALL'; // supported by PostgreSQL

	const _INTERSECT_DISTINCT_ = 'INTERSECT DISTINCT';

	const _MINUS_ = 'MINUS'; // supported by Oracle

	const _UNION_ = 'UNION'; // supported by DB2, Firebird, MS SQL, MySQL, Oracle, PostgreSQL, SQLite

	const _UNION_ALL_ = 'UNION ALL'; // supported by DB2, Firebird, MS SQL, MySQL, Oracle, PostgreSQL, SQLite

	const _UNION_DISTINCT_ = 'UNION DISTINCT'; // support by Firebird, MySQL

}
?>