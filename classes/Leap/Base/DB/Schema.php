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

namespace Leap\Base\DB {

	/**
	 * This class provides a way to access the scheme for a database.
	 *
	 * @package Leap
	 * @category Schema
	 * @version 2013-02-03
	 *
	 * @abstract
	 */
	abstract class Schema extends Core\Object {

		/**
		 * This variable stores a reference to the data source.
		 *
		 * @access protected
		 * @var DB\DataSource
		 */
		protected $data_source;

		/**
		 * This variable stores a reference to the helper class that implements the expression
		 * interface.
		 *
		 * @access protected
		 * @var DB\SQL\Precompiler
		 */
		protected $precompiler;

		/**
		 * This constructor instantiates this class using the specified data source.
		 *
		 * @access public
		 * @param mixed $config                  the data source configurations
		 */
		public function __construct($config) {
			$this->data_source = DB\DataSource::instance($config);
			$precompiler = '\\Leap\\DB\\' . $this->data_source->dialect . '\\Precompiler';
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
		 *
		 * @see http://kohanaframework.org/3.2/guide/api/Database#datatype
		 * @see http://www.contrib.andrew.cmu.edu/~shadow/sql/sql1992.txt
		 */
		public function data_type($type) {
			static $types = array(
				// SQL-92
				'BIT'                             => array('type' => 'Binary', 'max_length' => 1),
				'BIT VARYING'                     => array('type' => 'Binary', 'varying' => TRUE),
				'CHAR'                            => array('type' => 'String'),
				'CHAR VARYING'                    => array('type' => 'String', 'varying' => TRUE),
				'CHARACTER'                       => array('type' => 'String'),
				'CHARACTER VARYING'               => array('type' => 'String', 'varying' => TRUE),
				'DATE'                            => array('type' => 'Date'),
				'DEC'                             => array('type' => 'Decimal'),
				'DECIMAL'                         => array('type' => 'Decimal'),
				'DOUBLE PRECISION'                => array('type' => 'Double'),
				'FLOAT'                           => array('type' => 'Double'),
				'INT'                             => array('type' => 'Integer', 'range' => array(-2147483648, 2147483647)),
				'INTEGER'                         => array('type' => 'Integer', 'range' => array(-2147483648, 2147483647)),
				'INTERVAL'                        => array('type' => 'String'),
				'NATIONAL CHAR'                   => array('type' => 'String'),
				'NATIONAL CHAR VARYING'           => array('type' => 'String', 'varying' => TRUE),
				'NATIONAL CHARACTER'              => array('type' => 'String'),
				'NATIONAL CHARACTER VARYING'      => array('type' => 'String', 'varying' => TRUE),
				'NCHAR'                           => array('type' => 'String'),
				'NCHAR VARYING'                   => array('type' => 'String', 'varying' => TRUE),
				'NUMERIC'                         => array('type' => 'Decimal'),
				'REAL'                            => array('type' => 'Double'),
				'SMALLINT'                        => array('type' => 'Integer', 'range' => array(-32768, 32767)),
				'TIME'                            => array('type' => 'Time'),
				'TIME WITH TIME ZONE'             => array('type' => 'Time'),
				'TIMESTAMP'                       => array('type' => 'DateTime'),
				'TIMESTAMP WITH TIME ZONE'        => array('type' => 'DateTime'),
				'VARCHAR'                         => array('type' => 'String', 'varying' => TRUE),

				// SQL:1999
				'BINARY LARGE OBJECT'             => array('type' => 'Blob'),
				'BLOB'                            => array('type' => 'Blob'),
				'BOOLEAN'                         => array('type' => 'Boolean'),
				'CHAR LARGE OBJECT'               => array('type' => 'Text'),
				'CHARACTER LARGE OBJECT'          => array('type' => 'Text'),
				'CLOB'                            => array('type' => 'Text'),
				'NATIONAL CHARACTER LARGE OBJECT' => array('type' => 'Text'),
				'NCHAR LARGE OBJECT'              => array('type' => 'Text'),
				'NCLOB'                           => array('type' => 'Text'),
				'TIME WITHOUT TIME ZONE'          => array('type' => 'Time'),
				'TIMESTAMP WITHOUT TIME ZONE'     => array('type' => 'DateTime'),

				// SQL:2003
				'BIGINT'                          => array('type' => 'Integer', 'range' => array('-9223372036854775808', '9223372036854775807')),

				// SQL:2008
				'BINARY'                          => array('type' => 'Binary'),
				'BINARY VARYING'                  => array('type' => 'Binary', 'varying' => TRUE),
				'VARBINARY'                       => array('type' => 'Binary', 'varying' => TRUE),
			
				// SQL:MORE
				'DATETIME'                        => array('type' => 'DateTime'),
				'DOUBLE'                          => array('type' => 'Double'),
				'NATIONAL VARCHAR'                => array('type' => 'String', 'varying' => TRUE),
				'NVARCHAR'                        => array('type' => 'String', 'varying' => TRUE),
				'TEXT'                            => array('type' => 'Text'),
				'TINYINT'                         => array('type' => 'Integer', 'range' => array(-128, 127)),
				'VARBIT'                          => array('type' => 'Binary', 'varying' => TRUE),
			);

			$type = strtoupper($type);
		
			if (isset($types[$type])) {
				return $types[$type];
			}

			return array();
		}

		/**
		 * This function returns a result set of fields for the specified table.
		 *
		 * +---------------+---------------+------------------------------------------------------------+
		 * | field         | data type     | description                                                |
		 * +---------------+---------------+------------------------------------------------------------+
		 * | schema        | string        | The name of the schema that contains the table.            |
		 * | table         | string        | The name of the table.                                     |
		 * | column        | string        | The name of the column.                                    |
		 * | type          | string        | The data type of the column.                               |
		 * | max_length    | integer       | The max length, max digits, or precision of the column.    |
		 * | max_decimals  | integer       | The max decimals or scale of the column.                   |
		 * | attributes    | string        | Any additional attributes associated with the column.      |
		 * | seq_index     | integer       | The sequence index of the column.                          |
		 * | nullable      | boolean       | Indicates whether the column can contain a NULL value.     |
		 * | default       | mixed         | The default value of the column.                           |
		 * +---------------+---------------+------------------------------------------------------------+
		 *
		 * @access public
		 * @abstract
		 * @param string $table                 the table to evaluated
		 * @param string $like                  a like constraint on the query
		 * @return DB\ResultSet                 an array of fields within the specified
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
		 * @return DB\ResultSet                 a result set of indexes for the specified
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
		 * @return DB\ResultSet                 a result set of database tables
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
		 * @return DB\ResultSet                 a result set of triggers for the specified
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
		 * @return DB\ResultSet                 a result set of database views
		 */
		public abstract function views($like = '');

	}

}