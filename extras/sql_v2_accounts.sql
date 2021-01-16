-- phpMyAdmin SQL Dump
-- version 5.0.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jan 16, 2021 at 07:14 AM
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
  `type` int(1) NOT NULL COMMENT '0 = Faculty, 1 = Full-time Student, 2 = Part-time Student',
  `position` varchar(64) DEFAULT NULL COMMENT 'Faculty only',
  `experience` int(2) UNSIGNED DEFAULT NULL COMMENT 'Faculty only',
  `majors` json DEFAULT NULL COMMENT 'Faculty => JSON of varchar(8),\r\nStudent = varchar(8) -\r\n[majors.id]',
  `choices` json DEFAULT NULL COMMENT 'Students only -\r\nJSON of varchar(8) [projects.id]',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `accounts`
--

INSERT INTO `accounts` (`id`, `first_name`, `last_name`, `password`, `dob`, `email`, `type`, `position`, `experience`, `majors`, `choices`) VALUES
(1, 'Anakin', 'Skywalker', 'IAmYourFather', '1967-02-01 14:23:44', 'DarthVader@empire.com', 0, 'Sith Lord', 27, '[\"DARK101\", \"DARK102\"]', NULL),
(2, 'Luke', 'Skywalker', 'MissYouUncleBen', '1989-07-20 12:07:11', 'Rebels@republic.com', 1, NULL, NULL, '[\"LIGHT102\"]', NULL),
(3, 'Leia', 'Skywalker', 'ForAlderaan!', '1989-07-20 12:07:11', 'Official@alderaan.com', 2, NULL, NULL, '[\"LIGHT101\"]', NULL);

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
  `members` json NOT NULL COMMENT 'JSON of int(10) [accounts.id]',
  `project` varchar(10) NOT NULL,
  `deadline` datetime NOT NULL,
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
-- Dumping data for table `majors`
--

INSERT INTO `majors` (`id`, `name`, `description`) VALUES
('DARK101', 'Basics of the dark side', 'Learn the basic rules of the Sith such as the Rule of 2, the Dark Side of the force, etc.'),
('LIGHT101', 'Basics of the Light Side', 'Learn the basics of the Jedi such as maintaining balance, protecting the people, etc.'),
('DARK102', 'History of the Sith', 'Learn of the past teachings of the Sith, and how they can be used to further the Sith\'s influence.'),
('LIGHT102', 'History of the Jedi', 'Learn the old ways of the Jedi, and discover both the success and failings of the order.');

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
  `available_for` json NOT NULL COMMENT 'JSON of varchar(8) [majors.id]',
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
