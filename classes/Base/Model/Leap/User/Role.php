<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Copyright 2012 CubedEye
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
 * This class represents a record in the "user_roles" table.
 *
 * @package Leap
 * @category Model
 * @version 2012-05-15
 *
 * @abstract
 */
abstract class Base_Model_Leap_User_Role extends DB_ORM_Model {

	/**
	 * This constructor instantiates this class.
	 *
	 * @access public
	 */
	public function __construct() {
		parent::__construct();

		$this->fields = array(
			'user_id' => new DB_ORM_Field_Integer($this, array(
				'max_length' => 10,
				'nullable' => FALSE,
				'unsigned' => TRUE,
			)),
			'role_id' => new DB_ORM_Field_Integer($this, array(
				'max_length' => 10,
				'nullable' => FALSE,
				'unsigned' => TRUE,
			)),
		);

		$this->relations = array(
			'user' => new DB_ORM_Relation_BelongsTo($this, array(
				'child_key' => array('user_id'),
				'parent_key' => array('id'),
				'parent_model' => 'User',
			)),
			'role' => new DB_ORM_Relation_BelongsTo($this, array(
				'child_key' => array('role_id'),
				'parent_key' => array('id'),
				'parent_model' => 'Role',			
			)),
		);
	}

	/**
	 * This function returns the data source name.
	 *
	 * @access public
	 * @static
	 * @return string                               the data source name
	 */
	public static function data_source() {
		return 'default';
	}

	/**
	 * This function returns the database table's name.
	 *
	 * @access public
	 * @static
	 * @return string                               the database table's name
	 */
	public static function table() {
		return 'user_roles';
	}

	/**
	 * This function returns the primary key for the database table.
	 *
	 * @access public
	 * @static
	 * @return array                                the primary key
	 */
	public static function primary_key() {
		return array('user_id', 'role_id');	
	}

	/**
	 * This function returns whether the primary key auto increments.
	 *
	 * @access public
	 * @static
	 * @return boolean                              whether the primary key auto increments
	 */
	public static function is_auto_incremented() {
		return FALSE;	
	}

}
?>