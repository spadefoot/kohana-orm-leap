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
 * This class represents an "XML" adaptor for an XML formatted string field
 * in a database table.
 *
 * @package Leap
 * @category ORM
 * @version 2012-08-16
 *
 * @abstract
 */
abstract class Base_DB_ORM_Field_Adaptor_XML extends DB_ORM_Field_Adaptor {

	/**
	 * This constructor initializes the class.
	 *
	 * @access public
	 * @param DB_ORM_Model $model                   a reference to the implementing model
	 * @param array $metadata                       the adaptor's metadata
	 * @throws Kohana_InvalidArgument_Exception     indicates that an invalid field name
	 *                                              was specified
	 */
	public function __construct(DB_ORM_Model $model, Array $metadata = array()) {
		parent::__construct($model, $metadata['field']);
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
	public /*override*/ function __get($key) {
		switch ($key) {
			case 'value':
				$value = $this->model->{$this->metadata['field']};
				if ($value !== NULL && ! ($value instanceof DB_SQL_Expression)) {
					$value = new XML($value);
				}
				return $value;
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
	 * @param mixed $value                          the value of the property
	 * @throws Kohana_InvalidProperty_Exception     indicates that the specified property is
	 *                                              either inaccessible or undefined
	 */
	public /*override*/ function __set($key, $value) {
		switch ($key) {
			case 'value':
				if (is_object($value) && ($value instanceof SimpleXMLElement)) {
					$value = $value->asXML();
				}
				else if (is_array($value)) {
					$value = XML::encode($value, TRUE);
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