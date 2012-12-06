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
 * This class handles a standard Oracle connection.
 *
 * @package Leap
 * @category Oracle
 * @version 2012-12-05
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
	 * This function opens a connection using the data source provided.
	 *
	 * @access public
	 * @override
	 * @throws Throwable_Database_Exception     indicates that there is problem with
	 *                                          opening the connection
	 *
	 * @see http://www.php.net/manual/en/function.oci-connect.php
	 * @see http://download.oracle.com/docs/cd/E11882_01/network.112/e10836/naming.htm
	 * @see http://docs.oracle.com/cd/B10501_01/server.920/a96529/ch2.htm#100150
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
				throw new Throwable_Database_Exception('Message: Bad configuration. Reason: Data source needs to define either a //host[:port][/database] or a database name scheme.', array(':dsn' => $this->data_source->id));
			}
			$username = $this->data_source->username;
			$password = $this->data_source->password;
			if ( ! empty($this->data_source->charset)) {
				$charset = strtoupper($this->data_source->charset);
				$this->resource_id = ($this->data_source->is_persistent())
					? @oci_pconnect($username, $password, $connection_string, $charset)
					: @oci_connect($username, $password, $connection_string, $charset);
			}
			else {
				$this->resource_id = ($this->data_source->is_persistent())
					? @oci_pconnect($username, $password, $connection_string)
					: @oci_connect($username, $password, $connection_string);
			}
			if ($this->resource_id === FALSE) {
				$oci_error = oci_error();
				throw new Throwable_Database_Exception('Message: Failed to establish connection. Reason: :reason', array(':reason' => $oci_error['message']));
			}
			$this->execution_mode = OCI_COMMIT_ON_SUCCESS;
		}
	}

	/**
	 * This function begins a transaction.
	 *
	 * @access public
	 * @override
	 * @throws Throwable_SQL_Exception          indicates that the executed statement failed
	 *
	 * @see http://www.php.net/manual/en/function.oci-rollback.php
	 * @see http://www.php.net/manual/en/function.oci-commit.php
	 */
	public function begin_transaction() {
		if ( ! $this->is_connected()) {
			$this->execution_mode = OCI_COMMIT_ON_SUCCESS;
			throw new Throwable_SQL_Exception('Message: Failed to rollback SQL transaction. Reason: Unable to find connection.');
		}
		$this->execution_mode = (PHP_VERSION_ID > 50301)
			? OCI_NO_AUTO_COMMIT // Use with PHP > 5.3.1
			: OCI_DEFAULT;       // Use with PHP <= 5.3.1
	}

	/**
	 * This function processes an SQL statement that will return data.
	 *
	 * @access public
	 * @override
	 * @param string $sql						the SQL statement
	 * @param string $type						the return type to be used
	 * @return DB_ResultSet                     the result set
	 * @throws Throwable_SQL_Exception          indicates that the query failed
	 */
	public function query($sql, $type = 'array') {
		if ( ! $this->is_connected()) {
			throw new Throwable_SQL_Exception('Message: Failed to query SQL statement. Reason: Unable to find connection.');
		}
		$sql = trim($sql, "; \t\n\r\0\x0B");
		$result_set = $this->cache($sql, $type);
		if ($result_set !== NULL) {
			$this->sql = $sql;
			return $result_set;
		}
		$reader = new DB_Oracle_DataReader_Standard($this->resource_id, $sql, $this->execution_mode);
		$records = array();
		$size = 0;
		while ($reader->read()) {
			$records[] = $reader->row($type);
			$size++;
		}
		$reader->free();
		$result_set = $this->cache($sql, $type, new DB_ResultSet($records, $size, $type));
		$this->sql = $sql;
		return $result_set;
	}

	/**
	 * This function processes an SQL statement that will NOT return data.
	 *
	 * @access public
	 * @override
	 * @param string $sql						the SQL statement
	 * @throws Throwable_SQL_Exception          indicates that the executed statement failed
	 */
	public function execute($sql) {
		if ( ! $this->is_connected()) {
			throw new Throwable_SQL_Exception('Message: Failed to execute SQL statement. Reason: Unable to find connection.');
		}
		$sql = trim($sql, "; \t\n\r\0\x0B");
		$command_id = @oci_parse($this->resource_id, $sql);
		if (($command_id === FALSE) OR ! oci_execute($command_id, $this->execution_mode)) {
			$oci_error = oci_error($command_id);
			throw new Throwable_SQL_Exception('Message: Failed to execute SQL statement. Reason: :reason', array(':reason' => $oci_error['message']));
		}
		$this->sql = $sql;
		@oci_free_statement($command_id);
	}

	/**
	 * This function returns the last insert id.
	 *
	 * @access public
	 * @override
	 * @return integer                          the last insert id
	 * @throws Throwable_SQL_Exception          indicates that the query failed
	 *
	 * @see http://stackoverflow.com/questions/3131064/get-id-of-last-inserted-record-in-oracle-db
	 * @see http://stackoverflow.com/questions/3558433/php-oracle-take-the-autogenerated-id-after-an-insert
	 */
	public function get_last_insert_id() {
		if ( ! $this->is_connected()) {
			throw new Throwable_SQL_Exception('Message: Failed to fetch the last insert id. Reason: Unable to find connection.');
		}
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
			throw new Throwable_SQL_Exception(preg_replace('/Failed to query SQL statement./', 'Failed to fetch the last insert id.', $ex->getMessage()));
		}
	}

	/**
	 * This function rollbacks a transaction.
	 *
	 * @access public
	 * @override
	 * @throws Throwable_SQL_Exception          indicates that the executed statement failed
	 *
	 * @see http://www.php.net/manual/en/function.oci-rollback.php
	 */
	public function rollback() {
		$this->execution_mode = OCI_COMMIT_ON_SUCCESS;
		if ( ! $this->is_connected()) {
			throw new Throwable_SQL_Exception('Message: Failed to rollback SQL transaction. Reason: Unable to find connection.');
		}
		$command_id = @oci_rollback($this->resource_id);
		if ($command_id === FALSE) {
			$oci_error = oci_error($this->resource_id);
			throw new Throwable_SQL_Exception('Message: Failed to rollback SQL transaction. Reason: :reason', array(':reason' => $oci_error['message']));
		}
	}

	/**
	 * This function commits a transaction.
	 *
	 * @access public
	 * @override
	 * @throws Throwable_SQL_Exception          indicates that the executed statement failed
	 *
	 * @see http://www.php.net/manual/en/function.oci-commit.php
	 */
	public function commit() {
		$this->execution_mode = OCI_COMMIT_ON_SUCCESS;
		if ( ! $this->is_connected()) {
			throw new Throwable_SQL_Exception('Message: Failed to commit SQL transaction. Reason: Unable to find connection.');
		}
		$command_id = @oci_commit($this->resource_id);
		if ($command_id === FALSE) {
			$oci_error = oci_error($this->resource_id);
			throw new Throwable_SQL_Exception('Message: Failed to commit SQL transaction. Reason: :reason', array(':reason' => $oci_error['message']));
		}
	}

	/**
	 * This function closes an open connection.
	 *
	 * @access public
	 * @override
	 * @return boolean                          whether an open connection was closed
	 *
	 * @see http://www.php.net/manual/en/function.oci-close.php
	 */
	public function close() {
		if ($this->is_connected()) {
			if ( ! @oci_close($this->resource_id)) {
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
	 * @override
	 *
	 * @see http://www.php.net/manual/en/function.oci-close.php
	 */
	public function __destruct() {
		if (is_resource($this->resource_id)) {
			@oci_close($this->resource_id);
		}
	}

}
?>