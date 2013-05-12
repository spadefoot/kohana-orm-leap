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
 * This class acts as an extension to the a builder class.
 *
 * @package Leap
 * @category ORM
 * @version 2013-01-28
 *
 * @abstract
 */
abstract class Base\DB\ORM\Builder extends Core\Object {

	/**
	 * This variable stores an instance of the SQL builder class.
	 *
	 * @access protected
	 * @var DB\SQL\Builder
	 */
	protected $builder;

	/**
	 * This constructor instantiates this class.
	 *
	 * @access public
	 * @param DB\SQL\Builder $builder             the SQL builder class to be extended
	 */
	public function __construct(DB\SQL\Builder $builder) {
		$this->builder = $builder;
	}

}
