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
 * This class handles a standard MariaDB connection.
 *
 * @package Leap
 * @category MariaDB
 * @version 2011-11-20
 *
 * @see http://www.php.net/manual/en/book.mysql.php
 *
 * @abstract
 */
abstract class Base_DB_MariaDB_Connection_Std extends DB_SQL_Connection_Std {

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
			$this->link_id = @mysql_connect($this->data_source->get_host_server(), $this->data_source->get_username(), $this->data_source->get_password());
			if ($this->link_id === FALSE) {
				$this->error = 'Message: Failed to establish connection. Reason: ' . mysql_error();
				throw new Kohana_Database_Exception($this->error, array());
			}
			$database = @mysql_select_db($this->data_source->get_database(), $this->link_id);
			if ($database === FALSE) {
				$this->error = 'Message: Failed to connect to database. Reason: ' . mysql_error($this->link_id);
				throw new Kohana_Database_Exception($this->error, array());
			}
		}
	}

	/**
	 * This function begins a transaction.
	 *
	 * @access public
	 * @throws Kohana_SQL_Exception             indicates that the executed statement failed
	 *
	 * @see http://dev.mysql.com/doc/refman/5.0/en/commit.html
	 * @see http://php.net/manual/en/function.mysql-query.php
	 */
	public function begin_transaction() {
		$this->execute('START TRANSACTION;');
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
			$this->error = 'Message: Failed to query SQL statement. Reason: Unable to find connection.';
			throw new Kohana_SQL_Exception($this->error, array(':sql' => $sql, ':type' => $type));
		}
		$resource_id = @mysql_query($sql, $this->link_id);
		if ($resource_id === FALSE) {
			$this->error = 'Message: Failed to query SQL statement. Reason: ' . mysql_error($this->link_id);
			throw new Kohana_SQL_Exception($this->error, array(':sql' => $sql, ':type' => $type));
		}
		$records = array();
		$size = 0;
		while ($record = mysql_fetch_assoc($resource_id)) {
			$records[] = DB_Connection::type_cast($type, $record);
			$size++;
		}
		@mysql_free_result($resource_id);
		$result_set = new DB_ResultSet($records, $size);
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
			$this->error = 'Message: Failed to execute SQL statement. Reason: Unable to find connection.';
			throw new Kohana_SQL_Exception($this->error, array(':sql' => $sql));
		}
		$resource_id = @mysql_query($sql, $this->link_id);
		if ($resource_id === FALSE) {
			$this->error = 'Message: Failed to execute SQL statement. Reason: ' . mysql_error($this->link_id);
			throw new Kohana_SQL_Exception($this->error, array(':sql' => $sql));
		}
		$this->sql = $sql;
		@mysql_free_result($resource_id);
	}

	/**
	 * This function returns the last insert id.
	 *
	 * @access public
	 * @return integer                          the last insert id
	 * @throws Kohana_SQL_Exception             indicates that the query failed
	 */
	public function get_last_insert_id() {
		$insert_id = @mysql_insert_id($this->link_id);
		if ($insert_id === FALSE) {
			$this->error = 'Message: Failed to fetch the last insert id. Reason: ' . mysql_error($this->link_id);
			throw new Kohana_SQL_Exception($this->error, array(':sql' => $this->sql));
		}
		return $insert_id;
	}

	/**
	 * This function rollbacks a transaction.
	 *
	 * @access public
	 * @throws Kohana_SQL_Exception             indicates that the executed statement failed
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
	 * @throws Kohana_SQL_Exception             indicates that the executed statement failed
	 *
	 * @see http://dev.mysql.com/doc/refman/5.0/en/commit.html
	 * @see http://php.net/manual/en/function.mysql-query.php
	 */
	public function commit() {
		$this->execute('COMMIT;');
	}

	/**
	 * This function allows for the ability to close the connection that was opened.
	 *
	 * @access public
	 * @return boolean                          whether an open connection was closed
	 */
	public function close() {
		if ($this->is_connected()) {
			if (@mysql_close($this->link_id)) {
				$this->link_id = NULL;
				return TRUE;
			}
			return FALSE;
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
			@mysql_close($this->link_id);
		}
	}

}
?>