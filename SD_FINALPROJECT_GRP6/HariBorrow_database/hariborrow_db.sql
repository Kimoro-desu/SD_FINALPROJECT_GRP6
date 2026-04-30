-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 29, 2026 at 09:47 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `hariborrow_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `assets`
--

CREATE TABLE `assets` (
  `Asset_ID` int(11) NOT NULL,
  `Lender_ID` int(10) DEFAULT NULL,
  `asset_name` varchar(50) DEFAULT NULL,
  `asset_type` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `meetup_location` varchar(255) DEFAULT NULL,
  `proposed_penalty_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `daily_penalty` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `availability` varchar(50) DEFAULT 'unavailable',
  `time_created` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `asset_image` varchar(512) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `return_photos`
--

CREATE TABLE `return_photos` (
  `photo_id` int(11) NOT NULL AUTO_INCREMENT,
  `transaction_id` int(11) NOT NULL,
  `photo_path` varchar(512) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`photo_id`),
  KEY `fk_return_photos_txn` (`transaction_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `penalties`
--

CREATE TABLE `penalties` (
  `penalty_id` int(11) NOT NULL,
  `transaction_id` int(11) NOT NULL,
  `borrower_id` int(11) NOT NULL,
  `penalty_amount` decimal(10,2) DEFAULT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `is_paid` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `transaction_id` int(11) NOT NULL,
  `asset_id` int(11) NOT NULL,
  `borrower_id` int(11) NOT NULL,
  `request_status` varchar(45) NOT NULL DEFAULT 'Pending',
  `time_created` timestamp NULL DEFAULT current_timestamp(),
  `borrowed_at` datetime DEFAULT NULL,
  `due_date` datetime DEFAULT NULL,
  `return_date` datetime DEFAULT NULL,
  `penalty_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `rating_locked` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `User_ID` int(11) NOT NULL,
  `school_id_number` varchar(50) DEFAULT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `middle_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `password_hash` varchar(255) DEFAULT NULL,
  `user_role` varchar(50) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `plm_email` varchar(50) DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `background_picture` varchar(255) DEFAULT NULL,
  `account_status` varchar(32) NOT NULL DEFAULT 'active',
  `account_notes` varchar(500) DEFAULT NULL,
  UNIQUE KEY `plm_email` (`plm_email`),
  UNIQUE KEY `school_id_number` (`school_id_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `assets`
--
ALTER TABLE `assets`
  ADD PRIMARY KEY (`Asset_ID`),
  ADD KEY `idx_assets_lender` (`Lender_ID`),
  ADD KEY `idx_assets_status_availability` (`status`, `availability`);

--
-- Indexes for table `penalties`
--
ALTER TABLE `penalties`
  ADD PRIMARY KEY (`penalty_id`),
  ADD KEY `fk_penalty_transaction` (`transaction_id`),
  ADD KEY `fk_penalty_borrower` (`borrower_id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`transaction_id`),
  ADD KEY `fk_transaction_asset_idx` (`asset_id`),
  ADD KEY `fk_transaction_borrower_idx` (`borrower_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`User_ID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `assets`
--
ALTER TABLE `assets`
  MODIFY `Asset_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `penalties`
--
ALTER TABLE `penalties`
  MODIFY `penalty_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `User_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `return_photos`
--
ALTER TABLE `return_photos`
  ADD CONSTRAINT `fk_return_photos_txn` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`transaction_id`) ON DELETE CASCADE;

--
-- Constraints for table `penalties`
--
ALTER TABLE `penalties`
  ADD CONSTRAINT `fk_penalty_borrower` FOREIGN KEY (`borrower_id`) REFERENCES `users` (`User_ID`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_penalty_transaction` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`transaction_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `fk_transaction_asset` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`Asset_ID`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_transaction_borrower` FOREIGN KEY (`borrower_id`) REFERENCES `users` (`User_ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Table structure for table `user_notifications`
--
CREATE TABLE `user_notifications` (
  `notification_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `severity` varchar(32) NOT NULL DEFAULT 'info',
  `icon_class` varchar(64) NOT NULL DEFAULT 'ph-info',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`notification_id`),
  KEY `idx_user_notifications_user_time` (`user_id`,`created_at`),
  CONSTRAINT `fk_user_notifications_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`User_ID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Table structure for table `system_logs`
--
CREATE TABLE `system_logs` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `event_type` varchar(32) NOT NULL,
  `description` text NOT NULL,
  `actor` varchar(255) DEFAULT NULL,
  `ip_address` varchar(64) DEFAULT NULL,
  `status` varchar(32) NOT NULL DEFAULT 'Success',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`log_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Table structure for table `registration_requests`
--
CREATE TABLE `registration_requests` (
  `request_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `status` varchar(32) NOT NULL DEFAULT 'Pending',
  `requested_at` timestamp NULL DEFAULT current_timestamp(),
  `processed_at` datetime DEFAULT NULL,
  `processed_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`request_id`),
  UNIQUE KEY `uniq_user_request` (`user_id`),
  CONSTRAINT `fk_regreq_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`User_ID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Table structure for table `transaction_ratings`
--
CREATE TABLE `transaction_ratings` (
  `rating_id` int(11) NOT NULL AUTO_INCREMENT,
  `transaction_id` int(11) NOT NULL,
  `rater_id` int(11) NOT NULL,
  `ratee_id` int(11) NOT NULL,
  `rating` tinyint(3) UNSIGNED NOT NULL,
  `review_text` varchar(500) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`rating_id`),
  UNIQUE KEY `uniq_tx_rater` (`transaction_id`,`rater_id`),
  KEY `idx_tx` (`transaction_id`),
  KEY `idx_ratee` (`ratee_id`),
  KEY `idx_rater` (`rater_id`),
  CONSTRAINT `chk_rating_range` CHECK (`rating` >= 1 and `rating` <= 5)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Seed Data for `users`
--
INSERT INTO `users` (`school_id_number`, `first_name`, `last_name`, `password_hash`, `user_role`, `plm_email`) VALUES
('ADMIN-01', 'System', 'Admin', '$2y$10$gxe7hLE7dsSLA2cNG15mn.duXbzRYqm4CaeAdiV/ECi0zoxV1dvNm', 'Admin', 'admin@plm.edu.ph');

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
