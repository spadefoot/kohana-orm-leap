<?php defined("SYSPATH") OR die('No direct script access.');

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
 * This class is a driver that handles authentication.
 *
 * @package Leap
 * @category Model
 * @version 2012-11-14
 *
 * @abstract
 */
abstract class Base_Auth_Leap extends Auth {

	/**
	 * This variable stores a list of the models and their respective database
	 * tables.
	 *
	 * @access protected
	 * @var array
	 */
	protected $models = array(
		'role' => 'Role',
		'user' => 'User',
		'token' => 'User_Token',
	);

	/**
	 * This variable stores a list column aliases and their respective database
	 * column names.
	 *
	 * @access protected
	 * @var array
	 */
	protected $columns = array(
		'role_id' => 'id',
		'role_name' => 'name',
		'token' => 'token',
		'user_id' => 'id',
		'user_username' => 'username',
		'user_email' => 'email',
	);

	/**
	 * This variable stores a list of errors that of which have been encountered
	 * during the authentication process.
	 *
	 * @access protected
	 * @var array
	 */
	protected $errors = array();

	/**
	 * This constructor initializes the class using the specified config information.
	 *
	 * @access public
	 * @param mixed $config                     the config information to be used
	 */
	public function __construct($config = NULL) {
		parent::__construct($config);

		if (isset($config['models'])) {
			if (isset($config['models']['role'])) {
				$this->models['role'] = $config['models']['role'];
			}
			if (isset($config['models']['user'])) {
				$this->models['user'] = $config['models']['user'];
			}
			if (isset($config['models']['token'])) {
				$this->models['token'] = $config['models']['token'];
			}
		}

		if (isset($config['columns'])) {
			if (isset($config['columns']['role_id'])) {
				$this->columns['role_id'] = $config['columns']['role_id'];
			}
			if (isset($config['columns']['role_name'])) {
				$this->columns['role_name'] = $config['columns']['role_name'];
			}
			if (isset($config['columns']['token'])) {
				$this->columns['token'] = $config['columns']['token'];
			}
			if (isset($config['columns']['user_id'])) {
				$this->columns['user_id'] = $config['columns']['user_id'];
			}
			if (isset($config['columns']['user_username'])) {
				$this->columns['user_username'] = $config['columns']['user_username'];
			}
			if (isset($config['columns']['user_email'])) {
				$this->columns['user_email'] = $config['columns']['user_email'];
			}
		}

		if (empty($config['login_with_email']) AND empty($config['login_with_username'])) {
			throw new Throwable_Exception('Message: Unable to load configuration. Reason: A valid "login_with" setting must be set in you auth config file.');
		}
	}

	/**
	 * This function determines whether the session is active.
	 *
	 * @access public
	 * @param mixed $roles                      either an ORM role object or an
	 *                                          array of roles
	 * @param boolean $all_required             whether all roles are required
	 * @return boolean                          whether the session is active
	 */
	public function logged_in($roles = NULL, $all_required = TRUE) {
		$user = $this->get_user();

		$user_model = DB_ORM_Model::model_name($this->models['user']);
		$role_model = DB_ORM_Model::model_name($this->models['role']);

		// If there is a user, proceed
		if (($user instanceof $user_model) AND $user->is_loaded()) {
			// If no roles defined then user is just logged in
			if ( ! $roles) {
				return TRUE;
			}

			// Make array of user roles ids.
			$user_roles = array();
			foreach ($user->user_roles as $user_role) {
				$role = $user_role->role;
				if ($role instanceof $role_model) {
					array_push($user_roles, $role->id);
				}
			}

			if (is_array($roles)) { // Multiple roles passed
				$status = (bool) $all_required;

				foreach ($roles as $role) {
					// If you haven't passed in a role object then get it from the DB, by the name passed in the array.
					if ( ! is_object($role)) {
						$role = DB_ORM::select($this->models['role'])
							->where($this->columns['role_name'], DB_SQL_Operator::_EQUAL_TO_, $role)
							->limit(1)
							->query()
							->fetch(0);
					}
					if ( ! $role OR (($role instanceof $role_model) AND ! in_array($role->id, $user_roles))) { // If it's NOT a role or (if it IS a role but NOT in the user's role list)
						$status = FALSE;
						if ($all_required) {
							break;
						}
					}
					else if ( ! $all_required) { // If the role does exist in the users list and $all_required is false, then user has at least one role, so set $status to TRUE and break out of for loop
						$status = TRUE;	
						break;
					}
				}
			}
			else { // Single Role
				if ( ! is_object($roles)) {
					$role = DB_ORM::select($this->models['role'])
						->where($this->columns['role_name'], DB_SQL_Operator::_EQUAL_TO_, $roles)
						->limit(1)
						->query()
						->fetch(0);

					$status = FALSE;

					if ($role AND ($role instanceof $role_model)) {
						if (in_array($role->id, $user_roles)) {
							$status = TRUE;
						}
					}
				}
			}
			return $status;
		}
		return FALSE;
	}

