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
 * This class represents an "array" field (i.e. an JSON encoded string) in
 * a database table.
 *
 * @package Leap
 * @category ORM
 * @version 2011-12-03
 *
 * @abstract
 */
abstract class Base_DB_ORM_Alias_JSON extends DB_ORM_Alias {

    /**
     * This constructor initializes the class.
     *
     * @access public
     * @param DB_ORM_Model $model                   a reference to the implementing model
     * @param string $field                         the name of field in the database table
     * @param array $metadata                       the field's metadata
     * @throws Kohana_InvalidArgument_Exception     indicates that an invalid field name was specified
     */
    public function __construct(DB_ORM_Model $model, $field, Array $metadata = array()) {
        parent::__construct($model, $field);

        $this->metadata['prefix'] = (isset($metadata['prefix']))
            ? (string)$metadata['prefix']
            : '';

        $this->metadata['suffix'] = (isset($metadata['suffix']))
            ? (string)$metadata['suffix']
            : '';
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
            case 'value':
                $string = $this->model->{$this->metadata['field']};
                $start = strlen($this->metadata['prefix']);
                $length = strlen($string) - ($start + strlen($this->metadata['suffix']));
                if ($length >= 0) {
                    $string = substr($string, $start, $length);
                }
                $array = json_decode($string, TRUE);
                return $array;
            break;
            default:
                if (isset($this->metadata[$key])) { return $this->metadata[$key]; }
            break;
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
        switch ($key) {
            case 'value':
                if (is_array($value)) {
                    $value = $this->metadata['prefix'] . json_encode($value) . $this->metadata['suffix'];
                }
                $this->model->{$this->metadata['field']} = $value;
            break;
            default:
                throw new Kohana_InvalidProperty_Exception('Message: Unable to set the specified property. Reason: Property :key is either inaccessible or undefined.', array(':key' => $key, ':value' => $value));
            break;
        }
    }

}
?>