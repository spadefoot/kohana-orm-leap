<?php defined('SYSPATH') or die('No direct script access.');

class Base_Model_Leap_Session extends DB_ORM_Model
{
	public function __construct()
	{
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