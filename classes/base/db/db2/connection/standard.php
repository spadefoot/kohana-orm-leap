<?php defined('SYSPATH') OR die('No direct access allowed.');

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
 * This class handles a standard DB2 connection.
 *
 * @package Leap
 * @category DB2
 * @version 2012-02-09
 *
 * @see http://php.net/manual/en/ref.ibm-db2.php
 *
 * @abstract
 */
abstract class Base_DB_DB2_Connection_Standard extends DB_SQL_Connection_Standard {

	/**
	 * This function allows for the ability to open a connection using
	 * the configurations provided.
	 *
	 * @access public
	 * @throws Kohana_Database_Exception        indicates that there is problem with
	 *                                          the database connection
	 *
	 * @see http://www.php.net/manual/en/function.db2-connect.php
	 * @see http://www.php.net/manual/en/function.db2-conn-error.php
	 */
	public function open() {
		if ( ! $this->is_connected()) {
			$connection_string  = 'DRIVER={IBM DB2 ODBC DRIVER};';
			$connection_string .= 'DATABASE=' . $this->data_source->database . ';';
			$connection_string .= 'HOSTNAME=' . $this->data_source->host . ';';
			$connection_string .= 'PORT=' . $this->data_source->port . ';';
			$connection_string .= 'PROTOCOL=TCPIP;';
			$connection_string .= 'UID=' . $this->data_source->username . ';';
			$connection_string .= 'PWD=' . $this->data_source->password . ';';
			$this->link_id = ($this->data_source->is_persistent())
				? @db2_pconnect($connection_string, '', '')
				: @db2_connect($connection_string, '', '');
			if ($this->link_id === FALSE) {
				$this->error = 'Message: Failed to establish connection. Reason: ' . db2_conn_error();
				throw new Kohana_Database_Exception($this->error, array(':dsn' => $this->data_source->id));
			}
		}
	}

