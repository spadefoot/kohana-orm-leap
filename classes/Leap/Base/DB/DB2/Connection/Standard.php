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

namespace Leap\Base\DB\DB2\Connection {

	/**
	 * This class handles a standard DB2 connection.
	 *
	 * @package Leap
	 * @category DB2
	 * @version 2013-01-27
	 *
	 * @see http://php.net/manual/en/ref.ibm-db2.php
	 *
	 * @abstract
	 */
	abstract class Standard extends DB\SQL\Connection\Standard {

		/**
		 * This destructor ensures that the connection is closed.
		 *
		 * @access public
		 * @override
		 *
		 * @see http://www.php.net/manual/en/function.db2-close.php
		 */
		public function __destruct() {
			if (is_resource($this->resource)) {
				@db2_close($this->resource);
			}
		}

		/**
		 * This function begins a transaction.
		 *
		 * @access public
		 * @override
		 * @throws Throwable\SQL\Exception              indicates that the executed
		 *                                              statement failed
		 *
		 * @see http://www.php.net/manual/en/function.db2-autocommit.php
		 */
		public function begin_transaction() {
			if ( ! $this->is_connected()) {
				throw new Throwable\SQL\Exception('Message: Failed to begin SQL transaction. Reason: Unable to find connection.');
			}
			$command = @db2_autocommit($this->resource, DB2_AUTOCOMMIT_OFF);
			if ($command === FALSE) {
				throw new Throwable\SQL\Exception('Message: Failed to begin SQL transaction. Reason: :reason', array(':reason' => @db2_conn_error($this->resource)));
			}
			$this->sql = 'BEGIN TRANSACTION;';
		}

		/**
		 * This function closes an open connection.
		 *
		 * @access public
		 * @override
		 * @return boolean                              whether an open connection was closed
		 *
		 * @see http://www.php.net/manual/en/function.db2-close.php
		 */
		public function close() {
			if ($this->is_connected()) {
				if ( ! @db2_close($this->resource)) {
					return FALSE;
				}
				$this->resource = NULL;
			}
			return TRUE;
		}

		/**
		 * This function commits a transaction.
		 *
		 * @access public
		 * @override
		 * @throws Throwable\SQL\Exception              indicates that the executed
		 *                                              statement failed
		 *
		 * @see http://www.php.net/manual/en/function.db2-commit.php
		 */
		public function commit() {
			if ( ! $this->is_connected()) {
				throw new Throwable\SQL\Exception('Message: Failed to commit SQL transaction. Reason: Unable to find connection.');
			}
			$command = @db2_commit($this->resource);
			if ($command === FALSE) {
				throw new Throwable\SQL\Exception('Message: Failed to commit SQL transaction. Reason: :reason', array(':reason' => @db2_conn_error($this->resource)));
			}
			@db2_autocommit($this->resource, DB2_AUTOCOMMIT_ON);
			$this->sql = 'COMMIT;';
		}

		/**
		 * This function processes an SQL statement that will NOT return data.
		 *
		 * @access public
		 * @override
		 * @param string $sql                           the SQL statement
		 * @throws Throwable\SQL\Exception              indicates that the executed
		 *                                              statement failed
		 *
		 * @see http://www.php.net/manual/en/function.db2-exec.php
		 * @see http://www.php.net/manual/en/function.db2-free-result.php
		 */
		public function execute($sql) {
			if ( ! $this->is_connected()) {
				throw new Throwable\SQL\Exception('Message: Failed to execute SQL statement. Reason: Unable to find connection.');
			}
			$command = @db2_exec($this->resource, $sql);
			if ($command === FALSE) {
				throw new Throwable\SQL\Exception('Message: Failed to execute SQL statement. Reason: :reason', array(':reason' => @db2_stmt_errormsg($command)));
			}
			$this->sql = $sql;
			@db2_free_result($command);
		}

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
		 * @see http://www.php.net/manual/en/function.db2-last-insert-id.php
		 */
		public function get_last_insert_id($table = NULL, $column = 'id') {
			if ( ! $this->is_connected()) {
				throw new Throwable\SQL\Exception('Message: Failed to fetch the last insert id. Reason: Unable to find connection.');
			}
			if (is_string($table)) {
				$sql = $this->sql;
				$precompiler = DB\SQL::precompiler($this->data_source);
				$table = $precompiler->prepare_identifier($table);
				$column = $precompiler->prepare_identifier($column);
				$id = (int) $this->query("SELECT MAX({$column}) AS \"id\" FROM {$table};")->get('id', 0);
				$this->sql = $sql;
				return $id;
			}
			else {
				$id = @db2_last_insert_id($this->resource);
				if ($id === FALSE) {
					throw new Throwable\SQL\Exception('Message: Failed to fetch the last insert id. Reason: :reason', array(':reason' => @db2_conn_error($this->resource)));
				}
				settype($id, 'integer');
				return $id;
			}
		}

