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
 * This class handles a PDO connection.
 *
 * @package Leap
 * @category PDO
 * @version 2012-02-09
 *
 * @see http://www.php.net/manual/en/book.pdo.php
 * @see http://www.electrictoolbox.com/php-pdo-dsn-connection-string/
 *
 * @abstract
 */
abstract class Base_DB_SQL_Connection_PDO extends DB_Connection {

	/**
	 * This function stores the number of total connections made.
	 *
	 * @access protected
	 * @var integer
	 */
	protected static $counter = 0;

	/**
	 * This variable stores the PDO connection.
	 *
	 * @access protected
	 * @var PDO
	 */
	protected $connection = NULL;

	/**
	 * This function begins a transaction.
	 *
	 * @access public
	 * @throws Kohana_SQL_Exception             indicates that the executed statement failed
	 *
	 * @see http://www.php.net/manual/en/pdo.begintransaction.php
	 */
	public function begin_transaction() {
		try {
			$this->connection->beginTransaction();
		}
		catch (Exception $ex) {
			$this->error = 'Message: Failed to begin SQL transaction. Reason: ' . $ex->getMessage();
			throw new Kohana_SQL_Exception($this->error, array(':sql' => 'BEGIN TRANSACTION;'));
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
	 * @throws Kohana_SQL_Exception             indicates that the query failed
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
		$result = @$this->connection->query($sql);
		if ($result === FALSE) {
			$this->error = 'Message: Failed to query SQL statement. Reason: ' . $this->connection->errorInfo();
			throw new Kohana_SQL_Exception($this->error, array(':sql' => $sql, ':type' => $type));
		}
		$records = array();
		$size = 0;
		while ($record = $result->fetch(PDO::FETCH_ASSOC)) {
			$records[] = DB_Connection::type_cast($type, $record);
			$size++;
		}
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
	 * @throws Kohana_SQL_Exception             indicates that the executed statement failed
	 */
	public function execute($sql) {
		if ( ! $this->is_connected()) {
			$this->error = 'Message: Failed to execute SQL statement. Reason: Unable to find connection.';
			throw new Kohana_SQL_Exception($this->error, array(':sql' => $sql));
		}
		$result = @$this->connection->exec($sql);
		if ($result === FALSE) {
			$this->error = 'Message: Failed to execute SQL statement. Reason: ' . $this->connection->errorInfo();
			throw new Kohana_SQL_Exception($this->error, array(':sql' => $sql));
		}
		$this->sql = $sql;
	}

	/**
	 * This function returns the last insert id.
	 *
	 * @access public
	 * @return integer                          the last insert id
	 * @throws Kohana_SQL_Exception             indicates that the query failed
	 *
	 * @see http://www.php.net/manual/en/pdo.lastinsertid.php
	 */
	public function get_last_insert_id() {
		try {
			$insert_id = $this->connection->lastInsertId();
			return $insert_id;
		}
		catch (Exception $ex) {
			$this->error = 'Message: Failed to fetch the last insert id. Reason: ' . $ex->getMessage();
			throw new Kohana_SQL_Exception($this->error, array(':sql' => $this->sql));
		}
	}

	/**
	 * This function is for determining whether a connection is established.
	 *
	 * @access public
	 * @return boolean                          whether a connection is established
	 */
	public function is_connected() {
		return ! empty($this->connection);
	}

	/**
	 * This function rollbacks a transaction.
	 *
	 * @access public
	 * @throws Kohana_SQL_Exception             indicates that the executed statement failed
	 *
	 * @see http://www.php.net/manual/en/pdo.rollback.php
	 */
	public function rollback() {
		try {
			$this->connection->rollBack();
		}
		catch (Exception $ex) {
			$this->error = 'Message: Failed to rollback SQL transaction. Reason: ' . $ex->getMessage();
			throw new Kohana_SQL_Exception($this->error, array(':sql' => 'ROLLBACK;'));
		}
	}

	/**
	 * This function commits a transaction.
	 *
	 * @access public
	 * @throws Kohana_SQL_Exception             indicates that the executed statement failed
	 *
	 * @see http://www.php.net/manual/en/pdo.commit.php
	 */
	public function commit() {
		try {
			$this->connection->commit();
		}
		catch (Exception $ex) {
			$this->error = 'Message: Failed to commit SQL transaction. Reason: ' . $ex->getMessage();
			throw new Kohana_SQL_Exception($this->error, array(':sql' => 'COMMIT;'));
		}
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
		$string = $this->connection->quote($string);

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
			unset($this->connection);
			$this->connection = NULL;
		}
		return TRUE;
	}

	/**
	 * This destructor will ensure that the connection is closed.
	 *
	 * @access public
	 */
	public function __destruct() {
		if ( ! is_null($this->connection)) {
		   unset($this->connection);
		}
	}

}
?>