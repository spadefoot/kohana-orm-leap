<?php defined('SYSPATH') OR die('No direct script access.');

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
 * @version 2012-11-14
 *
 * @abstract
 */
abstract class Base_DB_ORM_Model extends Core_Object {

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
	 * This function returns whether a property is set.
	 *
	 * @access public
	 * @param string $name                          the name of the property
	 * @return boolean								whether the property is set
	 */
	public function __isset($name) {
		return (isset($this->fields[$name]) OR isset($this->aliases[$name]) OR isset($this->adaptors[$name]) OR isset($this->relations[$name]));
	}

	/**
	 * This function returns the value associated with the specified property.
	 *
	 * @access public
	 * @param string $name                          the name of the property
	 * @return mixed                                the value of the property
	 * @throws Throwable_InvalidProperty_Exception     indicates that the specified property is
	 *                                              either inaccessible or undefined
	 */
	public function __get($name) {
		if (isset($this->fields[$name])) {
			return $this->fields[$name]->value;
		}
		else if (isset($this->aliases[$name])) {
			return $this->aliases[$name]->value;
		}
		else if (isset($this->adaptors[$name])) {
			return $this->adaptors[$name]->value;
		}
		else if (isset($this->relations[$name])) {
			return $this->relations[$name]->result;
		}
		else {
			throw new Throwable_InvalidProperty_Exception('Message: Unable to get the specified property. Reason: Property :key is either inaccessible or undefined.', array(':key' => $name));
		}
	}

