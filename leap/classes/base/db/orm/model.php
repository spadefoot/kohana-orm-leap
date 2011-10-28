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
 * This class represents an active record for an SQL database table.
 *
 * @package Leap
 * @category ORM
 * @version 2011-06-09
 *
 * @abstract
 */
abstract class Base_DB_ORM_Model extends Kohana_Object {

    /**
    * This variable stores the record's metadata.
    *
    * @access protected
    * @var array
    */
    protected $metadata = array();

    /**
    * This variable stores the record's fields.
    *
    * @access protected
    * @var array
    */
    protected $fields = array();

	/**
	 * This variable stores the aliases for certain fields.
	 *
	 * @access protected
	 * @var array
	 */
	protected $aliases = array();

	/**
	 * This variable stores the record's relations.
	 *
	 * @access protected
	 * @var array
	 */
	protected $relations = array();

    /**
    * This constructor instantiates this class.
    *
    * @access public
    */
    public function __construct() {
        $this->metadata['loaded'] = FALSE;
        $this->metadata['saved'] = NULL;
    }

    /**
     * This function returns the value associated with the specified property.
     *
     * @access public
     * @param string $key                           the name of the property
     * @return mixed                                the value of the property
     * @throws Kohana_InvalidProperty_Exception     indicates that the specified property is
     *                                              either inaccessible or undefined
     */
    public function __get($key) {
        if (isset($this->aliases[$key])) {
			return $this->aliases[$key]->value;
		}
		else if (isset($this->fields[$key])) {
       		return $this->fields[$key]->value;
        }
		else if (isset($this->relations[$key])) {
			return $this->relations[$key]->result;
		}
		throw new Kohana_InvalidProperty_Exception('Message: Unable to get the specified property. Reason: Property :key is either inaccessible or undefined.', array(':key' => $key));
	}

    /**
     * This function sets the value for the specified key.
     *
     * @access public
     * @param string $key                           the name of the property
     * @return mixed                                the value of the property
     * @throws Kohana_InvalidProperty_Exception     indicates that the specified property is
     *                                              either inaccessible or undefined
     */
    public function __set($key, $value) {
        if (!isset($this->fields[$key])) {
            throw new Kohana_InvalidProperty_Exception('Message: Unable to set the specified property. Reason: Property :key is either inaccessible or undefined.', array(':key' => $key, ':value' => $value));
        }
        $this->fields[$key]->value = $value;
        $this->metadata['loaded'] = TRUE;
    }

    /**
     * This function deletes the record matching the primary key from the database.
     *
     * @access public
     * @param boolean $reset                        whether to reset each column's value back
     *                                              to its original value
	 * @throws Kohana_Marshalling_Exception
     */
    public function delete($reset = FALSE) {
        $self = get_class($this);
        $is_savable = call_user_func(array($self, 'is_savable'));
        if (!$is_savable) {
            throw new Kohana_Marshalling_Exception('Message: Failed to delete record from database. Reason: Model is not savable.', array(':class' => self::get_called_class()));
        }
        $primary_key = call_user_func(array($self, 'primary_key'));
        if (!is_array($primary_key) || empty($primary_key)) {
            throw new Kohana_Marshalling_Exception('Message: Failed to delete record from database. Reason: No primary key has been declared.');
        }
        $data_source = call_user_func(array($self, 'data_source'));
        $table = call_user_func(array($self, 'table'));
        $sql = DB_SQL::delete($data_source)->from($table);
        foreach ($primary_key as $column) {
            $sql->where($column, DB_SQL_Operator::_EQUAL_TO_, $this->fields[$column]->value);
        }
        $sql->execute();
        if ($reset) {
            $this->reset();
        }
        else {
            $this->metadata['saved'] = NULL;
        }
    }

