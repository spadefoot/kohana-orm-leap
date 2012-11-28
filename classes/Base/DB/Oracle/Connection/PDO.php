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
 * This class handles a PDO Oracle connection.
 *
 * @package Leap
 * @category Oracle
 * @version 2012-11-28
 *
 * @see http://www.php.net/manual/en/ref.pdo-oci.php
 *
 * @abstract
 */
abstract class Base_DB_Oracle_Connection_PDO extends DB_SQL_Connection_PDO {

	/**
	 * This function opens a connection using the data source provided.
	 *
	 * @access public
	 * @throws Throwable_Database_Exception        indicates that there is problem with
	 *                                          opening the connection
	 *
	 * @see http://www.php.net/manual/en/ref.pdo-oci.php
	 * @see http://www.php.net/manual/en/ref.pdo-oci.connection.php
	 * @see http://docs.oracle.com/cd/B10501_01/server.920/a96529/ch2.htm#100150
	 */
	public function open() {
		if ( ! $this->is_connected()) {
			try {
				$connection_string = 'oci:';
				if ( ! empty($this->data_source->host)) {
					$connection_string .= 'dbname=//' . $this->data_source->host;
					$port = $this->data_source->port; // default port is 1521
					if ( ! empty($port)) {
						$connection_string .= ':' . $port;
					}
					$connection_string .= '/' . $this->data_source->database;
				}
				else {
					$connection_string .= 'dbname='. $this->data_source->database;
				}
				if ( ! empty($this->data_source->charset)) {
				    $connection_string .= ';charset=' . $this->data_source->charset;
				}
				$attributes = array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION);
				if ($this->data_source->is_persistent()) {
					$attributes[PDO::ATTR_PERSISTENT] = TRUE;
				}
				$this->connection = new PDO($connection_string, $this->data_source->username, $this->data_source->password, $attributes);
				$this->resource_id = static::$counter++;
			}
			catch (PDOException $ex) {
				$this->connection = NULL;
				throw new Throwable_Database_Exception('Message: Failed to establish connection. Reason: :reason', array(':reason' => $ex->getMessage()));
			}
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
		$sql = trim($sql, "; \t\n\r\0\x0B");
		$result_set = parent::query($sql, $type);
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
		$sql = trim($sql, "; \t\n\r\0\x0B");
		parent::execute($sql);
	}

}
?>