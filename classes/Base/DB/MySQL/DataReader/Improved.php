<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Copyright 2012 Spadefoot
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
 * This class is used to read data from a MySQL database using the improved
 * driver.
 *
 * @package Leap
 * @category MySQL
 * @version 2012-12-04
 *
 * @see http://www.php.net/manual/en/book.mysqli.php
 *
 * @abstract
 */
abstract class Base_DB_MySQL_DataReader_Improved extends DB_SQL_DataReader_Standard {

	/**
	 * This function initializes the class.
	 *
	 * @access public
	 * @param mixed $resource                   the resource to be used
	 * @param string $sql                       the SQL statement to be queried
	 * @param integer $mode                     the execution mode to be used
	 */
	public function __construct($resource, $sql, $mode = 32) {
		$command = @mysqli_query($resource, $sql);
		if ($command === FALSE) {
			throw new Throwable_SQL_Exception('Message: Failed to query SQL statement. Reason: :reason', array(':reason' => @mysqli_error($resource)));
		}
		$this->command = $command;
		$this->record = FALSE;
	}

	/**
	 * This function frees the command reference.
	 *
	 * @access public
	 */
	public function free() {
		@mysqli_free_result($this->command);
		$this->record = FALSE;
	}

	/**
	 * This function advances the reader to the next record.
	 *
	 * @access public
	 * @return boolean                          whether another record was fetched
	 */
	public function read() {
		$this->record = @mysqli_fetch_assoc($this->command);
		return ($this->record !== FALSE);
	}

}
?>