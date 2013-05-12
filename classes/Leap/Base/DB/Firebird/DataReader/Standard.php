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
 * This class is used to read data from a Firebird database using the standard
 * driver.
 *
 * @package Leap
 * @category Firebird
 * @version 2013-03-19
 *
 * @abstract
 */
abstract class Base\DB\Firebird\DataReader\Standard extends DB\SQL\DataReader\Standard {

	/**
	 * This variable is used to store the connection's resource.
	 *
	 * @access protected
	 * @var resource
	 */
	protected $resource;

	/**
	 * This variable stores the names of all blob fields.
	 *
	 * @access protected
	 * @var string
	 */
	protected $blobs;

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
		$this->resource = $connection->get_resource();
		$command = @ibase_query($this->resource, $sql);
		if ($command === FALSE) {
			throw new Throwable\SQL\Exception('Message: Failed to query SQL statement. Reason: :reason', array(':reason' => @ibase_errmsg()));
		}
		$this->command = $command;
		$this->record = FALSE;
		$this->blobs = array();
		$count = (int) @ibase_num_fields($command);
		for ($i = 0; $i < $count; $i++) {
			$field = ibase_field_info($command, $i);
			if ($field['type'] == 'BLOB') {
				$this->blobs[] = $field['name'];
			}
		}
	}

	/**
	 * This function frees the command reference.
	 *
	 * @access public
	 * @override
	 */
	public function free() {
		if ($this->command !== NULL) {
			@ibase_free_result($this->command);
			$this->command = NULL;
			$this->record = FALSE;
			$this->blobs = array();
			$this->resource = NULL;
		}
	}

	/**
	 * This function advances the reader to the next record.
	 *
	 * @access public
	 * @override
	 * @return boolean                          whether another record was fetched
	 *
	 * @see http://php.net/manual/en/function.ibase-blob-get.php
	 */
	public function read() {
		$this->record = @ibase_fetch_assoc($this->command);
		if ($this->record !== FALSE) {
			foreach ($this->blobs as $field) {
				$info = @ibase_blob_info($this->resource, $this->record[$field]);
				if (is_array($info) AND ! $info['isnull']) {
					$buffer = '';
					$handle = @ibase_blob_open($this->resource, $this->record[$field]);
					if ($handle !== FALSE) {
						for ($i = 0; $i < $info[1]; $i++) {
							$size = ($i == ($info[1] - 1))
								? $info[0] - ($i * $info[2])
								: $info[2];
							$value = @ibase_blob_get($handle, $size);
							if ($value !== FALSE) {
								$buffer .= $value;
							}
						}
						@ibase_blob_close($handle);
					}
					$this->record[$field] = $buffer;
				}
				else {
					$this->record[$field] = NULL;
				}
			}
			return TRUE;
		}
		return FALSE;
	}

}
