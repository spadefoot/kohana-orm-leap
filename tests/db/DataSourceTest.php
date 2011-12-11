<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Copyright 2011 Spadefoot
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
 * @version 2011-12-11
 */
class DB_DataSourceTest extends PHPUnit_Framework_TestCase {

	/**
	 * This function provides the test data for self::test_constructor().
	 *
	 * @access public
	 */
	public function provider_constructor() {
		return array(
			array(array(NULL), '', 'std', 'localhost', 'default', '', '', 'mysql', '', FALSE),
			array(array('default'), '', 'std', 'localhost', 'default', '', '', 'mysql', '', FALSE),
		);
	}

	/**
	 * This function tests DB_DataSource::__construct().
	 *
	 * @access public
	 * @param array $test_values                        the test values
	 * @param string $expected_database                 the expected database value
	 * @param string $expected_driver                   the expected driver value
	 * @param string $expected_host_server              the expected host server value
	 * @param string $expected_id                       the expected id value
	 * @param string $expected_password                 the expected password value
	 * @param string $expected_port                     the expected port value
	 * @param string $expected_resource_type            the expected resource type value
	 * @param string $expected_username                 the expected username value
	 * @param string $expected_persistent               the expected persistent value
	 */
	public function test_constructor($test_values, $expected_database, $expected_driver, $expected_host_server, $expected_id, $expected_password, $expected_port, $expected_resource_type, $expected_username, $expected_persistent) {
		// Initialization
		$source = new DB_DataSource(reset($test_values));
		// Assertions
		$this->assertSame($expected_database, $source->get_database());
		$this->assertSame($expected_driver, $source->get_driver());
		$this->assertSame($expected_host_server, $source->get_host_server());
		$this->assertGreaterThan(0, strlen($source->get_id()));
		$this->assertSame($expected_password, $source->get_password());
		$this->assertSame($expected_port, $source->get_port());
		$this->assertSame($expected_resource_type, $source->get_resource_type());
		$this->assertSame($expected_username, $source->get_username());
		$this->assertSame($expected_persistent, $source->is_persistent());
	}

}
?>