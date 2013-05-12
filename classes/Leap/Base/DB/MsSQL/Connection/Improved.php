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
 * This class handles an improved MS SQL connection.
 *
 * @package Leap
 * @category MS SQL
 * @version 2013-05-01
 *
 * @see http://php.net/manual/en/ref.sqlsrv.php
 * @see http://blogs.msdn.com/b/brian_swan/archive/2010/03/08/mssql-vs-sqlsrv-what-s-the-difference-part-1.aspx
 * @see http://blogs.msdn.com/b/brian_swan/archive/2010/03/10/mssql-vs-sqlsrv-what-s-the-difference-part-2.aspx
 *
 * @abstract
 */
abstract class Base\DB\MsSQL\Connection\Improved extends DB\SQL\Connection\Standard {

	/**
	 * This destructor ensures that the connection is closed.
	 *
	 * @access public
	 * @override
	 */
	public function __destruct() {
		if (is_resource($this->resource)) {
			@sqlsrv_close($this->resource);
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
	 * @see http://msdn.microsoft.com/en-us/library/ms188929.aspx
	 * @see http://php.net/manual/en/function.sqlsrv-begin-transaction.php
	 */
	public function begin_transaction() {
		if ( ! $this->is_connected()) {
			throw new Throwable\SQL\Exception('Message: Failed to begin SQL transaction. Reason: Unable to find connection.');
		}
		$command = @sqlsrv_begin_transaction($this->resource);
		if ($command === FALSE) {
			$errors = @sqlsrv_errors(SQLSRV_ERR_ALL);
			$reason = (is_array($errors) AND isset($errors[0]['message']))
				? $errors[0]['message']
				: 'Unable to perform command.';
			throw new Throwable\SQL\Exception('Message: Failed to begin the transaction. Reason: :reason', array(':reason' => $reason));
		}
		$this->sql = 'BEGIN TRAN;';
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
			if ( ! @sqlsrv_close($this->resource)) {
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
	 * @see http://msdn.microsoft.com/en-us/library/ms190295.aspx
	 * @see http://php.net/manual/en/function.sqlsrv-commit.php
	 */
	public function commit() {
		if ( ! $this->is_connected()) {
			throw new Throwable\SQL\Exception('Message: Failed to rollback SQL transaction. Reason: Unable to find connection.');
		}
		$command = @sqlsrv_commit($this->resource);
		if ($command === FALSE) {
			$errors = @sqlsrv_errors(SQLSRV_ERR_ALL);
			$reason = (is_array($errors) AND isset($errors[0]['message']))
				? $errors[0]['message']
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
	 *
	 * @see http://php.net/manual/en/function.sqlsrv-query.php
	 * @see http://php.net/manual/en/function.sqlsrv-free-stmt.php
	 */
	public function execute($sql) {
		if ( ! $this->is_connected()) {
			throw new Throwable\SQL\Exception('Message: Failed to execute SQL statement. Reason: Unable to find connection.');
		}
		$command = @sqlsrv_query($this->resource, $sql);
		if ($command === FALSE) {
			$errors = @sqlsrv_errors(SQLSRV_ERR_ALL);
			$reason = (is_array($errors) AND isset($errors[0]['message']))
				? $errors[0]['message']
				: 'Unable to perform command.';
			throw new Throwable\SQL\Exception('Message: Failed to execute SQL statement. Reason: :reason', array(':reason' => $reason));
		}
		@sqlsrv_free_stmt($command);
		$this->sql = $sql;
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
				$id = (int) $this->query("SELECT MAX({$column}) AS [id] FROM {$table};")->get('id', 0);
				$this->sql = $sql;
				return $id;
			}
			else {
				$sql = $this->sql;
				if (preg_match('/^INSERT\s+(TOP.+\s+)?INTO\s+(.*?)\s+/i', $sql, $matches)) {
					$table = isset($matches[2]) ? $matches[2] : NULL;
					$query = ( ! empty($table)) ? "SELECT IDENT_CURRENT('{$table}') AS [id];" : 'SELECT SCOPE_IDENTITY() AS [id];';
					$id = (int) $this->query($query)->get('id', 0);
					$this->sql = $sql;
					return $id;
				}
				return 0;
			}
		}
		catch (\Exception $ex) {
			throw new Throwable\SQL\Exception('Message: Failed to fetch the last insert id. Reason: :reason', array(':reason' => $ex->getMessage()));
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
	 * @see http://php.net/manual/en/function.sqlsrv-connect.php
	 * @see http://msdn.microsoft.com/en-us/library/cc644930.aspx
	 */
	public function open() {
		if ( ! $this->is_connected()) {
			$connection_string = $this->data_source->host;
			$port = $this->data_source->port;
			if ( ! empty($port)) {
				$connection_string .= ':' . $port;
			}

			$configurations = array();

			$configurations['Database'] = $this->data_source->database;
			$configurations['UID'] = $this->data_source->username;
			$configurations['PWD'] = $this->data_source->password;

			if ( ! empty($this->data_source->charset)) {
				$configurations['CharacterSet'] = $this->data_source->charset;
			}

			if ( ! $this->data_source->is_persistent()) {
				$configurations['ConnectionPooling'] = FALSE;
			}

			$this->resource = @sqlsrv_connect($connection_string, $configurations);

			if ($this->resource === FALSE) {
				$errors = @sqlsrv_errors(SQLSRV_ERR_ALL);
				$reason = (is_array($errors) AND isset($errors[0]['message']))
					? $errors[0]['message']
					: 'Unable to connect using the specified configurations.';
				throw new Throwable\Database\Exception('Message: Failed to establish connection. Reason: :reason', array(':reason' => $reason));
			}
		}
	}

	/**
	 * This function rollbacks a transaction.
	 *
	 * @access public
	 * @override
	 * @throws Throwable\SQL\Exception              indicates that the executed
	 *                                              statement failed
	 *
	 * @see http://php.net/manual/en/function.sqlsrv-rollback.php
	 */
	public function rollback() {
		if ( ! $this->is_connected()) {
			throw new Throwable\SQL\Exception('Message: Failed to rollback SQL transaction. Reason: Unable to find connection.');
		}
		$command = @sqlsrv_rollback($this->resource);
		if ($command === FALSE) {
			$errors = @sqlsrv_errors(SQLSRV_ERR_ALL);
			$reason = (is_array($errors) AND isset($errors[0]['message']))
				? $errors[0]['message']
				: 'Unable to perform command.';
			throw new Throwable\SQL\Exception('Message: Failed to rollback SQL transaction. Reason: :reason', array(':reason' => $reason));
		}
		$this->sql = 'ROLLBACK;';
	}

}
