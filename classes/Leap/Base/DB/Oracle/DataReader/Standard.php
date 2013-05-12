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
 * This class is used to read data from an Oracle database using the standard
 * driver.
 *
 * @package Leap
 * @category Oracle
 * @version 2013-03-19
 *
 * @see http://php.net/manual/en/book.oci8.php
 *
 * @abstract
 */
abstract class Base\DB\Oracle\DataReader\Standard extends DB\SQL\DataReader\Standard {

	/**
	 * This function initializes the class.
	 *
	 * @access public
	 * @override
	 * @param DB\Connection\Driver $connection  the connection to be used
	 * @param string $sql                       the SQL statement to be queried
	 * @param integer $mode                     the execution mode to be used
	 * @throws Throwable\SQL\Exception          indicates that the query failed
	 */
	public function __construct(DB\Connection\Driver $connection, $sql, $mode = NULL) {
		$resource = $connection->get_resource();
		$command = @oci_parse($resource, trim($sql, "; \t\n\r\0\x0B"));
		if ($command === FALSE) {
			$error = @oci_error($resource);
			$reason = (is_array($error) AND isset($error['message']))
				? $error['message']
				: 'Unable to perform command.';
			throw new Throwable\SQL\Exception('Message: Failed to query SQL statement. Reason: :reason', array(':reason' => $reason));
		}
		if ( ! is_integer($mode)) {
			$mode = 32;
		}
		if ( ! oci_execute($command, $mode)) {
			$error = @oci_error($command);
			$reason = (is_array($error) AND isset($error['message']))
				? $error['message']
				: 'Unable to perform command.';
			throw new Throwable\SQL\Exception('Message: Failed to query SQL statement. Reason: :reason', array(':reason' => $reason));
		}
		$this->command = $command;
		$this->record = FALSE;
	}

	/**
	 * This function frees the command reference.
	 *
	 * @access public
	 * @override
	 */
	public function free() {
		if ($this->command !== NULL) {
			@oci_free_statement($this->command);
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
	 */
	public function read() {
		$this->record = @oci_fetch_assoc($this->command);
		return ($this->record !== FALSE);
	}

}
