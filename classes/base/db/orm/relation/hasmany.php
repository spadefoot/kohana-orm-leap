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
 * This class represents a "has many" relation in a database table.
 *
 * @package Leap
 * @category ORM
 * @version 2011-12-17
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

		// the candidate key is an ordered list of field names in the parent model
		if (isset($metadata['candidate_key'])) {
			$this->metadata['candidate_key'] = $metadata['candidate_key'];
		}
		else if (isset($metadata['parent_key'])) {
			$this->metadata['candidate_key'] = $metadata['parent_key'];
		}
		else {
			$this->metadata['candidate_key'] = call_user_func(array($this->metadata['parent_model'], 'primary_key'));
		}

		// the child model is the referencing table
		$this->metadata['child_model'] = DB_ORM_Model::model_name($metadata['child_model']);

		// the foreign key is an ordered list of field names in the child model
		$this->metadata['foreign_key'] = (isset($metadata['foreign_key']))
			? $metadata['foreign_key']
			: $metadata['child_key'];

		$this->metadata['options'] = (isset($metadata['options']))
			? (array) $metadata['options'] // a set of options that will modify the query
			: array();
	}

	/**
	 * This function loads the corresponding model(s).
	 *
	 * @access protected
	 * @return DB_ResultSet                         the corresponding model(s)
	 */
	protected function load() {
		$child_model = $this->metadata['child_model'];

		$candidate_key = $this->metadata['candidate_key'];
		$foreign_key = $this->metadata['foreign_key'];

		$field_count = count($foreign_key);

		$sql = DB_ORM::select($child_model);
		for ($i = 0; $i < $field_count; $i++) {
			$sql->where($foreign_key[$i], DB_SQL_Operator::_EQUAL_TO_, $this->model->{$candidate_key[$i]});
		}
		foreach ($this->metadata['options'] as $option) {
			call_user_func_array(array($sql, $option[0]), $option[1]);
		}
		$result = $sql->query();

		return $result;
	}

}
?>