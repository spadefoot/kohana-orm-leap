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
 * This class handles an improved MySQL connection.
 *
 * @package Leap
 * @category MySQL
 * @version 2012-04-08
 *
 * @see http://www.php.net/manual/en/book.mysqli.php
 *
 * @abstract
 */
abstract class Base_DB_MySQL_Connection_Improved extends DB_SQL_Connection_Standard {

	/**
	 * This function allows for the ability to open a connection using
	 * the configurations provided.
	 *
	 * @access public
	 * @throws Kohana_Database_Exception        indicates that there is problem with
	 *                                          the database connection
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
			$this->link_id = @mysqli_connect($host, $username, $password, $database);
			if ($this->link_id === FALSE) {
				throw new Kohana_Database_Exception('Message: Failed to establish connection. Reason: :reason', array(':reason' => mysqli_connect_error()));
			}
			if ( ! empty($this->data_source->charset) && ! @mysqli_set_charset($this->link_id, strtolower($this->data_source->charset))) {
				throw new Kohana_Database_Exception('Message: Failed to set character set. Reason: :reason', array(':reason' => mysqli_error($this->link_id)));
			}
		}
	}

	/**
	 * This function begins a transaction.
	 *
	 * @access public
	 * @throws Kohana_SQL_Exception             indicates that the executed statement failed
	 *
	 * @see http://www.php.net/manual/en/mysqli.autocommit.php
	 */
	public function begin_transaction() {
		if ( ! $this->is_connected()) {
			throw new Kohana_SQL_Exception('Message: Failed to begin SQL transaction. Reason: Unable to find connection.');
		}
		$resource_id = @mysqli_autocommit($this->link_id, FALSE);
		if ($resource_id === FALSE) {
			throw new Kohana_SQL_Exception('Message: Failed to begin SQL transaction. Reason: :reason', array(':reason' => mysqli_error($this->link_id)));
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
	 * @throws Kohana_SQL_Exception             indicates that the query failed
	 */
	public function query($sql, $type = 'array') {
		if ( ! $this->is_connected()) {
			throw new Kohana_SQL_Exception('Message: Failed to query SQL statement. Reason: Unable to find connection.');
		}
		$result_set = $this->cache($sql, $type);
		if ( ! is_null($result_set)) {
			$this->sql = $sql;
			return $result_set;
		}
		$resource_id = @mysqli_query($this->link_id, $sql);
		if ($resource_id === FALSE) {
			throw new Kohana_SQL_Exception('Message: Failed to query SQL statement. Reason: :reason', array(':reason' => mysqli_error($this->link_id)));
		}
		$records = array();
		$size = 0;
		while ($record = mysqli_fetch_assoc($resource_id)) {
			$records[] = DB_Connection::type_cast($type, $record);
			$size++;
		}
		@mysqli_free_result($resource_id);
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
	 * @throws Kohana_SQL_Exception             indicates that the executed statement failed
	 */
	public function execute($sql) {
		if ( ! $this->is_connected()) {
			throw new Kohana_SQL_Exception('Message: Failed to execute SQL statement. Reason: Unable to find connection.');
		}
		$resource_id = @mysqli_query($this->link_id, $sql);
		if ($resource_id === FALSE) {
			throw new Kohana_SQL_Exception('Message: Failed to execute SQL statement. Reason: :reason', array(':reason' => mysqli_error($this->link_id)));
		}
		$this->sql = $sql;
		@mysqli_free_result($resource_id);
	}

	/**
	 * This function returns the last insert id.
	 *
	 * @access public
	 * @return integer                          the last insert id
	 * @throws Kohana_SQL_Exception             indicates that the query failed
	 */
	public function get_last_insert_id() {
		if ( ! $this->is_connected()) {
			throw new Kohana_SQL_Exception('Message: Failed to fetch the last insert id. Reason: Unable to find connection.');
		}
		$insert_id = @mysqli_insert_id($this->link_id);
		if ($insert_id === FALSE) {
			throw new Kohana_SQL_Exception('Message: Failed to fetch the last insert id. Reason: :reason', array(':reason' => mysqli_error($this->link_id)));
		}
		return $insert_id;
	}

	/**
	 * This function rollbacks a transaction.
	 *
	 * @access public
	 * @throws Kohana_SQL_Exception             indicates that the executed statement failed
	 *
	 * @see http://www.php.net/manual/en/mysqli.rollback.php
	 */
	public function rollback() {
		if ( ! $this->is_connected()) {
			throw new Kohana_SQL_Exception('Message: Failed to rollback SQL transaction. Reason: Unable to find connection.');
		}
		$resource_id = @mysqli_rollback($this->link_id);
		if ($resource_id === FALSE) {
			throw new Kohana_SQL_Exception('Message: Failed to rollback SQL transaction. Reason: :reason', array(':reason' => mysqli_error($this->link_id)));
		}
		@mysqli_autocommit($this->link_id, TRUE);
	}

	/**
	 * This function commits a transaction.
	 *
	 * @access public
	 * @throws Kohana_SQL_Exception             indicates that the executed statement failed
	 *
	 * @see http://www.php.net/manual/en/mysqli.commit.php
	 */
	public function commit() {
		if ( ! $this->is_connected()) {
			throw new Kohana_SQL_Exception('Message: Failed to commit SQL transaction. Reason: Unable to find connection.');
		}
		$resource_id = @mysqli_commit($this->link_id);
		if ($resource_id === FALSE) {
			throw new Kohana_SQL_Exception('Message: Failed to commit SQL transaction. Reason: :reason', array(':reason' => mysqli_error($this->link_id)));
		}
		@mysqli_autocommit($this->link_id, TRUE);
	}

	/**
	 * This function escapes a string to be used in an SQL statement.
	 *
	 * @access public
	 * @param string $string                    the string to be escaped
	 * @param char $escape                      the escape character
	 * @return string                           the quoted string
	 * @throws Kohana_SQL_Exception             indicates that no connection could
	 *                                          be found
	 */
	public function quote($string, $escape = NULL) {
		if ( ! $this->is_connected()) {
			throw new Kohana_SQL_Exception('Message: Failed to quote/escape string. Reason: Unable to find connection.');
		}

		$string = "'" . mysqli_real_escape_string($this->link_id, $string) . "'";

		if (is_string($escape) || ! empty($escape)) {
			$string .= " ESCAPE '{$escape[0]}'";
		}

		return $string;
	}

	/**
	 * This function allows for the ability to close the connection that was opened.
	 *
	 * @access public
	 * @return boolean                          whether an open connection was closed
	 */
	public function close() {
		if ($this->is_connected()) {
			if ( ! @mysqli_close($this->link_id)) {
				return FALSE;
			}
			$this->link_id = NULL;
		}
		return TRUE;
	}

	/**
	 * This destructor will ensure that the connection is closed.
	 *
	 * @access public
	 */
	public function __destruct() {
		if (is_resource($this->link_id)) {
			@mysqli_close($this->link_id);
		}
	}

}
?>