<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Copyright 2011-2012 Spadefoot
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
 * @version 2012-03-08
 *
 * @abstract
 */
abstract class Base_DB_ORM_Model extends Kohana_Object {

	/**
	 * This variable stores the record's adaptors.
	 *
	 * @access protected
	 * @var array
	 */
	protected $adaptors = array();

	/**
	 * This variable stores the aliases for certain fields.
	 *
	 * @access protected
	 * @var array
	 */
	protected $aliases = array();

	/**
	 * This variable stores the record's fields.
	 *
	 * @access protected
	 * @var array
	 */
	protected $fields = array();

	/**
	 * This variable stores the record's metadata.
	 *
	 * @access protected
	 * @var array
	 */
	protected $metadata = array();

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
	 * @param string $name                          the name of the property
	 * @return mixed                                the value of the property
	 * @throws Kohana_InvalidProperty_Exception     indicates that the specified property is
	 *                                              either inaccessible or undefined
	 */
	public function __get($name) {
		if ($this->is_field($name)) {
			return $this->fields[$name]->value;
		}
		else if ($this->is_alias($name)) {
			return $this->aliases[$name]->value;
		}
		else if ($this->is_adaptor($name)) {
			return $this->adaptors[$name]->value;
		}
		else if ($this->is_relation($name)) {
			return $this->relations[$name]->result;
		}
		else {
			throw new Kohana_InvalidProperty_Exception('Message: Unable to get the specified property. Reason: Property :key is either inaccessible or undefined.', array(':key' => $name));
		}
	}

	/**
	 * This function sets the value for the specified key.
	 *
	 * @access public
	 * @param string $name                          the name of the property
	 * @param mixed $value                          the value of the property
	 * @throws Kohana_InvalidProperty_Exception     indicates that the specified property is
	 *                                              either inaccessible or undefined
	 */
	public function __set($name, $value) {
		if ($this->is_field($name)) {
			$this->fields[$name]->value = $value;
			$this->metadata['loaded'] = TRUE;
		}
		else if ($this->is_alias($name)) {
			$this->aliases[$name]->value = $value;
		}
		else if ($this->is_adaptor($name)) {
			$this->adaptors[$name]->value = $value;
		}
		else {
			throw new Kohana_InvalidProperty_Exception('Message: Unable to set the specified property. Reason: Property :key is either inaccessible or undefined.', array(':key' => $name, ':value' => $value));
		}
	}

	/**
	 * This function will return an array of column/value mappings.
	 *
	 * @access public
	 * @return array                                an array of column/value mappings
	 */
	public function as_array() {
		$buffer = array();
		foreach ($this->relations as $name => $relation) {
			$buffer[$name] = $relation->result;
		}
		foreach ($this->fields as $name => $field) {
			$buffer[$name] = $field->value;
		}
		return $buffer;
	}

	/**
	 * This function will return the associated HTML form control for the specified
	 * field.
	 *
	 * @access public
	 * @param string $name                          the name of the field/alias
	 * @param array $attributes                     the HTML form tag's attributes
	 * @return string                               the HTML form control
	 */
	public function control($name, Array $attributes = NULL) {
		if ( ! is_array($attributes)) {
			$attributes = array();
		}
		$control = $this->fields[$name]->control($name, $attributes);
		return $control;
	}

	/**
	 * This function deletes the record matching the primary key from the database.
	 *
	 * @access public
	 * @param boolean $reset                        whether to reset each column's value back
	 *                                              to its original value
	 * @throws Kohana_Marshalling_Exception         indicates that the record could not be
	 *                                              deleted
	 */
	public function delete($reset = FALSE) {
		$self = get_class($this);
		$is_savable = call_user_func(array($self, 'is_savable'));
		if ( ! $is_savable) {
			throw new Kohana_Marshalling_Exception('Message: Failed to delete record from database. Reason: Model is not savable.', array(':class' => self::get_called_class()));
		}
		$primary_key = call_user_func(array($self, 'primary_key'));
		if ( ! is_array($primary_key) || empty($primary_key)) {
			throw new Kohana_Marshalling_Exception('Message: Failed to delete record from database. Reason: No primary key has been declared.');
		}
		$data_source = call_user_func(array($self, 'data_source'));
		$table = call_user_func(array($self, 'table'));
		$builder = DB_SQL::delete($data_source)->from($table);
		foreach ($primary_key as $column) {
			$builder->where($column, DB_SQL_Operator::_EQUAL_TO_, $this->fields[$column]->value);
		}
		$builder->execute();
		if ($reset) {
			$this->reset();
		}
		else {
			$this->metadata['saved'] = NULL;
		}
	}

	/**
	 * This function checks whether this model defines the specified name as
	 * an adaptor.
	 *
	 * @access public
	 * @param string $name                          the name of the adaptor
	 * @return boolean                              whether this model defines the specified
	 *                                              name as an adaptor
	 */
	public function is_adaptor($name) {
		return isset($this->adaptors[$name]);
	}

