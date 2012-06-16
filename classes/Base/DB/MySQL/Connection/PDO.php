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
 * This class handles a PDO MySQL connection.
 *
 * @package Leap
 * @category MySQL
 * @version 2012-05-22
 *
 * @see http://www.php.net/manual/en/ref.pdo-mysql.connection.php
 *
 * @abstract
 */
abstract class Base_DB_MySQL_Connection_PDO extends DB_SQL_Connection_PDO {

	/**
	 * This function opens a connection using the data source provided.
	 *
	 * @access public
	 * @throws Kohana_Database_Exception        indicates that there is problem with
	 *                                          opening the connection
	 *
	 * @see http://www.php.net/manual/en/ref.pdo-mysql.connection.php
	 */
	public function open() {
		if ( ! $this->is_connected()) {
			try {
				$connection_string  = 'mysql:';
				$connection_string .= 'host=' . $this->data_source->host . ';';
				$port = $this->data_source->port;
				if ( ! empty($port)) {
					$connection_string .= 'port=' . $port . ';';
				}
				$connection_string .= 'dbname=' . $this->data_source->database;
				$username = $this->data_source->username;
				$password = $this->data_source->password;
				$attributes = array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION);
				if ($this->data_source->is_persistent()) {
					$attributes[PDO::ATTR_PERSISTENT] = TRUE;
				}
				$this->connection = new PDO($connection_string, $username, $password, $attributes);
				$this->resource_id = self::$counter++;
			}
			catch (PDOException $ex) {
				$this->connection = NULL;
				throw new Kohana_Database_Exception('Message: Failed to establish connection. Reason: :reason', array(':reason' => $ex->getMessage()));
			}
			if ( ! empty($this->data_source->charset)) {
				$this->execute('SET NAMES ' . $this->quote(strtolower($this->data_source->charset)));
			}
		}
	}

}
?>