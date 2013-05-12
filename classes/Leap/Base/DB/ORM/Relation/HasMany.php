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
 * This class represents a "has many" relation in a database table.
 *
 * @package Leap
 * @category ORM
 * @version 2013-01-28
 *
 * @abstract
 */
abstract class Base\DB\ORM\Relation\HasMany extends DB\ORM\Relation {

	/**
	 * This constructor initializes the class.
	 *
	 * @access public
	 * @override
	 * @param DB\ORM\Model $model                   a reference to the implementing model
	 * @param array $metadata                       the relation's metadata
	 */
	public function __construct(DB\ORM\Model $model, Array $metadata = array()) {
		parent::__construct($model, 'has_many');

		// the parent model is the referenced table
		$parent_model = get_class($model);

		// Get parent model's name into variable, otherways a late static binding code throws a
		// syntax error when used like this: $this->metadata['parent_model']::primary_key()
		$this->metadata['parent_model'] = $parent_model;

		// the parent key (i.e. candidate key) is an ordered list of field names in the parent model
		$this->metadata['parent_key'] = (isset($metadata['parent_key']))
			? (array) $metadata['parent_key']
			: $parent_model::primary_key();

		// the through model is the pivot table
		if (isset($metadata['through_model'])) {
			$this->metadata['through_model'] = DB\ORM\Model::model_name($metadata['through_model']);
		}

		// the through keys is an array of two ordered lists of fields names: [0] matches with parent key and [1] matches with child key
		if (isset($metadata['through_keys'])) {
			$this->metadata['through_keys'] = (array) $metadata['through_keys'];
		}

		// the child model is the referencing table
		$this->metadata['child_model'] = DB\ORM\Model::model_name($metadata['child_model']);

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
	 * @override
	 * @return DB\ResultSet                         the corresponding model(s)
	 */
	protected function load() {
		$parent_key = $this->metadata['parent_key'];

		$child_model = $this->metadata['child_model'];
		$child_table = $child_model::table();
		$child_key = $this->metadata['child_key'];
		$child_source = $child_model::data_source(DB\DataSource::SLAVE_INSTANCE);

		if (isset($this->metadata['through_model']) AND isset($this->metadata['through_keys'])) {
			$through_model = $this->metadata['through_model'];
			$through_table = $through_model::table();
			$through_keys = $this->metadata['through_keys'];
			$through_source = $through_model::data_source(DB\DataSource::SLAVE_INSTANCE);

			if ($through_source != $child_source) {
				$builder = DB\SQL::select($through_source)
					->from($through_table);

				$field_count = count($through_keys[1]);
				for ($i = 0; $i < $field_count; $i++) {
					$builder->column("{$through_table}.{$through_keys[1][$i]}");
				}

				$field_count = count($through_keys[0]);
				for ($i = 0; $i < $field_count; $i++) {
					$builder->where("{$through_table}.{$through_keys[0][$i]}", DB\SQL\Operator::_EQUAL_TO_, $this->model->{$parent_key[$i]});
				}

				$records = $builder->query('array');

				$builder = DB\SQL::select($child_source)
					->all("{$child_table}.*")
					->from($child_table);

				$field_count = count($child_key);
				foreach ($records as $record) {
					$builder->where_block(DB\SQL\Builder::_OPENING_PARENTHESIS_, DB\SQL\Connector::_OR_);
					for ($i = 0; $i < $field_count; $i++) {
						$builder->where("{$child_table}.{$child_key[$i]}", DB\SQL\Operator::_EQUAL_TO_, $this->model->{$record[$through_keys[1][$i]]});
					}
					$builder->where_block(DB\SQL\Builder::_CLOSING_PARENTHESIS_);
				}

				foreach ($this->metadata['options'] as $option) {
					call_user_func_array(array($builder, $option[0]), $option[1]);
				}

				$result = $builder->query($child_model);
			}
			else {
				$builder = DB\SQL::select($child_source)
					->all("{$child_table}.*")
					->from($through_table)
					->join(DB\SQL\JoinType::_INNER_, $child_table);

				$field_count = count($child_key);
				for ($i = 0; $i < $field_count; $i++) {
					$builder->on("{$child_table}.{$child_key[$i]}", DB\SQL\Operator::_EQUAL_TO_, "{$through_table}.{$through_keys[1][$i]}");
				}

				$field_count = count($through_keys[0]);
				for ($i = 0; $i < $field_count; $i++) {
					$builder->where("{$through_table}.{$through_keys[0][$i]}", DB\SQL\Operator::_EQUAL_TO_, $this->model->{$parent_key[$i]});
				}

				foreach ($this->metadata['options'] as $option) {
					call_user_func_array(array($builder, $option[0]), $option[1]);
				}

				$result = $builder->query($child_model);
			}
		}
		else {
			$builder = DB\SQL::select($child_source)
				->all("{$child_table}.*")
				->from($child_table);

			$field_count = count($child_key);
			for ($i = 0; $i < $field_count; $i++) {
				$builder->where("{$child_table}.{$child_key[$i]}", DB\SQL\Operator::_EQUAL_TO_, $this->model->{$parent_key[$i]});
			}

			foreach ($this->metadata['options'] as $option) {
				call_user_func_array(array($builder, $option[0]), $option[1]);
			}

			$result = $builder->query($child_model);
		}

		return $result;
	}

}
