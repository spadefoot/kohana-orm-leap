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
 * This class handles a standard MS SQL connection.
 *
 * @package Leap
 * @category MS SQL
 * @version 2012-05-22
 *
 * @see http://www.php.net/manual/en/ref.mssql.php
 *
 * @abstract
 */
abstract class Base_DB_MsSQL_Connection_Sqlsrv extends DB_SQL_Connection_Sqlsrv {

	/**
	 * This function opens a connection using the data source provided.
	 *
	 * @access public
	 * @throws Kohana_Database_Exception        indicates that there is problem with
	 *                                          opening the connection
	 *
	 * @see http://stackoverflow.com/questions/1322421/php-sql-server-how-to-set-charset-for-connection
	 */
	public function open() {
		if ( ! $this->is_connected()) {
			try {
				$connection_string = $this->data_source->host;
				$port = $this->data_source->port;
				if ( ! empty($port)) {
					$connection_string .= ':' . $port;
				}
				$connection_info = array(
					'Database' => $this->data_source->database,
					'UID' => $this->data_source->username,
					'PWD' => $this->data_source->password,
				);

				if ( ! empty($this->data_source->charset)) {
					$connection_info['CharacterSet'] = $this->data_source->charset;
				}

				$this->resource_id = ($this->data_source->is_persistent())
						? sqlsrv_connect($connection_string, $connection_info)
						: sqlsrv_connect($connection_string, $connection_info);

				if (!$this->resource_id) {
					$errors = sqlsrv_errors();
					$error = Arr::get($errors, 0);
					$message = Arr::get($error, 'message');
					throw new Kohana_Database_Exception('Message: Failed to establish connection. Reason: :reason', array(':reason' => $message));
				}
			}
			catch (ErrorException $ex) {
				throw new Kohana_Database_Exception('Message: Failed to establish connection. Reason: :reason', array(':reason' => $ex->getMessage()));
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
		if (sqlsrv_begin_transaction($this->resource_id) === false) {
			$errors = sqlsrv_errors();
			$error = Arr::get($errors, 0);
			$message = Arr::get($error, 'message');
			throw new Kohana_Database_Exception('Message: Failed to begin the transaction. Reason: :reason', array(':reason' => $message));
		}
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
			throw new Kohana_SQL_Exception('Message: Failed to query SQL statement. Reason: Unable to find connection.');
		}
		$result_set = $this->cache($sql, $type);
		if ( ! is_null($result_set)) {
			$this->sql = $sql;
			return $result_set;
		}
		$command_id = sqlsrv_query($this->resource_id, $sql);
		if ($command_id === FALSE) {
			$errors = sqlsrv_errors();
			$error = Arr::get($errors, 0);
			$message = Arr::get($error, 'message');
			throw new Kohana_SQL_Exception('Message: Failed to query SQL statement. Reason: :reason', array(':reason' => $message));
		}
		$records = array();
		$size = 0;
		while ($record = sqlsrv_fetch_array($command_id, SQLSRV_FETCH_ASSOC)) {
			$records[] = DB_Connection::type_cast($type, $record);
			$size++;
		}
		sqlsrv_free_stmt($command_id);
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
		$command_id = sqlsrv_query($this->resource_id, $sql);
		if ($command_id === FALSE) {
			$errors = sqlsrv_errors();
			$error = Arr::get($errors, 0);
			$message = Arr::get($error, 'message');
			throw new Kohana_SQL_Exception('Message: Failed to execute SQL statement. Reason: :reason', array(':reason' => $message));
		}
		sqlsrv_free_stmt($command_id);
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
		if ( ! $this->is_connected()) {
			throw new Kohana_SQL_Exception('Message: Failed to fetch the last insert id. Reason: Unable to find connection.');
		}
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
			throw new Kohana_SQL_Exception('Message: Failed to fetch the last insert id. Reason: :reason', array(':reason' => $ex->getMessage()));
		}
	}

	/**
	 * This function rollbacks a transaction.
	 *
	 * @access public
	 * @throws Kohana_SQL_Exception             indicates that the executed statement failed
	 */
	public function rollback() {
		sqlsrv_rollback($this->resource_id);
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
		sqlsrv_commit($this->resource_id);
	}

	/**
	 * This function closes an open connection.
	 *
	 * @access public
	 * @return boolean                          whether an open connection was closed
	 */
	public function close() {
		if ($this->is_connected()) {
			if ( ! sqlsrv_close($this->resource_id)) {
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
			sqlsrv_close($this->resource_id);
		}
	}

}
