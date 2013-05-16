<?php

/**
 * Copyright © 2011–2013 Spadefoot Team.
 * Copyright © 2012 CubedEye.
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

namespace Leap\Base\Model {

	use \Leap\DB;

	/**
	 * This class represents a record in the "roles" table.
	 *
	 * @package Leap
	 * @category Model
	 * @version 2013-01-28
	 *
	 * @abstract
	 */
	abstract class Role extends DB\ORM\Model {

		/**
		 * This constructor instantiates this class.
		 *
		 * @access public
		 */
		public function __construct() {
			parent::__construct();

			$this->fields = array(
				'id' => new DB\ORM\Field\Integer($this, array(
					'max_length' => 11,
					'nullable' => FALSE,
					'unsigned' => TRUE,
				)),
				'name' => new DB\ORM\Field\String($this, array(
					'max_length' => 255,
					'nullable' => FALSE,
				)),
				'description' => new DB\ORM\Field\String($this, array(
					'max_length' => 255,
					'nullable' => FALSE,
				)),
			);

			$this->relations = array(
				'user_roles' => new DB\ORM\Relation\HasMany($this, array(
					'child_key' => array('id'),
					'child_model' => 'User_Role',
					'parent_key' => array('id'),
				)),	 
			);
		}

		/**
		 * This function returns the data source name.
		 *
		 * @access public
		 * @override
		 * @static
		 * @param integer $instance                     the data source instance to be used (e.g.
		 *                                              0 = master, 1 = slave, 2 = slave, etc.)
		 * @return string                               the data source name
		 */
		public static function data_source($instance = 0) {
			return 'default';	
		}

		/**
		 * This function returns the primary key for the database table.
		 *
		 * @access public
		 * @override
		 * @static
		 * @return array                                the primary key
		 */
		public static function primary_key() {
			return array('id');	
		}

		/**
		 * This function returns the database table's name.
		 *
		 * @access public
		 * @override
		 * @static
		 * @return string                               the database table's name
		 */
		public static function table() {
			return 'roles';	
		}

	}

}