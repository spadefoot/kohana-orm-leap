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
 * @version 2012-03-31
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
			'uID' => new DB_ORM_Field_Integer($this, array(
				'max_length' => 11,
				'nullable' => FALSE,
			)),
			'uUsername' => new DB_ORM_Field_String($this, array(
				'max_length' => 50,
				'nullable' => FALSE,
			)),
			'uEmail' => new DB_ORM_Field_String($this, array(
				'max_length' => 255,
				'nullable' => FALSE,
			)),
			'uPassword' => new DB_ORM_Field_String($this, array(
				'max_length' => 255,
				'nullable' => FALSE,
			)),
			// Personal Details
			'uFirstName' => new DB_ORM_Field_String($this, array(
				'max_length' => 100,
				'nullable' => FALSE,
			)),
			'uLastName' => new DB_ORM_Field_String($this, array(
				'max_length' => 100,
				'nullable' => FALSE,
			)),
			// Account Status Details
			'uActivated' => new DB_ORM_Field_Boolean($this, array(
				'default' => TRUE,
				'nullable' => FALSE,
			)),
			'uBanned' => new DB_ORM_Field_Boolean($this, array(
				'default' => FALSE,
				'nullable' => FALSE,
			)),
			'uBanReason' => new DB_ORM_Field_String($this, array(
				'max_length' => 255,
				'nullable' => TRUE,
			)),
			// Account Utility Details
			'uNewPasswordKey' => new DB_ORM_Field_String($this, array(
				'max_length' => 64,
				'nullable' => TRUE,
			)),
			'uNewPasswordRequested' => new DB_ORM_Field_DateTime($this, array(
				'nullable' => TRUE,
			)),
			'uNewEmail' => new DB_ORM_Field_String($this, array(
				'max_length' => 255,
				'nullable' => TRUE,
			)),
			'uNewEmailKey' => new DB_ORM_Field_String($this, array(
				'max_length' => 255,
				'nullable' => TRUE,
			)),
			// Account Metrics Details
			'uLastIp' => new DB_ORM_Field_String($this, array(
				'max_length' => 40,
				'nullable' => TRUE
			)),
			'uLastLogin' => new DB_ORM_Field_DateTime($this, array(
				'nullable' => TRUE, // Default set in database
			)),
			'uLogins' => new DB_ORM_Field_Integer($this, array(
				'max_length' => 11,
				'nullable' => FALSE,
				'default' => 0,
			)),
		);

		$this->aliases = array(
			'id' => new DB_ORM_Field_Alias($this, 'uID'),
			'username' => new DB_ORM_Field_Alias($this, 'uUsername'),
			'email' => new DB_ORM_Field_Alias($this, 'uEmail'),
			'password' => new DB_ORM_Field_Alias($this, 'uPassword'),
			'first_name' => new DB_ORM_Field_Alias($this, 'uFirstName'),
			'last_name' => new DB_ORM_Field_Alias($this, 'uLastName'),
			'activated' => new DB_ORM_Field_Alias($this, 'uActivated'),
			'banned' => new DB_ORM_Field_Alias($this, 'uBanned'),
			'ban_reason' => new DB_ORM_Field_Alias($this, 'uBanReason'),
			'new_password_key' => new DB_ORM_Field_Alias($this, 'uNewPasswordKey'),
			'new_password_requested' => new DB_ORM_Field_Alias($this, 'uNewPasswordRequested'),
			'new_email' => new DB_ORM_Field_Alias($this, 'uNewEmail'),
			'new_email_key' => new DB_ORM_Field_Alias($this, 'uNewEmailKey'),
			'last_ip' => new DB_ORM_Field_Alias($this, 'uLastIp'),
			'logins' => new DB_ORM_Field_Alias($this, 'uLogins'),
		);

		$this->adaptors = array(
			'last_login' => new DB_ORM_Field_Adaptor_DateTime($this, array(
				'field' => 'uLastLogin',
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
		return array('uID');	
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