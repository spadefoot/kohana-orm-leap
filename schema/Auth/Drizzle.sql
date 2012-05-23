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

CREATE TABLE IF NOT EXISTS `roles` (
	`id` INT NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(32) NOT NULL,
	`description` VARCHAR(255) NOT NULL,
	PRIMARY KEY (`id`),
	UNIQUE KEY `roles_name_ukey` (`name`)
) ENGINE=InnoDB;

----
-- Roles for the "roles" table 
----

-- INSERT INTO `roles` (`name`, `description`) VALUES ('login', 'Login privileges, granted after account confirmation.');
-- INSERT INTO `roles` (`name`, `description`) VALUES ('admin', 'Administrative user, has access to everything.');

----
-- Table structure for the "users" table
----

CREATE TABLE IF NOT EXISTS `users` (
	`id` INT NOT NULL AUTO_INCREMENT,
	`email` VARCHAR(254) NOT NULL,
	`username` VARCHAR(32) NOT NULL DEFAULT '',
	`password` VARCHAR(64) NOT NULL,
	`firstname` VARCHAR(35) DEFAULT NULL,
	`lastname` VARCHAR(50) DEFAULT NULL,
	`activated` INT NOT NULL DEFAULT 1,
	`banned` INT NOT NULL DEFAULT 0,
	`ban_reason` VARCHAR(255) DEFAULT NULL,
	`new_password_key` VARCHAR(64) DEFAULT NULL,
	`new_password_requested` INT DEFAULT NULL,
	`new_email` VARCHAR(254) DEFAULT NULL,
	`new_email_key` VARCHAR(64) DEFAULT NULL,
	`logins` INT NOT NULL DEFAULT 0,
	`last_login` INT DEFAULT NULL,
	`last_ip` VARCHAR(39) DEFAULT NULL,
	PRIMARY KEY (`id`),
	UNIQUE KEY `users_username_ukey` (`username`),
	UNIQUE KEY `users_email_ukey` (`email`)
) ENGINE=InnoDB;

----
-- Table structure for the "user_roles" table
----

CREATE TABLE IF NOT EXISTS `user_roles` (
	`user_id` INT NOT NULL,
	`role_id` INT NOT NULL,
	PRIMARY KEY (`user_id`,`role_id`),
	KEY `fk_role_id` (`role_id`)
) ENGINE=InnoDB;

----
-- Table structure for the "user_tokens" table
----

CREATE TABLE IF NOT EXISTS `user_tokens` (
	`id` INT NOT NULL AUTO_INCREMENT,
	`user_id` INT NOT NULL,
	`user_agent` VARCHAR(40) NOT NULL,
	`token` VARCHAR(40) NOT NULL,
	`type` VARCHAR(100) NOT NULL,
	`created` INT NOT NULL,
	`expires` INT NOT NULL,
	PRIMARY KEY (`id`),
	UNIQUE KEY `user_tokens_token_ukey` (`token`),
	KEY `fk_user_id` (`user_id`)
) ENGINE=InnoDB;

----
-- Constraints for the "user_roles" table
----

ALTER TABLE `user_roles`
	ADD CONSTRAINT `user_roles_user_id_fkey` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
	ADD CONSTRAINT `user_roles_role_id_fkey` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

----
-- Constraints for the "user_tokens" table
----

ALTER TABLE `user_tokens`
	ADD CONSTRAINT `user_tokens_user_id_fkey` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
