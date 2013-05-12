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
 * This class handles a standard MS SQL connection.
 *
 * @package Leap
 * @category MS SQL
 * @version 2013-01-28
 *
 * @see http://www.php.net/manual/en/ref.mssql.php
 * @see http://blogs.msdn.com/b/brian_swan/archive/2010/03/08/mssql-vs-sqlsrv-what-s-the-difference-part-1.aspx
 * @see http://blogs.msdn.com/b/brian_swan/archive/2010/03/10/mssql-vs-sqlsrv-what-s-the-difference-part-2.aspx
 *
 * @abstract
 */
abstract class Base\DB\MsSQL\Connection\Standard extends DB\SQL\Connection\Standard {

	/**
	 * This destructor ensures that the connection is closed.
	 *
	 * @access public
	 * @override
	 */
	public function __destruct() {
		if (is_resource($this->resource)) {
			@mssql_close($this->resource);
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
	 */
	public function begin_transaction() {
		$this->execute('BEGIN TRAN;');
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
			if ( ! @mssql_close($this->resource)) {
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
	 */
	public function commit() {
		$this->execute('COMMIT;');
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
		$command = @mssql_query($sql, $this->resource);
		if ($command === FALSE) {
			throw new Throwable\SQL\Exception('Message: Failed to execute SQL statement. Reason: :reason', array(':reason' => @mssql_get_last_message()));
		}
		@mssql_free_result($command);
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
				$username = $this->data_source->username;
				$password = $this->data_source->password;
				$this->resource = ($this->data_source->is_persistent())
					? mssql_pconnect($connection_string, $username, $password)
					: mssql_connect($connection_string, $username, $password, TRUE);
			}
			catch (\ErrorException $ex) {
				throw new Throwable\Database\Exception('Message: Failed to establish connection. Reason: :reason', array(':reason' => $ex->getMessage()));
			}
			$database = @mssql_select_db($this->data_source->database, $this->resource);
			if ($database === FALSE) {
				throw new Throwable\Database\Exception('Message: Failed to connect to database. Reason: :reason', array(':reason' => @mssql_get_last_message()));
			}
			if ( ! empty($this->data_source->charset)) {
				ini_set('mssql.charset', $this->data_source->charset);
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
	 */
	public function rollback() {
		$this->execute('ROLLBACK;');
	}

}
