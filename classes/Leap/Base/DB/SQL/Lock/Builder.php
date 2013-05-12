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
 * This class builds an SQL lock statement.
 *
 * @package Leap
 * @category SQL
 * @version 2013-01-12
 *
 * @abstract
 */
abstract class Base\DB\SQL\Lock\Builder extends Core\Object {

	/**
	 * This variable stores a reference to the database connection.
	 *
	 * @access protected
	 * @var DB\Connection\Driver
	 */
	protected $connection;

	/**
	 * This variable stores the build data for the SQL statement.
	 *
	 * @access protected
	 * @var array
	 */
	protected $data;

	/**
	 * This variable stores a reference to the pre-compiler.
	 *
	 * @access protected
	 * @var DB\SQL\Precompiler
	 */
	protected $precompiler;

	/**
	 * This constructor instantiates this class using the specified data source.
	 *
	 * @access public
	 * @param DB\Connection\Driver $connection         the connection to be used
	 */
	public function __construct(DB\Connection\Driver $connection) {
		$this->connection = $connection;
		$this->precompiler = DB\SQL::precompiler($connection->data_source);
		$this->reset();
	}

	/**
	 * This function acquires the required locks.
	 *
	 * @access public
	 * @abstract
	 * @return DB\SQL\Lock\Builder                     a reference to the current instance
	 */
	public abstract function acquire();

	/**
	 * This function adds a lock definition.
	 *
	 * @access public
	 * @abstract
	 * @param string $table                            the table to be locked
	 * @param array $hints                             the hints to be applied
	 * @return DB\SQL\Lock\Builder                     a reference to the current instance
	 */
	public abstract function add($table, Array $hints = NULL);

	/**
	 * This function releases all acquired locks.
	 *
	 * @access public
	 * @abstract
	 * @param string $method                           the method to be used to release
	 *                                                 the lock(s)
	 * @return DB\SQL\Lock\Builder                     a reference to the current instance
	 */
	public abstract function release($method = '');

	/**
	 * This function resets the current builder.
	 *
	 * @access public
	 * @return DB\SQL\Lock\Builder                      a reference to the current instance
	 */
	public function reset() {
		$this->data = array();
		return $this;
	}

	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This function returns an instance of the appropriate SQL lock builder.
	 *
	 * @access public
	 * @static
	 * @param DB\Connection\Driver $connection         the connection to be used
	 * @return DB\SQL\Lock\Builder                     an instance of the appropriate
	 *                                                 SQL lock builder
	 */
	public static function factory(DB\Connection\Driver $connection) {
		$class = '\\Leap\\DB\\' . $connection->data_source->dialect . '\\Lock\\Builder';
		$builder = new $class($connection);
		return $builder;
	}

}
