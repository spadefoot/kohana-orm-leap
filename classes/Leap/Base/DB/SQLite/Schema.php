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
 * This class provides a way to access the scheme for an SQLite database.
 *
 * @package Leap
 * @category SQLite
 * @version 2013-01-31
 *
 * @abstract
 */
abstract class Base\DB\SQLite\Schema extends DB\Schema {

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
		$table = $this->precompiler->prepare_value($table);
		$regex = $this->like_to_regex($like);

		$sql = "PRAGMA table_info({$table});";

		$connection = DB\Connection\Pool::instance()->get_connection($this->data_source);
		$records = $connection->query($sql);

		$fields = array();
		foreach ($records as $record) {
			if ( ! empty($regex) OR preg_match($regex, $record['name'])){
				list($type, $length) = $this->parse_type($record['Type']);

				$field = $this->data_type($type);

				$field['name'] = $record['name'];
				$field['type'] = $type;
				$field['primary_key'] = ($record['pk'] == 1);
				if ($field['primary_key']) {
					$field['attributes']['auto_incremented'] = ($record['Extra'] == 'auto_increment');
				}
				$field['nullable'] = ($record['notnull'] == 1);
				$field['default'] = $record['dflt_value'];

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

				$fields[$record['name']] = $field;
			}
		}

		return $records;
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
	 * @see http://stackoverflow.com/questions/157392/how-do-i-find-out-if-a-sqlite-index-is-unique-with-sql
	 * @see http://marc.info/?l=sqlite-users&m=107868394932015
	 * @see http://my.safaribooksonline.com/book/databases/sql/9781449394592/sqlite-pragmas/id3054722
	 * @see http://my.safaribooksonline.com/book/databases/sql/9781449394592/sqlite-pragmas/id3054537
	 */
	public function indexes($table, $like = '') {
		$connection = DB\Connection\Pool::instance()->get_connection($this->data_source);

		$path_info = pathinfo($this->data_source->database);
		$schema = $path_info['filename'];

		$table = trim(preg_replace('/[^a-z0-9$_ ]/i', '', $table));

		$sql = "PRAGMA INDEX_LIST('{$table}');";

		$indexes = $connection->query($sql);

		$records = array();

		foreach ($indexes as $index) {
			if (empty($like) OR preg_match(DB\ToolKit::regex($like), $index['name'])) {
				$reader = $connection->reader("PRAGMA INDEX_INFO('{$index['name']}');");
				while ($reader->read()) {
					$column = $reader->row('array');
					$record = array(
						'schema' => $schema,
						'table' => $table,
						'index' => $index['name'],
						'column' => $column['name'],
						'seq_index' => $column['cid'],
						'ordering' => NULL,
						'unique' => $index['unique'],
						'primary' => 0,
					);
					$records[] = $record;
				}
				$reader->free();
			}
		}

		$results = new DB\ResultSet($records);

		return $results;
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
	 * @see http://www.sqlite.org/faq.html#q7
	 */
	public function tables($like = '') {
		$path_info = pathinfo($this->data_source->database);
		$schema = $path_info['filename'];

		$builder = DB\SQL::select($this->data_source)
			->column(DB\SQL::expr("'{$schema}'"), 'schema')
			->column('name', 'table')
			->column(DB\SQL::expr("'BASE'"), 'type')
			->from(DB\SQL::expr('(SELECT * FROM [sqlite_master] UNION ALL SELECT * FROM [sqlite_temp_master])'))
			->where('type', DB\SQL\Operator::_EQUAL_TO_, 'table')
			->where('name', DB\SQL\Operator::_NOT_LIKE_, 'sqlite_%')
			->order_by(DB\SQL::expr('UPPER([name])'));

		if ( ! empty($like)) {
			$builder->where('name', DB\SQL\Operator::_LIKE_, $like);
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
	 * @see http://www.sqlite.org/lang_createtrigger.html
	 * @see http://linuxgazette.net/109/chirico1.html
	 */
	public function triggers($table, $like = '') {
		$path_info = pathinfo($this->data_source->database);
		$schema = $path_info['filename'];

		$builder = DB\SQL::select($this->data_source)
			->column(DB\SQL::expr("'{$schema}'"), 'schema')
			->column('tbl_name', 'table')
			->column('name', 'trigger')
			->column(DB\SQL::expr('NULL'), 'event')
			->column(DB\SQL::expr('NULL'), 'timing')
			->column(DB\SQL::expr("'ROW'"), 'per')
			->column('sql', 'action')
			->column(DB\SQL::expr('0'), 'seq_index')
			->column(DB\SQL::expr('NULL'), 'created')
			->from(DB\SQL::expr('(SELECT * FROM [sqlite_master] UNION ALL SELECT * FROM [sqlite_temp_master])'))
			->where('type', DB\SQL\Operator::_EQUAL_TO_, 'trigger')
			->where('tbl_name', DB\SQL\Operator::_NOT_LIKE_, 'sqlite_%')
			->order_by(DB\SQL::expr('UPPER([tbl_name])'))
			->order_by(DB\SQL::expr('UPPER([name])'));

		if ( ! empty($like)) {
			$builder->where('[name]', DB\SQL\Operator::_LIKE_, $like);
		}

		$reader = $builder->reader();

		$records = array();

		while ($reader->read()) {
			$record = $reader->row('array');
			if (isset($record['action'])) {
				$sql = trim($record['action'], "; \t\n\r\0\x0B");

				if (preg_match('/\s+INSERT\s+/i', $sql)) {
					$record['event'] = 'INSERT';
				}
				else if (preg_match('/\s+UPDATE\s+/i', $sql)) {
					$record['event'] = 'UPDATE';
				}
				else if (preg_match('/\s+DELETE\s+OF\s+/i', $sql)) {
					$record['event'] = 'DELETE';
				}

				if (preg_match('/\s+BEFORE\s+/i', $sql)) {
					$record['timing'] = 'BEFORE';
				}
				else if (preg_match('/\s+AFTER\s+/i', $sql)) {
					$record['timing'] = 'AFTER';
				}
				else if (preg_match('/\s+INSTEAD\s+OF\s+/i', $sql)) {
					$record['timing'] = 'INSTEAD OF';
				}

				$offest = stripos($sql, 'BEGIN') + 5;
				$length = (strlen($sql) - $offset) - 3;
				$record['action'] = trim(substr($sql, $offset, $length), "; \t\n\r\0\x0B");
			}
			$records[] = $record;
		}

		$reader->free();

		$results = new DB\ResultSet($records);

		return $results;
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
	 * @see http://www.sqlite.org/faq.html#q7
	 */
	public function views($like = '') {
		$path_info = pathinfo($this->data_source->database);
		$schema = $path_info['filename'];

		$builder = DB\SQL::select($this->data_source)
			->column(DB\SQL::expr("'{$schema}'"), 'schema')
			->column('name', 'table')
			->column(DB\SQL::expr("'VIEW'"), 'type')
			->from(DB\SQL::expr('(SELECT * FROM [sqlite_master] UNION ALL SELECT * FROM [sqlite_temp_master])'))
			->where('type', DB\SQL\Operator::_EQUAL_TO_, 'view')
			->where('name', DB\SQL\Operator::_NOT_LIKE_, 'sqlite_%')
			->order_by(DB\SQL::expr('UPPER([name])'));

		if ( ! empty($like)) {
			$builder->where('name', DB\SQL\Operator::_LIKE_, $like);
		}

		return $builder->query();
	}

}
