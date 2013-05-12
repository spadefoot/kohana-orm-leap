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
 * This class tests DB\ToolKit.
 *
 * @package Leap
 * @category DB
 * @version 2013-01-06
 *
 * @group spadefoot.leap
 */
class DB\ToolKitTest extends Unittest_Testcase {

	/**
	 * This function provides the test data for test_regex().
	 *
	 * @access public
	 */
	public function provider_regex() {
		return array(
			array(array('spade%', NULL), '/^spade.*$/'),
			array(array('%foot', NULL), '/^.*foot$/'),
			array(array('spade_', NULL), '/^spade.$/'),
			array(array('_foot', NULL), '/^.foot$/'),
			array(array('spade_%', NULL), '/^spade..*$/'),
			array(array('spade%_', NULL), '/^spade.*.$/'),
			array(array('spade\%', NULL), '/^spade%$/'),
			array(array('spade%%', NULL), '/^spade%%$/'),
			array(array('spade%%', '%'), '/^spade%$/'),
			array(array('spade%%%', '%'), '/^spade%%$/'),
			array(array('spade__', NULL), '/^spade__$/'),
			array(array('spade__', '_'), '/^spade_$/'),
			array(array('spade__', '%'), '/^spade__$/'),
			array(array('spade%_', '%'), '/^spade_$/'),
			array(array('spade__', '\\'), '/^spade__$/'),
			array(array('$padefoot', NULL), '/^\$padefoot$/'),
		);
	}

	/**
	 * This function provides the test data for test_slug().
	 *
	 * @access public
	 */
	public function provider_slug() {
		return array(
			array(NULL, ''),
			array('slug', 'slug'),
			array('slug test', 'slug-test'),
			array('$slug%&#_test?', 'slug-test'),
			array('%&#_', ''),
		);
	}

	/**
	 * This function tests DB\ToolKit::regex().
	 *
	 * @access public
	 * @param mixed $test_data                          the test data
	 * @param string $expected                          the expected value
	 *
	 * @dataProvider provider_regex
	 */
	public function test_regex($test_data, $expected) {
		$this->assertSame($expected, DB\ToolKit::regex($test_data[0], $test_data[1]), 'Failed when testing regex().');
	}

	/**
	 * This function tests DB\ToolKit::slug().
	 *
	 * @access public
	 * @param mixed $test_data                          the test data
	 * @param string $expected                          the expected value
	 *
	 * @dataProvider provider_slug
	 */
	public function test_slug($test_data, $expected) {
		$this->assertSame($expected, DB\ToolKit::slug($test_data), 'Failed when testing slug().');
	}

}
