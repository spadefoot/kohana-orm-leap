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
 * This class represents an SQL expression.
 *
 * @package Leap
 * @category SQL
 * @version 2013-01-22
 *
 * @abstract
 */
abstract class Base\DB\SQL\Expression extends Core\Object {

	/**
	 * This variable stores the raw SQL expression string.
	 *
	 * @access protected
	 * @var string
	 */
	protected $expr;

	/**
	 * This variable stores the unescaped parameters to be used in the SQL expression.
	 *
	 * @access protected
	 * @var array
	 */
	protected $params;

	/**
	 * This constructor instantiates the class with the specified SQL expression
	 * and parameter values.
	 *
	 * @access public
	 * @param string $expr                          the raw SQL expression
	 * @param array $params                         an associated array of parameter
	 *                                              key/values pairs
	 */
	public function __construct($expr, Array $params = array()) {
		$this->expr = (string) $expr;
		$this->params = $params;
	}

	/**
	 * This function returns the raw SQL expression.
	 *
	 * @access public
	 * @override
	 * @return string                               the raw SQL expression
	 */
	public function __toString() {
		return $this->expr;
	}

	/**
	 * This function binds a value to a parameter.
	 *
	 * @access public
	 * @param string $key                           the parameter key
	 * @param mixed &$value                         the parameter value
	 * @return DB\SQL\Expression                    a reference to the current instance
	 */
	public function bind($key, &$value) {
		$this->params[$key] = &$value;
		return $this;
	}

	/**
	 * This function sets the value of a parameter.
	 *
	 * @access public
	 * @param string $key                           the parameter key
	 * @param mixed $value                          the parameter value
	 * @return DB\SQL\Expression                    a reference to the current instance
	 */
	public function param($key, $value) {
		$this->params[$key] = $value;
		return $this;
	}

	/**
	 * This function adds multiple parameter values.
	 *
	 * @access public
	 * @param array $params                         an associated array of parameter
	 *                                              key/values pairs
	 * @return DB\SQL\Expression                    a reference to the current instance
	 */
	public function parameters(Array $params) {
		$this->params = $params + $this->params;
		return $this;
	}

	/**
	 * This function returns the compiled SQL expression as a string.
	 *
	 * @access public
	 * @param mixed $object                         an instance of the pre-compiler or
	 *                                              data source to be used
	 * @return string                               the compiled SQL expression
	 */
	public function value($object = NULL) {
		if (is_string($object) OR is_array($object) OR ($object instanceof DB\DataSource)) {
			$object = DB\SQL::precompiler($object);
		}
		$expr = $this->expr;
		if (($object instanceof DB\SQL\Precompiler) AND ! empty($this->params)) {
			$params = array_map(array($object, 'prepare_value'), $this->params);
			$expr = strtr($expr, $params);
		}
		return $expr;
	}

}
