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
 * This class provides a way to access the scheme for a Firebird database.
 *
 * @package Leap
 * @category Firebird
 * @version 2013-02-01
 *
 * @abstract
 */
abstract class Base\DB\Firebird\Schema extends DB\Schema {

	/**
	 * This function returns an associated array of default properties for the specified
	 * SQL data type.
	 *
	 * @access public
	 * @override
	 * @param string $type                   the SQL data type
	 * @return array                         an associated array of default properties
	 *                                       for the specified data type
	 *
	 * @license http://kohanaframework.org/license
	 *
	 * @see http://www.firebirdsql.org/manual/migration-mssql-data-types.html
	 * @see http://web.firebirdsql.org/dotnetfirebird/firebird-and-dotnet-framework-data-types-mapping.html
	 * @see http://www.promotic.eu/en/pmdoc/Subsystems/Db/FireBird/DataTypes.htm
	 * @see http://www.ibphoenix.com/resources/documents/general/doc_54
	 */
	public function data_type($type) {
		static $types = array(
			'BLOB'                                         => array('type' => 'Blob', 'max_length' => 2147483647),
			'BLOB_ID'                                      => array('type' => 'String'),
			'BLOB SUB_TYPE 0'                              => array('type' => 'Blob', 'max_length' => 2147483647), // 0 - BLOB, binary data (image, video, audio, whatever)
			'BLOB SUB_TYPE 1'                              => array('type' => 'Text', 'max_length' => 2147483647), // 1 - CLOB, text
			'BLOB SUB_TYPE 2'                              => array('type' => 'Text', 'max_length' => 2147483647), // 2 - BLR, definitions of procedures, triggers, etc.
			'BLOB SUB_TYPE 3'                              => array('type' => 'Text', 'max_length' => 2147483647), // 3 - ACL
			'BLOB SUB_TYPE 4'                              => array('type' => 'Text', 'max_length' => 2147483647), // 4 - RANGES, reserved
			'BLOB SUB_TYPE 5'                              => array('type' => 'Blob', 'max_length' => 2147483647), // 5 - SUMMARY, encoded-meta-data
			'BLOB SUB_TYPE 6'                              => array('type' => 'Text', 'max_length' => 2147483647), // 6 - FORMAT, irregular-finished-multi-db-tx 
			'BLOB SUB_TYPE 7'                              => array('type' => 'Text', 'max_length' => 2147483647), // 7 - TRANSACTION_DESCRIPTION
			'BLOB SUB_TYPE 8'                              => array('type' => 'Text', 'max_length' => 2147483647), // 8 - EXTERNAL_FILE_DESCRIPTION
			'BLOB SUB_TYPE 9'                              => array('type' => 'Text', 'max_length' => 2147483647), // 9
			'BLOB SUB_TYPE ACL'                            => array('type' => 'Text', 'max_length' => 2147483647), // 3 - ACL
			'BLOB SUB_TYPE BLR'                            => array('type' => 'Text', 'max_length' => 2147483647), // 2 - BLR, definitions of procedures, triggers, etc.
			'BLOB SUB_TYPE EXTERNAL_FILE_DESCRIPTION'      => array('type' => 'Text', 'max_length' => 2147483647), // 8 - EXTERNAL_FILE_DESCRIPTION
			'BLOB SUB_TYPE FORMAT'                         => array('type' => 'Text', 'max_length' => 2147483647), // 6 - FORMAT, irregular-finished-multi-db-tx
			'BLOB SUB_TYPE RANGES'                         => array('type' => 'Text', 'max_length' => 2147483647), // 4 - RANGES, reserved
			'BLOB SUB_TYPE SUMMARY'                        => array('type' => 'Blob', 'max_length' => 2147483647), // 5 - SUMMARY, encoded-meta-data
			'BLOB SUB_TYPE TEXT'                           => array('type' => 'Text', 'max_length' => 2147483647), // 1 - CLOB, text
			'BLOB SUB_TYPE TEXT CHARACTER SET'             => array('type' => 'Text', 'max_length' => 2147483647), // 1 - NCLOB, text
			'BLOB SUB_TYPE TRANSACTION_DESCRIPTION'        => array('type' => 'Text', 'max_length' => 2147483647), // 7 - TRANSACTION_DESCRIPTION
			'CSTRING'                                      => array('type' => 'String'),
			'D_FLOAT'                                      => array('type' => 'Double'),
			'INT64'                                        => array('type' => 'Integer', 'range' => array('-9223372036854775808', '9223372036854775807')),
			'QUAD'                                         => array('type' => 'Integer', 'range' => array('-9223372036854775808', '9223372036854775807')),
		);

		$type = (preg_match('/^BLOB SUB_TYPE TEXT CHARACTER SET.*$/i', $type))
			? 'BLOB SUB_TYPE TEXT CHARACTER SET'
			: strtoupper($type);

		if (isset($types[$type])) {
			return $types[$type];
		}

		return parent::data_type($type);
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
	 *
	 * @see http://stackoverflow.com/questions/12070162/how-can-i-get-the-table-description-fields-and-types-from-firebird-with-dbexpr
	 * @see http://wiert.wordpress.com/2009/08/13/interbasefirebird-query-to-show-which-fields-in-your-database-are-not-based-on-a-domain/
	 * @see http://wiert.wordpress.com/2009/08/13/interbasefirebird-querying-the-system-tables-to-get-your-actually-used-fieldcolumn-types/
	 * @see http://www.felix-colibri.com/papers/db/interbase/using_interbase_system_tables/using_interbase_system_tables.html
	 * @see http://www.alberton.info/firebird_sql_meta_info.html
	 * @see http://tech.dir.groups.yahoo.com/group/firebird-support/message/94553
	 * @see http://www.firebirdfaq.org/faq165/
	 * @see http://www.ibphoenix.com/resources/documents/general/doc_54
	 */
	public function fields($table, $like = '') {
		$path_info = pathinfo($this->data_source->database);
		$schema = $this->precompiler->prepare_value($path_info['filename']);

		$table = $this->precompiler->prepare_identifier($table);

		$builder = DB\SQL::select($this->data_source)
			->column(DB\SQL::expr($schema), 'schema')
			->column(DB\SQL::expr('TRIM("RDB$INDICES"."RDB$RELATION_NAME")'), 'table')
			->column(DB\SQL::expr('TRIM("RDB$RELATION_FIELDS"."RDB$FIELD_NAME")'), 'column')
			->column(DB\SQL::expr('CASE "RDB$FIELDS"."RDB$FIELD_TYPE"
					WHEN 7 THEN
						CASE "RDB$FIELDS"."RDB$FIELD_SUB_TYPE"
								WHEN 1 THEN \'NUMERIC(\' || "RDB$FIELDS"."RDB$FIELD_PRECISION" || \',\' || ("RDB$FIELDS"."RDB$FIELD_SCALE" * -1) || \')\'
								WHEN 2 THEN \'DECIMAL\'
								ELSE \'SMALLINT(5)\'
						END
					WHEN 8 THEN
						CASE "RDB$FIELDS"."RDB$FIELD_SUB_TYPE"
								WHEN 1 THEN \'NUMERIC(\' || "RDB$FIELDS"."RDB$FIELD_PRECISION" || \',\' || ("RDB$FIELDS"."RDB$FIELD_SCALE" * -1) || \')\'
								WHEN 2 THEN \'DECIMAL\'
								ELSE \'INTEGER(10)\'
						END
					WHEN 9 THEN \'QUAD\'
					WHEN 10 THEN \'FLOAT(15,15)\'
					WHEN 11 THEN \'D_FLOAT(15,15)\'
					WHEN 12 THEN \'DATE\'
					WHEN 13 THEN \'TIME\'
					WHEN 14 THEN \'CHAR(\' || (TRUNC("RDB$FIELDS"."RDB$FIELD_LENGTH" / "RDB$CHARACTER_SETS"."RDB$BYTES_PER_CHARACTER")) || \')\'
					WHEN 16 THEN
						CASE "RDB$FIELDS"."RDB$FIELD_SUB_TYPE"
								WHEN 1 THEN \'NUMERIC(\' || "RDB$FIELDS"."RDB$FIELD_PRECISION" || \',\' || ("RDB$FIELDS"."RDB$FIELD_SCALE" * -1) || \')\'
								WHEN 2 THEN \'DECIMAL\'
								ELSE \'BIGINT\'
						END
					WHEN 17 THEN \'BOOLEAN\'
					WHEN 27 THEN \'DOUBLE PRECISION(15,15)\'
					WHEN 35 THEN \'TIMESTAMP\'
					WHEN 37 THEN \'VARCHAR(\' || (TRUNC("RDB$FIELDS"."RDB$FIELD_LENGTH" / "RDB$CHARACTER_SETS"."RDB$BYTES_PER_CHARACTER")) || \')\'
					WHEN 40 THEN \'CSTRING(\' || (TRUNC("RDB$FIELDS"."RDB$FIELD_LENGTH" / "RDB$CHARACTER_SETS"."RDB$BYTES_PER_CHARACTER")) || \')\'
					WHEN 45 THEN \'BLOB_ID\'
					WHEN 261 THEN
						CASE "RDB$FIELDS"."RDB$FIELD_SUB_TYPE"
							WHEN 0 THEN \'BLOB SUB_TYPE 0\'
							WHEN 1 THEN \'BLOB SUB_TYPE 1\'
							WHEN 2 THEN \'BLOB SUB_TYPE 2\'
							WHEN 3 THEN \'BLOB SUB_TYPE 3\'
							WHEN 4 THEN \'BLOB SUB_TYPE 4\'
							WHEN 5 THEN \'BLOB SUB_TYPE 5\'
							WHEN 6 THEN \'BLOB SUB_TYPE 6\'
							WHEN 7 THEN \'BLOB SUB_TYPE 7\'
							WHEN 8 THEN \'BLOB SUB_TYPE 8\'
							WHEN 9 THEN \'BLOB SUB_TYPE 9\'
							ELSE \'BLOB\'
						END
					ELSE "RDB$FIELDS"."RDB$FIELD_TYPE"
			END'), 'type')
			->column(DB\SQL::expr('COALESCE("RDB$RELATION_FIELDS"."RDB$FIELD_POSITION", 0) + 1'), 'seq_index')
			->column(DB\SQL::expr('CASE COALESCE("RDB$RELATION_FIELDS"."RDB$NULL_FLAG", 0) WHEN 0 THEN 1 ELSE 0 END'), 'nullable')
			->column(DB\SQL::expr('SUBSTRING(CAST("RDB$RELATION_FIELDS"."RDB$DEFAULT_SOURCE" AS VARCHAR(255)) FROM 9)'), 'default')
			->from('RDB$RELATION_FIELDS')
			->join(NULL, 'RDB$FIELDS')
			->on('RDB$FIELDS.RDB$FIELD_NAME', DB\SQL\Operator::_EQUAL_TO_, 'RDB$RELATION_FIELDS.RDB$FIELD_SOURCE')
			->join(DB\SQL\JoinType::_LEFT_, 'RDB$CHARACTER_SETS')
			->on('RDB$CHARACTER_SETS.RDB$CHARACTER_SET_ID', DB\SQL\Operator::_EQUAL_TO_, 'RDB$FIELDS.RDB$CHARACTER_SET_ID')
			->where('RDB$RELATION_FIELDS.RDB$FIELD_SOURCE', DB\SQL\Operator::_LIKE_, 'RDB$%')
			->where(DB\SQL::expr('TRIM("RDB$RELATION_FIELDS"."RDB$RELATION_NAME")'), DB\SQL\Operator::_EQUAL_TO_, $table)
			->where(DB\SQL::expr('COALESCE("RDB$INDICES"."RDB$SYSTEM_FLAG", 0)'), DB\SQL\Operator::_EQUAL_TO_, 0)
			->order_by('RDB$RELATION_FIELDS.RDB$FIELD_POSITION');

		if ( ! empty($like)) {
			$builder->where(DB\SQL::expr('TRIM("RDB$RELATION_FIELDS"."RDB$FIELD_NAME")'), DB\SQL\Operator::_LIKE_, $like);
		}

		$reader = $builder->reader();

		$records = array();

		while ($reader->read()) {
			$buffer = $reader->row('array');
			$type = $this->parse_type($buffer['type']);
			$record = array(
				'schema' => $buffer['schema'],
				'table' => $buffer['table'],
				'column' => $buffer['column'],
				'type' => $type[0],
				'max_length' => $type[1], // max_digits, precision
				'max_decimals' => $type[2], // scale
				'attributes' => '',
				'seq_index' => $buffer['seq_index'],
				'nullable' => (bool) $buffer['nullable'],
				'default' => $buffer['default'],
			);
			$records[] = $record;
		}

		$reader->free();

		$results = new DB\ResultSet($records);

		return $results;
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
	 * @see http://www.felix-colibri.com/papers/db/interbase/using_interbase_system_tables/using_interbase_system_tables.html
	 * @see http://www.alberton.info/firebird_sql_meta_info.html
	 */
	public function indexes($table, $like = '') {
		$path_info = pathinfo($this->data_source->database);
		$schema = $path_info['filename'];

		$builder = DB\SQL::select($this->data_source)
			->column(DB\SQL::expr("'{$schema}'"), 'schema')
			->column(DB\SQL::expr('TRIM("RDB$INDICES"."RDB$RELATION_NAME")'), 'table')
			->column(DB\SQL::expr('TRIM("RDB$INDICES"."RDB$INDEX_NAME")'), 'index')
			->column(DB\SQL::expr('TRIM("RDB$INDEX_SEGMENTS"."RDB$FIELD_NAME")'), 'column')
			->column(DB\SQL::expr('CAST(("RDB$INDEX_SEGMENTS"."RDB$FIELD_POSITION" + 1) AS integer)'), 'seq_index')
			->column(DB\SQL::expr('0'), 'ordering')
			->column(DB\SQL::expr('RDB$INDICES.RDB$UNIQUE_FLAG'), 'unique')
			->column(DB\SQL::expr('IIF("RDB$RELATION_CONSTRAINTS"."RDB$CONSTRAINT_TYPE" = \'PRIMARY KEY\', 1, 0)'), 'primary')
			->from('RDB$INDEX_SEGMENTS')
			->join(DB\SQL\JoinType::_LEFT_, 'RDB$INDICES')
			->on('RDB$INDICES.RDB$INDEX_NAME', DB\SQL\Operator::_EQUAL_TO_, 'RDB$INDEX_SEGMENTS.RDB$INDEX_NAME')
			->join(DB\SQL\JoinType::_LEFT_, 'RDB$RELATION_CONSTRAINTS')
			->on('RDB$RELATION_CONSTRAINTS.RDB$INDEX_NAME', DB\SQL\Operator::_EQUAL_TO_, 'RDB$INDICES.RDB$INDEX_NAME')
			->where(DB\SQL::expr('COALESCE("RDB$INDICES"."RDB$SYSTEM_FLAG", 0)'), DB\SQL\Operator::_EQUAL_TO_, 0)
			->where('RDB$INDICES.RDB$RELATION_NAME', DB\SQL\Operator::_EQUAL_TO_, $table)
			->where('RDB$RELATION_CONSTRAINTS.RDB$CONSTRAINT_TYPE', DB\SQL\Operator::_IS_, NULL)
			->where('RDB$INDICES.RDB$INDEX_INACTIVE', DB\SQL\Operator::_NOT_EQUAL_TO_, 1)
			->order_by(DB\SQL::expr('UPPER("RDB$INDICES"."RDB$RELATION_NAME")'))
			->order_by(DB\SQL::expr('UPPER("RDB$INDICES"."RDB$INDEX_NAME")'))
			->order_by(DB\SQL::expr('CAST(("RDB$INDEX_SEGMENTS"."RDB$FIELD_POSITION" + 1) AS integer)'));

		if ( ! empty($like)) {
			$builder->where(DB\SQL::expr('TRIM("RDB$INDICES"."RDB$INDEX_NAME")'), DB\SQL\Operator::_LIKE_, $like);
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
	 * @see http://www.firebirdfaq.org/faq174/
	 * @see http://www.alberton.info/firebird_sql_meta_info.html
	 */
	public function tables($like = '') {
		$path_info = pathinfo($this->data_source->database);
		$schema = $path_info['filename'];

		$builder = DB\SQL::select($this->data_source)
			->column(DB\SQL::expr("'{$schema}'"), 'schema')
			->column(DB\SQL::expr('TRIM("RDB$RELATION_NAME")'), 'table')
			->column(DB\SQL::expr("'BASE'"), 'type')
			->from('RDB$RELATIONS')
			->where(DB\SQL::expr('COALESCE("RDB$SYSTEM_FLAG", 0)'), DB\SQL\Operator::_EQUAL_TO_, 0)
			->where('RDB$VIEW_BLR', DB\SQL\Operator::_IS_, NULL)
			->order_by(DB\SQL::expr('UPPER("RDB$RELATION_NAME")'));

		if ( ! empty($like)) {
			$builder->where(DB\SQL::expr('TRIM("RDB$RELATION_NAME")'), DB\SQL\Operator::_LIKE_, $like);
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
	 * @see http://www.alberton.info/firebird_sql_meta_info.html
	 */
	public function triggers($table, $like = '') {
		$path_info = pathinfo($this->data_source->database);
		$schema = $path_info['filename'];

		$builder = DB\SQL::select($this->data_source)
			->column(DB\SQL::expr("'{$schema}'"), 'schema')
			->column('RDB$RELATION_NAME', 'table')
			->column('RDB$TRIGGER_NAME', 'trigger')
			->column(DB\SQL::expr("CASE 'RDB\$TRIGGER_TYPE' WHEN 1 THEN 'INSERT' WHEN 2 THEN 'INSERT' WHEN 3 THEN 'UPDATE' WHEN 4 THEN 'UPDATE' ELSE 'DELETE' END"), 'event')
			->column(DB\SQL::expr("CASE 'RDB\$TRIGGER_TYPE' & 2 WHEN 0 THEN 'AFTER' ELSE 'BEFORE' END"), 'timing')
			->column(DB\SQL::expr("'ROW'"), 'per')
			->column('RDB$TRIGGER_SOURCE', 'action')
			->column('RDB$TRIGGER_SEQUENCE', 'seq_index')
			->column(DB\SQL::expr('NULL'), 'created')
			->from('RDB$TRIGGERS')
			->where(DB\SQL::expr('COALESCE("RDB$SYSTEM_FLAG", 0)'), DB\SQL\Operator::_EQUAL_TO_, 0)
			->where('RDB$RELATION_NAME', DB\SQL\Operator::_EQUAL_TO_, $table)
			->where('RDB$TRIGGER_INACTIVE', DB\SQL\Operator::_NOT_EQUAL_TO_, 1)
			->order_by(DB\SQL::expr('UPPER("RDB$RELATION_NAME")'))
			->order_by(DB\SQL::expr('UPPER("RDB$TRIGGER_NAME")'))
			->order_by('RDB$TRIGGER_SEQUENCE');

		if ( ! empty($like)) {
			$builder->where(DB\SQL::expr('TRIM("RDB$TRIGGER_NAME")'), DB\SQL\Operator::_LIKE_, $like);
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
	 * @see http://www.firebirdfaq.org/faq174/
	 * @see http://www.alberton.info/firebird_sql_meta_info.html
	 */
	public function views($like = '') {
		$path_info = pathinfo($this->data_source->database);
		$schema = $path_info['filename'];

		$builder = DB\SQL::select($this->data_source)
			->column(DB\SQL::expr("'{$schema}'"), 'schema')
			->column(DB\SQL::expr('TRIM("RDB$RELATION_NAME")'), 'table')
			->column(DB\SQL::expr("'VIEW'"), 'type')
			->from('RDB$RELATIONS')
			->where(DB\SQL::expr('COALESCE("RDB$SYSTEM_FLAG", 0)'), DB\SQL\Operator::_EQUAL_TO_, 0)
			->where('RDB$VIEW_BLR', DB\SQL\Operator::_IS_NOT_, NULL)
			->order_by(DB\SQL::expr('UPPER("RDB$RELATION_NAME")'));

		if ( ! empty($like)) {
			$builder->where(DB\SQL::expr('TRIM("RDB$RELATION_NAME")'), DB\SQL\Operator::_LIKE_, $like);
		}

		return $builder->query();
	}

}
