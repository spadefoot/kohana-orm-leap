<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Copyright © 2011–2013 Spadefoot Team.
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
 * @version 2013-01-13
 *
 * @see http://www.php.net/manual/en/book.pdo.php
 * @see http://www.electrictoolbox.com/php-pdo-dsn-connection-string/
 *
 * @abstract
 */
abstract class Base_DB_SQL_Connection_PDO extends DB_Connection_Driver {

	/**
	 * This destructor will ensure that the connection is closed.
	 *
	 * @access public
	 * @override
	 */
	public function __destruct() {
		if ($this->resource !== NULL) {
		   unset($this->resource);
		}
	}

	/**
	 * This function begins a transaction.
	 *
	 * @access public
	 * @override
	 * @throws Throwable_SQL_Exception              indicates that the executed
	 *                                              statement failed
	 *
	 * @see http://www.php.net/manual/en/pdo.begintransaction.php
	 */
	public function begin_transaction() {
		try {
			$this->resource->beginTransaction();
		}
		catch (Exception $ex) {
			throw new Throwable_SQL_Exception('Message: Failed to begin SQL transaction. Reason: :reason', array(':reason' => $ex->getMessage()));
		}
	}
	/**
	 * This function allows for the ability to close the connection that was opened.
	 *
	 * @access public
	 * @override
	 * @return boolean                              whether an open connection was closed
	 */
	public function close() {
		if ($this->is_connected()) {
			unset($this->resource);
			$this->resource = NULL;
		}
		return TRUE;
	}

	/**
	 * This function commits a transaction.
	 *
	 * @access public
	 * @override
	 * @throws Throwable_SQL_Exception              indicates that the executed
	 *                                              statement failed
	 *
	 * @see http://www.php.net/manual/en/pdo.commit.php
	 */
	public function commit() {
		try {
			$this->resource->commit();
		}
		catch (Exception $ex) {
			throw new Throwable_SQL_Exception('Message: Failed to commit SQL transaction. Reason: :reason', array(':reason' => $ex->getMessage()));
		}
	}

	/**
	 * This function processes an SQL statement that will NOT return data.
	 *
	 * @access public
	 * @override
	 * @param string $sql                           the SQL statement
	 * @throws Throwable_SQL_Exception              indicates that the executed
	 *                                              statement failed
	 */
	public function execute($sql) {
		if ( ! $this->is_connected()) {
			throw new Throwable_SQL_Exception('Message: Failed to execute SQL statement. Reason: Unable to find connection.');
		}
		$command = @$this->resource->exec($sql);
		if ($command === FALSE) {
			throw new Throwable_SQL_Exception('Message: Failed to execute SQL statement. Reason: :reason', array(':reason' => $this->resource->errorInfo()));
		}
		$this->sql = $sql;
	}

	/**
	 * This function returns the last insert id.
	 *
	 * @access public
	 * @override
	 * @param string $table                         the table to be queried
	 * @param string $column                        the column representing table's id
	 * @return integer                              the last insert id
	 * @throws Throwable_SQL_Exception              indicates that the query failed
	 *
	 * @see http://www.php.net/manual/en/pdo.lastinsertid.php
	 */
	public function get_last_insert_id($table = NULL, $column = 'id') {
		if ( ! $this->is_connected()) {
			throw new Throwable_SQL_Exception('Message: Failed to fetch the last insert id. Reason: Unable to find connection.');
		}
		try {
			if (is_string($table)) {
				$sql = $this->sql;
				$precompiler = DB_SQL::precompiler($this->data_source);
				$table = $precompiler->prepare_identifier($table);
				$column = $precompiler->prepare_identifier($column);
				$alias = $precompiler->prepare_alias('id');
				$id = (int) $this->query("SELECT MAX({$column}) AS {$alias} FROM {$table};")->get('id', 0);
				$this->sql = $sql;
				return $id;
			}
			return $this->resource->lastInsertId();
		}
		catch (Exception $ex) {
			throw new Throwable_SQL_Exception('Message: Failed to fetch the last insert id. Reason: :reason', array(':reason' => $ex->getMessage()));
		}
	}

	/**
	 * This function is for determining whether a connection is established.
	 *
	 * @access public
	 * @override
	 * @return boolean                              whether a connection is established
	 */
	public function is_connected() {
		return ! empty($this->resource);
	}

	/**
	 * This function escapes a string to be used in an SQL statement.
	 *
	 * @access public
	 * @override
	 * @param string $string                        the string to be escaped
	 * @param char $escape                          the escape character
	 * @return string                               the quoted string
	 * @throws Throwable_SQL_Exception              indicates that no connection could
	 *                                              be found
	 *
	 * @see http://www.php.net/manual/en/mbstring.supported-encodings.php
	 */
	public function quote($string, $escape = NULL) {
		if ( ! $this->is_connected()) {
			throw new Throwable_SQL_Exception('Message: Failed to quote/escape string. Reason: Unable to find connection.');
		}

		$string = $this->resource->quote($string);

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
	 * @throws Throwable_SQL_Exception              indicates that the executed
	 *                                              statement failed
	 *
	 * @see http://www.php.net/manual/en/pdo.rollback.php
	 */
	public function rollback() {
		try {
			$this->resource->rollBack();
		}
		catch (Exception $ex) {
			throw new Throwable_SQL_Exception('Message: Failed to rollback SQL transaction. Reason: :reason', array(':reason' => $ex->getMessage()));
		}
	}

}
