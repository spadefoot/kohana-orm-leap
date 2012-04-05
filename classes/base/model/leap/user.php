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
 * This class represents a record in the "users" table.
 *
 * @package Leap
 * @category Model
 * @version 2012-04-05
 */
class Base_Model_Leap_User extends DB_ORM_Model {

	/**
	 * This constructor instantiates this class.
	 *
	 * @access public
	 */
	public function __construct() {
		parent::__construct();

		$this->fields = array(
			'id' => new DB_ORM_Field_Integer($this, array(
				'max_length' => 11,
				'nullable' => FALSE,
			)),
			'username' => new DB_ORM_Field_String($this, array(
				'max_length' => 50,
				'nullable' => FALSE,
			)),
			'email' => new DB_ORM_Field_String($this, array(
				'max_length' => 255,
				'nullable' => FALSE,
			)),
			'password' => new DB_ORM_Field_String($this, array(
				'max_length' => 255,
				'nullable' => FALSE,
			)),
			// Personal Details
			'firstname' => new DB_ORM_Field_String($this, array(
				'max_length' => 100,
				'nullable' => FALSE,
			)),
			'lastname' => new DB_ORM_Field_String($this, array(
				'max_length' => 100,
				'nullable' => FALSE,
			)),
			// Account Status Details
			'activated' => new DB_ORM_Field_Boolean($this, array(
				'default' => TRUE,
				'nullable' => FALSE,
			)),
			'banned' => new DB_ORM_Field_Boolean($this, array(
				'default' => FALSE,
				'nullable' => FALSE,
			)),
			'ban_reason' => new DB_ORM_Field_String($this, array(
				'max_length' => 255,
				'nullable' => TRUE,
			)),
			// Account Utility Details
			'new_password_key' => new DB_ORM_Field_String($this, array(
				'max_length' => 64,
				'nullable' => TRUE,
			)),
			'new_password_requested' => new DB_ORM_Field_DateTime($this, array(
				'nullable' => TRUE,
			)),
			'new_email' => new DB_ORM_Field_String($this, array(
				'max_length' => 255,
				'nullable' => TRUE,
			)),
			'new_email_key' => new DB_ORM_Field_String($this, array(
				'max_length' => 255,
				'nullable' => TRUE,
			)),
			// Account Metrics Details
			'last_ip' => new DB_ORM_Field_String($this, array(
				'max_length' => 40,
				'nullable' => TRUE
			)),
			'last_login' => new DB_ORM_Field_DateTime($this, array(
				'nullable' => TRUE, // Default set in database
			)),
			'logins' => new DB_ORM_Field_Integer($this, array(
				'max_length' => 11,
				'nullable' => FALSE,
				'default' => 0,
			)),
		);

		$this->adaptors = array(
			'last_login_formatted' => new DB_ORM_Field_Adaptor_DateTime($this, array(
				'field' => 'last_login',
			)),
		);

		$this->relations = array(
			'user_roles' => new DB_ORM_Relation_HasMany($this, array(
				'child_key' => array('uID'),
				'child_model' => 'User_Role',
				'parent_key' => array('uID'),
			)),
			'user_token' => new DB_ORM_Relation_HasMany($this, array(
				'child_key' => array('uID'),
				'child_model' => 'User_Token',
				'parent_key' => array('uID'),
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
		return 'users';	
	}

	/**
	 * This function returns the primary key for the database table.
	 *
	 * @access public
	 * @static
	 * @return array                                the primary key
	 */
	public static function primary_key() {
		return array('id');	
	}

	/**
	 * This function completes the user's login.
	 *
	 * @access public
	 */
	public function complete_login() {
		$this->logins++;
		$this->last_login = time();
		$this->last_ip = Request::$client_ip;
		$this->save();
	}

}
?>