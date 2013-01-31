<?php defined('SYSPATH') OR die('No direct script access.');

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
 * This class provides a way to access the scheme for a database.
 *
 * @package Leap
 * @category Schema
 * @version 2013-01-30
 *
 * @abstract
 */
abstract class Base_DB_Schema extends Core_Object {

	/**
	 * This variable stores a reference to the data source.
	 *
	 * @access protected
	 * @var DB_DataSource
	 */
	protected $data_source;

	/**
	 * This variable stores a reference to the helper class that implements the expression
	 * interface.
	 *
	 * @access protected
	 * @var DB_SQL_Precompiler
	 */
	protected $precompiler;

	/**
	 * This constructor instantiates this class using the specified data source.
	 *
	 * @access public
	 * @param mixed $config                  the data source configurations
	 */
	public function __construct($config) {
		$this->data_source = new DB_DataSource($config);
		$precompiler = 'DB_' . $this->data_source->dialect . '_Precompiler';
		$this->precompiler = new $precompiler();
	}

	/**
	 * This function returns an associated array of default properties for the specified
	 * SQL data type.
	 *
	 * @access public
	 * @param string $type                   the SQL data type
	 * @return array                         an associated array of default properties
	 *                                       for the specified data type
	 *
	 * @license http://kohanaframework.org/license
	 */
	public function data_type($type) {
		static $types = array(
			// SQL-92
			'BIT'                             => array('type' => 'string', 'exact' => TRUE),
			'BIT VARYING'                     => array('type' => 'string'),
			'CHAR'                            => array('type' => 'string', 'exact' => TRUE),
			'CHAR VARYING'                    => array('type' => 'string'),
			'CHARACTER'                       => array('type' => 'string', 'exact' => TRUE),
			'CHARACTER VARYING'               => array('type' => 'string'),
			'DATE'                            => array('type' => 'string'),
			'DEC'                             => array('type' => 'float', 'exact' => TRUE),
			'DECIMAL'                         => array('type' => 'float', 'exact' => TRUE),
			'DOUBLE PRECISION'                => array('type' => 'float'),
			'FLOAT'                           => array('type' => 'float'),
			'INT'                             => array('type' => 'int', 'min' => '-2147483648', 'max' => '2147483647'),
			'INTEGER'                         => array('type' => 'int', 'min' => '-2147483648', 'max' => '2147483647'),
			'INTERVAL'                        => array('type' => 'string'),
			'NATIONAL CHAR'                   => array('type' => 'string', 'exact' => TRUE),
			'NATIONAL CHAR VARYING'           => array('type' => 'string'),
			'NATIONAL CHARACTER'              => array('type' => 'string', 'exact' => TRUE),
			'NATIONAL CHARACTER VARYING'      => array('type' => 'string'),
			'NCHAR'                           => array('type' => 'string', 'exact' => TRUE),
			'NCHAR VARYING'                   => array('type' => 'string'),
			'NUMERIC'                         => array('type' => 'float', 'exact' => TRUE),
			'REAL'                            => array('type' => 'float'),
			'SMALLINT'                        => array('type' => 'int', 'min' => '-32768', 'max' => '32767'),
			'TIME'                            => array('type' => 'string'),
			'TIME WITH TIME ZONE'             => array('type' => 'string'),
			'TIMESTAMP'                       => array('type' => 'string'),
			'TIMESTAMP WITH TIME ZONE'        => array('type' => 'string'),
			'VARCHAR'                         => array('type' => 'string'),

			// SQL:1999
			'BINARY LARGE OBJECT'             => array('type' => 'string', 'binary' => TRUE),
			'BLOB'                            => array('type' => 'string', 'binary' => TRUE),
			'BOOLEAN'                         => array('type' => 'bool'),
			'CHAR LARGE OBJECT'               => array('type' => 'string'),
			'CHARACTER LARGE OBJECT'          => array('type' => 'string'),
			'CLOB'                            => array('type' => 'string'),
			'NATIONAL CHARACTER LARGE OBJECT' => array('type' => 'string'),
			'NCHAR LARGE OBJECT'              => array('type' => 'string'),
			'NCLOB'                           => array('type' => 'string'),
			'TIME WITHOUT TIME ZONE'          => array('type' => 'string'),
			'TIMESTAMP WITHOUT TIME ZONE'     => array('type' => 'string'),

			// SQL:2003
			'BIGINT'                          => array('type' => 'int', 'min' => '-9223372036854775808', 'max' => '9223372036854775807'),

			// SQL:2008
			'BINARY'                          => array('type' => 'string', 'binary' => TRUE, 'exact' => TRUE),
			'BINARY VARYING'                  => array('type' => 'string', 'binary' => TRUE),
			'VARBINARY'                       => array('type' => 'string', 'binary' => TRUE),
		);

		if (isset($types[$type])) {
			return $types[$type];
		}

		return array();
	}

	/**
	 * This function returns a result set that contains an array of all fields in
	 * the specified database table/view.
	 *
	 * @access public
	 * @abstract
	 * @param string $table                 the table/view to evaluated
	 * @param string $like                  a like constraint on the query
	 * @return array                        an array of fields within the specified
	 *                                      table
	 */
	public abstract function fields($table, $like = '');

