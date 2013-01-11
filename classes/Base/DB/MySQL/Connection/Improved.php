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
 * This class handles an improved MySQL connection.
 *
 * @package Leap
 * @category MySQL
 * @version 2013-01-11
 *
 * @see http://www.php.net/manual/en/book.mysqli.php
 *
 * @abstract
 */
abstract class Base_DB_MySQL_Connection_Improved extends DB_SQL_Connection_Standard {

	/**
	 * This destructor ensures that the connection is closed.
	 *
	 * @access public
	 * @override
	 */
	public function __destruct() {
		if ($this->resource !== NULL) {
			@mysqli_close($this->resource);
		}
	}

	/**
	 * This function begins a transaction.
	 *
	 * @access public
	 * @override
	 * @throws Throwable_SQL_Exception              indicates that the executed
	 *                                              statement failed
	 *
	 * @see http://www.php.net/manual/en/mysqli.autocommit.php
	 */
	public function begin_transaction() {
		if ( ! $this->is_connected()) {
			throw new Throwable_SQL_Exception('Message: Failed to begin SQL transaction. Reason: Unable to find connection.');
		}
		$command = @mysqli_autocommit($this->resource, FALSE);
		if ($command === FALSE) {
			throw new Throwable_SQL_Exception('Message: Failed to begin SQL transaction. Reason: :reason', array(':reason' => @mysqli_error($this->resource)));
		}
	}

	/**
	 * This function closes an open connection.
	 *
	 * @access public
	 * @override
	 * @return boolean                              whether an open connection was closed
	 */
	public function close() {
		if ($this->is_connected()) {
			if ( ! @mysqli_close($this->resource)) {
				return FALSE;
			}
			$this->resource = NULL;
		}
		return TRUE;
	}

	/**
	 * This function commits a transaction.
	 *
	 * @access public
	 * @override
	 * @throws Throwable_SQL_Exception              indicates that the executed
	 *                                              statement failed
	 *
	 * @see http://www.php.net/manual/en/mysqli.commit.php
	 */
	public function commit() {
		if ( ! $this->is_connected()) {
			throw new Throwable_SQL_Exception('Message: Failed to commit SQL transaction. Reason: Unable to find connection.');
		}
		$command = @mysqli_commit($this->resource);
		if ($command === FALSE) {
			throw new Throwable_SQL_Exception('Message: Failed to commit SQL transaction. Reason: :reason', array(':reason' => @mysqli_error($this->resource)));
		}
		@mysqli_autocommit($this->resource, TRUE);
	}

	/**
	 * This function processes an SQL statement that will NOT return data.
	 *
	 * @access public
	 * @override
	 * @param string $sql						    the SQL statement
	 * @throws Throwable_SQL_Exception              indicates that the executed
	 *                                              statement failed
	 */
	public function execute($sql) {
		if ( ! $this->is_connected()) {
			throw new Throwable_SQL_Exception('Message: Failed to execute SQL statement. Reason: Unable to find connection.');
		}
		$command = @mysqli_query($this->resource, $sql);
		if ($command === FALSE) {
			throw new Throwable_SQL_Exception('Message: Failed to execute SQL statement. Reason: :reason', array(':reason' => @mysqli_error($this->resource)));
		}
		$this->sql = $sql;
		@mysqli_free_result($command);
	}

	/**
	 * This function returns the last insert id.
	 *
	 * @access public
	 * @override
	 * @param string $table                         the table to be queried
	 * @param string $id                            the name of column's id
	 * @return integer                              the last insert id
	 * @throws Throwable_SQL_Exception              indicates that the query failed
	 */
	public function get_last_insert_id($table = NULL, $id = 'id') {
		if ( ! $this->is_connected()) {
			throw new Throwable_SQL_Exception('Message: Failed to fetch the last insert id. Reason: Unable to find connection.');
		}
		if (is_string($table)) {
			$sql = $this->sql;
			$precompiler = DB_SQL::precompiler($this->data_source);
			$table = $precompiler->prepare_identifier($table);
			$id = $precompiler->prepare_identifier($id);
			$insert_id = (int) $this->query("SELECT MAX({$id}) AS `id` FROM {$table};")->get('id', 0);
			$this->sql = $sql;
			return $insert_id;
		}
		else {
			$insert_id = @mysqli_insert_id($this->resource);
			if ($insert_id === FALSE) {
				throw new Throwable_SQL_Exception('Message: Failed to fetch the last insert id. Reason: :reason', array(':reason' => @mysqli_error($this->resource)));
			}
			return $insert_id;
		}
	}

	/**
	 * This function is for determining whether a connection is established.
	 *
	 * @access public
	 * @override
	 * @return boolean                              whether a connection is established
	 */
	public function is_connected() {
		return ! empty($this->resource);
	}

	/**
	 * This function opens a connection using the data source provided.
	 *
	 * @access public
	 * @override
	 * @throws Throwable_Database_Exception         indicates that there is problem with
	 *                                              opening the connection
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
			$this->resource = @mysqli_connect($host, $username, $password, $database);
			if ($this->resource === FALSE) {
				throw new Throwable_Database_Exception('Message: Failed to establish connection. Reason: :reason', array(':reason' => @mysqli_connect_error()));
			}
			if ( ! empty($this->data_source->charset) AND ! @mysqli_set_charset($this->resource, strtolower($this->data_source->charset))) {
				throw new Throwable_Database_Exception('Message: Failed to set character set. Reason: :reason', array(':reason' => @mysqli_error($this->resource)));
			}
		}
	}

	/**
	 * This function escapes a string to be used in an SQL statement.
	 *
	 * @access public
	 * @override
	 * @param string $string                        the string to be escaped
	 * @param char $escape                          the escape character
	 * @return string                               the quoted string
	 * @throws Throwable_SQL_Exception              indicates that no connection could
	 *                                              be found
	 */
	public function quote($string, $escape = NULL) {
		if ( ! $this->is_connected()) {
			throw new Throwable_SQL_Exception('Message: Failed to quote/escape string. Reason: Unable to find connection.');
		}

		$string = "'" . mysqli_real_escape_string($this->resource, $string) . "'";

		if (is_string($escape) OR ! empty($escape)) {
			$string .= " ESCAPE '{$escape}'";
		}

		return $string;
	}

	/**
	 * This function rollbacks a transaction.
	 *
	 * @access public
	 * @override
	 * @throws Throwable_SQL_Exception              indicates that the executed
	 *                                              statement failed
	 *
	 * @see http://www.php.net/manual/en/mysqli.rollback.php
	 */
	public function rollback() {
		if ( ! $this->is_connected()) {
			throw new Throwable_SQL_Exception('Message: Failed to rollback SQL transaction. Reason: Unable to find connection.');
		}
		$command = @mysqli_rollback($this->resource);
		if ($command === FALSE) {
			throw new Throwable_SQL_Exception('Message: Failed to rollback SQL transaction. Reason: :reason', array(':reason' => @mysqli_error($this->resource)));
		}
		@mysqli_autocommit($this->resource, TRUE);
	}

}
