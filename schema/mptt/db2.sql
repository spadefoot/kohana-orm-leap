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
-- Table structure for the "mptt_example" table
----

CREATE TABLE "mptt_example" (
	"id" INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
	"name" VARCHAR(35) NOT NULL DEFAULT '',
	"lft" INTEGER NOT NULL,
	"rgt" INTEGER NOT NULL,
	"lvl" INTEGER NOT NULL,
	"scope" INTEGER NOT NULL
);
