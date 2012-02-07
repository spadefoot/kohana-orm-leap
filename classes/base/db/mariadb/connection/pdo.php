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
 * This class handles a PDO MariaDB connection.
 *
 * @package Leap
 * @category MariaDB
 * @version 2012-02-06
 *
 * @see http://www.php.net/manual/en/ref.pdo-mysql.connection.php
 *
 * @abstract
 */
abstract class Base_DB_MariaDB_Connection_PDO extends DB_SQL_Connection_PDO {

	/**
	 * This function allows for the ability to open a connection using
	 * the configurations provided.
	 *
	 * @access public
	 * @throws Kohana_Database_Exception        indicates that there is problem with
	 *                                          the database connection
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
				$this->link_id = self::$counter++;
			}
			catch (PDOException $ex) {
				$this->connection = NULL;
				$this->error = 'Message: Failed to establish connection. Reason: ' . $ex->getMessage();
				throw new Kohana_Database_Exception($this->error, array(':dsn' => $this->data_source->id));
			}
		}
	}

}
?>