<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Copyright © 2011–2013 Spadefoot Team.
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
 * This class handles a standard Drizzle connection.
 *
 * @package Leap
 * @category Drizzle
 * @version 2013-01-06
 *
 * @see http://www.php.net/manual/en/book.mysql.php
 *
 * @abstract
 */
abstract class Base_DB_Drizzle_Connection_Standard extends DB_SQL_Connection_Standard {

	/**
	 * This function opens a connection using the data source provided.
	 *
	 * @access public
	 * @override
	 * @throws Throwable_Database_Exception     indicates that there is problem with
	 *                                          opening the connection
	 */
	public function open() {
		if ( ! $this->is_connected()) {
			$host = $this->data_source->host;
			$username = $this->data_source->username;
			$password = $this->data_source->password;
			$this->resource = ($this->data_source->is_persistent())
				? @mysql_pconnect($host, $username, $password)
				: @mysql_connect($host, $username, $password, TRUE);
			if ($this->resource === FALSE) {
				throw new Throwable_Database_Exception('Message: Failed to establish connection. Reason: :reason', array(':reason' => @mysql_error()));
			}
			if ( ! @mysql_select_db($this->data_source->database, $this->resource)) {
				throw new Throwable_Database_Exception('Message: Failed to connect to database. Reason: :reason', array(':reason' => @mysql_error($this->resource)));
			}
			// "There is no CHARSET or CHARACTER SET commands, everything defaults to UTF-8."
		}
	}

	/**
	 * This function begins a transaction.
	 *
	 * @access public
	 * @override
	 * @throws Throwable_SQL_Exception          indicates that the executed statement failed
	 *
	 * @see http://dev.mysql.com/doc/refman/5.0/en/commit.html
	 * @see http://php.net/manual/en/function.mysql-query.php
	 */
	public function begin_transaction() {
		$this->execute('START TRANSACTION;');
	}

	/**
	 * This function processes an SQL statement that will NOT return data.
	 *
	 * @access public
	 * @override
	 * @param string $sql						the SQL statement
	 * @throws Throwable_SQL_Exception          indicates that the executed statement failed
	 */
	public function execute($sql) {
		if ( ! $this->is_connected()) {
			throw new Throwable_SQL_Exception('Message: Failed to execute SQL statement. Reason: Unable to find connection.');
		}
		$command = @mysql_query($sql, $this->resource);
		if ($command === FALSE) {
			throw new Throwable_SQL_Exception('Message: Failed to execute SQL statement. Reason: :reason', array(':reason' => @mysql_error($this->resource)));
		}
		$this->sql = $sql;
		@mysql_free_result($command);
	}

	/**
	 * This function returns the last insert id.
	 *
	 * @access public
	 * @override
	 * @return integer                          the last insert id
	 * @throws Throwable_SQL_Exception          indicates that the query failed
	 */
	public function get_last_insert_id() {
		if ( ! $this->is_connected()) {
			throw new Throwable_SQL_Exception('Message: Failed to fetch the last insert id. Reason: Unable to find connection.');
		}
		$insert_id = @mysql_insert_id($this->resource);
		if ($insert_id === FALSE) {
			throw new Throwable_SQL_Exception('Message: Failed to fetch the last insert id. Reason: :reason', array(':reason' => @mysql_error($this->resource)));
		}
		return $insert_id;
	}

	/**
	 * This function rollbacks a transaction.
	 *
	 * @access public
	 * @override
	 * @throws Throwable_SQL_Exception          indicates that the executed statement failed
	 *
	 * @see http://php.net/manual/en/function.mysql-query.php
	 */
	public function rollback() {
		$this->execute('ROLLBACK;');
	}

	/**
	 * This function commits a transaction.
	 *
	 * @access public
	 * @override
	 * @throws Throwable_SQL_Exception          indicates that the executed statement failed
	 *
	 * @see http://dev.mysql.com/doc/refman/5.0/en/commit.html
	 * @see http://php.net/manual/en/function.mysql-query.php
	 */
	public function commit() {
		$this->execute('COMMIT;');
	}

	/**
	 * This function escapes a string to be used in an SQL statement.
	 *
	 * @access public
	 * @override
	 * @param string $string                    the string to be escaped
	 * @param char $escape                      the escape character
	 * @return string                           the quoted string
	 * @throws Throwable_SQL_Exception          indicates that no connection could
	 *                                          be found
	 */
	public function quote($string, $escape = NULL) {
		if ( ! $this->is_connected()) {
			throw new Throwable_SQL_Exception('Message: Failed to quote/escape string. Reason: Unable to find connection.');
		}

		$string = "'" . mysql_real_escape_string($string, $this->resource) . "'";

		if (is_string($escape) OR ! empty($escape)) {
			$string .= " ESCAPE '{$escape}'";
		}

		return $string;
	}

	/**
	 * This function closes an open connection.
	 *
	 * @access public
	 * @override
	 * @return boolean                          whether an open connection was closed
	 */
	public function close() {
		if ($this->is_connected()) {
			if ( ! @mysql_close($this->resource)) {
				return FALSE;
			}
			$this->resource = NULL;
		}
		return TRUE;
	}

	/**
	 * This destructor ensures that the connection is closed.
	 *
	 * @access public
	 * @override
	 */
	public function __destruct() {
		if (is_resource($this->resource)) {
			@mysql_close($this->resource);
		}
	}

}
