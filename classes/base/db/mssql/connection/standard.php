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
 * This class handles a standard MS SQL connection.
 *
 * @package Leap
 * @category MS SQL
 * @version 2012-02-06
 *
 * @see http://www.php.net/manual/en/ref.mssql.php
 *
 * @abstract
 */
abstract class Base_DB_MsSQL_Connection_Standard extends DB_SQL_Connection_Standard {

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
			try {
				$connection_string = $this->data_source->host;
				$port = $this->data_source->port;
				if ( ! empty($port)) {
					$connection_string .= ':' . $port;
				}
				$username = $this->data_source->username;
				$password = $this->data_source->password;
				$this->link_id = ($this->data_source->is_persistent())
					? mssql_pconnect($connection_string, $username, $password)
					: mssql_connect($connection_string, $username, $password, TRUE);
			}
			catch (ErrorException $ex) {
				$this->error = 'Message: Failed to establish connection. Reason: ' . $ex->getMessage();
				throw new Kohana_Database_Exception($this->error, array(':dsn' => $this->data_source->id));
			}
			$database = @mssql_select_db($this->data_source->database, $this->link_id);
			if ($database === FALSE) {
				$this->error = 'Message: Failed to connect to database. Reason: ' . mssql_get_last_message();
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
	 * @see http://msdn.microsoft.com/en-us/library/ms188929.aspx
	 */
	public function begin_transaction() {
		$this->execute('BEGIN TRAN;');
	}

	/**
	 * This function allows for the ability to process a query that will return data
	 * using the passed string.
	 *
	 * @access public
	 * @param string $sql						the SQL statement
	 * @param string $type 						the return type to be used
	 * @return DB_ResultSet                     the result set
	 * @throws Kohana_SQL_Exception             indicates that the query failed
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
		$resource_id = @mssql_query($sql, $this->link_id);
		if ($resource_id === FALSE) {
			$this->error = 'Message: Failed to query SQL statement. Reason: ' . mssql_get_last_message();
			throw new Kohana_SQL_Exception($this->error, array(':sql' => $sql, ':type' => $type));
		}
		$records = array();
		$size = 0;
		while ($record = mssql_fetch_assoc($resource_id)) {
			$records[] = DB_Connection::type_cast($type, $record);
			$size++;
		}
		@mssql_free_result($resource_id);
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
			$this->error = 'Message: Failed to execute SQL statement. Reason: Unable to find connection.';
			throw new Kohana_SQL_Exception($this->error, array(':sql' => $sql));
		}
		$resource_id = @mssql_query($sql, $this->link_id);
		if ($resource_id === FALSE) {
			$this->error = 'Message: Failed to execute SQL statement. Reason: ' . mssql_get_last_message();
			throw new Kohana_SQL_Exception($this->error, array(':sql' => $sql));
		}
		@mssql_free_result($resource_id);
		$this->sql = $sql;
	}

	/**
	 * This function returns the last insert id.
	 *
	 * @access public
	 * @return integer                          the last insert id
	 * @throws Kohana_SQL_Exception             indicates that the query failed
	 */
	public function get_last_insert_id() {
		try {
			$sql = $this->sql;
			if (preg_match('/^INSERT\s+(TOP.+\s+)?INTO\s+(.*?)\s+/i', $sql, $matches)) {
				$table = Arr::get($matches, 2);
				$query = ( ! empty($table)) ? "SELECT IDENT_CURRENT('{$table}') AS insert_id" : 'SELECT SCOPE_IDENTITY() AS insert_id';
				$result_set = $this->query($query);
				$insert_id = ($result_set->is_loaded()) ? ( (int)  Arr::get($result_set->fetch(0), 'insert_id')) : 0;
				$this->sql = $sql;
				return $insert_id;
			}
			return 0;
		}
		catch (Exception $ex) {
			$this->error = 'Message: Failed to fetch the last insert id. Reason: ' . $ex->getMessage();
			throw new Kohana_SQL_Exception($this->error, array(':sql' => $this->sql));
		}
	}

	/**
	 * This function rollbacks a transaction.
	 *
	 * @access public
	 * @throws Kohana_SQL_Exception             indicates that the executed statement failed
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
	 * @see http://msdn.microsoft.com/en-us/library/ms190295.aspx
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
			if ( ! @mssql_close($this->link_id)) {
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
			@mssql_close($this->link_id);
		}
	}

}
?>