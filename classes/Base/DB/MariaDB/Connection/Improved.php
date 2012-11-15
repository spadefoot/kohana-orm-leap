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
 * This class handles an improved MariaDB connection.
 *
 * @package Leap
 * @category MariaDB
 * @version 2012-11-14
 *
 * @see http://www.php.net/manual/en/book.mysqli.php
 * @see http://programmers.stackexchange.com/questions/120178/whats-the-difference-between-mariadb-and-mysql
 *
 * @abstract
 */
abstract class Base_DB_MariaDB_Connection_Improved extends DB_SQL_Connection_Standard {

	/**
	 * This function opens a connection using the data source provided.
	 *
	 * @access public
	 * @throws Throwable_Database_Exception        indicates that there is problem with
	 *                                          opening the connection
	 *
	 * @see http://php.net/manual/en/mysqli.persistconns.php
	 * @see http://kb.askmonty.org/en/character-sets-and-collations
	 */
	public function open() {
		if ( ! $this->is_connected()) {
			$host = $this->data_source->host;
			if ($this->data_source->is_persistent()) {
				$host = 'p:' . $host;
			}
			$username = $this->data_source->username;
			$password = $this->data_source->password;
			$database = $this->data_source->database;
			$this->resource_id = @mysqli_connect($host, $username, $password, $database);
			if ($this->resource_id === FALSE) {
				throw new Throwable_Database_Exception('Message: Failed to establish connection. Reason: :reason', array(':reason' => mysqli_connect_error()));
			}
			if ( ! empty($this->data_source->charset) AND ! @mysqli_set_charset($this->resource_id, strtolower($this->data_source->charset))) {
				throw new Throwable_Database_Exception('Message: Failed to set character set. Reason: :reason', array(':reason' => mysqli_error($this->resource_id)));
			}
		}
	}

	/**
	 * This function begins a transaction.
	 *
	 * @access public
	 * @throws Throwable_SQL_Exception             indicates that the executed statement failed
	 *
	 * @see http://www.php.net/manual/en/mysqli.autocommit.php
	 */
	public function begin_transaction() {
		if ( ! $this->is_connected()) {
			throw new Throwable_SQL_Exception('Message: Failed to begin SQL transaction. Reason: Unable to find connection.');
		}
		$command_id = @mysqli_autocommit($this->resource_id, FALSE);
		if ($command_id === FALSE) {
			throw new Throwable_SQL_Exception('Message: Failed to begin SQL transaction. Reason: :reason', array(':reason' => mysqli_error($this->resource_id)));
		}
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
		$command_id = @mysqli_query($this->resource_id, $sql);
		if ($command_id === FALSE) {
			throw new Throwable_SQL_Exception('Message: Failed to query SQL statement. Reason: :reason', array(':reason' => mysqli_error($this->resource_id)));
		}
		$records = array();
		$size = 0;
		while ($record = mysqli_fetch_assoc($command_id)) {
			$records[] = DB_Connection::type_cast($type, $record);
			$size++;
		}
		@mysqli_free_result($command_id);
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
		$command_id = @mysqli_query($this->resource_id, $sql);
		if ($command_id === FALSE) {
			throw new Throwable_SQL_Exception('Message: Failed to execute SQL statement. Reason: :reason', array(':reason' => mysqli_error($this->resource_id)));
		}
		$this->sql = $sql;
		@mysqli_free_result($command_id);
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
		$insert_id = @mysqli_insert_id($this->resource_id);
		if ($insert_id === FALSE) {
			throw new Throwable_SQL_Exception('Message: Failed to fetch the last insert id. Reason: :reason', array(':reason' => mysqli_error($this->resource_id)));
		}
		return $insert_id;
	}

	/**
	 * This function rollbacks a transaction.
	 *
	 * @access public
	 * @throws Throwable_SQL_Exception             indicates that the executed statement failed
	 *
	 * @see http://www.php.net/manual/en/mysqli.rollback.php
	 */
	public function rollback() {
		if ( ! $this->is_connected()) {
			throw new Throwable_SQL_Exception('Message: Failed to rollback SQL transaction. Reason: Unable to find connection.');
		}
		$command_id = @mysqli_rollback($this->resource_id);
		if ($command_id === FALSE) {
			throw new Throwable_SQL_Exception('Message: Failed to rollback SQL transaction. Reason: :reason', array(':reason' => mysqli_error($this->resource_id)));
		}
		@mysqli_autocommit($this->resource_id, TRUE);
	}

	/**
	 * This function commits a transaction.
	 *
	 * @access public
	 * @throws Throwable_SQL_Exception             indicates that the executed statement failed
	 *
	 * @see http://www.php.net/manual/en/mysqli.commit.php
	 */
	public function commit() {
		if ( ! $this->is_connected()) {
			throw new Throwable_SQL_Exception('Message: Failed to commit SQL transaction. Reason: Unable to find connection.');
		}
		$command_id = @mysqli_commit($this->resource_id);
		if ($command_id === FALSE) {
			throw new Throwable_SQL_Exception('Message: Failed to commit SQL transaction. Reason: :reason', array(':reason' => mysqli_error($this->resource_id)));
		}
		@mysqli_autocommit($this->resource_id, TRUE);
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
	 */
	public function quote($string, $escape = NULL) {
		if ( ! $this->is_connected()) {
			throw new Throwable_SQL_Exception('Message: Failed to quote/escape string. Reason: Unable to find connection.');
		}

		$string = "'" . mysqli_real_escape_string($this->resource_id, $string) . "'";

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
			if ( ! @mysqli_close($this->resource_id)) {
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
			@mysqli_close($this->resource_id);
		}
	}

}
?>