	/**
	 * This function attempts to log a user in.
	 *
	 * @access protected
	 * @param mixed $user                       the user's name or object
	 * @param string $password                  the user's password
	 * @param boolean $remember                 enables auto-login
	 * @return boolean                          whether the login was successful
	 */
	protected function _login($user, $password, $remember) {
		if ( ! is_object($user)) {
			$user = $this->get_user_by_login($user);
		}

		if (is_string($password)) {
			$password = $this->hash($password);
		}

		if ($user) { // User Model Found

			if ($user->password === $password) { // Authentication Successful

				if ($user->banned == 1) {
					$this->errors['banned'] = $user->ban_reason;
				}
				else if (($user->activated == 0) AND ! empty($this->_config['activation'])) {
					$this->errors['not_activated'] = '';
				}
				else {
					if ($remember === TRUE) {
						$token = DB_ORM::model($this->models['token']);
						$token->user_id = $user->id;
						$token->expires = time() + $this->_config['lifetime'];
						$token->user_agent = sha1(Request::$user_agent);
						$token->save();

						Cookie::set('authautologin', $token->token, $this->_config['lifetime']);
					}

					// Finish the login
					$this->complete_login($user);

					return TRUE;
				}
			}

			// Login failed
			return FALSE;
		}
	}

	/**
	 * This function forces a user to be logged in without a password.
	 *
	 * @access public
	 * @param mixed $user                       the user's name or object
	 * @param boolean $mark_session_as_forced   whether to mark the session as
	 *                                          forced
	 * @return boolean                          whether the login was successful
	 */
	public function force_login($user, $mark_session_as_forced = TRUE) {
		if ( ! is_object($user)) {
			$user = $this->get_user_by_login($user);
		}

		if ($mark_session_as_forced === TRUE) {
			// Mark the session as forced, to prevent users from changing account information
			$this->_session->set('auth_forced', TRUE);
		}

		// Run the standard completion
		$success = (bool) $this->complete_login($user);

		return $success;
	}

	/**
	 * This function attempts to log the user in based on the "authautologin" cookie.
	 *
	 * @access public
	 * @return mixed                            either a user object or false
	 */
	public function auto_login() {
		if ($token = Cookie::get('authautologin')) {
			$token = DB_ORM::select($this->models['token'])
				->where($this->columns['token'], DB_SQL_Operator::_EQUAL_TO_, $token)
				->limit(1)
				->query()
				->fetch(0);
			$token_model = DB_ORM_Model::model_name($this->models['token']);
			if (($token instanceof $token_model) AND $token->is_loaded() AND $token->user->is_loaded()) {
				if ($token->user_agent === sha1(Request::$user_agent)) {
					// Save the token to create a new unique token
					$token->save();

					// Set the new token
					Cookie::set('authautologin', $token->token, $token->expires - time());

					// Complete the login with the found data
					$this->complete_login($token->user);

					// Automatic login was successful
					return $token->user;
				}

				// Token is invalid
				$token->delete();
			}
		}
		return FALSE;
	}

