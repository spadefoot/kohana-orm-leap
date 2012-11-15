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
 * This class handles a standard PostgreSQL connection.
 *
 * @package Leap
 * @category PostgreSQL
 * @version 2012-11-14
 *
 * @see http://php.net/manual/en/ref.pgsql.php
 *
 * @abstract
 */
abstract class Base_DB_PostgreSQL_Connection_Standard extends DB_SQL_Connection_Standard {

	/**
	 * This function opens a connection using the data source provided.
	 *
	 * @access public
	 * @throws Throwable_Database_Exception        indicates that there is problem with
	 *                                          opening the connection
	 * 
	 * @see http://www.php.net/manual/en/function.pg-connect.php
	 */
	public function open() {
		if ( ! $this->is_connected()) {
			$connection_string  = 'host=' . $this->data_source->host;
			$port = $this->data_source->port;
			if ( ! empty($port)) {
				$connection_string .= ' port=' . $port;
			}
			$connection_string .= ' dbname=' . $this->data_source->database;
			$connection_string .= ' user=' . $this->data_source->username;
			$connection_string .= ' password=' . $this->data_source->password;
			//if ( ! empty($this->data_source->charset)) {
			//    $connection_string .= " options='--client_encoding=" . strtoupper($this->data_source->charset) . "'";
			//}
			$this->resource_id = ($this->data_source->is_persistent())
				? @pg_pconnect($connection_string)
				: @pg_connect($connection_string, PGSQL_CONNECT_FORCE_NEW);
			if ($this->resource_id === FALSE) {
				throw new Throwable_Database_Exception('Message: Failed to establish connection. Reason: :reason', array(':reason' => pg_last_error()));
			}
			if ( ! empty($this->data_source->charset) AND abs(pg_set_client_encoding($this->link, strtoupper($this->data_source->charset)))) {
				throw new Throwable_Database_Exception('Message: Failed to set character set. Reason: :reason', array(':reason' => pg_last_error($this->resource_id)));
			}
		}
	}

	/**
	 * This function begins a transaction.
	 *
	 * @access public
	 * @throws Throwable_SQL_Exception             indicates that the executed statement failed
	 *
	 * @see http://www.postgresql.org/docs/8.3/static/sql-start-transaction.html
	 */
	public function begin_transaction() {
		$this->execute('START TRANSACTION;');
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
	 * @see http://www.php.net/manual/en/function.pg-query.php
	 * @see http://www.php.net/manual/en/function.pg-last-error.php
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
		$command_id = @pg_query($this->resource_id, $sql);
		if ($command_id === FALSE) {
			throw new Throwable_SQL_Exception('Message: Failed to query SQL statement. Reason: :reason', array(':reason' => pg_last_error($this->resource_id)));
		}
		$records = array();
		$size = 0;
		while ($record = pg_fetch_assoc($command_id)) {
			$records[] = DB_Connection::type_cast($type, $record);
			$size++;
		}
		@pg_free_result($command_id);
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
	* @see http://www.php.net/manual/en/function.pg-insert.php
	*/
	public function execute($sql) {
		if ( ! $this->is_connected()) {
			throw new Throwable_SQL_Exception('Message: Failed to execute SQL statement. Reason: Unable to find connection.');
		}
		$command_id = @pg_query($this->resource_id, $sql);
		if ($command_id === FALSE) {
			throw new Throwable_SQL_Exception('Message: Failed to execute SQL statement. Reason: :reason', array(':reason' => pg_last_error($this->resource_id)));
		}
		$this->sql = $sql;
		@pg_free_result($command_id);
	}

	/**
	 * This function returns the last insert id.
	 *
	 * @access public
	 * @return integer                          the last insert id
	 * @throws Throwable_SQL_Exception             indicates that the query failed
	 *
	 * @see http://www.php.net/manual/en/function.pg-last-oid.php
	 * @see https://github.com/spadefoot/kohana-orm-leap/issues/44
	 */
	public function get_last_insert_id() {
		if ( ! $this->is_connected()) {
			throw new Throwable_SQL_Exception('Message: Failed to fetch the last insert id. Reason: Unable to find connection.');
		}
		
		// Option #1: Using 'SELECT lastval();'
		
		$command_id = @pg_query($this->resource_id, 'SELECT lastval();');
		
		if ($command_id === FALSE) {
			throw new Throwable_SQL_Exception('Message: Failed to fetch the last insert id. Reason: :reason', array(':reason' => pg_last_error($this->resource_id)));
		}
		
		$record = @pg_fetch_row($command_id);
		
		if ($record === FALSE) {
			throw new Throwable_SQL_Exception('Message: Failed to fetch the last insert id. Reason: :reason', array(':reason' => pg_last_error($this->resource_id)));
		}
		
		return $record[0];
		
		// Option #2: Using pg_last_oid($this->resource_id)
		
		//$insert_id = @pg_last_oid($this->resource_id);
		
		//if ($insert_id === FALSE) {
		//	throw new Throwable_SQL_Exception('Message: Failed to fetch the last insert id. Reason: :reason', array(':reason' => pg_last_error($this->resource_id)));
		//}
		
		//return $insert_id;
	}

	/**
	 * This function rollbacks a transaction.
	 *
	 * @access public
	 * @throws Throwable_SQL_Exception             indicates that the executed statement failed
	 */
	public function rollback() {
		$this->execute('ROLLBACK;');
	}

	/**
	 * This function commits a transaction.
	 *
	 * @access public
	 * @throws Throwable_SQL_Exception             indicates that the executed statement failed
	 */
	public function commit() {
		$this->execute('COMMIT;');
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
	 * @see http://www.php.net/manual/en/function.pg-escape-string.php
	 */
	public function quote($string, $escape = NULL) {
		if ( ! $this->is_connected()) {
			throw new Throwable_SQL_Exception('Message: Failed to quote/escape string. Reason: Unable to find connection.');
		}

		$string = "'" . pg_escape_string($this->resource_id, $string) . "'";

		if (is_string($escape) OR ! empty($escape)) {
			$string .= " ESCAPE '{$escape}'";
		}

		return $string;
	}

	/**
	 * This function closes an open connection.
	 *
	 * @access public
	 * @return boolean                           whether an open connection was closed
	 *
	 * @see http://www.php.net/manual/en/function.pg-close.php
	 */
	public function close() {
		if ($this->is_connected()) {
			if ( ! @pg_close($this->resource_id)) {
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
	 * @see http://www.php.net/manual/en/function.pg-close.php
	 */
	public function __destruct() {
		if (is_resource($this->resource_id)) {
			@pg_close($this->resource_id);
		}
	}

}
?>