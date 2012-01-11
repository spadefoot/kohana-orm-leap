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
 * This class represents a "has many" relation in a database table.
 *
 * @package Leap
 * @category ORM
 * @version 2011-12-30
 *
 * @abstract
 */
abstract class Base_DB_ORM_Relation_HasMany extends DB_ORM_Relation {

	/**
	 * This constructor initializes the class.
	 *
	 * @access public
	 * @param DB_ORM_Model $model                   a reference to the implementing model
	 * @param array $metadata                       the relation's metadata
	 */
	public function __construct(DB_ORM_Model $model, Array $metadata = array()) {
		parent::__construct($model, 'has_many');

		// the parent model is the referenced table
		$this->metadata['parent_model'] = get_class($model);

		// the parent key (i.e. candidate key) is an ordered list of field names in the parent model
		$this->metadata['parent_key'] = (isset($metadata['parent_key']))
		    ? (array) $metadata['parent_key']
		    : call_user_func(array($this->metadata['parent_model'], 'primary_key'));

		// the child model is the referencing table
		$this->metadata['child_model'] = DB_ORM_Model::model_name($metadata['child_model']);

		// the child key (i.e. foreign key) is an ordered list of field names in the child model
		$this->metadata['child_key'] = (array) $metadata['child_key'];

        // a set of options that will modify the query
		$this->metadata['options'] = (isset($metadata['options']))
			? (array) $metadata['options']
			: array();
	}

	/**
	 * This function loads the corresponding model(s).
	 *
	 * @access protected
	 * @return DB_ResultSet                         the corresponding model(s)
	 */
	protected function load() {
		$parent_key = $this->metadata['parent_key'];

		$child_model = $this->metadata['child_model'];
		$child_key = $this->metadata['child_key'];

		$field_count = count($child_key);

		$builder = DB_ORM::select($child_model);
		for ($i = 0; $i < $field_count; $i++) {
			$builder->where($child_key[$i], DB_SQL_Operator::_EQUAL_TO_, $this->model->{$parent_key[$i]});
		}
		foreach ($this->metadata['options'] as $option) {
			call_user_func_array(array($builder, $option[0]), $option[1]);
		}
		$result = $builder->query();

		return $result;
	}

}
?>