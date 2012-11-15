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
 * This class represents a session.
 *
 * @package Leap
 * @category Session
 * @version 2012-11-14
 *
 * @abstract
 */
abstract class Base_Session_Leap extends Session {

	/**
	 * This variable stores the name of the session model.
	 *
	 * @access protected
	 * @var string
	 */
	protected $_table = 'Session'; // Session Model

	/**
	 * This variable stores a list column aliases and their respective database
	 * column names.
	 *
	 * @access protected
	 * @var array
	 */
	protected $_columns = array(
		'session_id'  => 'id',
		'last_active' => 'last_active',
		'contents'    => 'contents',
	);

	/**
	 * This variable stores how often a garbage collection request is made.
	 *
	 * @access protected
	 * @var integer
	 */
	protected $_gc = 500;

	/**
	 * This variable stores the current session id.
	 *
	 * @access protected
	 * @var string
	 */
	protected $_session_id;

	/**
	 * This variable stores the old session id.
	 *
	 * @access protected
	 * @var string
	 */
	protected $_update_id;

	/**
	 * This constructor initializes the class using the specified config
	 * information and/or session id.
	 *
	 * @access public
	 * @param mixed $config                     the config information to be used
	 * @param string $id                        the session id
	 */
	public function __construct(array $config = NULL, $id = NULL) {
		// Set the table name
		if (isset($config['table'])) {
			$this->_table = (string) $config['table'];
		}

		// Set the gc chance
		if (isset($config['gc'])) {
			$this->_gc = (int) $config['gc'];
		}

		// Overload column names
		if (isset($config['columns'])) {
			$this->_columns = $config['columns'];
		}

		parent::__construct($config, $id);

		// Run garbage collection
		// This will average out to run once every X requests
		if (mt_rand(0, $this->_gc) === $this->_gc) {
			$this->_gc();
		}
	}

	/**
	 * This function returns the current session id.
	 *
	 * @access public
	 * @return string                           the current session id
	 */
	public function id() {
		return $this->_session_id;
	}

	/**
	 * This function returns the raw session data string.
	 *
	 * @access protected
	 * @param string $id                        the session id
	 * @return string                           the raw session data string
	 */
	protected function _read($id = NULL) {
		if ($id OR $id = Cookie::get($this->_name)) {
            
			try {
				$contents = DB_ORM::select($this->_table, array($this->_columns['contents']))
					->where($this->_columns['session_id'], DB_SQL_Operator::_EQUAL_TO_, $id)
					->limit(1)
					->query()
					->fetch(0)
					->contents;
			}
			catch (ErrorException $ex) {
				$contents = FALSE;
			}

			if ($contents !== FALSE) {
				// Set the current session id
				$this->_session_id = $this->_update_id = $id;

				// Return the contents
				return $contents;
			}
		}

		// Create a new session id
		$this->_regenerate();

		return NULL;
	}

	/**
	 * This function generates a new session.
	 *
	 * @access protected
	 * @return string                           the new session id
	 */
	protected function _regenerate() {
		do {
			// Create a new session id
			$id = str_replace('.', '-', uniqid(NULL, TRUE));
            $count = DB_ORM::select($this->_table, array($this->_columns['session_id']))
                ->where($this->_columns['session_id'], '=', $id)
                ->query()
                ->count();
		}
		while ($count > 0);

		return $this->_session_id = $id;
	}

	/**
	 * This function saves the current session to the database.
	 *
	 * @access protected
	 * @return boolean                          whether the current session was
	 *                                          successfully saved
	 */
	protected function _write() {
		if ($this->_update_id === NULL) {
			// Insert a new row
			$query = DB_ORM::insert($this->_table)
				->column($this->_columns['last_active'], $this->_data['last_active'])
				->column($this->_columns['contents'], $this->__toString())
				->column($this->_columns['session_id'], $this->_session_id); 
		}
		else {
			// Update the row
			$query = DB_ORM::update($this->_table)
				->set($this->_columns['last_active'], $this->_data['last_active'])
				->set($this->_columns['contents'], $this->__toString())
				->where($this->_columns['session_id'], DB_SQL_Operator::_EQUAL_TO_, $this->_update_id);

			if ($this->_update_id !== $this->_session_id) {
				// Also update the session id
				$query->set($this->_columns['session_id'], $this->_session_id);
			}
		}

		// Execute the query
		$query->execute();

		// The update and the session id are now the same
		$this->_update_id = $this->_session_id;

		// Update the cookie with the new session id
		Cookie::set($this->_name, $this->_session_id, $this->_lifetime);

		return TRUE;
	}

	/**
	 * This function destroys the current session.
	 *
	 * @access protected
	 * @return boolean                          whether the current session was
	 *                                          successfully destroyed
	 */
	protected function _destroy() {
		// Session has not been created yet
		if ($this->_update_id === NULL) {
			return TRUE;
		}

		// Delete the current session
		DB_ORM::delete($this->_table)
			->where($this->_columns['session_id'], DB_SQL_Operator::_EQUAL_TO_, $this->_update_id)
			->execute();

		try {
			// Delete the cookie
			Cookie::delete($this->_name);
		}
		catch (Exception $ex) {
			// An error occurred, the session has not been deleted
			return FALSE;
		}

		return TRUE;
	}

	/**
	 * This function handles garbage collection.
	 *
	 * @access protected
	 */
	protected function _gc() {
		$expires = ($this->_lifetime)
			? $this->_lifetime	// Expire sessions when their lifetime is up
			: Date::MONTH; 		// Expire sessions after one month

		// Delete all sessions that have expired
		DB_ORM::delete($this->_table)
			->where($this->_columns['last_active'], DB_SQL_Operator::_LESS_THAN_, time() - $expires)
			->execute();
	}

	/**
	 * This function restarts the current session.
	 *
	 * @access protected
	 * @return boolean                          whether the current session was
	 *                                          successfully restarted
	 */
	protected function _restart() {
		$this->_regenerate();
		return TRUE;
	}

}
?>