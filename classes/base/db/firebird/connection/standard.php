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
 * @version 2012-02-06
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
	 * This function allows for the ability to open a connection using
	 * the configurations provided.
	 *
	 * @access public
	 * @throws Kohana_Database_Exception        indicates that there is problem with
	 *                                          the database connection
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
			$this->link_id = ($this->data_source->is_persistent())
				? @ibase_pconnect($connection_string, $username, $password)
				: @ibase_connect($connection_string, $username, $password);
			if ($this->link_id === FALSE) {
				$this->error = 'Message: Failed to establish connection. Reason: ' . ibase_errmsg();
				throw new Kohana_Database_Exception($this->error, array(':dsn' => $this->data_source->id));
			}
		}
	}

	/**
	 * This function begins a transaction.
	 *
	 * @access public
	 * @throws Kohana_SQL_Exception             indicates that the executed statement failed
	 */
	public function begin_transaction() {
		if ( ! $this->is_connected()) {
			$this->error = 'Message: Failed to begin SQL transaction. Reason: Unable to find connection.';
			throw new Kohana_SQL_Exception($this->error, array(':sql' => 'BEGIN TRANSACTION;'));
		}
		$resource_id = @ibase_trans($this->link_id, IBASE_READ | IBASE_WRITE);
		if ($resource_id === FALSE) {
			$this->error = 'Message: Failed to begin SQL transaction. Reason: ' . ibase_errmsg();
			throw new Kohana_SQL_Exception($this->error, array(':sql' => 'BEGIN TRANSACTION;'));
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
		$resource_id = @ibase_query($this->link_id, $sql);
		if ($resource_id === FALSE) {
			$this->error = 'Message: Failed to query SQL statement. Reason: ' . ibase_errmsg();
			throw new Kohana_SQL_Exception($this->error, array(':sql' => $sql, ':type' => $type));
		}
		$records = array();
		$size = 0;
		while ($record = ibase_fetch_assoc($resource_id)) {
			$records[] = DB_Connection::type_cast($type, $record);
			$size++;
		}
		@ibase_free_result($resource_id);
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
		$stmt = ibase_prepare($this->link_id, $sql);
		$resource_id = @ibase_execute($stmt);
		if ($resource_id === FALSE) {
			$this->error = 'Message: Failed to execute SQL statement. Reason: ' . ibase_errmsg();
			throw new Kohana_SQL_Exception($this->error, array(':sql' => $sql));
		}
		$this->sql = $sql;
	}

	/**
	 * This function returns the last insert id.
	 *
	 * @access public
	 * @return integer                          the last insert id
	 * @throws Kohana_SQL_Exception             indicates that the query failed
	 *
	 * @see http://www.firebirdfaq.org/faq243/
	 */
	public function get_last_insert_id() {
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
			$this->error = preg_replace('/Failed to query SQL statement./', 'Failed to fetch the last insert id.', $ex->getMessage());
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
		if ( ! $this->is_connected()) {
			$this->error = 'Message: Failed to rollback SQL transaction. Reason: Unable to find connection.';
			throw new Kohana_SQL_Exception($this->error, array(':sql' => 'ROLLBACK;'));
		}
		$resource_id = @ibase_rollback($this->link_id);
		if ($resource_id === FALSE) {
			$this->error = 'Message: Failed to rollback SQL transaction. Reason: ' . ibase_errmsg();
			throw new Kohana_SQL_Exception($this->error, array(':sql' => 'ROLLBACK;'));
		}
	}

	/**
	 * This function commits a transaction.
	 *
	 * @access public
	 * @throws Kohana_SQL_Exception             indicates that the executed statement failed
	 */
	public function commit() {
		if ( ! $this->is_connected()) {
			$this->error = 'Message: Failed to commit SQL transaction. Reason: Unable to find connection.';
			throw new Kohana_SQL_Exception($this->error, array(':sql' => 'COMMIT;'));
		}
		$resource_id = @ibase_commit($this->link_id);
		if ($resource_id === FALSE) {
			$this->error = 'Message: Failed to commit SQL transaction. Reason: ' . ibase_errmsg();
			throw new Kohana_SQL_Exception($this->error, array(':sql' => 'COMMIT;'));
		}
	}

	/**
	 * This function allows for the ability to close the connection that was opened.
	 *
	 * @access public
	 * @return boolean                          whether an open connection was closed
	 */
	public function close() {
		if ($this->is_connected()) {
			if ( ! @ibase_close($this->link_id)) {
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
			@ibase_close($this->link_id);
		}
	}

}
?>