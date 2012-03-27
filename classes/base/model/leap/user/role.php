<?php defined('SYSPATH') or die('No direct script access.');

class Base_Model_Leap_User_Role extends DB_ORM_Model
{
	public function __construct()
	{
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
	
	public static function data_source()
	{
		return 'default';
	}
	
	public static function table()
	{
		return 'user_roles';
	}
	
	public static function primary_key()
	{
		return array('uID', 'rID');	
	}
	
	public static function is_auto_incremented()
	{
		return FALSE;	
	}
}