	/**
	 * This function checks whether this model defines the specified name as
	 * an alias.
	 *
	 * @access public
	 * @param string $name                          the name of the alias
	 * @return boolean                              whether this model defines the specified
	 *                                              name as an alias
	 */
	public function is_alias($name) {
		return isset($this->aliases[$name]);
	}

	/**
	 * This function checks whether this model defines the specified name as
	 * a field.
	 *
	 * @access public
	 * @param string $name                          the name of the field
	 * @return boolean                              whether this model defines the specified
	 *                                              name as a field
	 */
	public function is_field($name) {
		return isset($this->fields[$name]);
	}

	/**
	 * This function returns whether the record contains any data.
	 *
	 * @access public
	 * @return boolean                              whether the record contains any data
	 */
	public function is_loaded() {
		return $this->metadata['loaded'];
	}

	/**
	 * This function checks whether this model defines the specified name as
	 * a relation.
	 *
	 * @access public
	 * @param string $name                          the name of the relation
	 * @return boolean                              whether this model defines the specified
	 *                                              name as a relation
	 */
	public function is_relation($name) {
		return isset($this->relations[$name]);
	}

	/**
	 * This function generates a hash code that will be used to indicate whether the
	 * record is saved in the database.
	 *
	 * @access protected
	 * @return string                               the generated hash code
	 */
	protected function hash_code() {
		$self = get_class($this);
		$primary_key = call_user_func(array($self, 'primary_key'));
		if (is_array($primary_key) && ! empty($primary_key)) {
			$buffer = '';
			foreach ($primary_key as $column) {
				if ( ! isset($this->fields[$column])) {
					throw new Kohana_InvalidProperty_Exception('Message: Unable to generate hash code for model. Reason: Primary key contains a non-existent field name.', array(':primary_key' => $primary_key));
				}
				$value = $this->fields[$column]->value;
				if ( ! is_null($value)) {
                    $buffer .= "{$column}={$value}";
                }
			}
			return ($buffer != '') ? sha1($buffer) : NULL;
		}
		throw new Kohana_EmptyCollection_Exception('Message: Unable to generate hash code for model. Reason: No primary key has been declared.', array(':primary_key' => $primary_key));
	}

	/**
	 * This function will return the associated HTML form label for the specified
	 * field.
	 *
	 * @access public
	 * @param string $name                          the name of the field/alias
	 * @param array $attributes                     the HTML form tag's attributes
	 * @return string                               the HTML form label
	 */
	public function label($name, Array $attributes = NULL) {
		$key = $name;
		if ($this->is_alias($key)) {
			$key = $this->aliases[$name]->field;
		}
		return $this->fields[$key]->label($name, $attributes);
	}

