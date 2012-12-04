<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Copyright 2011-2012 Spadefoot
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
 * This class is used to read data from an SQL database.
 *
 * @package Leap
 * @category SQL
 * @version 2012-12-04
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
	 * @param mixed $resource                   the resource to be used
	 * @param string $sql                       the SQL statement to be queried
	 * @param integer $mode                     the execution mode to be used
	 */
	public abstract function __construct($resource, $sql, $mode = 32);

	/**
	 * This function frees the command reference.
	 *
	 * @access public
	 * @abstract
	 */
	public abstract function free();

	/**
	 * This function returns the last record fetched.
	 *
	 * @access public
	 * @return array                            the last record fetched
	 */
	public function row($type = 'array') {
		return static::type_cast($type, $this->record);
	}

	/**
	 * This function advances the reader to the next record.
	 *
	 * @access public
	 * @abstract
	 * @return boolean                          whether another record was fetched
	 */
	public abstract function read();

	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This variable stores an array of serialized class objects, which is
	 * used when type casting a result set.
	 *
	 * @access protected
	 * @var array
	 */
	protected static $objects = array();

	/**
	 * This function type casts an associated array to the declared return type.
	 *
	 * @access protected
	 * @static
	 * @param string $type						the return type to be used
	 * @param array $record						the record to be casted
	 * @return mixed                            the casted record
	 *
	 * @see http://www.richardcastera.com/blog/php-convert-array-to-object-with-stdclass
	 * @see http://codeigniter.com/forums/viewthread/103493/
	 */
	protected static function type_cast($type, Array $record) {
		switch ($type) {
			case 'array':
				return $record;
			break;
			case 'object':
				return (object) $record;
			break;
			default:
				if ( ! isset(static::$objects[$type])) {
					$object = new $type();
					static::$objects[$type] = serialize($object);
				}
				else {
					$object = unserialize( (string) static::$objects[$type]);
				}
				foreach ($record as $key => $value) {
					$object->{$key} = $value;
				}
				return $object;
			break;
		}
	}

}
?>