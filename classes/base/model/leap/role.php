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
 * This class represents a record in the "roles" table.
 *
 * @package Leap
 * @category Model
 * @version 2012-03-28
 */
class Base_Model_Leap_Role extends DB_ORM_Model {

	public function __construct() {
		parent::__construct();

		$this->fields = array(
			'rID' => new DB_ORM_Field_Integer($this, array(
				'max_length' => 11,
				'nullable' => TRUE,
				//'default' => NULL,
			)),
			'rName' => new DB_ORM_Field_String($this, array(
				'max_length' => 255,
				'nullable' => FALSE,
			)),
			'rDescription' => new DB_ORM_Field_String($this, array(
				'max_length' => 255,
				'nullable' => FALSE,
			)),
		);

		$this->aliases = array(
			'id' => new DB_ORM_Field_Alias($this, 'rID'),
			'name' => new DB_ORM_Field_Alias($this, 'rName'),
			'description' => new DB_ORM_Field_Alias($this, 'rDescription'),
		);

		$this->relations = array(
			'role_users' => new DB_ORM_Relation_HasMany($this, array(
				'child_key' => array('rID'),
				'child_model' => 'User_Role',
				'parent_key' => array('rID'),
			)),	 
		);	
	}
	
	public static function data_source() {
		return 'default';	
	}
	
	public static function table() {
		return 'roles';	
	}
	
	public static function primary_key() {
		return array('rID');	
	}

}
?>