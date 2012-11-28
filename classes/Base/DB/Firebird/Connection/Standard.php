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
 * This class handles a standard Firebird connection.
 *
 * Firebird installation instruction:
 *
 *     	To install interbase (aka ibase) copy C:\Program Files\FishBowl\Client\bin\fbclient.dll
 *		into "C:\WINDOWS\system32\" and rename file to gds32.dll.
 *
 *     	Edit C:\WINDOWS\system32\drivers\etc\services by appending to the end the following:
 *     	gds_db           3050/tcp    fb                     #Firebird
 *
 *     	Restart either Apache or the computer
 *
 * @package Leap
 * @category Firebird
 * @version 2012-11-28
 *
 * @see http://us3.php.net/manual/en/book.ibase.php
 * @see http://us2.php.net/manual/en/ibase.installation.php
 * @see http://www.firebirdfaq.org/faq227/
 * @see http://www.firebirdfaq.org/cat3/
 *
 * @abstract
 */
abstract class Base_DB_Firebird_Connection_Standard extends DB_SQL_Connection_Standard {

	/**
	 * This function opens a connection using the data source provided.
	 *
	 * @access public
	 * @throws Throwable_Database_Exception        indicates that there is problem with
	 *                                          opening the connection
	 *
	 * @see http://www.destructor.de/firebird/charsets.htm
	 */
	public function open() {
		if ( ! $this->is_connected()) {
			$connection_string = $this->data_source->host;
			if ( ! preg_match('/^localhost$/i', $connection_string)) {
				$port = $this->data_source->port;
				if ( ! empty($port)) {
					$connection_string .= '/' . $port;
				}
			}
			$connection_string .= ':' . $this->data_source->database;
			$username = $this->data_source->username;
			$password = $this->data_source->password;
			$charset = $this->data_source->charset;
			if ( ! empty($charset)) {
				$charset = strtoupper($charset);
			}
			$role = ( ! empty($this->data_source->role))
				? $this->data_source->role
				: NULL;
			$this->resource_id = ($this->data_source->is_persistent())
				? @ibase_pconnect($connection_string, $username, $password, $charset, 0, 3, $role)
				: @ibase_connect($connection_string, $username, $password, $charset, 0, 3, $role);
			if ($this->resource_id === FALSE) {
				throw new Throwable_Database_Exception('Message: Failed to establish connection. Reason: :reason', array(':reason' => ibase_errmsg()));
			}
		}
	}

	/**
	 * This function begins a transaction.
	 *
	 * @access public
	 * @throws Throwable_SQL_Exception             indicates that the executed statement failed
	 */
	public function begin_transaction() {
		if ( ! $this->is_connected()) {
			throw new Throwable_SQL_Exception('Message: Failed to begin SQL transaction. Reason: Unable to find connection.');
		}
		$command_id = @ibase_trans($this->resource_id, IBASE_READ | IBASE_WRITE);
		if ($command_id === FALSE) {
			throw new Throwable_SQL_Exception('Message: Failed to begin SQL transaction. Reason: :reason', array(':sql' => ibase_errmsg()));
		}
	}

	/**
	 * This function allows for the ability to process a query that will return data
	 * using the passed string.
	 *
	 * @access public
	 * @param string $sql						the SQL statement
	 * @param string $type               		the return type to be used
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
		$command_id = @ibase_query($this->resource_id, $sql);
		if ($command_id === FALSE) {
			throw new Throwable_SQL_Exception('Message: Failed to query SQL statement. Reason: :reason', array(':reason' => ibase_errmsg()));
		}
		$records = array();
		$size = 0;
		while ($record = ibase_fetch_assoc($command_id)) {
			$records[] = DB_Connection::type_cast($type, $record);
			$size++;
		}
		@ibase_free_result($command_id);
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
		$stmt = ibase_prepare($this->resource_id, $sql);
		$command_id = @ibase_execute($stmt);
		if ($command_id === FALSE) {
			throw new Throwable_SQL_Exception('Message: Failed to execute SQL statement. Reason: :reason', array(':reason' => ibase_errmsg()));
		}
		$this->sql = $sql;
	}

	/**
	 * This function returns the last insert id.
	 *
	 * @access public
	 * @return integer                          the last insert id
	 * @throws Throwable_SQL_Exception             indicates that the query failed
	 *
	 * @see http://www.firebirdfaq.org/faq243/
	 */
	public function get_last_insert_id() {
		if ( ! $this->is_connected()) {
			throw new Throwable_SQL_Exception('Message: Failed to fetch the last insert id. Reason: Unable to find connection.');
		}
		try {
			$sql = $this->sql;
			if (preg_match('/^INSERT\s+INTO\s+(.*?)\s+/i', $sql, $matches)) {
				$table = Arr::get($matches, 1);
				$result = $this->query("SELECT ID FROM {$table} ORDER BY ID DESC ROWS 1;");
				$insert_id = ($result->is_loaded()) ? ( (int)  Arr::get($result->fetch(0), 'ID')) : 0;
				$this->sql = $sql;
				return $insert_id;
			}
			return 0;
		}
		catch (Exception $ex) {
			throw new Throwable_SQL_Exception(preg_replace('/Failed to query SQL statement./', 'Failed to fetch the last insert id.', $ex->getMessage()));
		}
	}

	/**
	 * This function rollbacks a transaction.
	 *
	 * @access public
	 * @throws Throwable_SQL_Exception             indicates that the executed statement failed
	 */
	public function rollback() {
		if ( ! $this->is_connected()) {
			throw new Throwable_SQL_Exception('Message: Failed to rollback SQL transaction. Reason: Unable to find connection.');
		}
		$command_id = @ibase_rollback($this->resource_id);
		if ($command_id === FALSE) {
			throw new Throwable_SQL_Exception('Message: Failed to rollback SQL transaction. Reason: :reason', array(':reason' => ibase_errmsg()));
		}
	}

	/**
	 * This function commits a transaction.
	 *
	 * @access public
	 * @throws Throwable_SQL_Exception             indicates that the executed statement failed
	 */
	public function commit() {
		if ( ! $this->is_connected()) {
			throw new Throwable_SQL_Exception('Message: Failed to commit SQL transaction. Reason: Unable to find connection.');
		}
		$command_id = @ibase_commit($this->resource_id);
		if ($command_id === FALSE) {
			throw new Throwable_SQL_Exception('Message: Failed to commit SQL transaction. Reason: :reason', array(':reason' => ibase_errmsg()));
		}
	}

	/**
	 * This function closes an open connection.
	 *
	 * @access public
	 * @return boolean                          whether an open connection was closed
	 */
	public function close() {
		if ($this->is_connected()) {
			if ( ! @ibase_close($this->resource_id)) {
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
			@ibase_close($this->resource_id);
		}
	}

}
?>