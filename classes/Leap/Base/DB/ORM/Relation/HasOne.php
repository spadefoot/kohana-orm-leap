<?php

/**
 * Copyright © 2011–2013 Spadefoot Team.
 *
 * Unless otherwise noted, LEAP is licensed under the Apache License,
 * Version 2.0 (the "License"); you may not use this file except in
 * compliance with the License. You may obtain a copy of the License
 * at:
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
 * This class represents a "has one" relation in a database table.
 *
 * @package Leap
 * @category ORM
 * @version 2013-01-28
 *
 * @abstract
 */
abstract class Base\DB\ORM\Relation\HasOne extends DB\ORM\Relation {

	/**
	 * This constructor initializes the class.
	 *
	 * @access public
	 * @override
	 * @param DB\ORM\Model $model                   a reference to the implementing model
	 * @param array $metadata                       the relation's metadata
	 */
	public function __construct(DB\ORM\Model $model, Array $metadata = array()) {
		parent::__construct($model, 'has_one');

		// the parent model is the referenced table
		$parent_model = get_class($model);

		// Get parent model's name into variable, otherways a late static binding code throws a
		// syntax error when used like this: $this->metadata['parent_model']::primary_key()
		$this->metadata['parent_model'] = $parent_model;

		// the parent key (i.e. candidate key) is an ordered list of field names in the parent model
		$this->metadata['parent_key'] = (isset($metadata['parent_key']))
			? (array) $metadata['parent_key']
			: $parent_model::primary_key();

		// the child model is the referencing table
		$this->metadata['child_model'] = DB\ORM\Model::model_name($metadata['child_model']);

		// the child key (i.e. foreign key) is an ordered list of field names in the child model
		$this->metadata['child_key'] = (array) $metadata['child_key'];
	}

	/**
	 * This function loads the corresponding model.
	 *
	 * @access protected
	 * @override
	 * @return DB\ORM\Model							the corresponding model
	 */
	protected function load() {
		$parent_key = $this->metadata['parent_key'];

		$child_model = $this->metadata['child_model'];
		$child_table = $child_model::table();
		$child_key = $this->metadata['child_key'];
		$child_source = $child_model::data_source(DB\DataSource::SLAVE_INSTANCE);

		$builder = DB\SQL::select($child_source)
			->all("{$child_table}.*")
			->from($child_table);

		$field_count = count($child_key);
		for ($i = 0; $i < $field_count; $i++) {
			$builder->where("{$child_table}.{$child_key[$i]}", DB\SQL\Operator::_EQUAL_TO_, $this->model->{$parent_key[$i]});
		}

		$result = $builder->limit(1)->query($child_model);

		if ($result->is_loaded()) {
			return $result->fetch(0);
		}

		$record = new $child_model();
		for ($i = 0; $i < $field_count; $i++) {
			$record->{$child_key[$i]} = $this->model->{$parent_key[$i]};
		}
		return $record;
	}

}
