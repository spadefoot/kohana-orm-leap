<?php defined('SYSPATH') or die('No direct script access.');

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
 * @version 2012-03-28
 */
class Base_Model_Leap_User_Role extends DB_ORM_Model {

	public function __construct() {
		parent::__construct();

		$this->fields = array(
			'uID' => new DB_ORM_Field_Integer($this, array(
				'max_length' => 11,
				'nullable' => FALSE,
			)),
			'rID' => new DB_ORM_Field_Integer($this, array(
				'max_length' => 255,
				'nullable' => FALSE,
			)),
		);

		$this->aliases = array(
			'user_id' => new DB_ORM_Field_Alias($this, 'uID'),
			'role_id' => new DB_ORM_Field_Alias($this, 'rID'),
		);

		$this->relations = array(
			'user' => new DB_ORM_Relation_BelongsTo($this, array(
				'child_key' => array('uID'),
				'parent_key' => array('uID'),
				'parent_model' => 'User',
			)),
			'role' => new DB_ORM_Relation_BelongsTo($this, array(
				'child_key' => array('rID'),
				'parent_key' => array('rID'),
				'parent_model' => 'Role',			
			)),
		);
	}
	
	public static function data_source() {
		return 'default';
	}
	
	public static function table() {
		return 'user_roles';
	}
	
	public static function primary_key() {
		return array('uID', 'rID');	
	}
	
	public static function is_auto_incremented() {
		return FALSE;	
	}

}
?>