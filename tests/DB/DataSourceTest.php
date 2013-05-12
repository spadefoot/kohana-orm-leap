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
 * This class tests DB\DataSource.
 *
 * @package Leap
 * @category DB
 * @version 2013-01-28
 *
 * @group spadefoot.leap
 */
class DB\DataSourceTest extends Unittest_Testcase {

	/**
	 * This function provides the test data for test_constructor().
	 *
	 * @access public
	 */
	public function provider_constructor() {
		$expected = array(
			'type' => 'SQL',
			'dialect' => 'MySQL',
			'driver' => 'Standard',
			'connection' => array(
				'persistent' => FALSE,
				'hostname' => 'localhost',
				'port' => '',
				'database' => '',
				'username' => 'root',
				'password' => 'root',
				'role' => '',
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
			array(new DB\DataSource('default'), $expected),
		);
	}

	/**
	 * This function tests DB\DataSource::__construct().
	 *
	 * @access public
	 * @param mixed $test_data                          the test data
	 * @param string $expected                          the expected values
	 *
	 * @dataProvider provider_constructor
	 */
	public function test_constructor($test_data, $expected) {
		// Initialization
		$data_source = new DB\DataSource($test_data);
		// Assertions
		$this->assertRegExp('/^(database|unique_id)\.[a-zA-Z0-9_]+$/', $data_source->id, 'Failed when testing "id" property.');
		$this->assertSame($expected['type'], $data_source->type, 'Failed when testing "type" property.');
		$this->assertSame($expected['dialect'], $data_source->dialect, 'Failed when testing "dialect" property.');
		$this->assertSame($expected['driver'], $data_source->driver, 'Failed when testing "driver" property.');
		$this->assertSame($expected['connection']['persistent'], $data_source->is_persistent(), 'Failed when testing is_persistent().');
		$this->assertSame($expected['connection']['hostname'], $data_source->host, 'Failed when testing "host" property.');
		$this->assertSame($expected['connection']['port'], $data_source->port, 'Failed when testing "port" property.');
		$this->assertSame($expected['connection']['database'], $data_source->database, 'Failed when testing "database" property.');
		$this->assertSame($expected['connection']['username'], $data_source->username, 'Failed when testing "username" property.');
		$this->assertSame($expected['connection']['password'], $data_source->password, 'Failed when testing "password" property.');
		$this->assertSame($expected['connection']['role'], $data_source->role, 'Failed when testing "role" property.');
		$this->assertSame($expected['charset'], $data_source->charset, 'Failed when testing "charset" property.');
	}

}
