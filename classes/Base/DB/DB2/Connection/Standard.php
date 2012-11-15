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
 * This class handles a standard DB2 connection.
 *
 * @package Leap
 * @category DB2
 * @version 2012-11-14
 *
 * @see http://php.net/manual/en/ref.ibm-db2.php
 *
 * @abstract
 */
abstract class Base_DB_DB2_Connection_Standard extends DB_SQL_Connection_Standard {

	/**
	 * This function opens a connection using the data source provided.
	 *
	 * @access public
	 * @throws Throwable_Database_Exception     indicates that there is problem with
	 *                                          opening the connection
	 *
	 * @see http://www.php.net/manual/en/function.db2-connect.php
	 * @see http://www.php.net/manual/en/function.db2-conn-error.php
	 * @see http://www.zinox.com/node/132
	 * @see http://www.ibm.com/developerworks/data/library/techarticle/dm-0505furlong/
	 */
	public function open() {
		if ( ! $this->is_connected()) {
			$connection_string  = 'DRIVER={IBM DB2 ODBC DRIVER};';
			$connection_string .= 'DATABASE=' . $this->data_source->database . ';';
			$connection_string .= 'HOSTNAME=' . $this->data_source->host . ';';
			$connection_string .= 'PORT=' . $this->data_source->port . ';';
			$connection_string .= 'PROTOCOL=TCPIP;';
			$connection_string .= 'UID=' . $this->data_source->username . ';';
			$connection_string .= 'PWD=' . $this->data_source->password . ';';
			$this->resource_id = ($this->data_source->is_persistent())
				? @db2_pconnect($connection_string, '', '')
				: @db2_connect($connection_string, '', '');
			if ($this->resource_id === FALSE) {
				throw new Throwable_Database_Exception('Message: Failed to establish connection. Reason: :reason', array(':reason' => db2_conn_error()));
			}
			// "To use UTF-8 when talking to a DB2 instance, use the following command from the DB2 home at the command prompt: db2set DB2CODEPAGE=1208"
		}
	}

	/**
	 * This function begins a transaction.
	 *
	 * @access public
	 * @throws Throwable_SQL_Exception             indicates that the executed statement failed
	 *
	 * @see http://www.php.net/manual/en/function.db2-autocommit.php
	 */
	public function begin_transaction() {
		if ( ! $this->is_connected()) {
			throw new Throwable_SQL_Exception('Message: Failed to begin SQL transaction. Reason: Unable to find connection.');
		}
		$command_id = @db2_autocommit($this->resource_id, DB2_AUTOCOMMIT_OFF);
		if ($command_id === FALSE) {
			throw new Throwable_SQL_Exception('Message: Failed to begin SQL transaction. Reason: :reason', array(':reason' => db2_conn_error($this->resource_id)));
		}
	}

	/**
	 * This function allows for the ability to process a query that will return data
	 * using the passed string.
	 *
	 * @access public
	 * @param string $sql						the SQL statement
	 * @param string $type						the return type to be used
	 * @return DB_ResultSet                     the result set
	 * @throws Throwable_SQL_Exception             indicates that the query failed
	 *
	 * @see http://www.php.net/manual/en/function.db2-prepare.php
	 * @see http://www.php.net/manual/en/function.db2-execute.php
	 * @see http://www.php.net/manual/en/function.db2-stmt-error.php
	 * @see http://www.php.net/manual/en/function.db2-fetch-assoc.php
	 * @see http://www.php.net/manual/en/function.db2-free-result.php
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
		$command_id = @db2_prepare($this->resource_id, $sql);
		if (($command_id === FALSE) OR ! db2_execute($command_id)) {
			throw new Throwable_SQL_Exception('Message: Failed to query SQL statement. Reason: :reason', array(':reason' => db2_stmt_error($command_id)));
		}
		$records = array();
		$size = 0;
		while ($record = db2_fetch_assoc($command_id)) {
			$records[] = DB_Connection::type_cast($type, $record);
			$size++;
		}
		@db2_free_result($command_id);
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
	 * @throws Throwable_SQL_Exception              indicates that the executed statement failed
	 *
	 * @see http://www.php.net/manual/en/function.db2-exec.php
	 * @see http://www.php.net/manual/en/function.db2-free-result.php
	 */
	public function execute($sql) {
		if ( ! $this->is_connected()) {
			throw new Throwable_SQL_Exception('Message: Failed to execute SQL statement. Reason: Unable to find connection.');
		}
		$command_id = @db2_exec($this->resource_id, $sql);
		if ($command_id === FALSE) {
			throw new Throwable_SQL_Exception('Message: Failed to execute SQL statement. Reason: :reason', array(':reason' => db2_stmt_error($command_id)));
		}
		$this->sql = $sql;
		@db2_free_result($command_id);
	}

