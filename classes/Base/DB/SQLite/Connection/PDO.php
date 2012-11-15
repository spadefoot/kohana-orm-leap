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
 * This class handles a PDO SQLite connection.
 *
 * @package Leap
 * @category SQLite
 * @version 2012-11-14
 *
 * @see http://www.php.net/manual/en/ref.pdo-sqlite.php
 *
 * @abstract
 */
abstract class Base_DB_SQLite_Connection_PDO extends DB_SQL_Connection_PDO {

	/**
	 * This function opens a connection using the data source provided.
	 *
	 * @access public
	 * @throws Throwable_Database_Exception        indicates that there is problem with
	 *                                          opening the connection
	 *
	 * @see http://www.php.net/manual/en/ref.pdo-sqlite.php
	 * @see http://www.sqlite.org/pragma.html#pragma_encoding
	 * @see http://stackoverflow.com/questions/263056/how-to-change-character-encoding-of-a-pdo-sqlite-connection-in-php
	 */
	public function open() {
		if ( ! $this->is_connected()) {
			try {
				$connection_string  = 'sqlite:';
				$connection_string .= $this->data_source->database;
				$attributes = array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION);
				if ($this->data_source->is_persistent()) {
					$attributes[PDO::ATTR_PERSISTENT] = TRUE;
				}
				$this->connection = new PDO($connection_string, '', '', $attributes);
				$this->resource_id = static::$counter++;
			}
			catch (PDOException $ex) {
				$this->connection = NULL;
				throw new Throwable_Database_Exception('Message: Failed to establish connection. Reason: :reason', array(':reason' => $ex->getMessage()));
			}
			// "Once an encoding has been set for a database, it cannot be changed."
		}
	}

}
?>