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
 * This class contains a set of predefined join types.
 *
 * @package Leap
 * @category SQL
 * @version 2012-11-14
 *
 * @see http://publib.boulder.ibm.com/infocenter/iseries/v5r4/topic/sqlp/rbafyjoin.htm
 * @see http://www.craigsmullins.com/outer-j.htm
 * @see http://docs.drizzle.org/join.html
 * @see http://www.firebirdsql.org/refdocs/langrefupd21-select.html
 * @see http://msdn.microsoft.com/en-us/library/aa259187%28v=sql.80%29.aspx
 * @see http://dev.mysql.com/doc/refman/5.0/en/join.html
 * @see http://download.oracle.com/docs/cd/B14117_01/server.101/b10759/statements_10002.htm
 * @see http://etutorials.org/SQL/Mastering+Oracle+SQL/Chapter+3.+Joins/3.3+Types+of+Joins/
 * @see http://www.postgresql.org/docs/8.2/static/queries-table-expressions.html
 * @see http://www.sqlite.org/lang_select.html
 *
 * @abstract
 */
abstract class Base_DB_SQL_JoinType extends Core_Object {

    const _NONE_ = NULL; // supported by all

	const _CROSS_ = 'CROSS'; // supported by DB2, Drizzle, Firebird, MS SQL, MySQL, Oracle, PostgreSQL, SQLite

	const _EXCEPTION_ = 'EXCEPTION'; // supported by DB2

	const _INNER_ = 'INNER'; // supported by DB2, Firebird, MS SQL, MySQL, Oracle, PostgreSQL, SQLite

	const _LEFT_ = 'LEFT'; // supported by DB2, Firebird, MS SQL, MySQL, Oracle, PostgreSQL, SQLite

	const _LEFT_OUTER_ = 'LEFT OUTER'; // supported by DB2, Firebird, MS SQL, MySQL, Oracle, PostgreSQL, SQLite

	const _RIGHT_ = 'RIGHT'; // supported by DB2, Firebird, MS SQL, MySQL, Oracle, PostgreSQL

	const _RIGHT_OUTER_ = 'RIGHT OUTER'; // supported by DB2, Firebird, MS SQL, MySQL, Oracle, PostgreSQL

	const _FULL_ = 'FULL'; // supported by DB2, Firebird, MS SQL, Oracle, PostgreSQL

	const _FULL_OUTER_ = 'FULL OUTER'; // supported by DB2, Firebird, MS SQL, Oracle, PostgreSQL

	const _NATURAL_ = 'NATURAL'; // supported by Firebird, MySQL, Oracle, PostgreSQL, SQLite

	const _NATURAL_CROSS_ = 'NATURAL CROSS'; // supported by SQLite

	const _NATURAL_INNER_ = 'NATURAL INNER'; // supported by Firebird, Oracle, PostgreSQL, SQLite

	const _NATURAL_LEFT_ = 'NATURAL LEFT'; // supported by Firebird, MySQL, Oracle, PostgreSQL, SQLite

	const _NATURAL_LEFT_OUTER_ = 'NATURAL LEFT OUTER'; // supported by Firebird, MySQL, Oracle, PostgreSQL, SQLite

	const _NATURAL_RIGHT_ = 'NATURAL RIGHT'; // supported by Firebird, MySQL, Oracle, PostgreSQL

	const _NATURAL_RIGHT_OUTER_ = 'NATURAL RIGHT OUTER'; // supported by Firebird, MySQL, Oracle, PostgreSQL

	const _NATURAL_FULL_ = 'NATURAL FULL'; // supported by Firebird, MS SQL, Oracle, PostgreSQL

	const _NATURAL_FULL_OUTER_ = 'NATURAL FULL OUTER'; // supported by Firebird, MS SQL, Oracle, PostgreSQL

	const _STRAIGHT_ = 'STRAIGHT'; // supported by MySQL

}
?>