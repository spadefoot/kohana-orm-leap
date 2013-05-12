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
 * This class handles a PDO MS SQL connection.
 *
 * @package Leap
 * @category MS SQL
 * @version 2013-02-02
 *
 * @see http://www.php.net/manual/en/ref.pdo-dblib.php
 *
 * @abstract
 */
abstract class Base\DB\MsSQL\Connection\PDO extends DB\SQL\Connection\PDO {

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
	 * @see http://www.php.net/manual/en/pdo.lastinsertid.php
	 */
	public function get_last_insert_id($table = NULL, $column = 'id') {
		if ( ! $this->is_connected()) {
			throw new Throwable\SQL\Exception('Message: Failed to fetch the last insert id. Reason: Unable to find connection.');
		}
		try {
			if (is_string($table)) {
				$sql = $this->sql;
				$precompiler = DB\SQL::precompiler($this->data_source);
				$table = $precompiler->prepare_identifier($table);
				$column = $precompiler->prepare_identifier($column);
				$alias = $precompiler->prepare_alias('id');
				$id = (int) $this->query("SELECT MAX({$column}) AS {$alias} FROM {$table};")->get('id', 0);
				$this->sql = $sql;
				return $id;
			}
			return (int) $this->query('SELECT SCOPE_IDENTITY() AS [id];')->get('id', 0);
		}
		catch (\Exception $ex) {
			throw new Throwable\SQL\Exception('Message: Failed to fetch the last insert id. Reason: :reason', array(':reason' => $ex->getMessage()));
		}
	}

	/**
	 * This function opens a connection using the data source provided.
	 *
	 * @access public
	 * @override
	 * @throws Throwable\Database\Exception     indicates that there is problem with
	 *                                          opening the connection
	 *
	 * @see http://www.php.net/manual/en/ref.pdo-dblib.php
	 */
	public function open() {
		if ( ! $this->is_connected()) {
			try {
				$connection_string  = 'mssql:';
				$connection_string .= 'host=' . $this->data_source->host;
				$port = $this->data_source->port;
				if ( ! empty($port)) {
					$connection_string .= ':' . $port;
					// $connection_string .= ',' . $port;
				}
				$connection_string .= ';';
				$connection_string .= 'dbname=' . $this->data_source->database;
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
			//if ( ! empty($this->data_source->charset)) {
			//    ini_set('mssql.charset', $this->data_source->charset);
			//}
		}
	}

}
