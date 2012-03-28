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
 * This class represents a record in the "sessions" table.
 *
 * @package Leap
 * @category Model
 * @version 2012-03-27
 */
class Base_Model_Leap_Session extends DB_ORM_Model {

	public function __construct() {
		parent::__construct();
		$this->fields = array(
			'sesID' => new DB_ORM_Field_String($this, array(
				'max_length' => 24,
				'nullable' => FALSE,
			)),
			'sesLastActive' => new DB_ORM_Field_Integer($this, array(
				'max_length' => 11,
				'nullable' => FALSE,
			)),
			'sesContents' => new DB_ORM_Field_Text($this, array(
				'nullable' => FALSE,
			)),
		);
		
		$this->aliases = array(
			'id' => new DB_ORM_Field_Alias($this, 'sesID'),
			'last_active' => new DB_ORM_Field_Alias($this, 'sesLastActive'),
			'contents' => new DB_ORM_Field_Alias($this, 'sesContents'),
		);
	}
	
	public static function data_source()
	{
		return 'default';	
	}
	
	public static function table()
	{
		return 'sessions';
	}
	
	public static function primary_key()
	{
		return array('sesID');	
	}
	
	public static function is_auto_incremented()
	{
		return FALSE;	
	}
}