	/**
	 * This function either loads the record matching the primary key from the database
	 * or sets an array of values to their associated fields.
	 *
	 * @access public
	 * @param array $columns                        an array of column/value mappings
	 */
	public function load(Array $columns = array()) {
		if (empty($columns)) {
			$self = get_class($this);
			$primary_key = call_user_func(array($self, 'primary_key'));
			if ( ! is_array($primary_key) || empty($primary_key)) {
				throw new Kohana_Marshalling_Exception('Message: Failed to load record from database. Reason: No primary key has been declared.');
			}
			$data_source = call_user_func(array($self, 'data_source'));
			$table = call_user_func(array($self, 'table'));
			$builder = DB_SQL::select($data_source)->from($table)->limit(1);
			foreach ($primary_key as $column) {
				$builder->where($column, DB_SQL_Operator::_EQUAL_TO_, $this->fields[$column]->value);
			}
			$record = $builder->query();
			if ( ! $record->is_loaded()) {
				throw new Kohana_Marshalling_Exception('Message: Failed to load record from database. Reason: Unable to match primary key with a record.');
			}
			$columns = $record->fetch(0);
		    $this->metadata['loaded'] = TRUE;
    		$this->metadata['saved'] = $this->hash_code();
		}
		foreach ($columns as $column => $value) {
			if ($this->is_field($column)) {
				$this->fields[$column]->value = $value;
				$this->metadata['loaded'] = TRUE;
			}
			else if ($this->is_alias($column)) {
				$this->aliases[$column]->value = $value;
			}
			else if ($this->is_adaptor($column)) {
				$this->adaptors[$column]->value = $value;
			}
		}
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
	 * This function saves the record matching using the primary key.
	 *
	 * @access public
	 * @param boolean $reload                       whether the model should be reloaded
	 *                                              after the save is done
	 */
	public function save($reload = FALSE) {
		$self = get_class($this);
		$is_savable = call_user_func(array($self, 'is_savable'));
		if ( ! $is_savable) {
			throw new Kohana_Marshalling_Exception('Message: Failed to save record to database. Reason: Model is not savable.', array(':class' => self::get_called_class()));
		}
		$primary_key = call_user_func(array($self, 'primary_key'));
		if ( ! is_array($primary_key) || empty($primary_key)) {
			throw new Kohana_Marshalling_Exception('Message: Failed to save record to database. Reason: No primary key has been declared.');
		}
		$data_source = call_user_func(array($self, 'data_source'));
		$table = call_user_func(array($self, 'table'));
		$columns = array_keys($this->fields);
		$hash_code = $this->hash_code();
		$do_insert = is_null($hash_code);
		if ( ! $do_insert) {
			$do_insert = (is_null($this->metadata['saved']) || ($hash_code != $this->metadata['saved']));
			if ($do_insert) {
				$builder = DB_SQL::select($data_source)
						->column(DB_SQL::expr(1), 'IsFound')
						->from($table);
				foreach ($primary_key as $column) {
					$builder->where($column, DB_SQL_Operator::_EQUAL_TO_, $this->fields[$column]->value);
				}
				$do_insert = ! ($builder->limit(1)->query()->is_loaded());
			}
			if ( ! $do_insert) {
				foreach ($primary_key as $column) {
					$index = array_search($column, $columns);
					if ($index !== FALSE) {
						unset($columns[$index]);
					}
				}
				if ( ! empty($columns)) {
					$builder = DB_SQL::update($data_source)
						->table($table);
					$count = 0;
					foreach ($columns as $column) {
						if ($this->fields[$column]->savable && $this->fields[$column]->modified) {
							$builder->set($column, $this->fields[$column]->value);
							$this->fields[$column]->modified = FALSE;
							$count++;
						}
					}
					if ($count > 0) {
						foreach ($primary_key as $column) {
							$builder->where($column, DB_SQL_Operator::_EQUAL_TO_, $this->fields[$column]->value);
						}
						$builder->execute();
					}
					$this->metadata['saved'] = $hash_code;
				}
			}
		}
		if ($do_insert) {
			$is_auto_incremented = call_user_func(array($self, 'is_auto_incremented'));
			if ($is_auto_incremented || is_null($hash_code)) {
				foreach ($primary_key as $column) {
					$index = array_search($column, $columns);
					if ($index !== FALSE) {
						unset($columns[$index]);
					}
				}
			}
			if ( ! empty($columns)) {
				$builder = DB_SQL::insert($data_source)
					->into($table);
				$count = 0;
				foreach ($columns as $column) {
					if ($this->fields[$column]->savable) {
						$builder->column($column, $this->fields[$column]->value);
						$this->fields[$column]->modified = FALSE;
						$count++;
					}
				}
				if ($count > 0) {
					if ($is_auto_incremented && is_null($hash_code)) {
						$this->fields[$primary_key[0]]->value = $builder->execute(TRUE);
					}
					else {
						$builder->execute();
					}
				}
				$this->metadata['saved'] = $this->hash_code();
			}
		}
		if ($reload) {
			$this->load();
		}
	}

	////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This function returns the builder's class name.
	 *
	 * @access public
	 * @static
	 * @param string $builder                       the builder's name
	 * @return string                               the builder's class name
	 */
	public static function builder_name($builder) {
		$prefix = 'Builder_Leap_';
		if (preg_match('/^' . $prefix . '.*$/i', $builder)) {
			return $builder;
		}
		return $prefix . $builder;
	}

	/**
	 * This function returns a list of column names.
	 *
	 * @access public
	 * @static
	 * @return array                                a list of column names
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
	 * This function returns the data source.
	 *
	 * @access public
	 * @static
	 * @return string                               the data source
	 */
	public static function data_source() {
		return 'default'; // the key used in config/database.php
	}

	/**
	 * This function returns an instance of the specified model.
	 *
	 * @access public
	 * @static
	 * @param string $model                         the model's name
	 * @return mixed                                an instance of the specified model
	 */
	public static function factory($model) {
		$model = DB_ORM_Model::model_name($model);
		return new $model();
	}

	/**
	 * This function returns whether the primary key auto increments.
	 *
	 * @access public
	 * @static
	 * @return boolean                              whether the primary key auto increments
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
	 * @return boolean                              whether the active record can be saved
	 *                                              in the database
	 */
	public static function is_savable() {
		return TRUE;
	}

	/**
	 * This function returns the model's class name.
	 *
	 * @access public
	 * @static
	 * @param string $model                         the model's name
	 * @return string                               the model's class name
	 */
	public static function model_name($model) {
		$prefix = 'Model_Leap_';
		if (preg_match('/^' . $prefix . '.*$/i', $model)) {
			return $model;
		}
		return $prefix . $model;
	}

	/**
	 * This function returns the primary key for the database table.
	 *
	 * @access public
	 * @static
	 * @return array                                the primary key
	 */
	public static function primary_key() {
		return array('ID');
	}

	/**
	 * This function returns the database table's name.
	 *
	 * @access public
	 * @static
	 * @return string                               the database table's name
	 */
	public static function table() {
		$segments = preg_split('/_/', self::get_called_class());
		return $segments[count($segments) - 1];
	}

}
?>