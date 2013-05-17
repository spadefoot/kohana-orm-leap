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
	 * This class handles a PDO DB2 connection.
	 *
	 * @package Leap
	 * @category DB2
	 * @version 2012-12-11
	 *
	 * @see http://www.php.net/manual/en/ref.pdo-ibm.connection.php
	 *
	 * @abstract
	 */
	abstract class PDO extends DB\SQL\Connection\PDO {

		/**
		 * This function opens a connection using the data source provided.
		 *
		 * @access public
		 * @override
		 * @throws Throwable\Database\Exception     indicates that there is problem with
		 *                                          opening the connection
		 *
		 * @see http://www.php.net/manual/en/ref.pdo-ibm.connection.php
		 * @see http://www.zinox.com/node/132
		 * @see http://www.ibm.com/developerworks/data/library/techarticle/dm-0505furlong/
		 */
		public function open() {
			if ( ! $this->is_connected()) {
				try {
					$connection_string  = 'ibm:';
					$connection_string .= 'DRIVER={IBM DB2 ODBC DRIVER};';
					$connection_string .= 'DATABASE=' . $this->data_source->database . ';';
					$connection_string .= 'HOSTNAME=' . $this->data_source->host . ';';
					$connection_string .= 'PORT=' . $this->data_source->port . ';';
					$connection_string .= 'PROTOCOL=TCPIP;';
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
				// "To use UTF-8 when talking to a DB2 instance, use the following command from the DB2 home at the command prompt: db2set DB2CODEPAGE=1208"
			}
		}

	}

}