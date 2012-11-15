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
 * This class handles a standard SQLite connection.
 *
 * @package Leap
 * @category SQLite
 * @version 2012-11-14
 *
 * @see http://www.php.net/manual/en/ref.sqlite.php
 *
 * @abstract
 */
abstract class Base_DB_SQLite_Connection_Standard extends DB_SQL_Connection_Standard {

	/**
	 * This function opens a connection using the data source provided.
	 *
	 * @access public
	 * @throws Throwable_Database_Exception        indicates that there is problem with
	 *                                          opening the connection
	 *
	 * @see http://www.sqlite.org/pragma.html#pragma_encoding
	 * @see http://stackoverflow.com/questions/263056/how-to-change-character-encoding-of-a-pdo-sqlite-connection-in-php
	 */
	public function open() {
		if ( ! $this->is_connected()) {
			$connection_string = $this->data_source->database;
			$error = NULL;
			$this->resource_id = ($this->data_source->is_persistent())
				? @sqlite_popen($connection_string, 0666, $error)
				: @sqlite_open($connection_string, 0666, $error);
			if ($this->resource_id === FALSE) {
				throw new Throwable_Database_Exception('Message: Failed to establish connection. Reason: :reason', array(':reason' => $error));
			}
			// "Once an encoding has been set for a database, it cannot be changed."
		}
	}

	/**
	 * This function begins a transaction.
	 *
	 * @access public
	 * @throws Throwable_SQL_Exception             indicates that the executed statement failed
	 *
	 * @see http://www.sqlite.org/lang_transaction.html
	 * @see http://en.wikibooks.org/wiki/SQL_Dialects_Reference/Transactions
	 */
	public function begin_transaction() {
		$this->execute('BEGIN IMMEDIATE TRANSACTION;');
	}

	/**
	 * This function allows for the ability to process a query that will return data
	 * using the passed string.
	 *
	 * @access public
	 * @param string $sql						the SQL statement
	 * @param string $type						the return type to be used
	 * @return DB_ResultSet                     the result set
	 * @throws Throwable_SQL_Exception             indicates that the query failed
	 */
	public function query($sql, $type = 'array') {
		if ( ! $this->is_connected()) {
			throw new Throwable_SQL_Exception('Message: Failed to query SQL statement. Reason: Unable to find connection.');
		}
		$result_set = $this->cache($sql, $type);
		if ($result_set !== NULL) {
			$this->sql = $sql;
			return $result_set;
		}
		$command_id = @sqlite_query($this->resource_id, $sql);
		if ($command_id === FALSE) {
			throw new Throwable_SQL_Exception('Message: Failed to query SQL statement. Reason: :reason', array(':reason' => sqlite_error_string(sqlite_last_error($this->resource_id))));
		}
		$records = array();
		$size = 0;
		while ($record = sqlite_fetch_array($command_id, SQLITE_ASSOC)) {
			$records[] = DB_Connection::type_cast($type, $record);
			$size++;
		}
		$command_id = NULL;
		$result_set = $this->cache($sql, $type, new DB_ResultSet($records, $size, $type));
		$this->sql = $sql;
		return $result_set;
	}

	/**
	 * This function allows for the ability to process a query that will not return
	 * data using the passed string.
	 *
	 * @access public
	 * @param string $sql						the SQL statement
	 * @throws Throwable_SQL_Exception             indicates that the executed statement failed
	 */
	public function execute($sql) {
		if ( ! $this->is_connected()) {
			throw new Throwable_SQL_Exception('Message: Failed to execute SQL statement. Reason: Unable to find connection.');
		}
		$error = NULL;
		$command_id = @sqlite_exec($this->resource_id, $sql, $error);
		if ($command_id === FALSE) {
			throw new Throwable_SQL_Exception('Message: Failed to execute SQL statement. Reason: :reason', array(':reason' => $error));
		}
		$this->sql = $sql;
	}

	/**
	 * This function returns the last insert id.
	 *
	 * @access public
	 * @return integer                          the last insert id
	 * @throws Throwable_SQL_Exception             indicates that the query failed
	 */
	public function get_last_insert_id() {
		if ( ! $this->is_connected()) {
			throw new Throwable_SQL_Exception('Message: Failed to fetch the last insert id. Reason: Unable to find connection.');
		}
		$insert_id = @sqlite_last_insert_rowid($this->resource_id);
		if ($insert_id === FALSE) {
			throw new Throwable_SQL_Exception('Message: Failed to fetch the last insert id. Reason: :reason', array(':sql' => sqlite_error_string(sqlite_last_error($this->resource_id))));
		}
		return $insert_id;
	}

	/**
	 * This function rollbacks a transaction.
	 *
	 * @access public
	 * @throws Throwable_SQL_Exception             indicates that the executed statement failed
	 *
	 * @see http://www.sqlite.org/lang_transaction.html
	 * @see http://en.wikibooks.org/wiki/SQL_Dialects_Reference/Transactions
	 */
	public function rollback() {
		$this->execute('ROLLBACK TRANSACTION;');
	}

	/**
	 * This function commits a transaction.
	 *
	 * @access public
	 * @throws Throwable_SQL_Exception             indicates that the executed statement failed
	 *
	 * @see http://www.sqlite.org/lang_transaction.html
	 * @see http://en.wikibooks.org/wiki/SQL_Dialects_Reference/Transactions
	 */
	public function commit() {
		$this->execute('COMMIT TRANSACTION;');
	}

	/**
	 * This function escapes a string to be used in an SQL statement.
	 *
	 * @access public
	 * @param string $string                    the string to be escaped
	 * @param char $escape                      the escape character
	 * @return string                           the quoted string
	 * @throws Throwable_SQL_Exception             indicates that no connection could
	 *                                          be found
	 *
	 * @see http://www.php.net/manual/en/function.sqlite-escape-string.php
	 */
	public function quote($string, $escape = NULL) {
		if ( ! $this->is_connected()) {
			throw new Throwable_SQL_Exception('Message: Failed to quote/escape string. Reason: Unable to find connection.');
		}

		$string = "'" . sqlite_escape_string($string) . "'";

		if (is_string($escape) OR ! empty($escape)) {
			$string .= " ESCAPE '{$escape}'";
		}

		return $string;
	}

	/**
	 * This function closes an open connection.
	 *
	 * @access public
	 * @return boolean                          whether an open connection was closed
	 */
	public function close() {
		if ($this->is_connected()) {
			if ( ! @sqlite_close($this->resource_id)) {
				return FALSE;
			}
			$this->resource_id = NULL;
		}
		return TRUE;
	}

	/**
	 * This destructor ensures that the connection is closed.
	 *
	 * @access public
	 */
	public function __destruct() {
		if (is_resource($this->resource_id)) {
			@sqlite_close($this->resource_id);
		}
	}

}
?>