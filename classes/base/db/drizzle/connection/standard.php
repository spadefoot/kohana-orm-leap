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
 * This class handles a standard Drizzle connection.
 *
 * @package Leap
 * @category Drizzle
 * @version 2012-02-09
 *
 * @see http://devzone.zend.com/1504/getting-started-with-drizzle-and-php/
 * @see https://github.com/barce/partition_benchmarks/blob/master/db.php
 * @see http://plugins.svn.wordpress.org/drizzle/trunk/db.php
 *
 * @abstract
 */
abstract class Base_DB_Drizzle_Connection_Standard extends DB_SQL_Connection_Standard {

	/**
	 * This variable stores the last insert id.
	 *
	 * @access protected
	 * @var integer
	 */
	protected $insert_id = FALSE;

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
			$handle = drizzle_create();
			$host = $this->data_source->host;
			$port = $this->data_source->port;
			$database = $this->data_source->database;
			$username = $this->data_source->username;
			$password = $this->data_source->password;
			$this->link_id = @drizzle_con_add_tcp($handle, $host, $port, $username, $password, $database, 0);
			if ($this->link_id === FALSE) {
				$this->error = 'Message: Failed to establish connection. Reason: ' . drizzle_error($handle);
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
	 * @see http://docs.drizzle.org/start_transaction.html
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
	 */
	public function query($sql, $type = 'array') {
		if ( ! $this->is_connected()) {
			$this->error = 'Message: Failed to query SQL statement. Reason: Unable to find connection.';
			throw new Kohana_SQL_Exception($this->error, array(':sql' => $sql, ':type' => $type));
		}
		$result_set = $this->cache($sql, $type);
		if ( ! is_null($result_set)) {
			$this->insert_id = FALSE;
			$this->sql = $sql;
			return $result_set;
		}
		$resource_id = @drizzle_query($this->link_id, $sql);
		if ($resource_id === FALSE) {
			$this->error = 'Message: Failed to query SQL statement. Reason: ' . drizzle_con_error($this->link_id);
			throw new Kohana_SQL_Exception($this->error, array(':sql' => $sql, ':type' => $type));
		}
		if ( ! @drizzle_result_buffer($resource_id)) {
			$this->error = 'Message: Failed to query SQL statement. Reason: ' . drizzle_con_error($this->link_id);
			throw new Kohana_SQL_Exception($this->error, array(':sql' => $sql, ':type' => $type));
		}
		$records = array();
		$size = 0;
		if (@drizzle_result_row_count($resource_id)) {
			while ($record = drizzle_row_next($resource_id)) {
				$records[] = DB_Connection::type_cast($type, $record);
				$size++;
			}
		}
		@drizzle_result_free($resource_id);
		$result_set = $this->cache($sql, $type, new DB_ResultSet($records, $size, $type));
		$this->insert_id = FALSE;
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
		$resource_id = @drizzle_query($this->link_id, $sql);
		if ($resource_id === FALSE) {
			$this->error = 'Message: Failed to execute SQL statement. Reason: ' . drizzle_con_error($this->link_id);
			throw new Kohana_SQL_Exception($this->error, array(':sql' => $sql));
		}
		$this->insert_id = (preg_match("/^\\s*(insert|replace) /i", $sql))
			? @drizzle_result_insert_id($resource_id)
			: FALSE;
		$this->sql = $sql;
		@drizzle_result_free($resource_id);
	}

	/**
	 * This function returns the last insert id.
	 *
	 * @access public
	 * @return integer                          the last insert id
	 * @throws Kohana_SQL_Exception             indicates that the query failed
	 */
	public function get_last_insert_id() {
		if ($this->insert_id === FALSE) {
			$this->error = 'Message: Failed to fetch the last insert id. Reason: No insert id could be derived.';
			throw new Kohana_SQL_Exception($this->error, array(':sql' => $this->sql));
		}
		return $this->insert_id;
	}

	/**
	 * This function rollbacks a transaction.
	 *
	 * @access public
	 * @throws Kohana_SQL_Exception             indicates that the executed statement failed
	 *
	 * @see http://docs.drizzle.org/rollback.html
	 */
	public function rollback() {
		$this->execute('ROLLBACK;');
	}

	/**
	 * This function commits a transaction.
	 *
	 * @access public
	 * @throws Kohana_SQL_Exception             indicates that the executed statement failed
	 *
	 * @see http://docs.drizzle.org/commit.html
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
	 */
	public function quote($string, $escape = NULL) {
		$string = "'" . drizzle_escape_string($this->link_id, $string) . "'";

		if (is_string($escape) || ! empty($escape)) {
			$string .= " ESCAPE '{$escape[0]}'";
		}

		return $string;
	}

	/**
	 * This function allows for the ability to close the connection that was opened.
	 *
	 * @access public
	 * @return boolean                          whether an open connection was closed
	 */
	public function close() {
		if ($this->is_connected()) {
			if ( ! @drizzle_con_close($this->link_id)) {
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
			@drizzle_con_close($this->link_id);
		}
	}

}
?>