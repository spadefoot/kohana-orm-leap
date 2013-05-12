<?php

/**
 * Copyright © 2011–2013 Spadefoot Team.
 *
 * Unless otherwise noted, LEAP is licensed under the Apache License,
 * Version 2.0 (the "License"); you may not use this file except in
 * compliance with the License. You may obtain a copy of the License
 * at:
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
 * @version 2013-01-28
 *
 * @see http://php.net/manual/en/book.oci8.php
 *
 * @abstract
 */
abstract class Base\DB\Oracle\Connection\Standard extends DB\SQL\Connection\Standard {

	/**
	 * This variable stores the execution mode, which is used to handle transactions.
	 *
	 * @access protected
	 * @var integer
	 */
	protected $execution_mode;

	/**
	 * This destructor ensures that the connection is closed.
	 *
	 * @access public
	 * @override
	 *
	 * @see http://www.php.net/manual/en/function.oci-close.php
	 */
	public function __destruct() {
		if (is_resource($this->resource)) {
			@oci_close($this->resource);
		}
	}

	/**
	 * This function begins a transaction.
	 *
	 * @access public
	 * @override
	 * @throws Throwable\SQL\Exception              indicates that the executed
	 *                                              statement failed
	 *
	 * @see http://www.php.net/manual/en/function.oci-rollback.php
	 * @see http://www.php.net/manual/en/function.oci-commit.php
	 */
	public function begin_transaction() {
		if ( ! $this->is_connected()) {
			$this->execution_mode = OCI_COMMIT_ON_SUCCESS;
			throw new Throwable\SQL\Exception('Message: Failed to begin SQL transaction. Reason: Unable to find connection.');
		}
		$this->execution_mode = (PHP_VERSION_ID > 50301)
			? OCI_NO_AUTO_COMMIT // Use with PHP > 5.3.1
			: OCI_DEFAULT;       // Use with PHP <= 5.3.1
		$this->sql = 'BEGIN TRANSACTION;';
	}

