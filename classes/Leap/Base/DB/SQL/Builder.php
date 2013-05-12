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
 * This class provides the base functionality for an SQL statement.
 *
 * @package Leap
 * @category SQL
 * @version 2013-01-27
 *
 * @abstract
 */
abstract class Base\DB\SQL\Builder extends Core\Object implements DB\SQL\Statement {

	/**
	 * This constant represents a closing parenthesis.
	 *
	 * @access public
	 * @const string
	 */
	const _CLOSING_PARENTHESIS_ = ')';

	/**
	 * This constant represents an opening parenthesis.
	 *
	 * @access public
	 * @const string
	 */
	const _OPENING_PARENTHESIS_ = '(';

	/**
	 * This variable stores the build data for the SQL statement.
	 *
	 * @access protected
	 * @var array
	 */
	protected $data;

	/**
	 * This variable stores the name of the SQL dialect being used.
	 *
	 * @access protected
	 * @var string
	 */
	protected $dialect;

	/**
	 * This variable stores a reference to the pre-compiler.
	 *
	 * @access protected
	 * @var DB\SQL\Precompiler
	 */
	protected $precompiler;

	/**
	 * This function returns the raw SQL statement.
	 *
	 * @access public
	 * @override
	 * @return string                           the raw SQL statement
	 */
	public function __toString() {
		return $this->statement(TRUE);
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This function returns a new instance of the calling class.
	 *
	 * @access public
	 * @static
	 * @param DB\DataSource $data_source        the data source to be used
	 * @return DB\SQL\Builder                   a new instance of the calling class
	 */
	public static function factory(DB\DataSource $data_source) {
		$class = get_called_class();
		return new $class($data_source);
	}

}
