<?php defined('SYSPATH') or die('No direct script access.');

class Base_Model_Leap_User extends DB_ORM_Model
{
	public function __construct()
	{
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
//Account Metrics Details
			'uLastIp' => new DB_ORM_Field_String($this, array(
				'max_length' => 40,
				'nullable' => TRUE
			)),
			'uLastLogin' => new DB_ORM_Field_DateTime($this, array(
				'nullable' => TRUE, //Default set in database
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
	
	public static function data_source()
	{
		return 'default';	
	}
	
	public static function table()
	{
		return 'users';	
	}
	
	public static function primary_key()
	{
		return array('uID');	
	}
	
	public function complete_login()
	{
		$this->logins++;
		$this->last_login = time();
		$this->last_ip = Request::current()->$client_ip;
		$this->save();
	}
	
	public function save($reload = FALSE)
	{
		parent::save($reload);	
	}
}