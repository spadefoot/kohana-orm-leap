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
 * This class tests DB_ToolKit.
 *
 * @package Leap
 * @category DB
 * @version 2011-12-26
 *
 * @group spadefoot.leap
 */
class DB_ToolKitTest extends Unittest_Testcase {

	/**
	 * This function provides the test data for self::test_slug().
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
	 * This function tests DB_ToolKit::slug().
	 *
	 * @access public
	 * @param mixed $test_data                          the test data
	 * @param string $expected                          the expected value
	 *
	 * @dataProvider provider_slug
	 */
	public function test_slug($test_data, $expected) {
		$this->assertSame($expected, DB_ToolKit::slug($test_data), 'Failed when testing slug().');
	}

}
?>