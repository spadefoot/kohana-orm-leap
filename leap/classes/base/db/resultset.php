<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Copyright 2011 Spadefoot
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
 * This class represents a result set.
 *
 * @package Leap
 * @category Connection
 * @version 2011-09-12
 *
 * @abstract
 */
abstract class Base_DB_ResultSet extends Kohana_Object implements Countable, Iterator {

    /**
    * This variable stores the records.
    *
    * @access protected
    * @var array
    */
    protected $records;

	/**
	* This variable stores the current position in the records array.
	*
	* @access protected
	* @var integer
	*/
    protected $position;

	/**
	* This variable stores the length of the records array.
	*
	* @access protected
	* @var integer
	*/
    protected $size;

    /**
	 * This function initializes the class by wrapping the result set so that all database
	 * result sets are accessible alike.
	 *
	 * @access public
     * @param array $records					an array of records
	 * @param integer $size						the total number of records
	 */
	public function __construct(Array $records, $size) {
		$this->records = $records;
		$this->position = 0;
		$this->size = $size;
	}

    /**
    * This function returns the total number of records contained in result set.
    *
	* @access public
	* @return integer                           the total number of records
    */
    public function count() {
        return $this->size;
    }

	/**
	 * This function returns the current record.
	 *
	 * @access public
	 * @return mixed						      the current record
	 */
	public function current() {
		return $this->records[$this->position];
	}

    /**
    * This function returns a record of the desired object type.
    *
    * @access public
    * @abstract
    * @param integer $index                     the record's index
    * @return mixed                             the record
    */
    public function fetch($index = -1) {
    	settype($index, 'integer');
    	if ($index < 0) {
    	    $index = $this->position;
    	    $this->position++;
    	}
	    
	    if (isset($this->records[$index])) {
		    return $this->records[$index];
	    }

	    return FALSE;
	}

    /**
    * This function returns an array of records of the desired object type.
    *
    * @access public
    * @return array                             an array of records
    */
	public function fetch_all() {
		return $this->records;
	}

    /**
    * This function frees all data stored in the result set.
    *
    * @access public
    */
    public function free() {
		$this->records = array();
		$this->position = 0;
		$this->size = 0;
    }

    /**
    * This function returns whether any records were loaded.
    *
    * @access public
    * @return boolean                           whether any records were loaded
    */
    public function is_loaded() {
        return ($this->size > 0);
    }

	/**
	* This function returns the position to the current record.
	*
	* @access public
	* @return integer					        the position of the current record
	*/
	public function key() {
		return $this->position;
	}

	/**
	* This function moves forward the position to the next record, lazy loading only
	* when necessary.
	*
	* @access public
	*/
	public function next() {
        $this->position++;
	}

	/**
	* This function returns the current iterator position.
	*
	* @access public
	* @return integer					        the current iterator position
	*/
	public function position() {
		return $this->position;
	}

	/**
	* This function rewinds the iterator back to starting position.
	*
	* @access public
	*/
	public function rewind() {
		$this->position = 0;
	}

	/**
	* This function checks if the current iterator position is valid.
	*
	* @access public
	* @return boolean					        whether the current iterator position is valid
	*/
	public function valid() {
	    return isset($this->records[$this->position]);
	}

}
?>