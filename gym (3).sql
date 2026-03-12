-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 03, 2026 at 08:52 PM
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
-- Database: `gym`
--

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `posted_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `attendance_date` date NOT NULL,
  `status` enum('Present','Absent','Unmarked') DEFAULT 'Unmarked',
  `check_in_time` time DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `check_out_time` time DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`id`, `member_id`, `attendance_date`, `status`, `check_in_time`, `created_at`, `check_out_time`) VALUES
(1, 1, '2026-02-20', 'Present', '23:23:18', '2026-02-20 17:53:18', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `equipment`
--

CREATE TABLE `equipment` (
  `id` int(11) NOT NULL,
  `equipment_name` varchar(100) NOT NULL,
  `quantity` int(11) DEFAULT 1,
  `status` enum('Working','Maintenance','Out of Order') DEFAULT 'Working',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `members`
--

CREATE TABLE `members` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `dob` date NOT NULL,
  `gender` enum('Male','Female','Other') NOT NULL,
  `address` text NOT NULL,
  `membership_type` varchar(50) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `duration` varchar(20) NOT NULL,
  `membership_status` enum('Active','Inactive','Expired') DEFAULT 'Active',
  `fitness_level` enum('Beginner','Medium','Advanced') DEFAULT 'Beginner',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `members`
--

INSERT INTO `members` (`id`, `full_name`, `email`, `phone`, `dob`, `gender`, `address`, `membership_type`, `start_date`, `end_date`, `duration`, `membership_status`, `fitness_level`, `created_at`) VALUES
(1, 'Shania', 'shania@gmail.com', '98654479976', '2026-02-20', 'Female', 'bandra worli', 'Premium - 1299/month', '2026-02-18', '2026-02-06', '3 Months', 'Active', 'Medium', '2026-02-17 17:32:22'),
(2, 'john', 'john@ngo.org', '98654479976', '2026-02-15', 'Male', 'bandra worli', 'Standard - 999/month', '2026-03-01', '2026-05-31', '3 Months', 'Active', 'Medium', '2026-02-20 18:02:01');

-- --------------------------------------------------------

--
-- Table structure for table `membership_plans`
--

CREATE TABLE `membership_plans` (
  `id` int(11) NOT NULL,
  `plan_name` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `duration_months` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `payment_date` date NOT NULL,
  `amount_paid` decimal(10,2) NOT NULL,
  `payment_method` enum('Cash','Card','UPI','Cheque','Online') NOT NULL,
  `service_type` varchar(100) DEFAULT 'Fitness',
  `plan_type` varchar(50) DEFAULT 'Monthly',
  `payment_for_month` varchar(20) DEFAULT NULL,
  `receipt_number` varchar(50) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

CREATE TABLE `staff` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `role` enum('trainer','receptionist','accountant','manager','maintenance','other') NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `join_date` date NOT NULL,
  `salary` decimal(10,2) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `skills` text DEFAULT NULL,
  `experience` varchar(100) DEFAULT NULL,
  `emergency_contact` varchar(20) DEFAULT NULL,
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `staff`
--

INSERT INTO `staff` (`id`, `full_name`, `role`, `email`, `phone`, `join_date`, `salary`, `address`, `skills`, `experience`, `emergency_contact`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Rahul Sharma', 'trainer', 'trainer@gmail.com', '9876543201', '2025-01-10', 28000.00, 'Bandra, Mumbai', 'Weight Training, Cardio, HIIT', '3 years', '9876500011', 'Active', '2026-02-21 17:23:37', '2026-02-21 17:23:37'),
(2, 'Priya Desai', 'trainer', 'trainer2@gmail.com', '9876543202', '2025-03-15', 26000.00, 'Andheri, Mumbai', 'Yoga, Zumba, Flexibility', '2 years', '9876500012', 'Active', '2026-02-21 17:23:37', '2026-02-21 17:23:37'),
(3, 'Amit Kulkarni', 'accountant', 'accountant@gmail.com', '9876543203', '2024-11-01', 35000.00, 'Pune, Maharashtra', 'Tally, GST, Accounting', '5 years', '9876500013', 'Active', '2026-02-21 17:23:37', '2026-02-21 17:23:37'),
(4, 'Sneha Patil', 'receptionist', 'receptionist@gmail.com', '9876543204', '2025-06-01', 20000.00, 'Wakad, Pune', 'Customer Service, MS Office', '1 year', '9876500014', 'Active', '2026-02-21 17:23:37', '2026-02-21 17:23:37');

-- --------------------------------------------------------

--
-- Table structure for table `trainer_assignments`
--

CREATE TABLE `trainer_assignments` (
  `id` int(11) NOT NULL,
  `trainer_id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `assigned_date` date DEFAULT curdate(),
  `status` enum('Active','Inactive') DEFAULT 'Active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `trainer_assignments`