	/**
	 * This function returns a result set of indexes for the specified table.
	 *
	 * +---------------+---------------+------------------------------------------------------------+
	 * | field         | data type     | description                                                |
	 * +---------------+---------------+------------------------------------------------------------+
	 * | schema        | string        | The name of the schema that contains the table.            |
	 * | table         | string        | The name of the table.                                     |
	 * | index         | string        | The name of the index.                                     |
	 * | column        | string        | The name of the column.                                    |
	 * | seq_index     | integer       | The sequence index of the index.                           |
	 * | ordering      | string        | The ordering of the index.                                 |
	 * | unique        | boolean       | Indicates whether index on column is unique.               |
	 * | primary       | boolean       | Indicates whether index on column is a primary key.        |
	 * +---------------+---------------+------------------------------------------------------------+
	 *
	 * @access public
	 * @abstract
	 * @param string $table                 the table to evaluated
	 * @param string $like                  a like constraint on the query
	 * @return DB_ResultSet                 a result set of indexes for the specified
	 *                                      table
	 */
	public abstract function indexes($table, $like = '');

	/**
	 * This function extracts a field's data type information.  For example:
	 *
	 *     'INTEGER' becomes array('INTEGER', 0, 0)
	 *     'CHAR(6)' becomes array('CHAR', 6, 0)
	 *     'DECIMAL(10, 5)' becomes array('DECIMAL', 10, 5)
	 *
	 * @access protected
	 * @param string $type                  the data type to be parsed
	 * @return array                        an array with the field's type
	 *                                      information
	 *
	 * @license http://kohanaframework.org/license
	 *
	 * @see http://kohanaframework.org/3.2/guide/api/Database#_parse_type
	 */
	protected function parse_type($type) {
		if (($open = strpos($type, '(')) === FALSE) {
			return array(strtoupper($type), 0, 0);
		}

		$close = strpos($type, ')', $open);

		$args = preg_split('/,/', substr($type, $open + 1, $close - 1 - $open));

		$info = array();

		$info[0] = strtoupper(substr($type, 0, $open) . substr($type, $close + 1)); // type
		$info[1] = (isset($args[0])) ? (int) trim($args[0]) : 0; // max_length, max_digits, precision
		$info[2] = (isset($args[1])) ? (int) trim($args[1]) : 0; // max_decimals, scale

		return $info;
	}

	/**
	 * This function returns a result set of database tables.
	 *
	 * +---------------+---------------+------------------------------------------------------------+
	 * | field         | data type     | description                                                |
	 * +---------------+---------------+------------------------------------------------------------+
	 * | schema        | string        | The name of the schema that contains the table.            |
	 * | table         | string        | The name of the table.                                     |
	 * | type          | string        | The type of table.                                         |
	 * +---------------+---------------+------------------------------------------------------------+
	 *
	 * @access public
	 * @abstract
	 * @param string $like                  a like constraint on the query
	 * @return DB_ResultSet                 a result set of database tables
	 */
	public abstract function tables($like = '');

	/**
	 * This function returns a result set of triggers for the specified table.
	 *
	 * +---------------+---------------+------------------------------------------------------------+
	 * | field         | data type     | description                                                |
	 * +---------------+---------------+------------------------------------------------------------+
	 * | schema        | string        | The name of the schema that contains the table.            |
	 * | table         | string        | The name of the table to which the trigger is defined on.  |
	 * | trigger       | string        | The name of the trigger.                                   |
	 * | event         | string        | 'INSERT', 'DELETE', or 'UPDATE'                            |
	 * | timing        | string        | 'BEFORE', 'AFTER', or 'INSTEAD OF'                         |
	 * | per           | string        | 'ROW', 'STATEMENT', or 'EVENT'                             |
	 * | action        | string        | The action that will be triggered.                         |
	 * | seq_index     | integer       | The sequence index of the trigger.                         |
	 * | created       | date/time     | The date/time of when the trigger was created.             |
	 * +---------------+---------------+------------------------------------------------------------+
	 *
	 * @access public
	 * @abstract
	 * @param string $table                 the table to evaluated
	 * @param string $like                  a like constraint on the query
	 * @return DB_ResultSet                 a result set of triggers for the specified
	 *                                      table
	 */
	public abstract function triggers($table, $like = '');

	/**
	 * This function returns a result set of database views.
	 *
	 * +---------------+---------------+------------------------------------------------------------+
	 * | field         | data type     | description                                                |
	 * +---------------+---------------+------------------------------------------------------------+
	 * | schema        | string        | The name of the schema that contains the table.            |
	 * | table         | string        | The name of the table.                                     |
	 * | type          | string        | The type of table.                                         |
	 * +---------------+---------------+------------------------------------------------------------+
	 *
	 * @access public
	 * @abstract
	 * @param string $like                  a like constraint on the query
	 * @return DB_ResultSet                 a result set of database views
	 */
	public abstract function views($like = '');

}
