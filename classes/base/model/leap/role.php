<?php defined('SYSPATH') or die('No direct script access.');

class Base_Model_Leap_Role extends DB_ORM_Model
{
	public function __construct()
	{
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
	
	public static function data_source()
	{
		return 'default';	
	}
	
	public static function table()
	{
		return 'roles';	
	}
	
	public static function primary_key()
	{
		return array('rID');	
	}
}