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
 * This class acts as an extension to the a builder class.
 *
 * @package Leap
 * @category ORM
 * @version 2012-11-14
 *
 * @abstract
 */
abstract class Base_DB_ORM_Builder extends Core_Object {

	/**
	 * This variable stores an instance of the SQL builder class.
	 *
	 * @access protected
	 * @var DB_SQL_Builder
	 */
	protected $builder = NULL;

	/**
	 * This constructor instantiates this class.
	 *
	 * @access public
	 * @param DB_SQL_Builder $builder             the SQL builder class to be extended
	 */
	public function __construct(DB_SQL_Builder $builder) {
		$this->builder = $builder;
	}

}
?>