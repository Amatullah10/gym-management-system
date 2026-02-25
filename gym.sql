-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 25, 2026 at 12:23 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

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
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `posted_by` int(11) NOT NULL,
  `category` enum('General','Urgent','Event','Maintenance') NOT NULL DEFAULT 'General',
  `is_pinned` tinyint(1) NOT NULL DEFAULT 0,
  `posted_by_name` varchar(150) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`id`, `title`, `message`, `posted_by`, `category`, `is_pinned`, `posted_by_name`, `created_at`) VALUES
(1, 'Membership Renewal Reminder', 'Members whose subscriptions are expiring this month are requested to renew at the earliest to avoid any interruption in services. Visit the front desk or pay online.', 7, 'Urgent', 0, 'Sneha Patil (Receptionist)', '2026-02-22 22:08:35'),
(2, 'Gym Closed on Republic Day', 'The gym will remain closed on 26th January for Republic Day. Regular timings will resume from 27th January. Wishing everyone a Happy Republic Day!', 1, 'General', 0, 'Admin', '2026-02-18 22:08:35'),
(3, 'Equipment Maintenance Notice', 'The treadmill section will be under maintenance on Feb 25th from 2:00 PM to 5:00 PM. Please plan your workouts accordingly. We apologize for the inconvenience.', 1, 'Maintenance', 0, 'Admin', '2026-02-21 22:08:35'),
(4, 'New Batch Starting — Zumba Morning', 'A new Zumba batch is starting from March 1st every Monday and Wednesday at 7:00 AM. Limited slots available. Contact the front desk to enroll.', 7, 'Event', 1, 'Sneha Patil (Receptionist)', '2026-02-23 22:08:35');

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `attendance_date` date NOT NULL,
  `status` enum('Present','Absent','Unmarked') NOT NULL DEFAULT 'Unmarked',
  `check_in_time` time DEFAULT NULL,
  `check_out_time` time DEFAULT NULL,
  `marked_by` varchar(100) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`id`, `member_id`, `attendance_date`, `status`, `check_in_time`, `check_out_time`, `marked_by`, `updated_at`) VALUES
(1, 1, '2026-02-01', 'Present', '07:30:00', '09:30:00', 'admin@gmail.com', '2026-02-01 07:30:00'),
(2, 2, '2026-02-01', 'Absent', NULL, NULL, 'admin@gmail.com', '2026-02-01 07:30:00'),
(3, 4, '2026-02-01', 'Present', '08:15:00', '10:00:00', 'receptionist@gmail.com', '2026-02-01 08:15:00'),
(4, 5, '2026-02-01', 'Absent', NULL, NULL, 'receptionist@gmail.com', '2026-02-01 08:15:00'),
(5, 1, '2026-02-10', 'Present', '06:45:00', '08:30:00', 'trainer@gmail.com', '2026-02-10 06:45:00'),
(6, 2, '2026-02-10', 'Present', '07:00:00', '09:00:00', 'trainer@gmail.com', '2026-02-10 07:00:00'),
(7, 4, '2026-02-10', 'Present', '09:10:00', '10:45:00', 'trainer@gmail.com', '2026-02-10 09:10:00'),
(8, 5, '2026-02-10', 'Absent', NULL, NULL, 'trainer@gmail.com', '2026-02-10 07:00:00'),
(9, 1, '2026-02-15', 'Present', '07:20:00', '09:20:00', 'admin@gmail.com', '2026-02-15 07:20:00'),
(10, 2, '2026-02-15', 'Present', '08:00:00', '09:45:00', 'admin@gmail.com', '2026-02-15 08:00:00');

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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `members`
--

INSERT INTO `members` (`id`, `full_name`, `email`, `phone`, `dob`, `gender`, `address`, `membership_type`, `start_date`, `end_date`, `duration`, `membership_status`, `fitness_level`, `created_at`, `updated_at`) VALUES
(1, 'Rahul Sharma', 'rahul.sharma@email.com', '+91 98765 43210', '1995-03-15', 'Male', '123 MG Road, Mumbai', 'Premium - 1299/month', '2024-01-15', '2025-01-15', '12 Months', 'Active', 'Medium', '2026-01-17 19:06:40', '2026-01-17 19:06:40'),
(2, 'Priya Patel', 'priya.patel@email.com', '+91 87654 32109', '1998-07-22', 'Female', '456 Linking Road, Bandra', 'Basic - 799/month', '2024-03-20', '2025-03-20', '6 Months', 'Active', 'Beginner', '2026-01-17 19:06:40', '2026-01-17 19:06:40'),
(3, 'Amit Kumar', 'amit.kumar@email.com', '+91 76543 21098', '1992-11-08', 'Male', '789 Andheri West, Mumbai', 'Premium - 1299/month', '2023-06-10', '2024-06-10', '3 Months', 'Expired', 'Advanced', '2026-01-17 19:06:40', '2026-01-17 19:06:40'),
(4, 'Amatullah Imran Dhorajiwala', 'ammu@gmail.com', '8850596172', '2005-10-14', 'Female', 'xyz abc 401209', 'Premium - 1299/month', '2026-01-01', '2026-02-01', '1 Month', 'Active', 'Medium', '2026-01-17 19:10:50', '2026-01-17 19:10:50'),
(5, 'Hatim Dhorajiwala', 'hatim@gmail.com', '88505961098', '2026-01-01', 'Male', 'abc xyz', 'Basic - 799/month', '2026-01-01', '2026-02-01', '3 Months', 'Inactive', 'Advanced', '2026-01-17 19:16:23', '2026-01-18 18:21:03');

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

