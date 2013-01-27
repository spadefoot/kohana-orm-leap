<?php defined('SYSPATH') OR die('No direct script access.');

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
 * This class is used to read data from an SQL database.
 *
 * @package Leap
 * @category SQL
 * @version 2013-01-22
 *
 * @abstract
 */
abstract class Base_DB_SQL_DataReader extends Core_Object {

	/**
	 * This variable stores the command reference being utilized.
	 *
	 * @access protected
	 * @var resource
	 */
	protected $command;

	/**
	 * This variable stores the last record fetched.
	 *
	 * @access protected
	 * @var array
	 */
	protected $record;

	/**
	 * This function initializes the class.
	 *
	 * @access public
	 * @abstract
	 * @param DB_Connection_Driver $connection  the connection to be used
	 * @param string $sql                       the SQL statement to be queried
	 * @param integer $mode                     the execution mode to be used
	 */
	public abstract function __construct(DB_Connection_Driver $connection, $sql, $mode = NULL);

	/**
	 * This destructor ensures that the command reference has been freed.
	 *
	 * @access public
	 */
	public function __destruct() {
		$this->free();
	}

	/**
	 * This function frees the command reference.
	 *
	 * @access public
	 * @abstract
	 */
	public abstract function free();

	/**
	 * This function advances the reader to the next record.
	 *
	 * @access public
	 * @abstract
	 * @return boolean                          whether another record was fetched
	 */
	public abstract function read();

	/**
	 * This function returns the last record fetched.
	 *
	 * @access public
	 * @return array                            the last record fetched
	 *
	 * @see http://www.richardcastera.com/blog/php-convert-array-to-object-with-stdclass
	 * @see http://codeigniter.com/forums/viewthread/103493/
	 */
	public function row($type = 'array') {
		switch ($type) {
			case 'array':
				return $this->record;
			case 'object':
				return (object) $this->record;
			default:
				if ( ! isset(static::$objects[$type])) {
					$object = new $type();
					static::$objects[$type] = serialize($object);
				}
				else {
					$object = unserialize( (string) static::$objects[$type]);
				}
				foreach ($this->record as $key => $value) {
					$object->{$key} = $value;
				}
				return $object;
		}
	}

	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This variable stores an array of serialized class objects, which is
	 * used when type casting a result set.
	 *
	 * @access protected
	 * @static
	 * @var array
	 */
	protected static $objects = array();

	/**
	 * This function returns an instance of the appropriate SQL data reader.
	 *
	 * @access public
	 * @static
	 * @param DB_Connection_Driver $connection         the connection to be used
	 * @param string $sql                              the SQL statement to be queried
	 * @param integer $mode                            the execution mode to be used
	 * @return DB_SQL_DataReader                       an instance of the appropriate
	 *                                                 SQL data reader
	 */
	public static function factory(DB_Connection_Driver $connection, $sql, $mode = NULL) {
		$class = 'DB_' . $connection->data_source->dialect . '_DataReader_' . $connection->data_source->driver;
		$reader = new $class($connection, $sql, $mode);
		return $reader;
	}

}
