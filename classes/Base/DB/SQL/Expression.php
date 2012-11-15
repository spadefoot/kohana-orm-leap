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
 * This class represents an SQL expression.
 *
 * @package Leap
 * @category SQL
 * @version 2012-11-14
 *
 * @abstract
 */
abstract class Base_DB_SQL_Expression extends Core_Object {

	/**
	 * This variable stores the raw SQL expression string.
	 *
	 * @access protected
	 * @var string
	 */
	protected $expr = NULL;

	/**
	 * This variable stores the unescaped parameters to be used in the SQL expression.
	 *
	 * @access protected
	 * @var array
	 */
	protected $params = NULL;

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
	 * This function binds a value to a parameter.
	 *
	 * @access public
	 * @param string $key                           the parameter key
	 * @param mixed &$value                         the parameter value
	 * @return DB_SQL_Expression                    a reference to the current instance
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
	 * @return DB_SQL_Expression                    a reference to the current instance
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
	 * @return DB_SQL_Expression                    a reference to the current instance
	 */
	public function parameters(Array $params) {
		$this->params = $params + $this->params;
		return $this;
	}

	/**
	 * This function returns the compiled SQL expression as a string.
	 *
	 * @access public
	 * @param mixed $compiler                       an instance of the compiler or data
	 *                                              source to be used
	 * @return string                               the compiled SQL expression
	 */
	public function value($compiler = NULL) {
		if (is_string($compiler) OR is_array($compiler) OR ($compiler instanceof DB_DataSource)) {
			$source = new DB_DataSource($compiler);
			$compiler = 'DB_' . $source->dialect . '_Expression';
			$compiler = new $compiler($source);
		}
		$expr = $this->expr;
		if (($compiler instanceof DB_SQL_Expression_Interface) AND ! empty($this->params)) {
			$params = array_map(array($compiler, 'prepare_value'), $this->params);
			$expr = strtr($expr, $params);
		}
		return $expr;
	}

	/**
	 * This function returns the raw SQL expression.
	 *
	 * @access public
	 * @return string                               the raw SQL expression
	 */
	public function __toString() {
		return $this->expr;
	}

}
?>
