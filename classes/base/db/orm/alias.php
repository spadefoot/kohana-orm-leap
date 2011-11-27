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
 * This class represents an alias for a field in a database table.
 *
 * @package Leap
 * @category ORM
 * @version 2011-11-26
 *
 * @abstract
 */
abstract class Base_DB_ORM_Alias extends Kohana_Object {

    /**
     * This variable stores the name of the field in the database table.
     *
     * @access protected
     * @var string
     */
    protected $field = NULL;

    /**
     * This constructor initializes the class.
     *
     * @access public
     * @param DB_ORM_Model $active_record           a reference to the implementing active record
     * @param string $field                         the name of field in the database table
     */
    public function __construct(DB_ORM_Model $active_record, $field) {
        $this->field = $field;
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
        switch ($key) {
            case 'field':
                return $this->field;
            break;
        }
        throw new Kohana_InvalidProperty_Exception('Message: Unable to get the specified property. Reason: Property :key is either inaccessible or undefined.', array(':key' => $key));
    }

}
?>