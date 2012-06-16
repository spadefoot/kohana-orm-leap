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
	"id" NUMBER(11) NOT NULL,
	"name" VARCHAR2(32) NOT NULL,
	"description" VARCHAR(255) NOT NULL,
	CONSTRAINT "roles_id_pkey" PRIMARY KEY ("id"),
	CONSTRAINT "roles_name_ukey" UNIQUE ("name")
);

----
-- Auto-increment the "roles" table (see, http://earlruby.org/2009/01/creating-auto-increment-columns-in-oracle/)
----

CREATE SEQUENCE "roles_id_seq" START WITH 1 INCREMENT BY 1;

CREATE TRIGGER "roles_id_trig" BEFORE INSERT ON "roles" FOR EACH ROW
DECLARE
    max_id NUMBER;
    cur_seq NUMBER;
BEGIN
    IF :new.id IS NULL THEN
        -- No ID passed, get one from the sequence
        SELECT "roles_id_seq".nextval INTO :new.id FROM dual;
    ELSE
        -- ID was set via insert, so update the sequence
        SELECT greatest(nvl(max(id),0), :new.id) INTO max_id FROM "roles";
        SELECT "roles_id_seq".nextval INTO cur_seq FROM dual;
        WHILE cur_seq < max_id
        LOOP
            SELECT "roles_id_seq".nextval INTO cur_seq FROM dual;
        END LOOP;
    END IF;
END;

----
-- Roles for the "roles" table 
----

-- INSERT INTO roles ("name", "description") VALUES ('login', 'Login privileges, granted after account confirmation.');
-- INSERT INTO roles ("name", "description") VALUES ('admin', 'Administrative user, has access to everything.');

----
-- Table structure for the "users" table
----

CREATE TABLE "users" (
	"id" NUMBER(11) NOT NULL,
	"email" VARCHAR2(254) NOT NULL,
	"username" VARCHAR2(32) NOT NULL DEFAULT '',
	"password" VARCHAR2(64) NOT NULL,
	"firstname" VARCHAR2(35) DEFAULT NULL,
	"lastname" VARCHAR2(50) DEFAULT NULL,
	"activated" NUMBER(1) NOT NULL DEFAULT 1,
	"banned" NUMBER(1) NOT NULL DEFAULT 0,
	"ban_reason" VARCHAR2(255) DEFAULT NULL,
	"new_password_key" VARCHAR2(64) DEFAULT NULL,
	"new_password_requested" NUMBER(11) DEFAULT NULL,
	"new_email" VARCHAR2(254) DEFAULT NULL,
	"new_email_key" VARCHAR2(64) DEFAULT NULL,
	"logins" NUMBER(10) NOT NULL DEFAULT 0,
	"last_login" NUMBER(10),
	"last_ip" VARCHAR2(39) DEFAULT NULL,
	CONSTRAINT "users_id_pkey" PRIMARY KEY ("id"),
	CONSTRAINT "users_username_ukey" UNIQUE ("username"),
	CONSTRAINT "users_email_ukey" UNIQUE ("email"),
	CONSTRAINT "users_logins_check" CHECK ("logins" >= 0)
);

----
-- Auto-increment the "users" table (see, http://earlruby.org/2009/01/creating-auto-increment-columns-in-oracle/)
----

CREATE SEQUENCE "users_id_seq" START WITH 1 INCREMENT BY 1;

CREATE TRIGGER "users_id_trig" BEFORE INSERT ON "users" FOR EACH ROW
DECLARE
    max_id NUMBER;
    cur_seq NUMBER;
BEGIN
    IF :new.id IS NULL THEN
        -- No ID passed, get one from the sequence
        SELECT "users_id_seq".nextval INTO :new.id FROM dual;
    ELSE
        -- ID was set via insert, so update the sequence
        SELECT greatest(nvl(max(id),0), :new.id) INTO max_id FROM "users";
        SELECT "users_id_seq".nextval INTO cur_seq FROM dual;
        WHILE cur_seq < max_id
        LOOP
            SELECT "users_id_seq".nextval INTO cur_seq FROM dual;
        END LOOP;
    END IF;
END;

----
-- Table structure for the "user_roles" table
----

CREATE TABLE "user_roles" (
	"user_id" NUMBER(10),
	"role_id" NUMBER(10),
	CONSTRAINT "users_roles_pkey" PRIMARY KEY ("user_id", "role_id")
);

----
-- Table structure for the "user_tokens" table
----

CREATE TABLE "user_tokens" (
	"id" NUMBER(11) NOT NULL,
	"user_id" NUMBER(11) NOT NULL,
	"user_agent" VARCHAR2(40) NOT NULL,
	"token" VARCHAR2(40) NOT NULL,
	"type" VARCHAR2(100) NOT NULL,
	"created" NUMBER(11) NOT NULL,
	"expires" NUMBER(11) NOT NULL,
	CONSTRAINT "user_tokens_id_pkey" PRIMARY KEY ("id"),
	CONSTRAINT "user_tokens_token_ukey" UNIQUE ("token")
);

----
-- Auto-increment the "users" table (see, http://earlruby.org/2009/01/creating-auto-increment-columns-in-oracle/)
----

CREATE SEQUENCE "user_tokens_id_seq" START WITH 1 INCREMENT BY 1;

CREATE TRIGGER "user_tokens_id_trig" BEFORE INSERT ON "user_tokens" FOR EACH ROW
DECLARE
    max_id NUMBER;
    cur_seq NUMBER;
BEGIN
    IF :new.id IS NULL THEN
        -- No ID passed, get one from the sequence
        SELECT "user_tokens_id_seq".nextval INTO :new.id FROM dual;
    ELSE
        -- ID was set via insert, so update the sequence
        SELECT greatest(nvl(max(id),0), :new.id) INTO max_id FROM "user_tokens";
        SELECT "user_tokens_id_seq".nextval INTO cur_seq FROM dual;
        WHILE cur_seq < max_id
        LOOP
            SELECT "user_tokens_id_seq".nextval INTO cur_seq FROM dual;
        END LOOP;
    END IF;
END;

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