	/**
	 * This function closes an open connection.
	 *
	 * @access public
	 * @override
	 * @return boolean                              whether an open connection was closed
	 *
	 * @see http://www.php.net/manual/en/function.oci-close.php
	 */
	public function close() {
		if ($this->is_connected()) {
			if ( ! @oci_close($this->resource)) {
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
	 * @throws Throwable\SQL\Exception              indicates that the executed
	 *                                              statement failed
	 *
	 * @see http://www.php.net/manual/en/function.oci-commit.php
	 */
	public function commit() {
		$this->execution_mode = OCI_COMMIT_ON_SUCCESS;
		if ( ! $this->is_connected()) {
			throw new Throwable\SQL\Exception('Message: Failed to commit SQL transaction. Reason: Unable to find connection.');
		}
		$command = @oci_commit($this->resource);
		if ($command === FALSE) {
			$error = @oci_error($this->resource);
			$reason = (is_array($error) AND isset($error['message']))
				? $error['message']
				: 'Unable to perform command.';
			throw new Throwable\SQL\Exception('Message: Failed to commit SQL transaction. Reason: :reason', array(':reason' => $reason));
		}
		$this->sql = 'COMMIT;';
	}

	/**
	 * This function processes an SQL statement that will NOT return data.
	 *
	 * @access public
	 * @override
	 * @param string $sql                           the SQL statement
	 * @throws Throwable\SQL\Exception              indicates that the executed
	 *                                              statement failed
	 */
	public function execute($sql) {
		if ( ! $this->is_connected()) {
			throw new Throwable\SQL\Exception('Message: Failed to execute SQL statement. Reason: Unable to find connection.');
		}
		$command = @oci_parse($this->resource, trim($sql, "; \t\n\r\0\x0B"));
		if ($command === FALSE) {
			$error = @oci_error($this->resource);
			$reason = (is_array($error) AND isset($error['message']))
				? $error['message']
				: 'Unable to perform command.';
			throw new Throwable\SQL\Exception('Message: Failed to execute SQL statement. Reason: :reason', array(':reason' => $reason));
		}
		if ( ! oci_execute($command, $this->execution_mode)) {
			$error = @oci_error($command);
			$reason = (is_array($error) AND isset($error['message']))
				? $error['message']
				: 'Unable to perform command.';
			throw new Throwable\SQL\Exception('Message: Failed to execute SQL statement. Reason: :reason', array(':reason' => $reason));
		}
		$this->sql = $sql;
		@oci_free_statement($command);
	}

	/**
	 * This function returns the last insert id.
	 *
	 * @access public
	 * @override
	 * @param string $table                         the table to be queried
	 * @param string $column                        the column representing the table's id
	 * @return integer                              the last insert id
	 * @throws Throwable\SQL\Exception              indicates that the query failed
	 *
	 * @see http://stackoverflow.com/questions/3131064/get-id-of-last-inserted-record-in-oracle-db
	 * @see http://stackoverflow.com/questions/3558433/php-oracle-take-the-autogenerated-id-after-an-insert
	 */
	public function get_last_insert_id($table = NULL, $column = 'id') {
		if ( ! $this->is_connected()) {
			throw new Throwable\SQL\Exception('Message: Failed to fetch the last insert id. Reason: Unable to find connection.');
		}
		try {
			if (is_string($table)) {
				$sql = $this->sql;
				$precompiler = DB\SQL::precompiler($this->data_source);
				$table = $precompiler->prepare_identifier($table);
				$column = $precompiler->prepare_identifier($column);
				$id = (int) $this->query("SELECT MAX({$column}) AS \"id\" FROM {$table};")->get('id', 0);
				$this->sql = $sql;
				return $id;
			}
			else {
				$sql = $this->sql;
				if (preg_match('/^INSERT\s+INTO\s+(.*?)\s+/i', $sql, $matches)) {
					if (isset($matches[1])) {
						$table = $matches[1];
						$id = (int) $this->query("SELECT MAX(ID) AS \"id\" FROM {$table};")->get('id', 0);
						$this->sql = $sql;
						return $id;
					}
				}
				return 0;
			}
		}
		catch (\Exception $ex) {
			throw new Throwable\SQL\Exception(preg_replace('/Failed to query SQL statement./', 'Failed to fetch the last insert id.', $ex->getMessage()));
		}
	}

	/**
	 * This function opens a connection using the data source provided.
	 *
	 * @access public
	 * @override
	 * @throws Throwable\Database\Exception         indicates that there is problem with
	 *                                              opening the connection
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
				throw new Throwable\Database\Exception('Message: Bad configuration. Reason: Data source needs to define either a //host[:port][/database] or a database name scheme.', array(':dsn' => $this->data_source->id));
			}
			$username = $this->data_source->username;
			$password = $this->data_source->password;
			if ( ! empty($this->data_source->charset)) {
				$charset = strtoupper($this->data_source->charset);
				$this->resource = ($this->data_source->is_persistent())
					? @oci_pconnect($username, $password, $connection_string, $charset)
					: @oci_connect($username, $password, $connection_string, $charset);
			}
			else {
				$this->resource = ($this->data_source->is_persistent())
					? @oci_pconnect($username, $password, $connection_string)
					: @oci_connect($username, $password, $connection_string);
			}
			if ($this->resource === FALSE) {
				$error = @oci_error();
				$reason = (is_array($error) AND isset($error['message']))
					? $error['message']
					: 'Unable to connect to database.';
				throw new Throwable\Database\Exception('Message: Failed to establish connection. Reason: :reason', array(':reason' => $reason));
			}
			$this->execution_mode = OCI_COMMIT_ON_SUCCESS;
		}
	}

	/**
	 * This function processes an SQL statement that will return data.
	 *
	 * @access public
	 * @override
	 * @param string $sql                           the SQL statement
	 * @param string $type						    the return type to be used
	 * @return DB\ResultSet                         the result set
	 * @throws Throwable\SQL\Exception              indicates that the query failed
	 */
	public function query($sql, $type = 'array') {
		if ( ! $this->is_connected()) {
			throw new Throwable\SQL\Exception('Message: Failed to query SQL statement. Reason: Unable to find connection.');
		}
		$result_set = $this->cache($sql, $type);
		if ($result_set !== NULL) {
			$this->sql = $sql;
			return $result_set;
		}
		$reader = DB\SQL\DataReader::factory($this, $sql, $this->execution_mode);
		$result_set = $this->cache($sql, $type, new DB\ResultSet($reader, $type));
		$this->sql = $sql;
		return $result_set;
	}

	/**
	 * This function creates a data reader for query the specified SQL statement.
	 *
	 * @access public
	 * @param string $sql						    the SQL statement
	 * @return DB\SQL\DataReader                    the SQL data reader
	 * @throws Throwable\SQL\Exception              indicates that the query failed
	 */
	public function reader($sql) {
		if ( ! $this->is_connected()) {
			throw new Throwable\SQL\Exception('Message: Failed to create SQL data reader. Reason: Unable to find connection.');
		}
		$reader = DB\SQL\DataReader::factory($this, $sql, $this->execution_mode);
		$this->sql = $sql;
		return $reader;
	}

	/**
	 * This function rollbacks a transaction.
	 *
	 * @access public
	 * @override
	 * @throws Throwable\SQL\Exception              indicates that the executed
	 *                                              statement failed
	 *
	 * @see http://www.php.net/manual/en/function.oci-rollback.php
	 */
	public function rollback() {
		$this->execution_mode = OCI_COMMIT_ON_SUCCESS;
		if ( ! $this->is_connected()) {
			throw new Throwable\SQL\Exception('Message: Failed to rollback SQL transaction. Reason: Unable to find connection.');
		}
		$command = @oci_rollback($this->resource);
		if ($command === FALSE) {
			$error = @oci_error($this->resource);
			$reason = (is_array($error) AND isset($error['message']))
				? $error['message']
				: 'Unable to perform command.';
			throw new Throwable\SQL\Exception('Message: Failed to rollback SQL transaction. Reason: :reason', array(':reason' => $reason));
		}
		$this->sql = 'ROLLBACK;';
	}

}
