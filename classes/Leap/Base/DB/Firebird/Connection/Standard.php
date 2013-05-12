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
 * @version 2013-01-27
 *
 * @see http://us3.php.net/manual/en/book.ibase.php
 * @see http://us2.php.net/manual/en/ibase.installation.php
 * @see http://www.firebirdfaq.org/faq227/
 * @see http://www.firebirdfaq.org/cat3/
 *
 * @abstract
 */
abstract class Base\DB\Firebird\Connection\Standard extends DB\SQL\Connection\Standard {

	/**
	 * This destructor ensures that the connection is closed.
	 *
	 * @access public
	 * @override
	 */
	public function __destruct() {
		if (is_resource($this->resource)) {
			@ibase_close($this->resource);
		}
	}

	/**
	 * This function begins a transaction.
	 *
	 * @access public
	 * @override
	 * @throws Throwable\SQL\Exception              indicates that the executed
	 *                                              statement failed
	 */
	public function begin_transaction() {
		if ( ! $this->is_connected()) {
			throw new Throwable\SQL\Exception('Message: Failed to begin SQL transaction. Reason: Unable to find connection.');
		}
		$command = @ibase_trans($this->resource, IBASE_READ | IBASE_WRITE);
		if ($command === FALSE) {
			throw new Throwable\SQL\Exception('Message: Failed to begin SQL transaction. Reason: :reason', array(':reason' => @ibase_errmsg()));
		}
		$this->sql = 'BEGIN TRANSACTION;';
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
			if ( ! @ibase_close($this->resource)) {
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
	 */
	public function commit() {
		if ( ! $this->is_connected()) {
			throw new Throwable\SQL\Exception('Message: Failed to commit SQL transaction. Reason: Unable to find connection.');
		}
		$command = @ibase_commit($this->resource);
		if ($command === FALSE) {
			throw new Throwable\SQL\Exception('Message: Failed to commit SQL transaction. Reason: :reason', array(':reason' => @ibase_errmsg()));
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
		$statement = @ibase_prepare($this->resource, $sql);
		$command = @ibase_execute($statement);
		if ($command === FALSE) {
			throw new Throwable\SQL\Exception('Message: Failed to execute SQL statement. Reason: :reason', array(':reason' => @ibase_errmsg()));
		}
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
	 *
	 * @see http://www.firebirdfaq.org/faq243/
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
						$id = (int) $this->query("SELECT \"ID\" AS \"id\" FROM {$table} ORDER BY \"ID\" DESC ROWS 1;")->get('id', 0);
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
			$this->resource = ($this->data_source->is_persistent())
				? @ibase_pconnect($connection_string, $username, $password, $charset, 0, 3, $role)
				: @ibase_connect($connection_string, $username, $password, $charset, 0, 3, $role);
			if ($this->resource === FALSE) {
				throw new Throwable\Database\Exception('Message: Failed to establish connection. Reason: :reason', array(':reason' => @ibase_errmsg()));
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
		if ( ! $this->is_connected()) {
			throw new Throwable\SQL\Exception('Message: Failed to rollback SQL transaction. Reason: Unable to find connection.');
		}
		$command = @ibase_rollback($this->resource);
		if ($command === FALSE) {
			throw new Throwable\SQL\Exception('Message: Failed to rollback SQL transaction. Reason: :reason', array(':reason' => @ibase_errmsg()));
		}
		$this->sql = 'ROLLBACK;';
	}

}
