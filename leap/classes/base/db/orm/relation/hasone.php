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
 * This class represents a "has one" relation in a database table.
 *
 * @package Leap
 * @category ORM
 * @version 2011-06-05
 *
 * @abstract
 */
abstract class Base_DB_ORM_Relation_HasOne extends DB_ORM_Relation {

    /**
     * This constructor initializes the class.
     *
     * @access public
     * @param DB_ORM_Model $active_record           a reference to the implementing active record
     * @param array $metadata                       the relation's metadata
     */
    public function __construct(DB_ORM_Model $active_record, Array $metadata = array()) {
        parent::__construct($active_record, 'has_one');

        $this->metadata['parent_model'] = get_class($active_record); // the referenced table

        $this->metadata['candidate_key'] = (isset($metadata['candidate_key'])) // an ordered list of field names in parent model
                ? $metadata['candidate_key']
                : call_user_func(array($this->metadata['parent_model'], 'primary_key'));

        $this->metadata['child_model'] = DB_ORM_Model::model_name($metadata['child_model']); // the referencing table

        $this->metadata['foreign_key'] = $metadata['foreign_key']; // an ordered list of field names in child model
    }

	/**
	 * This function loads the corresponding active record(s).
	 *
	 * @access protected
	 * @return mixed								the corresponding active record(s)
	 */
	protected function load() {
		$child_model = $this->metadata['child_model'];

        $candidate_key = $this->metadata['candidate_key'];
		$foreign_key = $this->metadata['foreign_key'];

        $field_count = count($foreign_key);

		$sql = DB_ORM::select($child_model);
        for ($i = 0; $i < $field_count; $i++) {
            $sql->where($foreign_key[$i], DB_SQL_Operator::_EQUAL_TO_, $this->active_record->{$candidate_key[$i]});
        }
        $result = $sql->limit(1)->query();

        if (!$result->is_loaded()) {
            $record = new $child_model();
            for ($i = 0; $i < $field_count; $i++) {
                $record->{$foreign_key[$i]} = $this->active_record->{$candidate_key[$i]};
            }
            return $record;
        }

        return $result->fetch();
    }

}
?>