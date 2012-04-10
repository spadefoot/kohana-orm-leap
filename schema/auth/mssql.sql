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

CREATE TABLE [roles] (
	[id] [int] IDENTITY(1,1) NOT NULL PRIMARY KEY,
	[name] [varchar](32) NOT NULL UNIQUE,
	[description] [varchar](255) NOT NULL
);

----
-- Roles for the "roles" table 
----

-- INSERT INTO [roles] ([name], [description]) VALUES ('login', 'Login privileges, granted after account confirmation.');
-- INSERT INTO [roles] ([name], [description]) VALUES ('admin', 'Administrative user, has access to everything.');

----
-- Table structure for the "users" table
----

CREATE TABLE [users] (
	[id] [int] IDENTITY(1,1) NOT NULL PRIMARY KEY,
	[email] [varchar](254) NOT NULL UNIQUE,
	[username] [varchar](32) NOT NULL DEFAULT ('') UNIQUE,
	[password] [varchar](64) NOT NULL,
	[firstname] [varchar](35) DEFAULT NULL,
	[lastname] [varchar](50) DEFAULT NULL,
	[activated] [tinyint] NOT NULL DEFAULT 1,
	[banned] [tinyint] NOT NULL DEFAULT 0,
	[ban_reason] [varchar](255) DEFAULT NULL,
	[new_password_key] [varchar](64) DEFAULT NULL,
	[new_password_requested] [int] DEFAULT NULL,
	[new_email] [varchar](254) DEFAULT NULL,
	[new_email_key] [varchar](64) DEFAULT NULL,
	[logins] [int] NOT NULL DEFAULT 0,
	[last_login] [int] DEFAULT NULL,
	[last_ip] [varchar](39) DEFAULT NULL
);

----
-- Table structure for the "user_roles" table
----

CREATE TABLE [user_roles] (
	[user_id] [int] NOT NULL,
	[role_id] [int] NOT NULL,
	PRIMARY KEY ([user_id],[role_id]),
	FOREIGN KEY ([user_id]) REFERENCES [users] ON DELETE CASCADE,
	FOREIGN KEY ([role_id]) REFERENCES [roles] ON DELETE CASCADE
);

----
-- Table structure for the "user_tokens" table
----

CREATE TABLE [user_tokens] (
	[id] [int] IDENTITY(1,1) NOT NULL PRIMARY KEY,
	[user_id] [int] NOT NULL,
	[user_agent] [varchar](40) NOT NULL,
	[token] [varchar](40) NOT NULL UNIQUE,
	[type] [varchar](100) NOT NULL,
	[created] [int] NOT NULL,
	[expires] [int] NOT NULL,
	FOREIGN KEY([user_id]) REFERENCES [users] ON DELETE CASCADE
);
