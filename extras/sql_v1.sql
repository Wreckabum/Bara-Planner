-- phpMyAdmin SQL Dump
-- version 5.0.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jan 09, 2021 at 01:47 PM
-- Server version: 5.7.19
-- PHP Version: 7.4.11

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `fyp_mgmt_tool`
--
CREATE DATABASE IF NOT EXISTS `fyp_mgmt_tool` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `fyp_mgmt_tool`;

-- --------------------------------------------------------

--
-- Table structure for table `accounts`
--

DROP TABLE IF EXISTS `accounts`;
CREATE TABLE IF NOT EXISTS `accounts` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `first_name` tinytext NOT NULL,
  `last_name` tinytext NOT NULL,
  `password` varchar(64) NOT NULL,
  `dob` datetime NOT NULL,
  `email` varchar(64) NOT NULL,
  `type` int(1) NOT NULL COMMENT '0 = Faculty, 1 = Student',
  `position` varchar(64) NOT NULL COMMENT 'Faculty only',
  `experience` int(2) UNSIGNED NOT NULL COMMENT 'Faculty only',
  `majors` varchar(8) NOT NULL COMMENT 'Faculty => 1, Student = 1',
  `enrol_date` datetime NOT NULL COMMENT 'Student only',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `fk_majors` (`majors`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Triggers `accounts`
--
DROP TRIGGER IF EXISTS `after_supervisor_delete`;
DELIMITER $$
CREATE TRIGGER `after_supervisor_delete` AFTER DELETE ON `accounts` FOR EACH ROW IF old.type = 0 THEN
	UPDATE `groups`
		SET `supervisor` = ''
		WHERE `supervisor` = old.id;
END IF
$$
DELIMITER ;
DROP TRIGGER IF EXISTS `after_supervisor_update`;
DELIMITER $$
CREATE TRIGGER `after_supervisor_update` AFTER UPDATE ON `accounts` FOR EACH ROW IF old.type = 0 THEN
	UPDATE `groups`
		SET `supervisor` = new.id
		WHERE `supervisor` = old.id;
END IF
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `groups`
--

DROP TABLE IF EXISTS `groups`;
CREATE TABLE IF NOT EXISTS `groups` (
  `id` varchar(16) NOT NULL,
  `name` varchar(32) NOT NULL,
  `supervisor` int(10) UNSIGNED NOT NULL,
  `members` json NOT NULL COMMENT 'JSON of int(10)',
  `project` varchar(10) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `fk_supervisor` (`supervisor`),
  KEY `fk_project` (`project`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `majors`
--

DROP TABLE IF EXISTS `majors`;
CREATE TABLE IF NOT EXISTS `majors` (
  `id` varchar(8) NOT NULL,
  `name` varchar(64) NOT NULL,
  `description` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Triggers `majors`
--
DROP TRIGGER IF EXISTS `after_major_delete`;
DELIMITER $$
CREATE TRIGGER `after_major_delete` AFTER DELETE ON `majors` FOR EACH ROW UPDATE `accounts` 
	SET `majors` = ''
	WHERE `majors` = old.id
$$
DELIMITER ;
DROP TRIGGER IF EXISTS `after_major_update`;
DELIMITER $$
CREATE TRIGGER `after_major_update` AFTER UPDATE ON `majors` FOR EACH ROW UPDATE `accounts` 
	SET `majors` = new.id
	WHERE `majors` = old.id
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

DROP TABLE IF EXISTS `projects`;
CREATE TABLE IF NOT EXISTS `projects` (
  `id` varchar(8) NOT NULL,
  `name` varchar(64) NOT NULL,
  `description` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Triggers `projects`
--
DROP TRIGGER IF EXISTS `after_project_delete`;
DELIMITER $$
CREATE TRIGGER `after_project_delete` AFTER DELETE ON `projects` FOR EACH ROW UPDATE `groups` 
	SET `project` = ''
	WHERE `project` = old.id
$$
DELIMITER ;
DROP TRIGGER IF EXISTS `after_project_update`;
DELIMITER $$
CREATE TRIGGER `after_project_update` AFTER UPDATE ON `projects` FOR EACH ROW UPDATE `groups` 
	SET `project` = new.id
	WHERE `project` = old.id
$$
DELIMITER ;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