--

INSERT INTO `trainer_assignments` (`id`, `trainer_id`, `member_id`, `assigned_date`, `status`) VALUES
(1, 1, 1, '2026-02-21', 'Active'),
(2, 2, 2, '2026-02-21', 'Active');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `role` enum('admin','trainer','receptionist','accountant') NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `role`, `email`, `password`) VALUES
(1, 'admin', 'admin@gmail.com', 'admin123'),
(2, '', 'customer@gmail.com', 'customer123'),
(5, 'trainer', 'trainer@gmail.com', 'trainer123'),
(6, 'accountant', 'accountant@gmail.com', 'accountant123'),
(7, 'receptionist', 'receptionist@gmail.com', 'reception123'),
(8, 'trainer', 'trainer2@gmail.com', 'trainer2123');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `posted_by` (`posted_by`);

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `member_id` (`member_id`,`attendance_date`);

--
-- Indexes for table `equipment`
--
ALTER TABLE `equipment`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `members`
--
ALTER TABLE `members`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `membership_plans`
--
ALTER TABLE `membership_plans`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_member_id` (`member_id`),
  ADD KEY `idx_payment_date` (`payment_date`),
  ADD KEY `idx_payment_method` (`payment_method`);

--
-- Indexes for table `staff`
--
ALTER TABLE `staff`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `trainer_assignments`
--
ALTER TABLE `trainer_assignments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `trainer_id` (`trainer_id`),
  ADD KEY `member_id` (`member_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `equipment`
--
ALTER TABLE `equipment`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `members`
--
ALTER TABLE `members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `membership_plans`
--
ALTER TABLE `membership_plans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `staff`
--
ALTER TABLE `staff`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `trainer_assignments`
--
ALTER TABLE `trainer_assignments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `announcements`
--
ALTER TABLE `announcements`
  ADD CONSTRAINT `announcements_ibfk_1` FOREIGN KEY (`posted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `trainer_assignments`
--
ALTER TABLE `trainer_assignments`
  ADD CONSTRAINT `trainer_assignments_ibfk_1` FOREIGN KEY (`trainer_id`) REFERENCES `staff` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `trainer_assignments_ibfk_2` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;


-- Payments Table
CREATE TABLE IF NOT EXISTS `payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_date` datetime NOT NULL DEFAULT current_timestamp(),
  `service` varchar(100) NOT NULL,
  `plan` enum('Monthly','Quarterly','Yearly') NOT NULL DEFAULT 'Monthly',
  `status` enum('Paid','Due','Overdue') NOT NULL DEFAULT 'Paid',
  `payment_method` enum('Cash','Card','UPI','Online') NOT NULL DEFAULT 'Cash',
  `transaction_id` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `member_id` (`member_id`),
  CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Payment Reminders Table
CREATE TABLE IF NOT EXISTS `payment_reminders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) NOT NULL,
  `type` enum('Due','Overdue','General') NOT NULL DEFAULT 'General',
  `message` text NOT NULL,
  `sent_date` datetime NOT NULL DEFAULT current_timestamp(),
  `sent_by` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `member_id` (`member_id`),
  CONSTRAINT `payment_reminders_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Sample payment data
INSERT INTO `payments` (`member_id`, `amount`, `payment_date`, `service`, `plan`, `status`, `payment_method`) VALUES
(1, 2500.00, '2026-02-24 10:00:00', 'Fitness', 'Monthly', 'Paid', 'Cash'),
(2, 6000.00, '2026-02-23 11:30:00', 'Fitness + Cardio', 'Quarterly', 'Paid', 'UPI'),
(3, 2500.00, '2026-02-20 09:00:00', 'Fitness', 'Monthly', 'Paid', 'Card'),
(4, 12000.00, '2026-02-18 14:00:00', 'Personal Training', 'Yearly', 'Paid', 'Online'),
(5, 2500.00, '2026-02-15 08:00:00', 'Fitness', 'Monthly', 'Overdue', 'Cash');