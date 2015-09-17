-- phpMyAdmin SQL Dump
-- version 4.1.14
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Jun 06, 2015 at 12:44 PM
-- Server version: 5.6.17
-- PHP Version: 5.5.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `course_builder`
--

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE IF NOT EXISTS `courses` (
  `id_course` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `full_name` varchar(255) NOT NULL,
  PRIMARY KEY (`id_course`),
  UNIQUE KEY `title` (`title`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;


--
-- Table structure for table `shares`
--

CREATE TABLE IF NOT EXISTS `shares` (
  `id_course` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `permission` enum('R','RW') NOT NULL DEFAULT 'R',
  `owner` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_course`,`id_user`),
  UNIQUE KEY `id_course` (`id_course`,`id_user`),
  KEY `fk_share_user` (`id_user`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


--
-- Table structure for table `slides`
--

CREATE TABLE IF NOT EXISTS `slides` (
  `id_slide` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `cuepoint` int(11) NOT NULL,
  `image` varchar(100) NOT NULL,
  `audio` varchar(100) NOT NULL,
  `text` varchar(1024) DEFAULT NULL,
  `slide_order` tinyint(1) NOT NULL,
  `id_course` int(11) NOT NULL,
  PRIMARY KEY (`id_slide`),
  KEY `id_course` (`id_course`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;


--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id_user` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(30) NOT NULL,
  `password` char(32) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `last_time` timestamp NULL DEFAULT NULL,
  `login_forge` int(1) NOT NULL DEFAULT '0',
  `type` enum('ADMIN','MANAGER') NOT NULL DEFAULT 'MANAGER',
  PRIMARY KEY (`id_user`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id_user`, `username`, `password`, `first_name`, `last_name`, `email`, `last_time`, `login_forge`, `type`) VALUES
(1, 'admin', 'admin123', 'Administrator', 'Course Builder', 'leccher@eng.it', '2015-06-06 10:37:59', 0, 'ADMIN');

--
-- Constraints for dumped tables
--

--
-- Constraints for table `shares`
--
ALTER TABLE `shares`
  ADD CONSTRAINT `fk_share_course` FOREIGN KEY (`id_course`) REFERENCES `courses` (`id_course`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_share_user` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `slides`
--
ALTER TABLE `slides`
  ADD CONSTRAINT `fk_course` FOREIGN KEY (`id_course`) REFERENCES `courses` (`id_course`) ON DELETE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
