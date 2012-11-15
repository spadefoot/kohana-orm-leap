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
 * This class provides a shortcut way to get the appropriate ORM builder class.
 *
 * @package Leap
 * @category ORM
 * @version 2012-11-14
 *
 * @abstract
 */
abstract class Base_DB_ORM extends Core_Object {

	/**
	 * This function returns an instance of the DB_ORM_Delete_Proxy.
	 *
	 * @access public
	 * @static
	 * @param string $model                 the model's name
	 * @return DB_ORM_Delete_Proxy          an instance of the class
	 */
	public static function delete($model) {
		$proxy = new DB_ORM_Delete_Proxy($model);
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
	 * @return DB_SQL_Expression                    the wrapped expression
	 */
	public static function expr($expr, Array $params = array()) {
		$expression = new DB_SQL_Expression($expr, $params);
		return $expression;
	}

	/**
	 * This function returns an instance of the DB_ORM_Insert_Proxy.
	 *
	 * @access public
	 * @static
	 * @param string $model                 the model's name
	 * @return DB_ORM_Insert_Proxy          an instance of the class
	 */
	public static function insert($model) {
		$proxy = new DB_ORM_Insert_Proxy($model);
		return $proxy;
	}

	/**
	 * This function returns an instance of the specified model.
	 *
	 * @access public
	 * @static
	 * @param string $model                 the model's name
	 * @param array $primary_key            the column values of the primary key
	 *                                      that will be used to load the model
	 * @return mixed                        an instance of the specified model
	 */
	public static function model($model, $primary_key = array()) {
		$model = DB_ORM_Model::factory($model);
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
	 * This function returns an instance of the DB_ORM_Select_Proxy.
	 *
	 * @access public
	 * @static
	 * @param string $model                 the model's name
	 * @param array $columns                the columns to be selected
	 * @return DB_ORM_Select_Proxy          an instance of the class
	 */
	public static function select($model, Array $columns = array()) {
		$proxy = new DB_ORM_Select_Proxy($model, $columns);
		return $proxy;
	}

	/**
	 * This function returns an instance of the DB_ORM_Update_Proxy.
	 *
	 * @access public
	 * @static
	 * @param string $model                 the model's name
	 * @return DB_ORM_Update_Proxy          an instance of the class
	 */
	public static function update($model) {
		$proxy = new DB_ORM_Update_Proxy($model);
		return $proxy;
	}

}
?>