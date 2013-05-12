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
 * This class handles a PDO Oracle connection.
 *
 * @package Leap
 * @category Oracle
 * @version 2013-01-28
 *
 * @see http://www.php.net/manual/en/ref.pdo-oci.php
 *
 * @abstract
 */
abstract class Base\DB\Oracle\Connection\PDO extends DB\SQL\Connection\PDO {

	/**
	 * This function processes an SQL statement that will NOT return data.
	 *
	 * @access public
	 * @override
	 * @param string $sql						    the SQL statement
	 * @throws Throwable\SQL\Exception              indicates that the executed
	 *                                              statement failed
	 */
	public function execute($sql) {
		parent::execute($this->trim($sql));
	}

	/**
	 * This function opens a connection using the data source provided.
	 *
	 * @access public
	 * @override
	 * @throws Throwable\Database\Exception         indicates that there is problem with
	 *                                              opening the connection
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
				$attributes = array(\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION);
				if ($this->data_source->is_persistent()) {
					$attributes[\PDO::ATTR_PERSISTENT] = TRUE;
				}
				$this->resource = new \PDO($connection_string, $this->data_source->username, $this->data_source->password, $attributes);
			}
			catch (\PDOException $ex) {
				$this->resource = NULL;
				throw new Throwable\Database\Exception('Message: Failed to establish connection. Reason: :reason', array(':reason' => $ex->getMessage()));
			}
		}
	}

	/**
	 * This function processes an SQL statement that will return data.
	 *
	 * @access public
	 * @override
	 * @param string $sql						    the SQL statement
	 * @param string $type						    the return type to be used
	 * @return DB\ResultSet                         the result set
	 * @throws Throwable\SQL\Exception              indicates that the query failed
	 */
	public function query($sql, $type = 'array') {
		return parent::query($this->trim($sql), $type);
	}

	/**
	 * This function creates a data reader for query the specified SQL statement.
	 *
	 * @access public
	 * @override
	 * @param string $sql						    the SQL statement
	 * @return DB\SQL\DataReader                    the SQL data reader
	 * @throws Throwable\SQL\Exception              indicates that the query failed
	 */
	public function reader($sql) {
		return parent::reader($this->trim($sql));
	}

	/**
	 * This function trims the semicolon off an SQL statement.
	 *
	 * @access protected
	 * @param string $sql						    the SQL statement
	 * @return string                               the SQL statement after being trimmed
	 */
	protected function trim($sql) {
		return trim($sql, "; \t\n\r\0\x0B");
	}

}