	/**
	 * This function returns the last insert id.
	 *
	 * @access public
	 * @return integer                          the last insert id
	 * @throws Throwable_SQL_Exception             indicates that the query failed
	 *
	 * @see http://www.php.net/manual/en/function.db2-last-insert-id.php
	 */
	public function get_last_insert_id() {
		if ( ! $this->is_connected()) {
			throw new Throwable_SQL_Exception('Message: Failed to fetch the last insert id. Reason: Unable to find connection.');
		}
		$insert_id = @db2_last_insert_id($this->resource_id);
		if ($insert_id === FALSE) {
			throw new Throwable_SQL_Exception('Message: Failed to fetch the last insert id. Reason: :reason', array(':reason' => db2_conn_error($this->resource_id)));
		}
		settype($insert_id, 'integer');
		return $insert_id;
	}

	/**
	 * This function rollbacks a transaction.
	 *
	 * @access public
	 * @throws Throwable_SQL_Exception             indicates that the executed statement failed
	 *
	 * @see http://www.php.net/manual/en/function.db2-rollback.php
	 */
	public function rollback() {
		if ( ! $this->is_connected()) {
			throw new Throwable_SQL_Exception('Message: Failed to rollback SQL transaction. Reason: Unable to find connection.');
		}
		$command_id = @db2_rollback($this->resource_id);
		if ($command_id === FALSE) {
			throw new Throwable_SQL_Exception('Message: Failed to rollback SQL transaction. Reason: :reason', array(':reason' => db2_conn_error($this->resource_id)));
		}
		@db2_autocommit($this->resource_id, DB2_AUTOCOMMIT_ON);
	}

	/**
	 * This function commits a transaction.
	 *
	 * @access public
	 * @throws Throwable_SQL_Exception             indicates that the executed statement failed
	 *
	 * @see http://www.php.net/manual/en/function.db2-commit.php
	 */
	public function commit() {
		if ( ! $this->is_connected()) {
			throw new Throwable_SQL_Exception('Message: Failed to commit SQL transaction. Reason: Unable to find connection.');
		}
		$command_id = @db2_commit($this->resource_id);
		if ($command_id === FALSE) {
			throw new Throwable_SQL_Exception('Message: Failed to commit SQL transaction. Reason: :reason', array(':reason' => db2_conn_error($this->resource_id)));
		}
		@db2_autocommit($this->resource_id, DB2_AUTOCOMMIT_ON);
	}

	/**
	 * This function escapes a string to be used in an SQL statement.
	 *
	 * @access public
	 * @param string $string                    the string to be escaped
	 * @param char $escape                      the escape character
	 * @return string                           the quoted string
	 * @throws Throwable_SQL_Exception             indicates that no connection could
	 *                                          be found
	 *
	 * @see http://www.php.net/manual/en/function.db2-escape-string.php
	 * @see http://publib.boulder.ibm.com/infocenter/db2luw/v8/index.jsp?topic=/com.ibm.db2.udb.doc/admin/c0010966.htm
	 * @see http://www.php.net/manual/en/mbstring.supported-encodings.php
	 */
	public function quote($string, $escape = NULL) {
		if ( ! $this->is_connected()) {
			throw new Throwable_SQL_Exception('Message: Failed to quote/escape string. Reason: Unable to find connection.');
		}

		$string = "'" . db2_escape_string($string) . "'";

		if (is_string($escape) OR ! empty($escape)) {
			$string .= " ESCAPE '{$escape}'";
		}

		return $string;
	}

	/**
	 * This function closes an open connection.
	 *
	 * @access public
	 * @return boolean                          whether an open connection was closed
	 *
	 * @see http://www.php.net/manual/en/function.db2-close.php
	 */
	public function close() {
		if ($this->is_connected()) {
			if ( ! @db2_close($this->resource_id)) {
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
	 *
	 * @see http://www.php.net/manual/en/function.db2-close.php
	 */
	public function __destruct() {
		if (is_resource($this->resource_id)) {
			@db2_close($this->resource_id);
		}
	}

}
?>