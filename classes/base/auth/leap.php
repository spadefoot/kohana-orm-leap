<?php defined("SYSPATH") or die('No direct script access.');

class Base_Auth_Leap extends Auth
{
	
	protected $models = array(
		'role' => 'Role',
		'user' => 'User',
		'token' => 'User_Token',
	);
	
	protected $columns = array(
		'role_id' => 'rID',
		'role_name' => 'rName',
		'token' => 'utToken',
		'user_id' => 'uID',
		'user_username' => 'uUsername',
		'user_email' => 'uEmail',
	);
	
	private $errors = array();
	
	public function __construct($config = NULL)
	{	
		parent::__construct($config);	
		
		if(isset($config['models'])){
			if(isset($config['models']['role']))
				$this->models['role'] = $config['models']['role'];
				
			if(isset($config['models']['user']))
				$this->models['user'] = $config['models']['user'];
			
			if(isset($config['models']['token']))
				$this->models['token'] = $config['models']['token'];
		}
		
		if(isset($config['columns'])){
			if(isset($config['columns']['role_id']))
				$this->columns['role_name'] = $config['columns']['role_id'];
				
			if(isset($config['columns']['role_name']))
				$this->columns['role_name'] = $config['columns']['role_name'];
				
			if(isset($config['columns']['token']))
				$this->columns['token'] = $config['columns']['token'];
			
			if(isset($config['columns']['user_id']))
				$this->columns['user_id'] = $config['columns']['user_id'];
				
			if(isset($config['columns']['user_username']))
				$this->columns['user_username'] = $config['columns']['user_username'];
				
			if(isset($config['columns']['user_email']))
				$this->columns['user_email'] = $config['columns']['user_email'];
		}
		
		if(empty($config['login_with_email']) and empty($config['login_with_username']))
		{
			throw new Kohana_Exception('A valid "login_with" setting must be set in you auth config file.');
		}
	}
	
	public function logged_in($roles = NULL, $all_required = TRUE)
	{
		$user = $this->get_user();
		$user_model = 'Model_Leap_'.$this->models['user'];
		$role_model = 'Model_Leap_'.$this->models['role'];
				
		if($user instanceof $user_model and $user->is_loaded())
		{
			if(!$roles)
				return TRUE;
				
			// Make array of user roles ids.
			$user_roles = array();
			foreach($user->user_roles as $user_role)
			{
				$_role = $user_role->role;
				if($_role instanceof $role_model)
				{
					array_push($user_roles, $_role->id);
				}
			}
			
			if(is_array($roles))
			{
				$status = (bool)$all_required;
						
				foreach($roles as $role)
				{
					if(!is_object($role))// or !($role instanceof Model_Leap_Role))
						$role = DB_ORM::select($this->models['role'])
										->where($this->columns['role_name'],DB_SQL_Operator::_EQUAL_TO_,$role)
										->query(1);
									
					if(!$role or (($role instanceof $role_model) and !in_array($role->id, $user_roles))){
						$status = FALSE;
						if($all_required){
							break;
						}
					}elseif(!$all_required){
						$status = TRUE;	
						break;
					}
				}
			}else{ //Single Role
				if(!is_object($roles)){// or !($role instanceof Model_Leap_Role))
					$role = DB_ORM::select($this->models['role'])
										->where($this->columns['role_name'],DB_SQL_Operator::_EQUAL_TO_,$roles)
										->query(1);
					$status = FALSE;
					
					if($role and $role instanceof $role_model){
						if(in_array($role->id, $user_roles)){
							$status = TRUE;	
						}
					}
				}	
			}
			return $status;
		}
		return FALSE;
	}
	
