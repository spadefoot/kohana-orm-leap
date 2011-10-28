<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Copyright 2011 Spadefoot
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
 * @version 2011-06-20
 *
 * @see http://www.php.net/manual/en/ref.pdo-oci.php
 *
 * @abstract
 */
abstract class Base_DB_Oracle_Connection_PDO extends DB_SQL_Connection_PDO {

	/**
	 * This function allows for the ability to open a connection using
	 * the configurations provided.
	 *
	 * @access public
	 * @throws Kohana_Database_Exception        indicates that there is problem with
	 *                                          the database connection
	 *
	 * @see http://www.php.net/manual/en/ref.pdo-oci.php
	 */
	public function open() {
		if (!$this->is_connected()) {
			$connection_string  = 'oci:';
			$connection_string .= 'dbname=//'. $this->data_source->get_host_server();
    		$port = $this->data_source->get_port(); // default port is 1521
    		if (!empty($port)) {
    		    $connection_string .= ':' . $port;
    		}
			$connection_string .= '/' . $this->data_source->get_database();
			$username = $this->data_source->get_username();
			$password = $this->data_source->get_password();
			try {
				$this->connection = new PDO($connection_string, $username, $password);
			}
			catch (PDOException $ex) {
				$this->connection = NULL;
				$this->error = 'Message: Failed to establish connection. Reason: ' . $ex->getMessage();
				throw new Kohana_Database_Exception($this->error, array(':source' => $connection_string, ':username' => $username, 'password' => $password));
			}
			$this->link_id = DB_SQL_Connection_PDO::$counter++;
		}
	}

}
?>