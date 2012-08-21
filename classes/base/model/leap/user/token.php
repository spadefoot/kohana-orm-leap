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
 * This class represents a record in the "user_tokens" table.
 *
 * @package Leap
 * @category Model
 * @version 2012-08-21
 *
 * @abstract
 */
abstract class Base_Model_Leap_User_Token extends DB_ORM_Model {

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
				'unsigned' => TRUE,
			)),
			'user_id' => new DB_ORM_Field_Integer($this, array(
				'max_length' => 11,
				'nullable' => FALSE,
				'unsigned' => TRUE,
			)),
			'user_agent' => new DB_ORM_Field_String($this, array(
				'max_length' => 40,
				'nullable' => FALSE,
			)),
			'token' => new DB_ORM_Field_String($this, array(
				'max_length' => 40,
				'nullable' => FALSE,
			)),
			'type' => new DB_ORM_Field_String($this, array(
				'max_length' => 100,
				'nullable' => FALSE,
			)),
			'created' => new DB_ORM_Field_Integer($this, array(
				'max_length' => 11,
				'nullable' => FALSE,
				'unsigned' => TRUE,
			)),
			'expires' => new DB_ORM_Field_Integer($this, array(
				'max_length' => 11,
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
		return 'user_tokens';
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
	 * This function saves the record matching using the primary key.
	 *
	 * @access public
	 * @param boolean $reload                       whether the model should be reloaded
	 *                                              after the save is done
	 */
	public function save($reload = FALSE) {
		$this->token = $this->create_token();
		parent::save($reload);
	}

	/**
	 * This function returns a new token.
	 *
	 * @access public
	 * @return string                               a new token
	 */
	public function create_token() {
		do {
			$token = sha1(uniqid(Text::random('alnum', 32), TRUE));
		}
		while(DB_SQL::select($this->data_source())->from($this->table())->where('token', DB_SQL_Operator::_EQUAL_TO_, $token)->query()->is_loaded());
		return $token;
	}

}
?>