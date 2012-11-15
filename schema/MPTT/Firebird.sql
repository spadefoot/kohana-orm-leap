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
	"id" INTEGER NOT NULL,
	"name" VARCHAR(32) NOT NULL DEFAULT '',
	"lft" INTEGER NOT NULL,
	"rgt" INTEGER NOT NULL,
	"lvl" INTEGER NOT NULL,
	"scope" INTEGER NOT NULL,
	CONSTRAINT "mptt_id_pkey" PRIMARY KEY ("id")
);

----
-- Auto-increment the "mptt" table (see, http://www.firebirdfaq.org/faq29/)
----

CREATE GENERATOR "mptt_id_gen";

SET GENERATOR "mptt_id_gen" TO 0;

SET TERM !! ;
CREATE TRIGGER "mptt_id_trig" FOR "mptt" ACTIVE BEFORE INSERT POSITION 0 AS
BEGIN
	IF (NEW.ID IS NULL) THEN NEW.ID = GEN_ID("mptt_id_gen", 1);
END!!
SET TERM ; !!