	protected function _login($user, $password, $remember)
	{
		if(!is_object($user))
		{	
			$user = $this->get_user_by_login($user);
		}
		
		if(is_string($password))
		{
			$password = $this->hash($password);
		}
		
		if($user) //User Model Found
		{
			if($user->password === $password) //Authentication Successful
			{
				if($user->banned == 1){
					$errors['banned'] = $user->ban_reason;	
				}else{
					if($user->activated == 0 and !empty($this->_config['activation']))
						$errors['not_activated'] = '';
					
					if($remember === TRUE)
					{		
						$token = DB_ORM::model($this->models['token']);
						$token->user_id = $user->id;
						$token->expires = time() + $this->_config['lifetime'];
						$token->user_agent = sha1(Request::$user_agent);
						$token->save();
						
						Cookie::set('authautologin', $token->token, $this->_config['lifetime']);	
					}
				}
					
				// Finish the login
				$this->complete_login($user);
	
				return TRUE;
			}
	
			// Login failed
			return FALSE;	
		}
	}
	
	public function force_login($user, $mark_session_as_forced = TRUE)
	{
		if(!is_object($user))
		{
			$user = $this->get_user_by_login($user);
		}
		
		if ($mark_session_as_forced === TRUE)
		{
			// Mark the session as forced, to prevent users from changing account information
			$this->_session->set('auth_forced', TRUE);
		}

		// Run the standard completion
		$this->complete_login($user);
	}
	
	public function auto_login()
	{
		if ($token = Cookie::get('authautologin'))
		{
			$token = DB_ORM::select($this->models['token'])
				->where($this->columns['token'], DB_SQL_Operator::_EQUAL_TO_, $token)
				->query();
			if ($token->is_loaded() AND $token->fetch(0)->user->is_loaded())
			{
				if ($token->user_agent === sha1(Request::$user_agent))
				{
					// Save the token to create a new unique token
					$token->save();

					// Set the new token
					Cookie::set('authautologin', $token->token, $token->expires - time());

					// Complete the login with the found data
					$this->complete_login($token->fetch(0)->user);

					// Automatic login was successful
					return $token->fetch(0)->user;
				}

				// Token is invalid
				$token->fetch(0)->delete();
			}
		}

		return FALSE;		
	}
	
	public function get_user($default = NULL)
	{
		$user = parent::get_user($default);

		if (!$user)
		{
			// check for "remembered" login
			$user = $this->auto_login();
		}

		return $user;
	}

	public function logout($destroy = FALSE, $logout_all = FALSE)
	{
		// Set by force_login()
		$this->_session->delete('auth_forced');

		if ($token = Cookie::get('authautologin'))
		{
			// Delete the autologin cookie to prevent re-login
			Cookie::delete('authautologin');

			// Clear the autologin token from the database
			$token = DB_ORM::select($this->models['token'])
				->where($this->columns['token'], DB_SQL_Operator::_EQUAL_TO_, $token)
				->query();

			if ($token->is_loaded() AND $logout_all)
			{
				$token = DB_ORM::delete($this->models['token'])
				->where($this->columns['user_id'], DB_SQL_Operator::_EQUAL_TO_, $token->fetch(0)->user)
				->execute();
			}
			elseif ($token->is_loaded())
			{
				$token->fetch(0)->delete();
			}
		}

		return parent::logout($destroy);
	}


	public function password($user)
	{
		if (!is_object($user))
		{
			$user = $this->get_user_by_login($user);
		}

		return $user->password;
	}
		
	protected function complete_login($user)
	{
		$user->complete_login();
		return parent::complete_login($user);
	}
	
	public function check_password($password)
	{
		$user = $this->get_user();

		if ( ! $user)
			return FALSE;

		return ($this->hash($password) === $user->password);
	}
	
	public function get_errors()
	{
		return $this->errors;	
	}
	
	protected function get_user_by_login($user)
	{
		$builder = DB_ORM::select('user');
		if(!empty($this->_config['login_with_email']) and !empty($this->_config['login_with_username'])){
			$builder->where($this->columns['user_username'], DB_SQL_Operator::_EQUAL_TO_, $user);
			$builder->where($this->columns['user_email'], DB_SQL_Operator::_EQUAL_TO_, $user, 'OR');
		}elseif(!empty($this->_config['login_with_email'])){
			$builder->where($this->columns['user_email'], DB_SQL_Operator::_EQUAL_TO_, $user);
		}else{
			$builder->where($this->columns['user_username'], DB_SQL_Operator::_EQUAL_TO_, $user);
		}
		$user = $builder->query()->fetch(0);
		
		return $user;		
	}
}