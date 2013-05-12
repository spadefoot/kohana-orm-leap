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
 * This class handles a PDO SQLite connection.
 *
 * @package Leap
 * @category SQLite
 * @version 2012-12-11
 *
 * @see http://www.php.net/manual/en/ref.pdo-sqlite.php
 *
 * @abstract
 */
abstract class Base\DB\SQLite\Connection\PDO extends DB\SQL\Connection\PDO {

	/**
	 * This function opens a connection using the data source provided.
	 *
	 * @access public
	 * @override
	 * @throws Throwable\Database\Exception        indicates that there is problem with
	 *                                             opening the connection
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
				$attributes = array(\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION);
				if ($this->data_source->is_persistent()) {
					$attributes[\PDO::ATTR_PERSISTENT] = TRUE;
				}
				$this->resource = new \PDO($connection_string, '', '', $attributes);
			}
			catch (\PDOException $ex) {
				$this->resource = NULL;
				throw new Throwable\Database\Exception('Message: Failed to establish connection. Reason: :reason', array(':reason' => $ex->getMessage()));
			}
			// "Once an encoding has been set for a database, it cannot be changed."
		}
	}

}
