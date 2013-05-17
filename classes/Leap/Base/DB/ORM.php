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
	 * This class provides a shortcut way to get the appropriate ORM builder class.
	 *
	 * @package Leap
	 * @category ORM
	 * @version 2013-01-28
	 *
	 * @abstract
	 */
	abstract class ORM extends Core\Object {

		/**
		 * This function returns an instance of the DB\ORM\Delete\Proxy.
		 *
		 * @access public
		 * @static
		 * @param string $model                         the model's name
		 * @return DB\ORM\Delete\Proxy                  an instance of the class
		 */
		public static function delete($model) {
			$proxy = new DB\ORM\Delete\Proxy($model);
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
		 * This function returns an instance of the DB\ORM\Insert\Proxy.
		 *
		 * @access public
		 * @static
		 * @param string $model                         the model's name
		 * @return DB\ORM\Insert\Proxy                  an instance of the class
		 */
		public static function insert($model) {
			$proxy = new DB\ORM\Insert\Proxy($model);
			return $proxy;
		}

		/**
		 * This function returns an instance of the specified model.
		 *
		 * @access public
		 * @static
		 * @param string $model                         the model's name
		 * @param array $primary_key                    the column values of the primary key
		 *                                              that will be used to load the model
		 * @return mixed                                an instance of the specified model
		 */
		public static function model($model, $primary_key = array()) {
			$model = DB\ORM\Model::factory($model);
			if ( ! empty($primary_key)) {
				if ( ! is_array($primary_key)) {
					$primary_key = array($primary_key);
				}
				$model_key = $model::primary_key();
				$count = count($model_key);
				for ($i = 0; $i < $count; $i++) {
					$column = $model_key[$i];
					$model->{$column} = $primary_key[$i];
				}
				$model->load();
			}
			return $model;
		}

		/**
		 * This function returns an instance of the appropriate pre-compiler for the
		 * specified model.
		 *
		 * @access public
		 * @static
		 * @param string $model                         the model's name
		 * @return DB\SQL\Precompiler                   an instance of the pre-compiler
		 */
		public static function precompiler($model) {
			$model = DB\ORM\Model::model_name($model);
			$data_source = $model::data_source(DB\DataSource::MASTER_INSTANCE);
			$precompiler = '\\Leap\\DB\\' . $data_source->dialect . '\\Precompiler';
			$object = new $precompiler($data_source);
			return $object;
		}

		/**
		 * This function returns an instance of the DB\ORM\Select\Proxy.
		 *
		 * @access public
		 * @static
		 * @param string $model                         the model's name
		 * @param array $columns                        the columns to be selected
		 * @return DB\ORM\Select\Proxy                  an instance of the class
		 */
		public static function select($model, Array $columns = array()) {
			$proxy = new DB\ORM\Select\Proxy($model, $columns);
			return $proxy;
		}

		/**
		 * This function returns an instance of the DB\ORM\Update\Proxy.
		 *
		 * @access public
		 * @static
		 * @param string $model                         the model's name
		 * @return DB\ORM\Update\Proxy                  an instance of the class
		 */
		public static function update($model) {
			$proxy = new DB\ORM\Update\Proxy($model);
			return $proxy;
		}

	}

}