    /**
     * This function loads the record matching the primary key from the database.
     *
     * @access public
     * @param array $columns                        an array of column/value mapping
     */
    public function load(Array $columns = array()) {
        if (empty($columns)) {
            $self = get_class($this);
            $primary_key = call_user_func(array($self, 'primary_key'));
            if (!is_array($primary_key) || empty($primary_key)) {
                throw new Kohana_Marshalling_Exception('Message: Failed to load record from database. Reason: No primary key has been declared.');
            }
            $data_source = call_user_func(array($self, 'data_source'));
            $table = call_user_func(array($self, 'table'));
            $sql = DB_SQL::select($data_source)->from($table);
            foreach ($primary_key as $column) {
                $sql->where($column, DB_SQL_Operator::_EQUAL_TO_, $this->fields[$column]->value);
            }
            $record = $sql->query(1);
            if (!$record->is_loaded()) {
                throw new Kohana_Marshalling_Exception('Message: Failed to load record from database. Reason: Unable to match primary key with a record.');
            }
            $columns = $record->fetch(0);
        }
        foreach ($columns as $column => $value) {
            if (isset($this->fields[$column])) {
                $this->fields[$column]->value = $value;
            }
        }
        $this->metadata['loaded'] = TRUE;
        $this->metadata['saved'] = $this->hash_code();
    }

    /**
    * This function saves the record matching using the primary key.
    *
    * @access public
    */
    public function save() {
        $self = get_class($this);
        $is_savable = call_user_func(array($self, 'is_savable'));
        if (!$is_savable) {
            throw new Kohana_Marshalling_Exception('Message: Failed to save record to database. Reason: Model is not savable.', array(':class' => self::get_called_class()));
        }
        $primary_key = call_user_func(array($self, 'primary_key'));
        if (!is_array($primary_key) || empty($primary_key)) {
            throw new Kohana_Marshalling_Exception('Message: Failed to save record to database. Reason: No primary key has been declared.');
        }
        $data_source = call_user_func(array($self, 'data_source'));
        $table = call_user_func(array($self, 'table'));
        $columns = call_user_func(array($self, 'columns'));
        $hash_code = $this->hash_code();
        $do_insert = is_null($hash_code);
        if (!$do_insert) {
            $do_insert = (is_null($this->metadata['saved']) || ($hash_code == $this->metadata['saved']));
            if ($do_insert) {
                $sql = DB_SQL::select($data_source)->from($table)->column(DB::expr(1), 'IsFound');
                foreach ($primary_key as $column) {
                    $sql->where($column, DB_SQL_Operator::_EQUAL_TO_, $this->fields[$column]->value);
                }
                $do_insert = $sql->query(1)->is_loaded();
            }
            if (!$do_insert) {
                foreach ($primary_key as $column) {
                    unset($columns[$column]);
                }
                if (!empty($columns)) {
                    $sql = DB_SQL::update($data_source)->table($table);
                    $count = 0;
                    foreach ($columns as $column) {
                        if (!$this->fields[$column]->savable && $this->fields[$column]->modified) {
                            $sql->set($column, $this->fields[$column]->value);
                            $this->fields[$column]->modified = FALSE;
                            $count++;
                        }
                    }
                    if ($count > 0) {
                        foreach ($primary_key as $column) {
                            $sql->where($column, DB_SQL_Operator::_EQUAL_TO_, $this->fields[$column]->value);
                        }
                        $sql->execute();
                    }
                    $this->metadata['saved'] = $hash_code;
                }
            }
        }
        if ($do_insert) {
            $is_auto_incremented = call_user_func(array($self, 'is_auto_incremented'));
            if ($is_auto_incremented && is_null($hash_code)) {
                foreach ($primary_key as $column) {
                    unset($columns[$column]);
                }
            }
            if (!empty($columns)) {
                $sql = DB_SQL::insert($data_source)->into($table);
                $count = 0;
                foreach ($columns as $column) {
                    if (!$this->fields[$column]->savable) {
                        $sql->column($column, $this->fields[$column]->value);
                        $this->fields[$column]->modified = FALSE;
                        $count++;
                    }
                }
                if ($count > 0) {
                    if ($is_auto_incremented && is_null($hash_code)) {
                        $this->fields[$primary_key[0]]->value = $sql->execute(TRUE);
                    }
                    else {
                        $sql->execute();
                    }
                }
                $this->metadata['saved'] = $this->hash_code();
            }
        }
    }

