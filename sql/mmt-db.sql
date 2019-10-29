-- phpMyAdmin SQL Dump
-- version 4.8.5
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Oct 08, 2019 at 06:24 AM
-- Server version: 5.7.26
-- PHP Version: 7.2.18

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `mmt-db`
--

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

DROP TABLE IF EXISTS `comments`;
CREATE TABLE IF NOT EXISTS `comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `weeklyreport_id` int(11) NOT NULL,
  `content` varchar(1000) DEFAULT NULL,
  `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_modified` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `weeklyreport_id` (`weeklyreport_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `members`
--

DROP TABLE IF EXISTS `members`;
CREATE TABLE IF NOT EXISTS `members` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `user_id` int(10) NOT NULL,
  `project_id` int(10) NOT NULL,
  `project_role` varchar(20) NOT NULL,
  `starting_date` date DEFAULT NULL,
  `ending_date` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_key` (`user_id`),
  KEY `project_key` (`project_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `metrics`
--

DROP TABLE IF EXISTS `metrics`;
CREATE TABLE IF NOT EXISTS `metrics` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `project_id` int(10) NOT NULL,
  `metrictype_id` int(10) NOT NULL,
  `weeklyreport_id` int(10) DEFAULT NULL,
  `date` date NOT NULL,
  `value` float NOT NULL,
  PRIMARY KEY (`id`),
  KEY `project_key` (`project_id`),
  KEY `metrictype_key` (`metrictype_id`),
  KEY `weeklyreport_key` (`weeklyreport_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `metrics_old`
--

DROP TABLE IF EXISTS `metrics_old`;
CREATE TABLE IF NOT EXISTS `metrics_old` (
  `metric_id` int(10) NOT NULL,
  `metric_name` varchar(20) CHARACTER SET latin1 DEFAULT NULL,
  `metric_descripition` varchar(50) CHARACTER SET latin1 DEFAULT NULL,
  `value_type` int(10) DEFAULT NULL,
  PRIMARY KEY (`metric_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `metrictypes`
--

DROP TABLE IF EXISTS `metrictypes`;
CREATE TABLE IF NOT EXISTS `metrictypes` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `description` varchar(200) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `metric_values`
--

DROP TABLE IF EXISTS `metric_values`;
CREATE TABLE IF NOT EXISTS `metric_values` (
  `value_id` int(10) NOT NULL,
  `project_id` int(10) DEFAULT NULL,
  `metric_id` int(10) DEFAULT NULL,
  `decimal_value` double(10,0) DEFAULT NULL,
  `other_value` varchar(10) CHARACTER SET latin1 DEFAULT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`value_id`),
  KEY `project_id` (`project_id`),
  KEY `metric_id` (`metric_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `newreports`
--

DROP TABLE IF EXISTS `newreports`;
CREATE TABLE IF NOT EXISTS `newreports` (
  `user_id` int(11) NOT NULL DEFAULT '0',
  `weeklyreport_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`user_id`,`weeklyreport_id`),
  KEY `weeklyreport_id` (`weeklyreport_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `notes`
--

DROP TABLE IF EXISTS `notes`;
CREATE TABLE IF NOT EXISTS `notes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `content` varchar(1000) DEFAULT NULL,
  `created_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `project_role` varchar(20) NOT NULL,
  `email` varchar(40) DEFAULT NULL,
  `contact_user` tinyint(1) DEFAULT NULL,
  `note_read` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
CREATE TABLE IF NOT EXISTS `notifications` (
  `comment_id` int(11) NOT NULL DEFAULT '0',
  `member_id` int(11) NOT NULL DEFAULT '0',
  `weeklyreport_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`comment_id`,`member_id`),
  KEY `member_id` (`member_id`),
  KEY `weeklyreport_id` (`weeklyreport_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

DROP TABLE IF EXISTS `projects`;
CREATE TABLE IF NOT EXISTS `projects` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `project_name` varchar(50) NOT NULL,
  `created_on` date NOT NULL,
  `updated_on` date DEFAULT NULL,
  `finished_date` date DEFAULT NULL,
  `description` varchar(100) DEFAULT NULL,
  `is_public` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `project_name` (`project_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `risks`
--

DROP TABLE IF EXISTS `risks`;
CREATE TABLE IF NOT EXISTS `risks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `description` varchar(45) NOT NULL,
  `impact` int(11) NOT NULL,
  `probability` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `slack`
--

DROP TABLE IF EXISTS `slack`;
CREATE TABLE IF NOT EXISTS `slack` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `webhookurl` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `trello`
--

DROP TABLE IF EXISTS `trello`;
CREATE TABLE IF NOT EXISTS `trello` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `board_id` varchar(45) NOT NULL,
  `app_key` varchar(45) NOT NULL,
  `token` varchar(180) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `trellolinks`
--

DROP TABLE IF EXISTS `trellolinks`;
CREATE TABLE IF NOT EXISTS `trellolinks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `trello_id` int(11) NOT NULL,
  `list_id` varchar(45) NOT NULL,
  `requirement_type` varchar(45) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `email` varchar(40) NOT NULL,
  `password` varchar(255) NOT NULL,
  `first_name` varchar(20) NOT NULL,
  `last_name` varchar(20) NOT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `role` varchar(20) NOT NULL,
  `password_key` varchar(90) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `users_old`
--

DROP TABLE IF EXISTS `users_old`;
CREATE TABLE IF NOT EXISTS `users_old` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `weeklyhours`
--

DROP TABLE IF EXISTS `weeklyhours`;
CREATE TABLE IF NOT EXISTS `weeklyhours` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `weeklyreport_id` int(10) NOT NULL,
  `member_id` int(10) NOT NULL,
  `duration` float NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `weeklyreport_id` (`weeklyreport_id`,`member_id`),
  KEY `member_key` (`member_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `weeklyreports`
--

DROP TABLE IF EXISTS `weeklyreports`;
CREATE TABLE IF NOT EXISTS `weeklyreports` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `project_id` int(10) NOT NULL,
  `title` varchar(70) DEFAULT NULL,
  `week` int(2) NOT NULL,
  `year` int(4) NOT NULL,
  `reglink` varchar(100) DEFAULT NULL,
  `problems` varchar(400) DEFAULT NULL,
  `meetings` varchar(400) NOT NULL,
  `additional` varchar(400) DEFAULT NULL,
  `created_on` date NOT NULL,
  `updated_on` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `week` (`week`,`year`,`project_id`),
  KEY `project_key` (`project_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `weeklyrisks`
--

DROP TABLE IF EXISTS `weeklyrisks`;
CREATE TABLE IF NOT EXISTS `weeklyrisks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `risk_id` int(11) NOT NULL,
  `weeklyreport_id` int(11) NOT NULL,
  `probability` int(11) NOT NULL,
  `impact` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `workinghours`
--

DROP TABLE IF EXISTS `workinghours`;
CREATE TABLE IF NOT EXISTS `workinghours` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `member_id` int(10) NOT NULL,
  `worktype_id` int(10) NOT NULL,
  `date` date NOT NULL,
  `description` varchar(100) NOT NULL,
  `duration` float NOT NULL,
  PRIMARY KEY (`id`),
  KEY `member_key` (`member_id`),
  KEY `worktype_key` (`worktype_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `worktypes`
--

DROP TABLE IF EXISTS `worktypes`;
CREATE TABLE IF NOT EXISTS `worktypes` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `description` varchar(200) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`weeklyreport_id`) REFERENCES `weeklyreports` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `members`
--
ALTER TABLE `members`
  ADD CONSTRAINT `members_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `members_ibfk_2` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`);

--
-- Constraints for table `metrics`
--
ALTER TABLE `metrics`
  ADD CONSTRAINT `metrics_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`),
  ADD CONSTRAINT `metrics_ibfk_2` FOREIGN KEY (`metrictype_id`) REFERENCES `metrictypes` (`id`),
  ADD CONSTRAINT `metrics_ibfk_3` FOREIGN KEY (`weeklyreport_id`) REFERENCES `weeklyreports` (`id`);

--
-- Constraints for table `newreports`
--
ALTER TABLE `newreports`
  ADD CONSTRAINT `newreports_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `newreports_ibfk_2` FOREIGN KEY (`weeklyreport_id`) REFERENCES `weeklyreports` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`comment_id`) REFERENCES `comments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notifications_ibfk_2` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notifications_ibfk_3` FOREIGN KEY (`weeklyreport_id`) REFERENCES `weeklyreports` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `weeklyhours`
--
ALTER TABLE `weeklyhours`
  ADD CONSTRAINT `weeklyhours_ibfk_1` FOREIGN KEY (`weeklyreport_id`) REFERENCES `weeklyreports` (`id`),
  ADD CONSTRAINT `weeklyhours_ibfk_2` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`);

--
-- Constraints for table `weeklyreports`
--
ALTER TABLE `weeklyreports`
  ADD CONSTRAINT `weeklyreports_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`);

--
-- Constraints for table `workinghours`
--
ALTER TABLE `workinghours`
  ADD CONSTRAINT `workinghours_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`),
  ADD CONSTRAINT `workinghours_ibfk_2` FOREIGN KEY (`worktype_id`) REFERENCES `worktypes` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
