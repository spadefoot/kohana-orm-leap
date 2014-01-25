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
 * This class handles a standard PostgreSQL connection.
 *
 * @package Leap
 * @category PostgreSQL
 * @version 2013-03-12
 *
 * @see http://php.net/manual/en/ref.pgsql.php
 *
 * @abstract
 */
abstract class Base\DB\PostgreSQL\Connection\Standard extends DB\SQL\Connection\Standard {

	/**
	 * This destructor ensures that the connection is closed.
	 *
	 * @access public
	 * @override
	 *
	 * @see http://www.php.net/manual/en/function.pg-close.php
	 */
	public function __destruct() {
		if (is_resource($this->resource)) {
			@pg_close($this->resource);
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
	 * @see http://www.postgresql.org/docs/8.3/static/sql-start-transaction.html
	 */
	public function begin_transaction() {
		$this->execute('START TRANSACTION;');
	}

	/**
	 * This function closes an open connection.
	 *
	 * @access public
	 * @override
	 * @return boolean                              whether an open connection was closed
	 *
	 * @see http://www.php.net/manual/en/function.pg-close.php
	 */
	public function close() {
		if ($this->is_connected()) {
			if ( ! @pg_close($this->resource)) {
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
		$this->execute('COMMIT;');
	}

	/**
	 * This function allows for the ability to process a query that will not return
	 * data using the passed string.
	 *
	 * @access public
	 * @override
	 * @param string $sql                           the SQL statement
	 * @throws Throwable\SQL\Exception              indicates that the executed
	 *                                              statement failed
	 *
	 * @see http://www.php.net/manual/en/function.pg-insert.php
	 */
	public function execute($sql) {
		if ( ! $this->is_connected()) {
			throw new Throwable\SQL\Exception('Message: Failed to execute SQL statement. Reason: Unable to find connection.');
		}
		$command = @pg_query($this->resource, $sql);
		if ($command === FALSE) {
			throw new Throwable\SQL\Exception('Message: Failed to execute SQL statement. Reason: :reason', array(':reason' => @pg_last_error($this->resource)));
		}
		$this->sql = $sql;
		@pg_free_result($command);
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
	 * @see http://www.php.net/manual/en/function.pg-last-oid.php
	 * @see https://github.com/spadefoot/kohana-orm-leap/issues/44
	 */
	public function get_last_insert_id($table = NULL, $column = 'id') {
		if ( ! $this->is_connected()) {
			throw new Throwable\SQL\Exception('Message: Failed to fetch the last insert id. Reason: Unable to find connection.');
		}
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
			// Option #1: Using 'SELECT LASTVAL();'

			$command = @pg_query($this->resource, 'SELECT LASTVAL();');

			if ($command === FALSE) {
				throw new Throwable\SQL\Exception('Message: Failed to fetch the last insert id. Reason: :reason', array(':reason' => @pg_last_error($this->resource)));
			}

			$record = @pg_fetch_row($command);

			if ($record === FALSE) {
				throw new Throwable\SQL\Exception('Message: Failed to fetch the last insert id. Reason: :reason', array(':reason' => @pg_last_error($this->resource)));
			}

			return $record[0];

			// Option #2: Using pg_last_oid($this->resource)

			//$id = @pg_last_oid($this->resource);

			//if ($id === FALSE) {
			//	throw new Throwable\SQL\Exception('Message: Failed to fetch the last insert id. Reason: :reason', array(':reason' => @pg_last_error($this->resource)));
			//}

			//return $id;
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
			$this->resource = ($this->data_source->is_persistent())
				? @pg_pconnect($connection_string)
				: @pg_connect($connection_string, PGSQL_CONNECT_FORCE_NEW);
			if ($this->resource === FALSE) {
				throw new Throwable\Database\Exception('Message: Failed to establish connection. Reason: :reason', array(':reason' => @pg_last_error()));
			}
			if ( ! empty($this->data_source->charset) AND abs(pg_set_client_encoding($this->resource, strtoupper($this->data_source->charset)))) {
				throw new Throwable\Database\Exception('Message: Failed to set character set. Reason: :reason', array(':reason' => @pg_last_error($this->resource)));
			}
		}
	}

	/**
	 * This function escapes a string to be used in an SQL statement.
	 *
	 * @access public
	 * @override
	 * @param string $string                        the string to be escaped
	 * @param char $escape                          the escape character
	 * @return string                               the quoted string
	 * @throws Throwable\SQL\Exception              indicates that no connection could
	 *                                              be found
	 *
	 * @see http://www.php.net/manual/en/function.pg-escape-string.php
	 */
	public function quote($string, $escape = NULL) {
		if ( ! $this->is_connected()) {
			throw new Throwable\SQL\Exception('Message: Failed to quote/escape string. Reason: Unable to find connection.');
		}

		$string = "'" . pg_escape_string($this->resource, $string) . "'";

		if (is_string($escape) OR ! empty($escape)) {
			$string .= " ESCAPE '{$escape}'";
		}

		return $string;
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
