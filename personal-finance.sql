-- phpMyAdmin SQL Dump
-- version 5.0.0-dev
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Nov 29, 2018 at 10:01 AM
-- Server version: 5.7.20
-- PHP Version: 7.1.20

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT = @@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS = @@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION = @@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `personal-finance`
--

-- --------------------------------------------------------

--
-- Table structure for table `ruble_wallet`
--

CREATE TABLE `ruble_wallet` (
  `id`           int(10) UNSIGNED NOT NULL,
  `money_amount` int(11)                   DEFAULT NULL,
  `user_id`      int(10) UNSIGNED NOT NULL DEFAULT ''0''
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

--
-- Dumping data for table `ruble_wallet`
--

INSERT INTO `ruble_wallet` (`id`, `money_amount`, `user_id`)
VALUES (4, 100000, 1);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id`        int(10) UNSIGNED NOT NULL,
  `firstname` varchar(255)     NOT NULL,
  `lastname`  varchar(255)     NOT NULL,
  `username`  varchar(255)     NOT NULL,
  `password`  varchar(255)     NOT NULL
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `firstname`, `lastname`, `username`, `password`)
VALUES (1, ''John'', ''Doe'', ''username1'', ''$2y$10$hrqOEUlekb0OgRpMg/D3EuRfCsjeeVfVl3JXO./4Yta8aPTFNgpty'');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `ruble_wallet`
--
ALTER TABLE `ruble_wallet`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `ruble_wallet`
--
ALTER TABLE `ruble_wallet`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  AUTO_INCREMENT = 5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  AUTO_INCREMENT = 2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `ruble_wallet`
--
ALTER TABLE `ruble_wallet`
  ADD CONSTRAINT `fk_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT = @OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS = @OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION = @OLD_COLLATION_CONNECTION */;