	/**
	 * This function gets the current user's object.
	 *
	 * @access public
	 * @param mixed $default                    the default value should no user
	 *                                          be logged in
	 * @return mixed                            either the current user's object
	 *                                          or the specified default value
	 */
	public function get_user($default = NULL) {
		$user = parent::get_user($default);

		if ( ! $user) {
			// check for "remembered" login
			$user = $this->auto_login();
		}

		return $user;
	}

	/**
	 * This function logs the current user out.
	 *
	 * @access public
	 * @param boolean $destroy                  whether the session is to be to completely
	 *                                          destroyed
	 * @param boolean $logout_all               whether all tokens for user are to be removed
	 * @param boolean                           whether the logout was successful
	 */
	public function logout($destroy = FALSE, $logout_all = FALSE) {
		// Set by force_login()
		$this->_session->delete('auth_forced');

		if ($token = Cookie::get('authautologin')) {
			// Delete the autologin cookie to prevent re-login
			Cookie::delete('authautologin');

			// Clear the autologin token from the database
			$token = DB_ORM::select($this->models['token'])
				->where($this->columns['token'], DB_SQL_Operator::_EQUAL_TO_, $token)
				->limit(1)
				->query()
				->fetch(0);
			$token_model = DB_ORM_Model::model_name($this->models['token']);
			if ($logout_all) {
				DB_ORM::delete($this->models['token'])
					->where($this->columns['user_id'], DB_SQL_Operator::_EQUAL_TO_, $token->user)
					->execute();
			}
			else if (($token instanceof $token_model) AND $token->is_loaded()) {
				$token->delete();
			}
		}

		return parent::logout($destroy);
	}

	/**
	 * This function gets the user's password.
	 *
	 * @access public
	 * @param mixed $user                       the user's name or object
	 * @return string                           the user's password
	 */
	public function password($user) {
		if ( ! is_object($user)) {
			$user = $this->get_user_by_login($user);
		}
		return $user->password;
	}

	/**
	 * This function completes the login by incrementing the logins and setting
	 * session data: user_id, username, roles.
	 *
	 * @access protected
	 * @param mixed $user                       the user's name or object
	 * @return boolean                          whether the login was completed
	 */
	protected function complete_login($user) {
		$user->complete_login();
		return parent::complete_login($user);
	}

	/**
	 * This function checks whether the specified password matches the user's defined
	 * password.
	 *
	 * @access public
	 * @param string $password                  the user's password
	 * @return boolean                          whether the password is valid
	 */
	public function check_password($password) {
		$user = $this->get_user();

		if ( ! $user) {
			return FALSE;
		}

		return ($this->hash($password) === $user->password);
	}

	/**
	 * This function return an array of errors encountered during the authentication
	 * process.
	 *
	 * @access public
	 * @return array                            an array of errors encountered during
	 *                                          the authentication process
	 */
	public function get_errors() {
		return $this->errors;
	}

	/**
	 * This function gets a user matching the login configuration information.
	 *
	 * @access protected
	 * @param string $user                      the user's name
	 * @return Model_Leap_User                  the user's object
	 */
	protected function get_user_by_login($user) {
		$builder = DB_ORM::select($this->models['user']);
		if ( ! empty($this->_config['login_with_email']) AND ! empty($this->_config['login_with_username'])) {
			$builder->where($this->columns['user_username'], DB_SQL_Operator::_EQUAL_TO_, $user);
			$builder->where($this->columns['user_email'], DB_SQL_Operator::_EQUAL_TO_, $user, DB_SQL_Connector::_OR_);
		}
		else if ( ! empty($this->_config['login_with_email'])) {
			$builder->where($this->columns['user_email'], DB_SQL_Operator::_EQUAL_TO_, $user);
		}
		else {
			$builder->where($this->columns['user_username'], DB_SQL_Operator::_EQUAL_TO_, $user);
		}
		$user = $builder->query()->fetch(0);
		return $user;
	}

}
?>