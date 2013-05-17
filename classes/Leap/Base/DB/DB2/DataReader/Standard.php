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

namespace Leap\Base\DB\DB2\DataReader {

	/**
	 * This class is used to read data from a DB2 database using the standard
	 * driver.
	 *
	 * @package Leap
	 * @category DB2
	 * @version 2013-03-19
	 *
	 * @see http://php.net/manual/en/ref.ibm-db2.php
	 *
	 * @abstract
	 */
	abstract class Standard extends DB\SQL\DataReader\Standard {

		/**
		 * This function initializes the class.
		 *
		 * @access public
		 * @override
		 * @param DB\Connection\Driver $connection  the connection to be used
		 * @param string $sql                       the SQL statement to be queried
		 * @param integer $mode                     the execution mode to be used
		 * @throws Throwable\SQL\Exception          indicates that the query failed
		 *
		 * @see http://www.php.net/manual/en/function.db2-prepare.php
		 * @see http://www.php.net/manual/en/function.db2-execute.php
		 * @see http://www.php.net/manual/en/function.db2-stmt-error.php
		 */
		public function __construct(DB\Connection\Driver $connection, $sql, $mode = NULL) {
			$resource = $connection->get_resource();
			$command = @db2_prepare($resource, $sql);
			if ($command === FALSE) {
				throw new Throwable\SQL\Exception('Message: Failed to query SQL statement. Reason: :reason', array(':reason' => @db2_conn_errormsg($resource)));
			}
			if ( ! @db2_execute($command)) {
				throw new Throwable\SQL\Exception('Message: Failed to query SQL statement. Reason: :reason', array(':reason' => @db2_stmt_errormsg($command)));
			}
			$this->command = $command;
			$this->record = FALSE;
		}

		/**
		 * This function frees the command reference.
		 *
		 * @access public
		 * @override
		 *
		 * @see http://www.php.net/manual/en/function.db2-free-result.php
		 */
		public function free() {
			if ($this->command !== NULL) {
				@db2_free_result($this->command);
				$this->command = NULL;
				$this->record = FALSE;
			}
		}

		/**
		 * This function advances the reader to the next record.
		 *
		 * @access public
		 * @override
		 * @return boolean                          whether another record was fetched
		 *
		 * @see http://www.php.net/manual/en/function.db2-fetch-assoc.php
		 */
		public function read() {
			$this->record = @db2_fetch_assoc($this->command);
			return ($this->record !== FALSE);
		}

	}

}