----
-- This license is a legal agreement between you and the Kohana Team for the use of Kohana Framework
-- (the "Software"). By obtaining the Software you agree to comply with the terms and conditions of
-- this license.
--
-- Copyright © 2007–2012 Kohana Team. All rights reserved.
--
-- Redistribution and use in source and binary forms, with or without modification, are permitted
-- provided that the following conditions are met:
--
--    * Redistributions of source code must retain the above copyright notice, this list of conditions
--      and the following disclaimer.
--    * Redistributions in binary form must reproduce the above copyright notice, this list of conditions
--      and the following disclaimer in the documentation and/or other materials provided with the distribution.
--    * Neither the name of the Kohana nor the names of its contributors may be used to endorse or promote
--      products derived from this software without specific prior written permission.
--
-- THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED
-- WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A
-- PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR
-- ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
-- LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
-- INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR
-- TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF
-- ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
----

----
-- Table structure for the "roles" table
----

CREATE TABLE "roles" (
	"id" INTEGER NOT NULL,
	"name" VARCHAR(32) NOT NULL,
	"description" VARCHAR(255) NOT NULL,
	CONSTRAINT "roles_id_pkey" PRIMARY KEY ("id"),
	CONSTRAINT "roles_name_ukey" UNIQUE ("name")
);

----
-- Auto-increment the "roles" table (see, http://www.firebirdfaq.org/faq29/)
----

CREATE GENERATOR "roles_id_gen";

SET GENERATOR "roles_id_gen" TO 0;

SET TERM !! ;
CREATE TRIGGER "roles_id_trig" FOR "roles" ACTIVE BEFORE INSERT POSITION 0 AS
BEGIN
	IF (NEW.ID IS NULL) THEN NEW.ID = GEN_ID("roles_id_gen", 1);
END!!
SET TERM ; !!

----
-- Roles for the "roles" table 
----

-- INSERT INTO "roles" ("name", "description") VALUES ('login', 'Login privileges, granted after account confirmation.');
-- INSERT INTO "roles" ("name", "description") VALUES ('admin', 'Administrative user, has access to everything.');

----
-- Table structure for the "users" table
----

CREATE TABLE "users" (
	"id" INTEGER NOT NULL,
	"email" VARCHAR(254) NOT NULL UNIQUE,
	"username" VARCHAR(32) NOT NULL DEFAULT '' UNIQUE,
	"password" VARCHAR(64) NOT NULL,
	"firstname" VARCHAR(35) DEFAULT NULL,
	"lastname" VARCHAR(50) DEFAULT NULL,
	"activated" SMALLINT NOT NULL DEFAULT 1,
	"banned" SMALLINT NOT NULL DEFAULT 0,
	"ban_reason" VARCHAR(255) DEFAULT NULL,
	"new_password_key" VARCHAR(64) DEFAULT NULL,
	"new_password_requested" INTEGER DEFAULT NULL,
	"new_email" VARCHAR(254) DEFAULT NULL,
	"new_email_key" VARCHAR(64) DEFAULT NULL,
	"logins" INTEGER NOT NULL DEFAULT 0,
	"last_login" INTEGER DEFAULT NULL,
	"last_ip" VARCHAR(39) DEFAULT NULL,
	CONSTRAINT "users_id_pkey" PRIMARY KEY ("id"),
	CONSTRAINT "users_username_key" UNIQUE ("username"),
	CONSTRAINT "users_email_ukey" UNIQUE ("email"),
	CONSTRAINT "users_logins_check" CHECK ("logins" >= 0)
);

----
-- Auto-increment the "roles" table (see, http://www.firebirdfaq.org/faq29/)
----

CREATE GENERATOR "users_id_gen";

SET GENERATOR "users_id_gen" TO 0;

SET TERM !! ;
CREATE TRIGGER "users_id_trig" FOR "users" ACTIVE BEFORE INSERT POSITION 0 AS
BEGIN
	IF (NEW.ID IS NULL) THEN NEW.ID = GEN_ID("users_id_gen", 1);
END!!
SET TERM ; !!

----
-- Table structure for the "user_roles" table
----

CREATE TABLE "user_roles" (
	"user_id" INTEGER NOT NULL,
	"role_id" INTEGER NOT NULL,
	CONSTRAINT "users_roles_pkey" PRIMARY KEY ("user_id", "role_id")
);

----
-- Table structure for the "user_tokens" table
----

CREATE TABLE "user_tokens" (
	"id" INTEGER NOT NULL,
	"user_id" INTEGER NOT NULL,
	"user_agent" VARCHAR(40) NOT NULL,
	"token" VARCHAR(40) NOT NULL,
	"type" VARCHAR(100) NOT NULL,
	"created" INTEGER NOT NULL,
	"expires" INTEGER NOT NULL,
	CONSTRAINT "user_tokens_id_pkey" PRIMARY KEY ("id"),
	CONSTRAINT "user_tokens_token_ukey" UNIQUE ("token")
);

----
-- Auto-increment the "roles" table (see, http://www.firebirdfaq.org/faq29/)
----

CREATE GENERATOR "user_tokens_id_gen";

SET GENERATOR "user_tokens_id_gen" TO 0;

SET TERM !! ;
CREATE TRIGGER "user_tokens_id_trig" FOR "user_tokens" ACTIVE BEFORE INSERT POSITION 0 AS
BEGIN
	IF (NEW.ID IS NULL) THEN NEW.ID = GEN_ID("user_tokens_id_gen", 1);
END!!
SET TERM ; !!

----
-- Constraints for the "user_roles" table
----

CREATE INDEX "user_id_idx" ON "user_roles" ("user_id");
CREATE INDEX "role_id_idx" ON "user_roles" ("role_id");

ALTER TABLE "user_roles"
	ADD CONSTRAINT "user_roles_user_id_fkey" FOREIGN KEY ("user_id") REFERENCES "users" ("id") ON DELETE CASCADE,
	ADD CONSTRAINT "user_roles_role_id_fkey" FOREIGN KEY ("role_id") REFERENCES "roles" ("id") ON DELETE CASCADE;

----
-- Constraints for the "user_tokens" table
----

ALTER TABLE "user_tokens"
	ADD CONSTRAINT "user_tokens_user_id_fkey" FOREIGN KEY ("user_id") REFERENCES "users" ("id") ON DELETE CASCADE;
