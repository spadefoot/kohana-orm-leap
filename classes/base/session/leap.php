<?php defined('SYSPATH') or die('No direct script access.');

class Base_Session_Leap extends Session
{
	protected $_table = 'Session';//Session Model

	// Database column names
	protected $_columns = array(
		'session_id'  => 'sesID',
		'last_active' => 'sesLastActive',
		'contents'    => 'sesContents'
	);
	
	// Garbage collection requests
	protected $_gc = 500;

	// The current session id
	protected $_session_id;

	// The old session id
	protected $_update_id;
	
	public function __construct(array $config = NULL, $id = NULL)
	{
		/*
		// Use the default group
		if ( !isset($config['group']) )
			$config['group'] = 'default';
		*/

		// Load the database
		//$this->_db = Doctrine::em();

		// Set the table name
		if (isset($config['table']))
			$this->_table = (string) $config['table'];

		// Set the gc chance
		if (isset($config['gc']))
			$this->_gc = (int) $config['gc'];

		// Overload column names
		if (isset($config['columns']))
			$this->_columns = $config['columns'];

		parent::__construct($config, $id);

		// Run garbage collection
		// This will average out to run once every X requests
		if (mt_rand(0, $this->_gc) === $this->_gc)
			$this->_gc();
	}
	
	public function id()
	{
		return $this->_session_id;
	}
	
	protected function _read($id = NULL)
	{
		if ($id OR $id = Cookie::get($this->_name))
		{
			try {
				$contents = DB_ORM::select($this->_table, array($this->_columns['contents']))
					->where($this->_columns['session_id'],'=',$id)
					->query()
					->fetch(0)->contents;
			} catch (ErrorException $e) { $contents = FALSE; }

			if ( $contents !== FALSE )
			{
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
	
	protected function _regenerate()
	{
		do
		{
			// Create a new session id
			$id = str_replace('.', '-', uniqid(NULL, TRUE));

			try {
				$result = DB_ORM::select($this->_table, array($this->_columns['session_id']))
				->where($this->_columns['session_id'], '=', $id)
				->query()
				->fetch(0)->id;
				
			} catch (ErrorException $e) { $result = false; }
		}
		while( $result !== false );

		return $this->_session_id = $id;
	}

	protected function _write()
	{
		if ($this->_update_id === NULL)
		{
			// Insert a new row
			$query = DB_ORM::insert($this->_table)
				->column($this->_columns['last_active'], $this->_data['last_active'])
				->column($this->_columns['contents'], $this->__toString())
				->column($this->_columns['session_id'], $this->_session_id); 
		}
		else
		{
			// Update the row
			$query = DB_ORM::update($this->_table)
				->set($this->_columns['last_active'], $this->_data['last_active'])
				->set($this->_columns['contents'], $this->__toString())
				->where($this->_columns['session_id'], '=', $this->_update_id);

			if ($this->_update_id !== $this->_session_id)
			{
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
	
	protected function _destroy()
	{
		// Session has not been created yet
		if ( $this->_update_id === NULL )
			return TRUE;

		// Delete the current session
		DB_ORM::delete($this->_table)
			->where($this->_columns['session_id'],'='.$this->_update_id)
			->execute();
			
		try
		{
			// Delete the cookie
			Cookie::delete($this->_name);
		}
		catch ( Exception $e )
		{
			// An error occurred, the session has not been deleted
			return FALSE;
		}

		return TRUE;
	}
	
	protected function _gc()
	{
		// Expire sessions when their lifetime is up
		if ($this->_lifetime)
			$expires = $this->_lifetime;
		// Expire sessions after one month
		else
			$expires = Date::MONTH;

		// Delete all sessions that have expired
		DB_ORM::delete($this->_table)
			->where($this->_columns['last_active'], '<', time()-$expires)
			->execute();
	}
	
	protected function _restart()
	{
		//$this->_regenerate();

		return TRUE;
	}
}
