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
 * This class tests DB_ResultSet.
 *
 * @package Leap
 * @category DB
 * @version 2012-01-11
 *
 * @group spadefoot.leap
 */
class DB_ResultSetTest extends Unittest_Testcase {

	/**
	 * This function provides the test data for self::test_constructor().
	 *
	 * @access public
	 */
	public function provider_constructor() {
		return array(
			array(array()),
			array(array(array('ID' => 1, 'Name' => 'A'), array('ID' => 1, 'Name' => 'B'), array('ID' => 1, 'Name' => 'C'))),
		);
	}

	/**
	 * This function tests DB_ResultSet::__construct().
	 *
	 * @access public
	 * @param mixed $test_data                          the test data
	 *
	 * @dataProvider provider_constructor
	 */
	public function test_constructor($test_data) {
		// Initialization
		$test_size = count($test_data);
		$results = new DB_ResultSet($test_data, $test_size, 'array');
		// Assertions
		$this->assertInternalType('array', $results->as_array(), 'Failed when testing as_array().');
		$this->assertCount($test_size, $results, 'Failed when testing count().');
		$this->assertEquals(($test_size > 0), $results->is_loaded(), 'Failed when testing is_loaded().');
		for ($i = 0; $i < $test_size; $i++) {
			$this->assertEquals($i, $results->key(), 'Failed when testing key().');
			$this->assertEquals($i, $results->position(), 'Failed when testing position().');
			$this->assertTrue($results->offsetExists($i), 'Failed when testing offsetExists($offset).');
			$this->assertTrue($results->valid(), 'Failed when testing valid().');
			$this->assertInternalType('array', $results->current(), 'Failed when testing current().');
			$this->assertInternalType('array', $results->offsetGet($i), 'Failed when testing offsetGet($offset).');
			$this->assertInternalType('array', $results[$i], 'Failed when testing array index.');
			$this->assertInternalType('array', $results->fetch($i), 'Failed when testing fetch($index).');
			$this->assertInternalType('array', $results->fetch(), 'Failed when testing fetch().');
		}
		$results->free();
		$this->assertCount(0, $results, 'Failed when testing free().');
		$this->assertFalse($results->is_loaded(), 'Failed when testing is_loaded().');
		$this->assertFalse($results->valid(), 'Failed when testing valid().');
	}

}
?>