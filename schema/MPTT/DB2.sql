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

CREATE TABLE "mptt" (
	"id" INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
	"title" VARCHAR(35) NOT NULL DEFAULT '',
	"parent_id" INTEGER NOT NULL,
	"left_id" INTEGER NOT NULL,
	"right_id" INTEGER NOT NULL,
	"level_id" INTEGER NOT NULL,
	"scope_id" INTEGER NOT NULL
);
