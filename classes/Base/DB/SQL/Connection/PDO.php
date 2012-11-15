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
 * This class handles a PDO connection.
 *
 * @package Leap
 * @category PDO
 * @version 2012-08-16
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
	 * @throws Throwable_SQL_Exception             indicates that the executed statement failed
	 *
	 * @see http://www.php.net/manual/en/pdo.begintransaction.php
	 */
	public function begin_transaction() {
		try {
			$this->connection->beginTransaction();
		}
		catch (Exception $ex) {
			throw new Throwable_SQL_Exception('Message: Failed to begin SQL transaction. Reason: :reason', array(':reason' => $ex->getMessage()));
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
		$command = @$this->connection->query($sql);
		if ($command === FALSE) {
			throw new Throwable_SQL_Exception('Message: Failed to query SQL statement. Reason: :reason', array(':reason' => $this->connection->errorInfo()));
		}
		$records = array();
		$size = 0;
		while ($record = $command->fetch(PDO::FETCH_ASSOC)) {
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
	 * @throws Throwable_SQL_Exception             indicates that the executed statement failed
	 */
	public function execute($sql) {
		if ( ! $this->is_connected()) {
			throw new Throwable_SQL_Exception('Message: Failed to execute SQL statement. Reason: Unable to find connection.');
		}
		$command = @$this->connection->exec($sql);
		if ($command === FALSE) {
			throw new Throwable_SQL_Exception('Message: Failed to execute SQL statement. Reason: :reason', array(':reason' => $this->connection->errorInfo()));
		}
		$this->sql = $sql;
	}

	/**
	 * This function returns the last insert id.
	 *
	 * @access public
	 * @return integer                          the last insert id
	 * @throws Throwable_SQL_Exception             indicates that the query failed
	 *
	 * @see http://www.php.net/manual/en/pdo.lastinsertid.php
	 */
	public function get_last_insert_id() {
		if ( ! $this->is_connected()) {
			throw new Throwable_SQL_Exception('Message: Failed to fetch the last insert id. Reason: Unable to find connection.');
		}
		try {
			return $this->connection->lastInsertId();
		}
		catch (Exception $ex) {
			throw new Throwable_SQL_Exception('Message: Failed to fetch the last insert id. Reason: :reason', array(':reason' => $ex->getMessage()));
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
	 * @throws Throwable_SQL_Exception             indicates that the executed statement failed
	 *
	 * @see http://www.php.net/manual/en/pdo.rollback.php
	 */
	public function rollback() {
		try {
			$this->connection->rollBack();
		}
		catch (Exception $ex) {
			throw new Throwable_SQL_Exception('Message: Failed to rollback SQL transaction. Reason: :reason', array(':reason' => $ex->getMessage()));
		}
	}

	/**
	 * This function commits a transaction.
	 *
	 * @access public
	 * @throws Throwable_SQL_Exception             indicates that the executed statement failed
	 *
	 * @see http://www.php.net/manual/en/pdo.commit.php
	 */
	public function commit() {
		try {
			$this->connection->commit();
		}
		catch (Exception $ex) {
			throw new Throwable_SQL_Exception('Message: Failed to commit SQL transaction. Reason: :reason', array(':reason' => $ex->getMessage()));
		}
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
	 * @see http://www.php.net/manual/en/mbstring.supported-encodings.php
	 */
	public function quote($string, $escape = NULL) {
		if ( ! $this->is_connected()) {
			throw new Throwable_SQL_Exception('Message: Failed to quote/escape string. Reason: Unable to find connection.');
		}

		$string = $this->connection->quote($string);

		if (is_string($escape) OR ! empty($escape)) {
			$string .= " ESCAPE '{$escape}'";
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
		if ($this->connection !== NULL) {
		   unset($this->connection);
		}
	}

}
?>