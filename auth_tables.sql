-- phpMyAdmin SQL Dump
-- version 3.4.5
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Mar 30, 2012 at 08:34 AM
-- Server version: 5.5.16
-- PHP Version: 5.3.8

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `test`
--

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE IF NOT EXISTS `roles` (
  `rID` int(11) NOT NULL AUTO_INCREMENT,
  `rName` varchar(255) COLLATE utf8_bin NOT NULL,
  `rDescription` varchar(255) COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`rID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE IF NOT EXISTS `sessions` (
  `sesID` varchar(24) COLLATE utf8_bin NOT NULL,
  `sesLastActive` int(11) NOT NULL,
  `sesContents` longtext COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`sesID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `uID` int(11) NOT NULL AUTO_INCREMENT,
  `uUsername` varchar(50) CHARACTER SET latin1 NOT NULL,
  `uEmail` varchar(255) CHARACTER SET latin1 NOT NULL,
  `uPassword` varchar(255) CHARACTER SET latin1 NOT NULL,
  `uFirstName` varchar(100) CHARACTER SET latin1 DEFAULT NULL,
  `uLastName` varchar(100) CHARACTER SET latin1 DEFAULT NULL,
  `uActivated` tinyint(1) NOT NULL DEFAULT '1',
  `uBanned` tinyint(1) NOT NULL DEFAULT '0',
  `uBanReason` varchar(255) CHARACTER SET latin1 DEFAULT NULL,
  `uNewPasswordKey` varchar(64) CHARACTER SET latin1 DEFAULT NULL,
  `uNewPasswordRequested` int(11) DEFAULT NULL,
  `uNewEmail` varchar(255) CHARACTER SET latin1 DEFAULT NULL,
  `uNewEmailKey` varchar(64) CHARACTER SET latin1 DEFAULT NULL,
  `uLastIp` varchar(40) CHARACTER SET latin1 DEFAULT NULL,
  `uLastLogin` int(11) DEFAULT NULL,
  `uLogins` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`uID`),
  UNIQUE KEY `uEmail` (`uEmail`),
  UNIQUE KEY `uUsername` (`uUsername`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=7 ;

-- --------------------------------------------------------

--
-- Table structure for table `user_roles`
--

CREATE TABLE IF NOT EXISTS `user_roles` (
  `uID` int(11) NOT NULL,
  `rID` int(11) NOT NULL,
  PRIMARY KEY (`uID`,`rID`),
  KEY `rID` (`rID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `user_tokens`
--

CREATE TABLE IF NOT EXISTS `user_tokens` (
  `utID` int(11) NOT NULL AUTO_INCREMENT,
  `utUserAgent` varchar(40) COLLATE utf8_bin NOT NULL,
  `utToken` varchar(40) COLLATE utf8_bin NOT NULL,
  `utType` varchar(100) COLLATE utf8_bin DEFAULT NULL,
  `utCreated` int(11) DEFAULT NULL,
  `utExpires` int(11) DEFAULT NULL,
  `uID` int(11) NOT NULL,
  PRIMARY KEY (`utID`),
  KEY `uID` (`uID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=6 ;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `user_roles`
--
ALTER TABLE `user_roles`
  ADD CONSTRAINT `user_roles_ibfk_3` FOREIGN KEY (`uID`) REFERENCES `users` (`uID`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_roles_ibfk_4` FOREIGN KEY (`rID`) REFERENCES `roles` (`rID`) ON DELETE CASCADE;

--
-- Constraints for table `user_tokens`
--
ALTER TABLE `user_tokens`
  ADD CONSTRAINT `user_tokens_ibfk_1` FOREIGN KEY (`uID`) REFERENCES `users` (`uID`) ON DELETE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
