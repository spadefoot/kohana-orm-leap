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
 * This class represents an "array" field (i.e. a delimitated string) in
 * a database table.
 *
 * @package Leap
 * @category ORM
 * @version 2011-12-02
 *
 * @abstract
 */
abstract class Base_DB_ORM_Field_Array extends DB_ORM_Field {

    /**
     * This constructor initializes the class.
     *
     * @access public
     * @param DB_ORM_Model $model                   a reference to the implementing model
     * @param array $metadata                       the field's metadata
     */
    public function __construct(DB_ORM_Model $model, Array $metadata = array()) {
        parent::__construct($model, 'array');

        $this->metadata['max_length'] = (integer)$metadata['max_length'];

		if (isset($metadata['savable'])) {
            $this->metadata['savable'] = (boolean)$metadata['savable'];
        }

        if (isset($metadata['nullable'])) {
            $this->metadata['nullable'] = (boolean)$metadata['nullable'];
        }

        if (isset($metadata['filter'])) {
            $this->metadata['filter'] = (string)$metadata['filter'];
        }

        if (isset($metadata['callback'])) {
            $this->metadata['callback'] = (string)$metadata['callback'];
        }
        
        $this->metadata['delimiter'] = (isset($metadata['delimiter']))
            ? (string)$metadata['delimiter']
            : ',';

        if (isset($metadata['default'])) {
            $default = $metadata['default'];
            if (!is_null($default)) {
                if (is_string($value)) {
                    $regex = '/' . preg_quote($this->metadata['delimiter']) . '/';
                    $default = preg_split($regex, $default);
                }
                settype($default, $this->metadata['type']);
                $this->validate($default);
            }
            $this->metadata['default'] = $default;
            $this->value = $default;
        }
        else if (!$this->metadata['nullable']) {
            $default = '';
            $this->metadata['default'] = $default;
            $this->value = $default;
        }
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
            case 'data':
                if (is_array($this->value)) {
                    return implode($this->metadata['delimiter'], $this->value);
                }
            case 'value':
                return $this->value;
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
                if (!is_null($value)) {
                    if (is_string($value)) {
                        $regex = '/' . preg_quote($this->metadata['delimiter']) . '/';
                        $value = preg_split($regex, $value);
                    }
                    settype($value, $this->metadata['type']);
                    $this->validate($value);
                    $this->value = $value;
                }
                else {
                    $this->value = $this->metadata['default'];
                }
                $this->metadata['modified'] = TRUE;
            break;
            case 'modified':
                $this->metadata['modified'] = (bool)$value;
            break;
            default:
                throw new Kohana_InvalidProperty_Exception('Message: Unable to set the specified property. Reason: Property :key is either inaccessible or undefined.', array(':key' => $key, ':value' => $value));
            break;
        }
    }

    /**
     * This function validates the specified value against any constraints.
     *
     * @access protected
     * @param mixed $value                          the value to be validated
     * @return boolean                              whether the specified value validates
     */
    protected function validate($value) {
        if (!is_null($value)) {
            if (strlen($value) > $this->metadata['max_length']) {
                return FALSE;
            }
        }
        return parent::validate($value);
    }

}
?>