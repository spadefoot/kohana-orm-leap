----
-- Copyright 2012 Spadefoot
--
-- Licensed under the Apache License, Version 2.0 (the "License");
-- you may not use this file except in compliance with the License.
-- You may obtain a copy of the License at
--
--      http://www.apache.org/licenses/LICENSE-2.0
--
-- Unless required by applicable law or agreed to in writing, software
-- distributed under the License is distributed on an "AS IS" BASIS,
-- WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
-- See the License for the specific language governing permissions and
-- limitations under the License.
----

----
-- Table structure for the "mptt" table
----

CREATE TABLE IF NOT EXISTS `mptt` (
	`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(35) NOT NULL DEFAULT '',
	`lft` INT(11) UNSIGNED NOT NULL,
	`rgt` INT(11) UNSIGNED NOT NULL,
	`lvl` INT(11) UNSIGNED NOT NULL,
	`scope` INT(11) UNSIGNED NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
