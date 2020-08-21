-- phpMyAdmin SQL Dump
-- version 4.8.5
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jun 03, 2020 at 09:47 AM
-- Server version: 5.7.26
-- PHP Version: 5.6.40

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `test`
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
-- Table structure for table `git`
--

DROP TABLE IF EXISTS `git`;
CREATE TABLE IF NOT EXISTS `git` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `repository` varchar(100) NOT NULL,
  `owner` varchar(40) NOT NULL,
  `token` varchar(200) NOT NULL,
  PRIMARY KEY (`id`)
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
  `target_hours` int(10) DEFAULT NULL,
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
-- Table structure for table `metrictypes`
--

DROP TABLE IF EXISTS `metrictypes`;
CREATE TABLE IF NOT EXISTS `metrictypes` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `description` varchar(200) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `metrictypes`
--

INSERT INTO `metrictypes` (`id`, `description`) VALUES
(1, 'phase'),
(2, 'totalPhases'),
(3, 'reqNew'),
(4, 'reqInProgress'),
(5, 'reqClosed'),
(6, 'reqRejected'),
(7, 'commits'),
(8, 'passedTestCases'),
(9, 'totalTestCases'),
(10, 'degreeReadiness'),
(11, 'overallStatus');

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
  `customer` varchar(200) DEFAULT NULL,
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
  `research_allowed` tinyint(2) DEFAULT '-1',
  `role` varchar(20) NOT NULL,
  `password_key` varchar(90) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `password`, `first_name`, `last_name`, `phone`, `research_allowed`, `role`, `password_key`) VALUES
(1, 'anon1@a.com', '1', 'Anonymous', '1', NULL, 1, 'user', NULL),
(2, 'anon2@a.com', '2', 'Anonymous', '2', NULL, 1, 'user', NULL),
(3, 'anon3@a.com', '3', 'Anonymous', '3', NULL, 1, 'user', NULL),
(4, 'anon4@a.com', '4', 'Anonymous', '4', NULL, 1, 'user', NULL),
(5, 'anon5@a.com', '5', 'Anonymous', '5', NULL, 1, 'user', NULL),
(6, 'anon6@a.com', '6', 'Anonymous', '6', NULL, 1, 'user', NULL),
(7, 'anon7@a.com', '7', 'Anonymous', '7', NULL, 1, 'user', NULL),
(8, 'anon8@a.com', '8', 'Anonymous', '8', NULL, 1, 'user', NULL),
(9, 'anon9@a.com', '9', 'Anonymous', '9', NULL, 1, 'user', NULL),
(10, 'anon10@a.com', '10', 'Anonymous', '10', NULL, 1, 'user', NULL),
(11, 'anon11@a.com', '11', 'Anonymous', '11', NULL, 1, 'user', NULL),
(12, 'anon12@a.com', '12', 'Anonymous', '12', NULL, 1, 'user', NULL),
(13, 'anon13@a.com', '13', 'Anonymous', '13', NULL, 1, 'user', NULL),
(14, 'anon14@a.com', '14', 'Anonymous', '14', NULL, 1, 'user', NULL),
(15, 'anon15@a.com', '15', 'Anonymous', '15', NULL, 1, 'user', NULL),
(16, 'anon16@a.com', '16', 'Anonymous', '16', NULL, 1, 'user', NULL),
(17, 'anon17@a.com', '17', 'Anonymous', '17', NULL, 1, 'user', NULL),
(18, 'anon18@a.com', '18', 'Anonymous', '18', NULL, 1, 'user', NULL),
(19, 'anon19@a.com', '19', 'Anonymous', '19', NULL, 1, 'user', NULL),
(20, 'anon20@a.com', '20', 'Anonymous', '20', NULL, 1, 'user', NULL),
(21, 'admin@admin.com', '$2y$10$zHzl5GChh7cXdJnMvRld.eHgp7EU8VMSFcmuGoSnuvjQm6VhqJcvS', 'A', 'Admin', NULL, 1, 'admin', NULL);

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
  `created_by` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
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
  `date` date DEFAULT NULL,
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
  `created_on` date DEFAULT NULL,
  `modified_on` date DEFAULT NULL,
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
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `worktypes`
--

INSERT INTO `worktypes` (`id`, `description`) VALUES
(1, 'Documentation'),
(2, 'Requirements'),
(3, 'Design'),
(4, 'Implementation'),
(5, 'Testing'),
(6, 'Meetings'),
(7, 'Studying'),
(8, 'Other'),
(9, 'Lectures');

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