    /**
     * This function returns whether the record contains any data.
     *
     * @access public
     * @return boolean                      whether the record contains any data
     */
    public function is_loaded() {
        return $this->metadata['loaded'];
    }

    /**
     * This function will return an array of column/value mappings.
     *
     * @access public
     * @return array                        an array of column/value mappings
     */
    public function as_array() {
        $buffer = array();
		foreach ($this->relations as $relation) {
            $buffer[$relation] = $relation->result;
		}
        foreach ($this->fields as $column) {
            $buffer[$column] = $column->value;
        }
        return $buffer;
    }

    /**
     * This function resets each column's value back to its original value.
     *
     * @access public
     */
    public function reset() {
        foreach ($this->fields as $field) {
            $field->reset();
        }
		foreach ($this->relations as $relation) {
			$relation->reset();
		}
        $this->metadata['loaded'] = FALSE;
        $this->metadata['saved'] = NULL;
    }

    /**
     * This function generates a hash code that will be used to indicate whether the
     * record is saved in the database.
     *
     * @access protected
     * @return string                       the generated hash code
     */
    protected function hash_code() {
        $self = get_class($this);
        $primary_key = call_user_func(array($self, 'primary_key'));
        if (is_array($primary_key) && !empty($primary_key)) {
            $buffer = '';
            foreach ($primary_key as $column) {
                if (!isset($this->fields[$column])) {
                    throw new Kohana_InvalidProperty_Exception('Message: Unable to generate hash code for model. Reason: Primary key contains a non-existent field name.', array(':primary_key' => $primary_key));
                }
                $buffer .= "{$column}={$this->fields[$column]->value}";
            }
            return sha1($buffer);
        }
        throw new Kohana_EmptyCollection_Exception('Message: Unable to generate hash code for model. Reason: No primary key has been declared.', array(':primary_key' => $primary_key));
    }

    /**
     * This function returns the model's class name.
     *
     * @access public
     * @static
     * @return string                       the model's class name
     */
    public static function model_name($model) {
        $prefix = 'Model_Leap_';
        if (preg_match('/^' . $prefix . '.*$/i', $model)) {
            return $model;
        }
        return $prefix . $model;
    }

    /**
     * This function returns the data source.
     *
     * @access public
     * @static
     * @return string                       the data source
     */
    public static function data_source() {
        return 'default'; // the key used in config/database.php
    }

    /**
     * This function returns the database table's name.
     *
     * @access public
     * @static
     * @return string                       the database table's name
     */
    public static function table() {
        $segments = preg_split('/_/', self::get_called_class());
        return $segments[count($segments) - 1];
    }

    /**
     * This function returns a list of column names.
     *
     * @access public
     * @static
     * @return array                        a list of column names
     */
    public static function columns() {
        static $columns = NULL;
        if (is_null($columns)) {
            $model = self::get_called_class();
            $record = new $model();
            $columns = array_keys($record->fields);
        }
        return $columns;
    }

    /**
    * This function returns the primary key for the database table.
    *
    * @access public
    * @static
    * @return array                         the primary key
    */
    public static function primary_key() {
        return array('ID');
    }

    /**
    * This function returns whether the primary key auto increments.
    *
    * @access public
    * @static
    * @return boolean                       whether the primary key auto increments
    */
    public static function is_auto_incremented() {
        if (count(self::primary_key()) > 1) {
            return FALSE;
        }
        return TRUE;
    }

    /**
    * This function returns whether the active record can be saved in the database.
    *
    * @access public
    * @static
    * @return boolean                       whether the active record can be saved
    *                                       in the database
    */
    public static function is_savable() {
        return TRUE;
    }

}
?>