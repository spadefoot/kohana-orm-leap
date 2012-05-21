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
 * This class tests DB_DataSource.
 *
 * @package Leap
 * @category DB
 * @version 2012-05-20
 *
 * @group spadefoot.leap
 */
class DB_DataSourceTest extends Unittest_Testcase {

	/**
	 * This function provides the test data for self::test_constructor().
	 *
	 * @access public
	 */
	public function provider_constructor() {
		$expected = array(
			'type' => 'sql',
			'dialect' => 'mysql',
			'driver' => 'standard',
			'connection' => array(
				'persistent' => FALSE,
				'hostname' => 'localhost',
				'port' => '',
				'database' => '',
				'username' => 'root',
				'password' => 'root',
			),
			'caching' => FALSE,
			'charset' => 'utf8',
			'profiling' => FALSE,
			'table_prefix' => '',
		);

		return array(
			array(NULL, $expected),
			array('default', $expected),
			array($expected, $expected),
			array(new DB_DataSource('default'), $expected),
		);
	}

	/**
	 * This function tests DB_DataSource::__construct().
	 *
	 * @access public
	 * @param mixed $test_data                          the test data
	 * @param string $expected                          the expected values
	 *
	 * @dataProvider provider_constructor
	 */
	public function test_constructor($test_data, $expected) {
		// Initialization
		$source = new DB_DataSource($test_data);
		// Assertions
		$this->assertSame($expected['connection']['database'], $source->database, 'Failed when testing "database" property.');
		$this->assertSame($expected['driver'], $source->driver, 'Failed when testing "driver" property.');
		$this->assertSame($expected['connection']['hostname'], $source->host, 'Failed when testing "host" property.');
		$this->assertRegExp('/^(database|unique_id)\.[a-zA-Z0-9_]+$/', $source->id, 'Failed when testing "id" property.');
		$this->assertSame($expected['connection']['password'], $source->password, 'Failed when testing "password" property.');
		$this->assertSame($expected['connection']['port'], $source->port, 'Failed when testing "port" property.');
		$this->assertSame($expected['dialect'], $source->dialect, 'Failed when testing "dialect" property.');
		$this->assertSame($expected['type'], $source->type, 'Failed when testing "type" property.');
		$this->assertSame($expected['connection']['username'], $source->username, 'Failed when testing "username" property.');
		$this->assertSame($expected['connection']['persistent'], $source->is_persistent(), 'Failed when testing is_persistent().');
	}

}
?>