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
 * This class handles a standard Drizzle connection.
 *
 * @package Leap
 * @category Drizzle
 * @version 2012-11-14
 *
 * @see http://devzone.zend.com/1504/getting-started-with-drizzle-and-php/
 * @see https://github.com/barce/partition_benchmarks/blob/master/db.php
 * @see http://plugins.svn.wordpress.org/drizzle/trunk/db.php
 * @see http://ronaldbradford.com/blog/a-beginners-look-at-drizzle-datatypes-and-tables-2009-04-01/
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
	 * This function opens a connection using the data source provided.
	 *
	 * @access public
	 * @throws Throwable_Database_Exception        indicates that there is problem with
	 *                                          opening the connection
	 *
	 * @see http://wiki.drizzle.org/MySQL_Differences
	 */
	public function open() {
		if ( ! $this->is_connected()) {
			$handle = drizzle_create();
			$host = $this->data_source->host;
			$port = $this->data_source->port;
			$database = $this->data_source->database;
			$username = $this->data_source->username;
			$password = $this->data_source->password;
			$this->resource_id = @drizzle_con_add_tcp($handle, $host, $port, $username, $password, $database, 0);
			if ($this->resource_id === FALSE) {
				throw new Throwable_Database_Exception('Message: Failed to establish connection. Reason: :reason', array(':reason' => drizzle_error($handle)));
			}
			// "There is no CHARSET or CHARACTER SET commands, everything defaults to UTF-8."
		}
	}

	/**
	 * This function begins a transaction.
	 *
	 * @access public
	 * @throws Throwable_SQL_Exception             indicates that the executed statement failed
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
	 * @throws Throwable_SQL_Exception             indicates that the query failed
	 */
	public function query($sql, $type = 'array') {
		if ( ! $this->is_connected()) {
			throw new Throwable_SQL_Exception('Message: Failed to query SQL statement. Reason: Unable to find connection.');
		}
		$result_set = $this->cache($sql, $type);
		if ($result_set !== NULL) {
			$this->insert_id = FALSE;
			$this->sql = $sql;
			return $result_set;
		}
		$command_id = @drizzle_query($this->resource_id, $sql);
		if ($command_id === FALSE) {
			throw new Throwable_SQL_Exception('Message: Failed to query SQL statement. Reason: :reason', array(':reason' => drizzle_con_error($this->resource_id)));
		}
		if ( ! @drizzle_result_buffer($command_id)) {
			throw new Throwable_SQL_Exception('Message: Failed to query SQL statement. Reason: :reason', array(':reason' => drizzle_con_error($this->resource_id)));
		}
		$records = array();
		$size = 0;
		if (@drizzle_result_row_count($command_id)) {
			while ($record = drizzle_row_next($command_id)) {
				$records[] = DB_Connection::type_cast($type, $record);
				$size++;
			}
		}
		@drizzle_result_free($command_id);
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
	 * @throws Throwable_SQL_Exception             indicates that the executed statement failed
	 */
	public function execute($sql) {
		if ( ! $this->is_connected()) {
			throw new Throwable_SQL_Exception('Message: Failed to execute SQL statement. Reason: Unable to find connection.');
		}
		$command_id = @drizzle_query($this->resource_id, $sql);
		if ($command_id === FALSE) {
			throw new Throwable_SQL_Exception('Message: Failed to execute SQL statement. Reason: :reason', array(':reason' => drizzle_con_error($this->resource_id)));
		}
		$this->insert_id = (preg_match("/^\\s*(insert|replace) /i", $sql))
			? @drizzle_result_insert_id($command_id)
			: FALSE;
		$this->sql = $sql;
		@drizzle_result_free($command_id);
	}

	/**
	 * This function returns the last insert id.
	 *
	 * @access public
	 * @return integer                          the last insert id
	 * @throws Throwable_SQL_Exception             indicates that the query failed
	 */
	public function get_last_insert_id() {
		if ( ! $this->is_connected()) {
			throw new Throwable_SQL_Exception('Message: Failed to fetch the last insert id. Reason: Unable to find connection.');
		}
		if ($this->insert_id === FALSE) {
			throw new Throwable_SQL_Exception('Message: Failed to fetch the last insert id. Reason: No insert id could be derived.');
		}
		return $this->insert_id;
	}

	/**
	 * This function rollbacks a transaction.
	 *
	 * @access public
	 * @throws Throwable_SQL_Exception             indicates that the executed statement failed
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
	 * @throws Throwable_SQL_Exception             indicates that the executed statement failed
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
	 * @throws Throwable_SQL_Exception             indicates that no connection could
	 *                                          be found
	 */
	public function quote($string, $escape = NULL) {
		if ( ! $this->is_connected()) {
			throw new Throwable_SQL_Exception('Message: Failed to quote/escape string. Reason: Unable to find connection.');
		}

		$string = "'" . drizzle_escape_string($this->resource_id, $string) . "'";

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
	 */
	public function close() {
		if ($this->is_connected()) {
			if ( ! @drizzle_con_close($this->resource_id)) {
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
	 */
	public function __destruct() {
		if (is_resource($this->resource_id)) {
			@drizzle_con_close($this->resource_id);
		}
	}

}
?>