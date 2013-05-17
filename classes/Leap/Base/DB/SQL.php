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

namespace Leap\Base\DB {

	/**
	 * This class provides a shortcut way to get the appropriate SQL builder class.
	 *
	 * @package Leap
	 * @category SQL
	 * @version 2013-02-03
	 *
	 * @abstract
	 */
	abstract class SQL extends Core\Object {

		/**
		 * This function returns an instance of the DB\SQL\Delete\Proxy.
		 *
		 * @access public
		 * @static
		 * @param mixed $config                         the data source configurations
		 * @return DB\SQL\Delete\Proxy                  an instance of the class
		 */
		public static function delete($config = 'default') {
			$proxy = new DB\SQL\Delete\Proxy($config);
			return $proxy;
		}

		/**
		 * This function will wrap a string so that it can be processed by a query
		 * builder.
		 *
		 * @access public
		 * @static
		 * @param string $expr                          the raw SQL expression
		 * @param array $params                         an associated array of parameter
		 *                                              key/values pairs
		 * @return DB\SQL\Expression                    the wrapped expression
		 */
		public static function expr($expr, Array $params = array()) {
			$expression = new DB\SQL\Expression($expr, $params);
			return $expression;
		}

		/**
		 * This function returns an instance of the DB\SQL\Insert\Proxy.
		 *
		 * @access public
		 * @static
		 * @param mixed $config                         the data source configurations
		 * @return DB\SQL\Insert\Proxy                  an instance of the class
		 */
		public static function insert($config = 'default') {
			$proxy = new DB\SQL\Insert\Proxy($config);
			return $proxy;
		}

		/**
		 * This function returns an instance of the appropriate pre-compiler for the
		 * specified data source/config.
		 *
		 * @access public
		 * @static
		 * @param mixed $config                         the data source configurations
		 * @return DB\SQL\Precompiler                   an instance of the pre-compiler
		 */
		public static function precompiler($config = 'default') {
			$data_source = DB\DataSource::instance($config);
			$precompiler = '\\Leap\\DB\\' . $data_source->dialect . '\\Precompiler';
			$object = new $precompiler($data_source);
			return $object;
		}

		/**
		 * This function returns an instance of the DB\SQL\Select\Proxy.
		 *
		 * @access public
		 * @static
		 * @param mixed $config                         the data source configurations
		 * @param array $columns                        the columns to be selected
		 * @return DB\SQL\Select\Proxy                  an instance of the class
		 */
		public static function select($config = 'default', Array $columns = array()) {
			$proxy = new DB\SQL\Select\Proxy($config, $columns);
			return $proxy;
		}

		/**
		 * This function returns an instance of the DB\SQL\Update\Proxy.
		 *
		 * @access public
		 * @static
		 * @param mixed $config                         the data source configurations
		 * @return DB\SQL\Update\Proxy                  an instance of the class
		 */
		public static function update($config = 'default') {
			$proxy = new DB\SQL\Update\Proxy($config);
			return $proxy;
		}

	}

}