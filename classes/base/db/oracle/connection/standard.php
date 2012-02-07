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
 * This class handles a standard Oracle connection.
 *
 * @package Leap
 * @category Oracle
 * @version 2012-02-06
 *
 * @see http://php.net/manual/en/book.oci8.php
 *
 * @abstract
 */
abstract class Base_DB_Oracle_Connection_Standard extends DB_SQL_Connection_Standard {

	/**
	 * This variable stores the execution mode, which is used to handle transactions.
	 *
	 * @access protected
	 * @var integer
	 */
	protected $execution_mode;

	/**
	 * This function allows for the ability to open a connection using
	 * the configurations provided.
	 *
	 * @access public
	 * @throws Kohana_Database_Exception        indicates that there is problem with
	 *                                          the database connection
	 *
	 * @see http://www.php.net/manual/en/function.oci-connect.php
	 * @see http://download.oracle.com/docs/cd/E11882_01/network.112/e10836/naming.htm
	 */
	public function open() {
		if ( ! $this->is_connected()) {
			$host = $this->data_source->host;
			$database = $this->data_source->database;
			if ( ! empty($host) ) {
				$connection_string = '//'. $host;
				$port = $this->data_source->port; // default port is 1521
				if ( ! empty($port)) {
					$connection_string .= ':' . $port;
				}
				$connection_string .= '/' . $database;
			}
			else if (isset($database)) {
				$connection_string = $database;
			}
			else {
				throw new Kohana_Database_Exception('Message: Bad configuration. Reason: Data source needs to define either a //host[:port][/database] or a database name scheme.', array(':dsn' => $this->data_source->id));
			}
			$username = $this->data_source->username;
			$password = $this->data_source->password;
			$this->link_id = ($this->data_source->is_persistent())
				? @oci_pconnect($username, $password, $connection_string)
				: @oci_connect($username, $password, $connection_string);
			if ($this->link_id === FALSE) {
				$oci_error = oci_error();
				$this->error = 'Message: Failed to establish connection. Reason: ' . $oci_error['message'];
				throw new Kohana_Database_Exception($this->error, array(':dsn' => $this->data_source->id));
			}
			$this->execution_mode = OCI_COMMIT_ON_SUCCESS;
		}
	}

	/**
	 * This function begins a transaction.
	 *
	 * @access public
	 * @throws Kohana_SQL_Exception             indicates that the executed statement failed
	 *
	 * @see http://www.php.net/manual/en/function.oci-rollback.php
	 * @see http://www.php.net/manual/en/function.oci-commit.php
	 */
	public function begin_transaction() {
		if ( ! $this->is_connected()) {
			$this->execution_mode = OCI_COMMIT_ON_SUCCESS;
			$this->error = 'Message: Failed to rollback SQL transaction. Reason: Unable to find connection.';
			throw new Kohana_SQL_Exception($this->error, array(':sql' => 'ROLLBACK;'));
		}
		// Use OCI_DEFAULT as the flag for PHP 5.3.1 <=
		$this->execution_mode = OCI_DEFAULT;
		// Use OCI_NO_AUTO_COMMIT for PHP 5.3.1 >
		//$this->execution_mode = OCI_NO_AUTO_COMMIT;
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
		$sql = trim($sql, "; \t\n\r\0\x0B");
		$result_set = $this->cache($sql, $type);
		if ( ! is_null($result_set)) {
			$this->sql = $sql;
			return $result_set;
		}
		$resource_id = @oci_parse($this->link_id, $sql);
		if (($resource_id === FALSE) || ! oci_execute($resource_id, $this->execution_mode)) {
			$oci_error = oci_error($resource_id);
			$this->error = 'Message: Failed to query SQL statement. Reason: ' . $oci_error['message'];
			throw new Kohana_SQL_Exception($this->error, array(':sql' => $sql, ':type' => $type));
		}
		$records = array();
		$size = 0;
		while ($record = oci_fetch_assoc($resource_id)) {
			$records[] = DB_Connection::type_cast($type, $record);
			$size++;
		}
		@oci_free_statement($resource_id);
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
		$sql = trim($sql, "; \t\n\r\0\x0B");
		$resource_id = @oci_parse($this->link_id, $sql);
		if (($resource_id === FALSE) || ! oci_execute($resource_id, $this->execution_mode)) {
			$oci_error = oci_error($resource_id);
			$this->error = 'Message: Failed to execute SQL statement. Reason: ' . $oci_error['message'];
			throw new Kohana_SQL_Exception($this->error, array(':sql' => $sql));
		}
		$this->sql = $sql;
		@oci_free_statement($resource_id);
	}

	/**
	 * This function returns the last insert id.
	 *
	 * @access public
	 * @return integer                          the last insert id
	 * @throws Kohana_SQL_Exception             indicates that the query failed
	 *
	 * @see http://stackoverflow.com/questions/3131064/get-id-of-last-inserted-record-in-oracle-db
	 * @see http://stackoverflow.com/questions/3558433/php-oracle-take-the-autogenerated-id-after-an-insert
	 */
	public function get_last_insert_id() {
		try {
			$sql = $this->sql;
			if (preg_match('/^INSERT\s+INTO\s+(.*?)\s+/i', $sql, $matches)) {
				$table = Arr::get($matches, 1, '');
				$query = "SELECT MAX(ID) FROM {$table};";
				$result = $this->query($query);
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
	 *
	 * @see http://www.php.net/manual/en/function.oci-rollback.php
	 */
	public function rollback() {
		$this->execution_mode = OCI_COMMIT_ON_SUCCESS;
		if ( ! $this->is_connected()) {
			$this->error = 'Message: Failed to rollback SQL transaction. Reason: Unable to find connection.';
			throw new Kohana_SQL_Exception($this->error, array(':sql' => 'ROLLBACK;'));
		}
		$resource_id = @oci_rollback($this->link_id);
		if ($resource_id === FALSE) {
			$oci_error = oci_error($this->link_id);
			$this->error = 'Message: Failed to rollback SQL transaction. Reason: ' . $oci_error['message'];
			throw new Kohana_SQL_Exception($this->error, array(':sql' => 'ROLLBACK;'));
		}
	}

	/**
	 * This function commits a transaction.
	 *
	 * @access public
	 * @throws Kohana_SQL_Exception             indicates that the executed statement failed
	 *
	 * @see http://www.php.net/manual/en/function.oci-commit.php
	 */
	public function commit() {
		$this->execution_mode = OCI_COMMIT_ON_SUCCESS;
		if ( ! $this->is_connected()) {
			$this->error = 'Message: Failed to commit SQL transaction. Reason: Unable to find connection.';
			throw new Kohana_SQL_Exception($this->error, array(':sql' => 'COMMIT;'));
		}
		$resource_id = @oci_commit($this->link_id);
		if ($resource_id === FALSE) {
			$oci_error = oci_error($this->link_id);
			$this->error = 'Message: Failed to commit SQL transaction. Reason: ' . $oci_error['message'];
			throw new Kohana_SQL_Exception($this->error, array(':sql' => 'COMMIT;'));
		}
	}

	/**
	 * This function allows for the ability to close the connection that was opened.
	 *
	 * @access public
	 * @return boolean                          whether an open connection was closed
	 *
	 * @see http://www.php.net/manual/en/function.oci-close.php
	 */
	public function close() {
		if ($this->is_connected()) {
			if ( ! @oci_close($this->link_id)) {
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
	 * @see http://www.php.net/manual/en/function.oci-close.php
	 */
	public function __destruct() {
		if (is_resource($this->link_id)) {
			@oci_close($this->link_id);
		}
	}

}
?>