CREATE TABLE `staff` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `role` enum('trainer','receptionist','accountant','manager','maintenance','other') NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `join_date` date NOT NULL,
  `salary` decimal(10,2) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `skills` text DEFAULT NULL,
  `experience` varchar(100) DEFAULT NULL,
  `emergency_contact` varchar(50) DEFAULT NULL,
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `staff`
--

INSERT INTO `staff` (`id`, `full_name`, `role`, `email`, `phone`, `join_date`, `salary`, `address`, `skills`, `experience`, `emergency_contact`, `status`, `created_at`) VALUES
(1, 'Rajesh Verma', 'trainer', 'trainer@gmail.com', '+91 99001 12345', '2023-04-01', 30000.00, 'Dadar, Mumbai', 'Weight Training, Cardio, HIIT', '4 years', '+91 99001 99999', 'Active', '2026-01-17 10:00:00'),
(2, 'Sunita Mehta', 'receptionist', 'receptionist@gmail.com', '+91 88002 23456', '2023-06-15', 22000.00, 'Borivali, Mumbai', 'Customer Service, MS Office', '2 years', '+91 88002 88888', 'Active', '2026-01-17 10:00:00'),
(3, 'Imran Shaikh', 'accountant', 'accountant@gmail.com', '+91 77003 34567', '2022-11-01', 35000.00, 'Kurla, Mumbai', 'Accounting, Tally, GST', '5 years', '+91 77003 77777', 'Active', '2026-01-17 10:00:00'),
(4, 'Pooja Nair', 'trainer', 'trainer2@gmail.com', '+91 66004 45678', '2024-01-10', 28000.00, 'Thane, Mumbai', 'Yoga, Zumba, Functional Training', '3 years', '+91 66004 66666', 'Active', '2026-01-17 10:00:00'),
(5, 'Deepak Patil', 'maintenance', 'maintenance@gmail.com', '+91 55005 56789', '2024-03-01', 18000.00, 'Mulund, Mumbai', 'Equipment Maintenance, Plumbing', '6 months', '+91 55005 55555', 'Active', '2026-01-17 10:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `role` enum('admin','trainer','accountant','receptionist','customer') NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `role`, `email`, `password`) VALUES
(1, 'admin', 'admin@gmail.com', 'admin123'),
(2, 'customer', 'customer@gmail.com', 'customer123'),
(5, 'trainer', 'trainer@gmail.com', 'trainer123'),
(6, 'accountant', 'accountant@gmail.com', 'accountant123'),
(7, 'receptionist', 'receptionist@gmail.com', 'reception123');

-- --------------------------------------------------------

--
-- Table structure for table `workout_plans`
--

CREATE TABLE `workout_plans` (
  `id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `goal` varchar(100) DEFAULT NULL,
  `current_plan` varchar(100) DEFAULT NULL,
  `progress` int(11) DEFAULT 0,
  `status` enum('Active','Pending','Inactive') DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `workout_plans`
--

INSERT INTO `workout_plans` (`id`, `member_id`, `goal`, `current_plan`, `progress`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 'Build Muscle', 'Strength Training Pro', 75, 'Active', '2026-01-18 18:19:34', '2026-01-18 18:19:34'),
(2, 2, 'Weight Loss', 'Fat Burn Challenge', 60, 'Active', '2026-01-18 18:19:34', '2026-01-18 18:19:34'),
(3, 3, 'General Fitness', 'Balanced Fitness', 45, 'Active', '2026-01-18 18:19:34', '2026-01-18 18:19:34'),
(4, 4, 'Strength & Toning', 'Womens Strength', 30, 'Pending', '2026-01-18 18:19:34', '2026-01-18 18:19:34'),
(5, 5, 'Weight Loss', 'Strength Training Pro', 50, 'Inactive', '2026-01-18 18:19:34', '2026-01-18 18:22:15');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_attendance` (`member_id`,`attendance_date`),
  ADD KEY `attendance_date` (`attendance_date`);

--
-- Indexes for table `members`
--
ALTER TABLE `members`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `staff`
--
ALTER TABLE `staff`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `workout_plans`
--
ALTER TABLE `workout_plans`
  ADD PRIMARY KEY (`id`),
  ADD KEY `member_id` (`member_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `members`
--
ALTER TABLE `members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `staff`
--
ALTER TABLE `staff`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `workout_plans`
--
ALTER TABLE `workout_plans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `workout_plans`
--
ALTER TABLE `workout_plans`
  ADD CONSTRAINT `workout_plans_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