		/**
		 * This function opens a connection using the data source provided.
		 *
		 * @access public
		 * @override
		 * @throws Throwable\Database\Exception         indicates that there is problem with
		 *                                              opening the connection
		 *
		 * @see http://www.php.net/manual/en/function.db2-connect.php
		 * @see http://www.php.net/manual/en/function.db2-conn-error.php
		 * @see http://www.zinox.com/node/132
		 * @see http://www.ibm.com/developerworks/data/library/techarticle/dm-0505furlong/
		 */
		public function open() {
			if ( ! $this->is_connected()) {
				$connection_string  = 'DRIVER={IBM DB2 ODBC DRIVER};';
				$connection_string .= 'DATABASE=' . $this->data_source->database . ';';
				$connection_string .= 'HOSTNAME=' . $this->data_source->host . ';';
				$connection_string .= 'PORT=' . $this->data_source->port . ';';
				$connection_string .= 'PROTOCOL=TCPIP;';
				$connection_string .= 'UID=' . $this->data_source->username . ';';
				$connection_string .= 'PWD=' . $this->data_source->password . ';';
				$this->resource = ($this->data_source->is_persistent())
					? @db2_pconnect($connection_string, '', '')
					: @db2_connect($connection_string, '', '');
				if ($this->resource === FALSE) {
					throw new Throwable\Database\Exception('Message: Failed to establish connection. Reason: :reason', array(':reason' => @db2_conn_error()));
				}
				// "To use UTF-8 when talking to a DB2 instance, use the following command from the DB2 home at the command prompt: db2set DB2CODEPAGE=1208"
			}
		}

		/**
		 * This function escapes a string to be used in an SQL statement.
		 *
		 * @access public
		 * @override
		 * @param string $string                        the string to be escaped
		 * @param char $escape                          the escape character
		 * @return string                               the quoted string
		 * @throws Throwable\SQL\Exception              indicates that no connection could
		 *                                              be found
		 *
		 * @see http://www.php.net/manual/en/function.db2-escape-string.php
		 * @see http://publib.boulder.ibm.com/infocenter/db2luw/v8/index.jsp?topic=/com.ibm.db2.udb.doc/admin/c0010966.htm
		 * @see http://www.php.net/manual/en/mbstring.supported-encodings.php
		 */
		public function quote($string, $escape = NULL) {
			if ( ! $this->is_connected()) {
				throw new Throwable\SQL\Exception('Message: Failed to quote/escape string. Reason: Unable to find connection.');
			}

			$string = "'" . db2_escape_string($string) . "'";

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
		 * @throws Throwable\SQL\Exception              indicates that the executed
		 *                                              statement failed
		 *
		 * @see http://www.php.net/manual/en/function.db2-rollback.php
		 */
		public function rollback() {
			if ( ! $this->is_connected()) {
				throw new Throwable\SQL\Exception('Message: Failed to rollback SQL transaction. Reason: Unable to find connection.');
			}
			$command = @db2_rollback($this->resource);
			if ($command === FALSE) {
				throw new Throwable\SQL\Exception('Message: Failed to rollback SQL transaction. Reason: :reason', array(':reason' => @db2_conn_error($this->resource)));
			}
			@db2_autocommit($this->resource, DB2_AUTOCOMMIT_ON);
			$this->sql = 'ROLLBACK;';
		}

	}

}