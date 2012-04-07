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
-- Enables foreign key constraints
----

PRAGMA foreign_keys = ON;

----
-- Table structure for the "roles" table
----

CREATE TABLE IF NOT EXISTS [roles] (
	[id] INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
	[name] VARCHAR(32) NOT NULL UNIQUE,
	[description] VARCHAR(255) NOT NULL
);

----
-- Roles for the "roles" table 
----

-- INSERT INTO [roles] ([id], [name], [description]) VALUES (1, 'login', 'Login privileges, granted after account confirmation.');
-- INSERT INTO [roles] ([id], [name], [description]) VALUES (2, 'admin', 'Administrative user, has access to everything.');

----
-- Table structure for the "users" table
----

CREATE TABLE IF NOT EXISTS [users] (
	[id] INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
	[email] VARCHAR(254) NOT NULL UNIQUE,
	[username] VARCHAR(32) NOT NULL DEFAULT '' UNIQUE,
	[password] VARCHAR(64) NOT NULL,
	[firstname] VARCHAR(35) DEFAULT NULL,
	[lastname] VARCHAR(50) DEFAULT NULL,
	[activated] BOOLEAN NOT NULL DEFAULT 1,
	[banned] BOOLEAN NOT NULL DEFAULT 0,
	[ban_reason] VARCHAR(255) DEFAULT NULL,
	[new_password_key] VARCHAR(64) DEFAULT NULL,
	[new_password_requested] INTEGER DEFAULT NULL,
	[new_email] VARCHAR(254) DEFAULT NULL,
	[new_email_key] VARCHAR(64) DEFAULT NULL,
	[logins] INTEGER NOT NULL DEFAULT 0,
	[last_login] INTEGER DEFAULT NULL,
	[last_ip] VARCHAR(39) DEFAULT NULL
);

----
-- Table structure for the "user_roles" table
----

CREATE TABLE IF NOT EXISTS [user_roles] (
	[user_id] INTEGER NOT NULL,
	[role_id] INTEGER NOT NULL,
	PRIMARY KEY ([user_id],[role_id]),
	FOREIGN KEY ([user_id]) REFERENCES [users] ON DELETE CASCADE,
	FOREIGN KEY ([role_id]) REFERENCES [roles] ON DELETE CASCADE
);

----
-- Table structure for the "user_tokens" table
----

CREATE TABLE IF NOT EXISTS [user_tokens] (
	[id] INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
	[user_id] INTEGER NOT NULL,
	[user_agent] VARCHAR(40) NOT NULL,
	[token] VARCHAR(40) NOT NULL UNIQUE,
	[type] VARCHAR(100) NOT NULL,
	[created] INTEGER NOT NULL,
	[expires] INTEGER NOT NULL,
	FOREIGN KEY([user_id]) REFERENCES [users] ON DELETE CASCADE
);
