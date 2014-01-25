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

namespace Leap\Base\DB\DB2 {

	/**
	 * This class provides a way to access the scheme for a DB2 database.
	 *
	 * @package Leap
	 * @category DB2
	 * @version 2013-01-30
	 *
	 * @abstract
	 */
	abstract class Schema extends DB\Schema {

		/**
		 * This function returns an associated array which describes the properties
		 * for the specified SQL data type.
		 *
		 * @access public
		 * @override
		 * @param string $type                   the SQL data type
		 * @return array                         an associated array which describes the properties
		 *                                       for the specified data type
		 *
		 * @license http://kohanaframework.org/license
		 *
		 * @see http://publib.boulder.ibm.com/infocenter/dzichelp/v2r2/topic/com.ibm.db2z10.doc.intro/src/tpc/db2z_datatypes.htm
		 */
		public function data_type($type) {
			/*
			static $types = array(
				'blob'                      => array('type' => 'string', 'binary' => TRUE, 'character_maximum_length' => '65535'),
				'bool'                      => array('type' => 'bool'),
				'bigint unsigned'           => array('type' => 'int', 'min' => '0', 'max' => '18446744073709551615'),
				'datetime'                  => array('type' => 'string'),
				'decimal unsigned'          => array('type' => 'float', 'exact' => TRUE, 'min' => '0'),
				'double'                    => array('type' => 'float'),
				'double precision unsigned' => array('type' => 'float', 'min' => '0'),
				'double unsigned'           => array('type' => 'float', 'min' => '0'),
				'enum'                      => array('type' => 'string'),
				'fixed'                     => array('type' => 'float', 'exact' => TRUE),
				'fixed unsigned'            => array('type' => 'float', 'exact' => TRUE, 'min' => '0'),
				'float unsigned'            => array('type' => 'float', 'min' => '0'),
				'int unsigned'              => array('type' => 'int', 'min' => '0', 'max' => '4294967295'),
				'integer unsigned'          => array('type' => 'int', 'min' => '0', 'max' => '4294967295'),
				'longblob'                  => array('type' => 'string', 'binary' => TRUE, 'character_maximum_length' => '4294967295'),
				'longtext'                  => array('type' => 'string', 'character_maximum_length' => '4294967295'),
				'mediumblob'                => array('type' => 'string', 'binary' => TRUE, 'character_maximum_length' => '16777215'),
				'mediumint'                 => array('type' => 'int', 'min' => '-8388608', 'max' => '8388607'),
				'mediumint unsigned'        => array('type' => 'int', 'min' => '0', 'max' => '16777215'),
				'mediumtext'                => array('type' => 'string', 'character_maximum_length' => '16777215'),
				'national varchar'          => array('type' => 'string'),
				'numeric unsigned'          => array('type' => 'float', 'exact' => TRUE, 'min' => '0'),
				'nvarchar'                  => array('type' => 'string'),
				'point'                     => array('type' => 'string', 'binary' => TRUE),
				'real unsigned'             => array('type' => 'float', 'min' => '0'),
				'set'                       => array('type' => 'string'),
				'smallint unsigned'         => array('type' => 'int', 'min' => '0', 'max' => '65535'),
				'text'                      => array('type' => 'string', 'character_maximum_length' => '65535'),
				'tinyblob'                  => array('type' => 'string', 'binary' => TRUE, 'character_maximum_length' => '255'),
				'tinyint'                   => array('type' => 'int', 'min' => '-128', 'max' => '127'),
				'tinyint unsigned'          => array('type' => 'int', 'min' => '0', 'max' => '255'),
				'tinytext'                  => array('type' => 'string', 'character_maximum_length' => '255'),
				'year'                      => array('type' => 'string'),
			);

			$type = strtoupper($type);
			$type = str_replace(' zerofill', '', $type);

			if (isset($types[$type])) {
				return $types[$type];
			}

			return parent::data_type($type);
			*/
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
		 * @override
		 * @param string $table                 the table to evaluated
		 * @param string $like                  a like constraint on the query
		 * @return DB\ResultSet                 an array of fields within the specified
		 *                                      table
		 */
		public function fields($table, $like = '') {
			/*
			$table = $this->precompiler->prepare_identifier($table);

			$sql = 'SHOW FULL COLUMNS FROM ' . $table;

			if ( ! empty($like)) {
				$like = $this->precompiler->prepare_value($like);
				$sql .= ' LIKE ' . $like;
			}

			$sql .= ';';

			$connection = DB\Connection\Pool::instance()->get_connection($this->data_source);
			$records = $connection->query($sql);

			$fields = array();

			foreach ($records as $record) {
				list($type, $length) = $this->parse_type($record['Type']);

				$field = $this->data_type($type);

				$field['name'] = $record['Field'];
				$field['type'] = $type;
				$field['primary_key'] = ($record['Key'] == 'PRI');
				if ($field['primary_key']) {
					$field['attributes']['auto_incremented'] = ($record['Extra'] == 'auto_increment');
				}
				$field['nullable'] = ($record['Null'] == 'YES');
				$field['default'] = $record['Default'];

				switch ($field['type']) {
					case 'float':
						if (isset($length)) {
							list($field['precision'], $field['scale']) = explode(',', $length);
						}
					break;
					case 'int':
						if (isset($length)) {
							$field['display'] = $length;
						}
					break;
					case 'string':
						switch ($field['data_type']) {
							case 'binary':
							case 'varbinary':
								$field['max_length'] = $length;
							break;
							case 'char':
							case 'varchar':
								$field['max_length'] = $length;
							case 'text':
							case 'tinytext':
							case 'mediumtext':
							case 'longtext':
								$field['collation'] = $record['Collation'];
							break;
							case 'enum':
							case 'set':
								$field['collation'] = $record['Collation'];
								$field['options'] = explode('\',\'', substr($length, 1, -1));
							break;
						}
					break;
				}

				$fields[$record['Field']] = $field;
			}

			return $fields;
			*/
		}

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
		 * @override
		 * @param string $table                 the table to evaluated
		 * @param string $like                  a like constraint on the query
		 * @return DB\ResultSet                 a result set of indexes for the specified
		 *                                      table
		 *
		 * @see http://www.devx.com/dbzone/Article/29585/0/page/4
		 * @see http://publib.boulder.ibm.com/infocenter/db2luw/v9/topic/com.ibm.db2.udb.admin.doc/doc/r0001047.htm
		 * @see http://pic.dhe.ibm.com/infocenter/db2luw/v9r7/topic/com.ibm.db2.luw.sql.ref.doc/doc/r0002316.html
		 * @see http://www.dbforums.com/db2/1614810-how-see-indexes-db2-tables.html
		 * @see http://www.tek-tips.com/viewthread.cfm?qid=128876&page=108
		 */
		public function indexes($table, $like = '') {
			$builder = DB\SQL::select($this->data_source)
				->column('t1.TABSCHEMA', 'schema')
				->column('t1.TABNAME', 'table')
				->column('t1.INDNAME', 'index')
				->column('t0.COLNAME', 'column')
				->column('t0.COLSEQ', 'seq_index')
				->column(DB\SQL::expr("CASE \"t0\".\"COLORDER\" WHEN 'A' THEN 'ASC' WHEN 'D' THEN 'DESC' ELSE NULL END"), 'ordering')
				->column(DB\SQL::expr("CASE \"t1\".\"UNIQUERULE\" WHEN 'D' THEN 0 ELSE 1 END"), 'unique')
				->column(DB\SQL::expr("CASE \"t1\".\"UNIQUERULE\" WHEN 'P' THEN 1 ELSE 0 END"), 'primary')
				->from('SYSCAT.INDEXCOLUSE', 't0')
				->join(DB\SQL\JoinType::_LEFT_, 'SYSCAT.INDEXES', 't1')
				->on('t1.INDSCHEMA', DB\SQL\Operator::_EQUAL_TO_, 't0.INDSCHEMA')
				->on('t1.INDNAME', DB\SQL\Operator::_EQUAL_TO_, 't0.INDNAME')
				->where('t1.TABSCHEMA', DB\SQL\Operator::_NOT_LIKE_, 'SYS%')
				->where('t1.TABNAME', DB\SQL\Operator::_EQUAL_TO_, $table)
				->order_by(DB\SQL::expr('UPPER("t1"."TABSCHEMA")'))
				->order_by(DB\SQL::expr('UPPER("t1"."TABNAME")'))
				->order_by(DB\SQL::expr('UPPER("t1"."INDNAME")'))
				->order_by('t0.COLSEQ');

			if ( ! empty($like)) {
				$builder->where('t1.INDNAME', DB\SQL\Operator::_LIKE_, $like);
			}

			return $builder->query();
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
		 * @override
		 * @param string $like                  a like constraint on the query
		 * @return DB\ResultSet                 a result set of database tables
		 *
		 * @see http://www.devx.com/dbzone/Article/29585/0/page/4
		 * @see http://www.ibm.com/developerworks/data/library/techarticle/dm-0411melnyk/
		 * @see http://www.dbforums.com/db2/1002209-select-all-tables-database.html
		 * @see http://www.selectorweb.com/db2.html
		 */
		public function tables($like = '') {
			$builder = DB\SQL::select($this->data_source)
				->column('TABSCHEMA', 'schema')
				->column('TABNAME', 'table')
				->column(DB\SQL::expr("'BASE'"), 'type')
				->from('SYSCAT.TABLES')
				->where('TABSCHEMA', DB\SQL\Operator::_NOT_LIKE_, 'SYS%')
				->where('TYPE', DB\SQL\Operator::_EQUAL_TO_, 'T')
				->order_by(DB\SQL::expr('UPPER("TABSCHEMA")'))
				->order_by(DB\SQL::expr('UPPER("TABNAME")'));

			if ( ! empty($like)) {
				$builder->where('TABNAME', DB\SQL\Operator::_LIKE_, $like);
			}

			return $builder->query();
		}

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
		 * @override
		 * @param string $table                 the table to evaluated
		 * @param string $like                  a like constraint on the query
		 * @return DB\ResultSet                 a result set of triggers for the specified
		 *                                      table
		 *
		 * @see http://www.devx.com/dbzone/Article/29585/0/page/4
		 * @see http://publib.boulder.ibm.com/infocenter/db2luw/v9/topic/com.ibm.db2.udb.admin.doc/doc/r0001066.htm
		 */
		public function triggers($table, $like = '') {
			$builder = DB\SQL::select($this->data_source)
				->column('TABSCHEMA', 'schema')
				->column('TABNAME', 'table')
				->column('TRIGNAME', 'trigger')
				->column('TRIGEVENT', 'event')
				->column('TRIGTIME', 'timing')
				->column(DB\SQL::expr("CASE GRANULARITY WHEN 'S' THEN 'STATEMENT' ELSE 'ROW' END"), 'per')
				->column('TEXT', 'action')
				->column(DB\SQL::expr('0'), 'seq_index')
				->column('CREATE_TIME', 'created')
				->from('SYSCAT.TRIGGERS')
				->where('TABSCHEMA', DB\SQL\Operator::_NOT_LIKE_, 'SYS%')
				->where('TABNAME', DB\SQL\Operator::_EQUAL_TO_, $table)
				->where('VALID', DB\SQL\Operator::_NOT_EQUIVALENT_, 'Y')
				->order_by(DB\SQL::expr('UPPER("TABSCHEMA")'))
				->order_by(DB\SQL::expr('UPPER("TABNAME")'))
				->order_by(DB\SQL::expr('UPPER("TRIGNAME")'));

			if ( ! empty($like)) {
				$builder->where('TRIGNAME', DB\SQL\Operator::_LIKE_, $like);
			}

			return $builder->query();
		}

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
		 * @override
		 * @param string $like                  a like constraint on the query
		 * @return DB\ResultSet                 a result set of database views
		 *
		 * @see http://www.devx.com/dbzone/Article/29585/0/page/4
		 * @see http://lpetr.org/blog/archives/find-a-list-of-views-marked-inoperative
		 * @see http://www.ibm.com/developerworks/data/library/techarticle/dm-0411melnyk/
		 */
		public function views($like = '') {
			$builder = DB\SQL::select($this->data_source)
				->column('VIEWSCHEMA', 'schema')
				->column('VIEWNAME', 'table')
				->column(DB\SQL::expr("'VIEW'"), 'type')
				->from('SYSCAT.VIEWS')
				->where('VIEWSCHEMA', DB\SQL\Operator::_NOT_LIKE_, 'SYS%')
				->where('VALID', DB\SQL\Operator::_NOT_EQUIVALENT_, 'Y')
				->order_by(DB\SQL::expr('UPPER("VIEWSCHEMA")'))
				->order_by(DB\SQL::expr('UPPER("VIEWNAME")'));

			if ( ! empty($like)) {
				$builder->where('VIEWNAME', DB\SQL\Operator::_LIKE_, $like);
			}

			return $builder->query();
		}

	}

}