	/**
	 * This function sets the value for the specified key.
	 *
	 * @access public
	 * @param string $name                          the name of the property
	 * @param mixed $value                          the value of the property
	 * @throws Throwable_InvalidProperty_Exception     indicates that the specified property is
	 *                                              either inaccessible or undefined
	 */
	public function __set($name, $value) {
		if (isset($this->fields[$name])) {
			$this->fields[$name]->value = $value;
			$this->metadata['loaded'] = TRUE;
		}
		else if (isset($this->aliases[$name])) {
			$this->aliases[$name]->value = $value;
		}
		else if (isset($this->adaptors[$name])) {
			$this->adaptors[$name]->value = $value;
		}
		else {
			throw new Throwable_InvalidProperty_Exception('Message: Unable to set the specified property. Reason: Property :key is either inaccessible or undefined.', array(':key' => $name, ':value' => $value));
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
		foreach ($this->aliases as $name => $alias) {
			$buffer[$name] = $alias->value;
		}
		foreach ($this->adaptors as $name => $adaptor) {
			$buffer[$name] = $adaptor->value;
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
		if ($attributes === NULL) {
			$attributes = array();
		}
		$control = $this->fields[$name]->control($name, $attributes);
		return $control;
	}

	/**
	 * Creates the record in database.
	 *
	 * @access public
	 * @param boolean $reload                       whether the model should be reloaded
	 *                                              after the save is done
	 */
	public function create($reload = FALSE) {
		$this->save($reload, TRUE);
	}

	/**
	 * This function deletes the record matching the primary key from the database.
	 *
	 * @access public
	 * @param boolean $reset                        whether to reset each column's value back
	 *                                              to its original value
	 * @throws Throwable_Marshalling_Exception         indicates that the record could not be
	 *                                              deleted
	 */
	public function delete($reset = FALSE) {
		if ( ! static::is_savable()) {
			throw new Throwable_Marshalling_Exception('Message: Failed to delete record from database. Reason: Model is not savable.', array(':class' => get_called_class()));
		}
		$primary_key = static::primary_key();
		if (empty($primary_key) OR ! is_array($primary_key)) {
			throw new Throwable_Marshalling_Exception('Message: Failed to delete record from database. Reason: No primary key has been declared.');
		}
		$builder = DB_SQL::delete(static::data_source())->from(static::table());
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
	 * This function checks whether the record exists in the database table.
	 *
	 * @access public
	 * @return boolean                              whether the record exists in the database
	 *                                              table
	 */
	public function is_saved() {
		$builder = DB_SQL::select(static::data_source())
			->from(static::table())
			->limit(1);
		foreach (static::primary_key() as $column) {
			$builder->where($column, DB_SQL_Operator::_EQUAL_TO_, $this->fields[$column]->value);
		}
		return $builder->query()->is_loaded();
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
		$primary_key = static::primary_key();
		if ( ! empty($primary_key) AND is_array($primary_key)) {
			if (static::is_auto_incremented()) {
				$column = $primary_key[0];
				if ( ! isset($this->fields[$column])) {
					throw new Throwable_InvalidProperty_Exception('Message: Unable to generate hash code for model. Reason: Primary key contains a non-existent field name.', array(':primary_key' => $primary_key));
				}
				$value = $this->fields[$column]->value;
				return ( ! empty($value)) ? sha1("{$column}={$value}") : NULL;
			}
			$buffer = '';
			foreach ($primary_key as $column) {
				if ( ! isset($this->fields[$column])) {
					throw new Throwable_InvalidProperty_Exception('Message: Unable to generate hash code for model. Reason: Primary key contains a non-existent field name.', array(':primary_key' => $primary_key));
				}
				$value = $this->fields[$column]->value;
				if ($value !== NULL) {
					$buffer .= "{$column}={$value}";
				}
			}
			return ($buffer != '') ? sha1($buffer) : NULL;
		}
		throw new Throwable_Database_Exception('Message: Unable to generate hash code for model. Reason: No primary key has been declared.', array(':primary_key' => $primary_key));
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
		if (isset($this->aliases[$key])) {
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
			$primary_key = static::primary_key();
			if (empty($primary_key) OR ! is_array($primary_key)) {
				throw new Throwable_Marshalling_Exception('Message: Failed to load record from database. Reason: No primary key has been declared.');
			}
			$builder = DB_SQL::select(static::data_source())->from(static::table())->limit(1);
			foreach ($primary_key as $column) {
				$builder->where($column, DB_SQL_Operator::_EQUAL_TO_, $this->fields[$column]->value);
			}
			$record = $builder->query();
			if ( ! $record->is_loaded()) {
				throw new Throwable_Marshalling_Exception('Message: Failed to load record from database. Reason: Unable to match primary key with a record.');
			}
			$columns = $record->fetch(0);
			$this->metadata['loaded'] = TRUE;
			$this->metadata['saved'] = $this->hash_code();
		}
		foreach ($columns as $column => $value) {
			if (isset($this->fields[$column])) {
				$this->fields[$column]->value = $value;
				$this->metadata['loaded'] = TRUE;
			}
			else if (isset($this->aliases[$column])) {
				$this->aliases[$column]->value = $value;
			}
			else if (isset($this->adaptors[$column])) {
				$this->adaptors[$column]->value = $value;
			}
		}
	}

	/**
	 * This function creates a new relation to be used by model's instance.
	 *
	 * @param string $name                          the relation's name
	 * @param enum $type                            the type of relation to be created (e.g.
	 *                                              'belongs_to', 'has_many', 'has_one')
	 * @param array $metadata                       the relation's metadata
	 */
	public function relate($name, $type, Array $metadata) {
		if ( ! is_string($name) OR isset($this->adaptors[$name]) OR isset($this->aliases[$name]) OR isset($this->fields[$name])) {
			throw new Throwable_InvalidArgument_Exception('Message: Invalid relation name defined. Reason: Name ":name" cannot be used for new relation.', array(':name' => $name));
		}
		$types = array('belongs_to' => 'DB_ORM_Relation_BelongsTo', 'has_many' => 'DB_ORM_Relation_HasMany', 'has_one' => 'DB_ORM_Relation_HasOne');
		if ( ! isset($types[$type])) {
			throw new Throwable_InvalidArgument_Exception('Message: Invalid value passed. Reason: Value must be of the correct enumerated type.', array(':name' => $name, ':type' => $type));
		}
		$type = $types[$type];
		$this->relations[$name] = new $type($this, $metadata);
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
	 * @param boolean $mode                         TRUE=save, FALSE=update, NULL=automatic
	 */
	public function save($reload = FALSE, $mode = NULL) {
		if ( ! static::is_savable()) {
			throw new Throwable_Marshalling_Exception('Message: Failed to save record to database. Reason: Model is not savable.', array(':class' => get_called_class()));
		}

		$primary_key = static::primary_key();

		if (empty($primary_key) OR ! is_array($primary_key)) {
			throw new Throwable_Marshalling_Exception('Message: Failed to save record to database. Reason: No primary key has been declared.');
		}

		$data_source = static::data_source();
		$table = static::table();
		$columns = array_keys($this->fields);
		$hash_code = $this->hash_code();

		// Set saving mode
		$do_insert = ($mode === NULL)
			? ($hash_code === NULL)
			: (bool) $mode;

		if ( ! $do_insert) {
			// Check if we have to detect saving mode automatically
			if ($mode === NULL) {
				// Check if the model has been already saved
				$do_insert = (($this->metadata['saved'] === NULL) OR ($hash_code != $this->metadata['saved']));

				// Check if the record exists in database
				if ($do_insert) {
					$builder = DB_SQL::select($data_source)
							->column(DB_SQL::expr(1), 'IsFound')
							->from($table);

					foreach ($primary_key as $column) {
						$builder->where($column, DB_SQL_Operator::_EQUAL_TO_, $this->fields[$column]->value);
					}

					$do_insert = ! ($builder->limit(1)->query()->is_loaded());
				}
			}

			if ( ! $do_insert) {
				if ( ! empty($columns)) {
					$builder = DB_SQL::update($data_source)
						->table($table);

					// Is there any data to save and it's worth to execute the query?
					$is_worth = FALSE;

					foreach ($columns as $column) {
						if ($this->fields[$column]->savable AND $this->fields[$column]->modified) {
							// It's worth do execute the query.
							$is_worth = TRUE;

							// Add column values to the query builder
							$builder->set($column, $this->fields[$column]->value);

							if (in_array($column, $primary_key) OR $this->fields[$column]->value instanceof DB_SQL_Expression) {
								// Reloading required because primary key has been changed or an SQL expression has been used
								$reload = TRUE;
							}
						}

						// Mark field as not modified
						$this->fields[$column]->modified = FALSE;
					}

					// Execute the query only if there is data to save
					if ($is_worth) {
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
			if ( ! empty($columns)) {
				$builder = DB_SQL::insert($data_source)
					->into($table);

				// Is any data to save and it's worth to execute the query?
				$is_worth = FALSE;

				foreach ($columns as $column) {
					if ($this->fields[$column]->savable AND $this->fields[$column]->modified) {
						// It's worth executing the query.
						$is_worth = TRUE;

						// Add column values to the query builder
						$builder->column($column, $this->fields[$column]->value);

						if ($this->fields[$column]->value instanceof DB_SQL_Expression) {
							// Reloading required, if using SQL expresions
							$reload = TRUE;
						}
					}

					// Mark field as not modified
					$this->fields[$column]->modified = FALSE;
				}

				// Execute the query only if there is data to save
				if ($is_worth) {
					if (static::is_auto_incremented() AND ($hash_code === NULL)) {
						// Execute the query and assign the result to the primary key field
						$this->fields[$primary_key[0]]->value = $builder->execute(TRUE);

						// Mark the primary key field as not modified
						$this->fields[$primary_key[0]]->modified = FALSE;
					}
					else {
						$builder->execute();
					}
				}

				$this->metadata['saved'] = $this->hash_code();
			}
		}

		if ($reload) {
			// Reload the record, if it's required
			$this->load();
		}
	}

	/**
	 * This method sets an array of values to their associated fields, aliases and adaptors.
	 * It uses only expected keys listed in $expected. If $expected is NULL, it expects
	 * keys of all fields, aliases and adaptors, except primary key(s), of this Model.
	 *
	 * @access public
	 * @param array $values                         an array of column/value mappings
	 * @param mixed $expected                       an array of keys to take from $values, or NULL
	 * @return DB_ORM_Model                         a reference to the current instance
	 */
	public function set_values(Array $values, Array $expected = NULL) {
		// Automatically create list expected keys
		if ($expected === NULL) {
			$expected = array_merge(
				array_keys($this->fields),
				array_keys($this->aliases),
				array_keys($this->adaptors)
			);

			$expected = array_flip($expected);

			$primary_key = static::primary_key();

			// Remove primary key(s)
			foreach ($primary_key as $key) {
				unset($expected[$key]);
			}
		}
		else {
			$expected = array_flip($expected);
		}

		foreach (array_intersect_key($values, $expected) as $key => $value) {
			$this->$key = $value;
		}

		return $this;
	}

	/**
	 * This function unrelates the specified relation.
	 *
	 * @param string $name                          the relation's name
	 */
	public function unrelate($name) {
		if (isset($this->relations[$name])) {
			unset($this->relations[$name]);
		}
	}

	/**
	 * Updates the record in database.
	 *
	 * @access public
	 * @param boolean $reload                       whether the model should be reloaded
	 *                                              after the save is done
	 */
	public function update($reload = FALSE) {
		$this->save($reload, FALSE);
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
		if ($columns === NULL) {
			$model = get_called_class();
			$record = new $model();
			$columns = array_keys($record->fields);
		}
		return $columns;
	}

	/**
	 * This function returns the data source name.
	 *
	 * @access public
	 * @static
	 * @return string                               the data source name
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
		return (count(static::primary_key()) === 1);
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
		$segments = preg_split('/_/', get_called_class());
		return $segments[count($segments) - 1];
	}

}
?>