	/**
	 * This function begins a transaction.
	 *
	 * @access public
	 * @throws Kohana_SQL_Exception             indicates that the executed statement failed
	 *
	 * @see http://www.php.net/manual/en/function.db2-autocommit.php
	 */
	public function begin_transaction() {
		if ( ! $this->is_connected()) {
			$this->error = 'Message: Failed to begin SQL transaction. Reason: Unable to find connection.';
			throw new Kohana_SQL_Exception($this->error, array(':sql' => 'BEGIN TRANSACTION;'));
		}
		$resource_id = @db2_autocommit($this->link_id, DB2_AUTOCOMMIT_OFF);
		if ($resource_id === FALSE) {
			$this->error = 'Message: Failed to begin SQL transaction. Reason: ' . db2_conn_error($this->link_id);
			throw new Kohana_SQL_Exception($this->error, array(':sql' => 'BEGIN TRANSACTION;'));
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
	 *
	 * @see http://www.php.net/manual/en/function.db2-prepare.php
	 * @see http://www.php.net/manual/en/function.db2-execute.php
	 * @see http://www.php.net/manual/en/function.db2-stmt-error.php
	 * @see http://www.php.net/manual/en/function.db2-fetch-assoc.php
	 * @see http://www.php.net/manual/en/function.db2-free-result.php
	 */
	public function query($sql, $type = 'array') {
		if ( ! $this->is_connected()) {
			$this->error = 'Message: Failed to query SQL statement. Reason: Unable to find connection.';
			throw new Kohana_SQL_Exception($this->error, array(':sql' => $sql, ':type' => $type));
		}
		$result_set = $this->cache($sql, $type);
		if ( ! is_null($result_set)) {
			$this->sql = $sql;
			return $result_set;
		}
		$resource_id = @db2_prepare($this->link_id, $sql);
		if (($resource_id === FALSE) || ! db2_execute($resource_id)) {
			$this->error = 'Message: Failed to query SQL statement. Reason: ' . db2_stmt_error($resource_id);
			throw new Kohana_SQL_Exception($this->error, array(':sql' => $sql, ':type' => $type));
		}
		$records = array();
		$size = 0;
		while ($record = db2_fetch_assoc($resource_id)) {
			$records[] = DB_Connection::type_cast($type, $record);
			$size++;
		}
		@db2_free_result($resource_id);
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
	 * @throws Kohana_SQL_Exception              indicates that the executed statement failed
	 *
	 * @see http://www.php.net/manual/en/function.db2-exec.php
	 * @see http://www.php.net/manual/en/function.db2-free-result.php
	 */
	public function execute($sql) {
		if ( ! $this->is_connected()) {
			$this->error = 'Message: Failed to execute SQL statement. Reason: Unable to find connection.';
			throw new Kohana_SQL_Exception($this->error, array(':sql' => $sql));
		}
		$resource_id = @db2_exec($this->link_id, $sql);
		if ($resource_id === FALSE) {
			$this->error = 'Message: Failed to execute SQL statement. Reason: ' . db2_stmt_error($resource_id);
			throw new Kohana_SQL_Exception($this->error, array(':sql' => $sql));
		}
		$this->sql = $sql;
		@db2_free_result($resource_id);
	}

	/**
	 * This function returns the last insert id.
	 *
	 * @access public
	 * @return integer                          the last insert id
	 * @throws Kohana_SQL_Exception             indicates that the query failed
	 *
	 * @see http://www.php.net/manual/en/function.db2-last-insert-id.php
	 */
	public function get_last_insert_id() {
		$insert_id = @db2_last_insert_id($this->link_id);
		if ($insert_id === FALSE) {
			$this->error = 'Message: Failed to fetch the last insert id. Reason: ' . db2_conn_error($this->link_id);
			throw new Kohana_SQL_Exception($this->error, array(':sql' => $this->sql));
		}
		settype($insert_id, 'integer');
		return $insert_id;
	}

	/**
	 * This function rollbacks a transaction.
	 *
	 * @access public
	 * @throws Kohana_SQL_Exception             indicates that the executed statement failed
	 *
	 * @see http://www.php.net/manual/en/function.db2-rollback.php
	 */
	public function rollback() {
		if ( ! $this->is_connected()) {
			$this->error = 'Message: Failed to rollback SQL transaction. Reason: Unable to find connection.';
			throw new Kohana_SQL_Exception($this->error, array(':sql' => 'ROLLBACK;'));
		}
		$resource_id = @db2_rollback($this->link_id);
		if ($resource_id === FALSE) {
			$this->error = 'Message: Failed to rollback SQL transaction. Reason: ' . db2_conn_error($this->link_id);
			throw new Kohana_SQL_Exception($this->error, array(':sql' => 'ROLLBACK;'));
		}
		@db2_autocommit($this->link_id, DB2_AUTOCOMMIT_ON);
	}

	/**
	 * This function commits a transaction.
	 *
	 * @access public
	 * @throws Kohana_SQL_Exception             indicates that the executed statement failed
	 *
	 * @see http://www.php.net/manual/en/function.db2-commit.php
	 */
	public function commit() {
		if ( ! $this->is_connected()) {
			$this->error = 'Message: Failed to commit SQL transaction. Reason: Unable to find connection.';
			throw new Kohana_SQL_Exception($this->error, array(':sql' => 'COMMIT;'));
		}
		$resource_id = @db2_commit($this->link_id);
		if ($resource_id === FALSE) {
			$this->error = 'Message: Failed to commit SQL transaction. Reason: ' . db2_conn_error($this->link_id);
			throw new Kohana_SQL_Exception($this->error, array(':sql' => 'COMMIT;'));
		}
		@db2_autocommit($this->link_id, DB2_AUTOCOMMIT_ON);
	}

	/**
	 * This function escapes a string to be used in an SQL statement.
	 *
	 * @access public
	 * @param string $string                    the string to be escaped
	 * @param char $escape                      the escape character
	 * @return string                           the quoted string
	 *
	 * @see http://www.php.net/manual/en/function.db2-escape-string.php
	 */
	public function quote($string, $escape = NULL) {
		$string = "'" . db2_escape_string($string) . "'";

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
	 *
	 * @see http://www.php.net/manual/en/function.db2-close.php
	 */
	public function close() {
		if ($this->is_connected()) {
			if ( ! @db2_close($this->link_id)) {
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
	 *
	 * @see http://www.php.net/manual/en/function.db2-close.php
	 */
	public function __destruct() {
		if (is_resource($this->link_id)) {
			@db2_close($this->link_id);
		}
	}

}
?>