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
 * This class handles a standard PostgreSQL connection.
 *
 * @package Leap
 * @category PostgreSQL
 * @version 2012-02-09
 *
 * @see http://php.net/manual/en/ref.pgsql.php
 *
 * @abstract
 */
abstract class Base_DB_PostgreSQL_Connection_Standard extends DB_SQL_Connection_Standard {

	/**
	 * This function allows for the ability to open a connection using
	 * the configurations provided.
	 *
	 * @access public
	 * @throws Kohana_Database_Exception         indicates that there is problem with
	 *                                           the database connection
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
			$this->link_id = ($this->data_source->is_persistent())
				? @pg_pconnect($connection_string)
				: @pg_connect($connection_string, PGSQL_CONNECT_FORCE_NEW);
			if ($this->link_id === FALSE) {
				$this->error = 'Message: Failed to establish connection. Reason: ' . pg_last_error();
				throw new Kohana_Database_Exception($this->error, array(':dsn' => $this->data_source->id));
			}
		}
	}

	/**
	 * This function begins a transaction.
	 *
	 * @access public
	 * @throws Kohana_SQL_Exception             indicates that the executed statement failed
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
	 * @throws Kohana_SQL_Exception             indicates that the query failed
	 *
	 * @see http://www.php.net/manual/en/function.pg-query.php
	 * @see http://www.php.net/manual/en/function.pg-last-error.php
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
		$resource_id = @pg_query($this->link_id, $sql);
		if ($resource_id === FALSE) {
			$this->error = 'Message: Failed to query SQL statement. Reason: ' . pg_last_error($this->link_id);
			throw new Kohana_SQL_Exception($this->error, array(':sql' => $sql, ':type' => $type));
		}
		$records = array();
		$size = 0;
		while ($record = pg_fetch_assoc($resource_id)) {
			$records[] = DB_Connection::type_cast($type, $record);
			$size++;
		}
		@pg_free_result($resource_id);
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
	* @throws Kohana_SQL_Exception              indicates that the executed statement failed
	*
	* @see http://www.php.net/manual/en/function.pg-insert.php
	*/
	public function execute($sql) {
		if ( ! $this->is_connected()) {
			$this->error = 'Message: Failed to execute SQL statement. Reason: Unable to find connection.';
			throw new Kohana_SQL_Exception($this->error, array(':sql' => $sql));
		}
		$resource_id = @pg_query($this->link_id, $sql);
		if ($resource_id === FALSE) {
			$this->error = 'Message: Failed to execute SQL statement. Reason: ' . pg_last_error($this->link_id);
			throw new Kohana_SQL_Exception($this->error, array(':sql' => $sql));
		}
		$this->sql = $sql;
		@pg_free_result($resource_id);
	}

	/**
	 * This function returns the last insert id.
	 *
	 * @access public
	 * @return integer                          the last insert id
	 * @throws Kohana_SQL_Exception             indicates that the query failed
	 *
	 * @see http://www.php.net/manual/en/function.pg-last-oid.php
	 */
	public function get_last_insert_id() {
		$insert_id = pg_last_oid($this->link_id);
		if ($insert_id === FALSE) {
			$this->error = 'Message: Failed to fetch the last insert id. Reason: ' . pg_last_error($this->link_id);
			throw new Kohana_SQL_Exception($this->error, array(':sql' => $this->sql));
		}
		return $insert_id;
	}

	/**
	 * This function rollbacks a transaction.
	 *
	 * @access public
	 * @throws Kohana_SQL_Exception             indicates that the executed statement failed
	 */
	public function rollback() {
		$this->execute('ROLLBACK;');
	}

	/**
	 * This function commits a transaction.
	 *
	 * @access public
	 * @throws Kohana_SQL_Exception             indicates that the executed statement failed
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
	 *
	 * @see http://www.php.net/manual/en/function.pg-escape-string.php
	 */
	public function quote($string, $escape = NULL) {
		$string = "'" . pg_escape_string($this->link_id, $string) . "'";

		if (is_string($escape) || ! empty($escape)) {
			$string .= " ESCAPE '{$escape[0]}'";
		}

		return $string;
	}

	/**
	 * This function allows for the ability to close the connection that was opened.
	 *
	 * @access public
	 * @return boolean                           whether an open connection was closed
	 *
	 * @see http://www.php.net/manual/en/function.pg-close.php
	 */
	public function close() {
		if ($this->is_connected()) {
			if ( ! @pg_close($this->link_id)) {
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
	 * @see http://www.php.net/manual/en/function.pg-close.php
	 */
	public function __destruct() {
		if (is_resource($this->link_id)) {
			@pg_close($this->link_id);
		}
	}

}
?>