-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 14, 2026 at 05:30 AM
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
-- Table structure for table `equipment`
--

CREATE TABLE `equipment` (
  `id` int(11) NOT NULL,
  `equipment_name` varchar(100) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `status` enum('Working','Maintenance','Out of Order') NOT NULL DEFAULT 'Working',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `equipment`
--

INSERT INTO `equipment` (`id`, `equipment_name`, `quantity`, `status`, `created_at`, `updated_at`) VALUES
(1, 'aa', 1, 'Out of Order', '2026-03-14 00:22:16', '2026-03-14 00:26:02');

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
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_date` datetime NOT NULL DEFAULT current_timestamp(),
  `service` varchar(100) NOT NULL,
  `plan` enum('Monthly','Quarterly','Yearly') NOT NULL DEFAULT 'Monthly',
  `status` enum('Paid','Due','Overdue') NOT NULL DEFAULT 'Paid',
  `payment_method` enum('Cash','Card','UPI','Online') NOT NULL DEFAULT 'Cash',
  `transaction_id` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `member_id`, `amount`, `payment_date`, `service`, `plan`, `status`, `payment_method`, `transaction_id`, `notes`, `created_at`) VALUES
(1, 1, 2500.00, '2026-02-24 10:00:00', 'Fitness', 'Monthly', 'Paid', 'Cash', NULL, NULL, '2026-03-12 18:42:13'),
(2, 2, 6000.00, '2026-02-23 11:30:00', 'Fitness + Cardio', 'Quarterly', 'Paid', 'UPI', NULL, NULL, '2026-03-12 18:42:13'),
(3, 3, 2500.00, '2026-02-20 09:00:00', 'Fitness', 'Monthly', 'Paid', 'Card', NULL, NULL, '2026-03-12 18:42:13'),
(4, 4, 12000.00, '2026-02-18 14:00:00', 'Personal Training', 'Yearly', 'Paid', 'Online', NULL, NULL, '2026-03-12 18:42:13'),
(5, 5, 2500.00, '2026-02-15 08:00:00', 'Fitness', 'Monthly', 'Overdue', 'Cash', NULL, NULL, '2026-03-12 18:42:13'),
(6, 4, 12000.00, '2026-03-13 00:24:33', 'Personal Training', 'Yearly', 'Paid', 'Cash', '', NULL, '2026-03-12 18:54:33'),
(7, 4, 12000.00, '2026-03-13 00:24:39', 'Personal Training', 'Yearly', 'Paid', 'Cash', '', NULL, '2026-03-12 18:54:39'),
(8, 4, 12000.00, '2026-03-13 00:24:59', 'Personal Training', 'Yearly', 'Paid', 'Cash', '', NULL, '2026-03-12 18:54:59'),
(9, 4, 12000.00, '2026-03-13 00:25:14', 'Personal Training', 'Yearly', 'Paid', 'Cash', '', NULL, '2026-03-12 18:55:14'),
(10, 4, 12000.00, '2026-03-13 00:26:47', 'Personal Training', 'Yearly', 'Paid', 'Cash', '', NULL, '2026-03-12 18:56:47'),
(11, 4, 12000.00, '2026-03-13 00:26:50', 'Personal Training', 'Yearly', 'Paid', 'Cash', '', NULL, '2026-03-12 18:56:50'),
(12, 2, 6000.00, '2026-03-13 00:26:56', 'Fitness + Cardio', 'Quarterly', 'Paid', 'Cash', '', NULL, '2026-03-12 18:56:56'),
(13, 3, 2500.00, '2026-03-13 00:27:58', 'Fitness', 'Monthly', 'Paid', 'Cash', '', NULL, '2026-03-12 18:57:58'),
(14, 1, 2500.00, '2026-03-13 00:29:46', 'Fitness', 'Monthly', 'Paid', 'Cash', '', NULL, '2026-03-12 18:59:46'),
(15, 4, 12000.00, '2026-03-13 00:29:52', 'Personal Training', 'Yearly', 'Paid', 'Cash', '', NULL, '2026-03-12 18:59:52'),
(16, 4, 12000.00, '2026-03-13 00:31:16', 'Personal Training', 'Yearly', 'Paid', 'Cash', '', NULL, '2026-03-12 19:01:16'),
(17, 4, 12000.00, '2026-03-13 00:31:22', 'Personal Training', 'Yearly', 'Paid', 'Cash', '', NULL, '2026-03-12 19:01:22'),
(18, 4, 12000.00, '2026-03-13 00:31:40', 'Personal Training', 'Yearly', 'Paid', 'Cash', '', NULL, '2026-03-12 19:01:40'),
(19, 4, 12000.00, '2026-03-13 00:31:43', 'Personal Training', 'Yearly', 'Paid', 'Cash', '', NULL, '2026-03-12 19:01:43'),
(20, 5, 2500.00, '2026-03-13 00:35:24', 'Fitness', 'Monthly', 'Paid', 'Cash', '', NULL, '2026-03-12 19:05:24'),
(21, 5, 2500.00, '2026-03-13 00:35:29', 'Fitness', 'Monthly', 'Paid', 'Cash', '', NULL, '2026-03-12 19:05:29'),
(22, 4, 12000.00, '2026-03-13 00:39:49', 'Personal Training', 'Yearly', 'Paid', 'Cash', '', NULL, '2026-03-12 19:09:49'),
(23, 4, 12000.00, '2026-03-13 00:39:53', 'Personal Training', 'Yearly', 'Paid', 'Cash', '', NULL, '2026-03-12 19:09:53'),
(24, 4, 12000.00, '2026-03-13 00:39:55', 'Personal Training', 'Yearly', 'Paid', 'Cash', '', NULL, '2026-03-12 19:09:55'),
(25, 4, 12000.00, '2026-03-13 00:40:01', 'Personal Training', 'Yearly', 'Paid', 'Cash', '', NULL, '2026-03-12 19:10:01'),
(26, 4, 12000.00, '2026-03-13 00:41:33', 'Personal Training', 'Yearly', 'Paid', 'Cash', '', NULL, '2026-03-12 19:11:33'),
(27, 4, 12000.00, '2026-03-13 00:44:00', 'Personal Training', 'Yearly', 'Paid', 'Cash', '', NULL, '2026-03-12 19:14:00'),
(28, 3, 2500.00, '2026-03-13 00:44:02', 'Fitness', 'Monthly', 'Paid', 'Cash', '', NULL, '2026-03-12 19:14:02'),
(29, 2, 6000.00, '2026-03-13 00:44:05', 'Fitness + Cardio', 'Quarterly', 'Paid', 'Cash', '', NULL, '2026-03-12 19:14:05'),
(30, 4, 12000.00, '2026-03-13 00:45:22', 'Personal Training', 'Yearly', 'Paid', 'Cash', '', NULL, '2026-03-12 19:15:22'),
(31, 4, 12000.00, '2026-03-13 00:46:23', 'Personal Training', 'Yearly', 'Paid', 'Cash', '', NULL, '2026-03-12 19:16:23');

-- --------------------------------------------------------

--
-- Table structure for table `payment_reminders`
--

CREATE TABLE `payment_reminders` (
  `id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `type` enum('Due','Overdue','General') NOT NULL DEFAULT 'General',
  `message` text NOT NULL,
  `sent_date` datetime NOT NULL DEFAULT current_timestamp(),
  `sent_by` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment_reminders`
--

INSERT INTO `payment_reminders` (`id`, `member_id`, `type`, `message`, `sent_date`, `sent_by`) VALUES
(1, 4, 'General', 'Payment reminder sent to Amatullah Imran Dhorajiwala by admin@gmail.com', '2026-03-13 00:31:24', 'admin@gmail.com');

-- --------------------------------------------------------

--
-- Table structure for table `progress_reports`
--

CREATE TABLE `progress_reports` (
  `id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `date` date NOT NULL DEFAULT curdate(),
  `weight` decimal(5,2) DEFAULT NULL,
  `height` decimal(5,2) DEFAULT NULL,
  `bmi` decimal(5,2) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `recorded_by` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
-- Table structure for table `trainer_sessions`
--

CREATE TABLE `trainer_sessions` (
  `id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `trainer_email` varchar(100) NOT NULL,
  `title` varchar(100) NOT NULL,
  `session_type` enum('Training Session','Consultation','Assessment') NOT NULL DEFAULT 'Training Session',
  `session_date` date NOT NULL,
  `start_time` time NOT NULL,
  `duration` int(11) NOT NULL DEFAULT 60,
  `location` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('Upcoming','Completed','Cancelled') NOT NULL DEFAULT 'Upcoming',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `trainer_sessions`
--

INSERT INTO `trainer_sessions` (`id`, `member_id`, `trainer_email`, `title`, `session_type`, `session_date`, `start_time`, `duration`, `location`, `notes`, `status`, `created_at`) VALUES
(1, 4, 'trainer@gmail.com', 'sww', 'Consultation', '2026-03-14', '08:34:00', 60, 'ss', 'ss', 'Completed', '2026-03-14 02:04:57');

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
-- Table structure for table `weight_progress`
--

CREATE TABLE `weight_progress` (
  `id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `weight` decimal(5,2) NOT NULL,
  `recorded_date` date NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(4, 4, 'Strength & Toning', 'Fat Burn Challenge', 30, 'Active', '2026-01-18 18:19:34', '2026-03-14 04:26:46'),
(5, 5, 'Weight Loss', 'Strength Training Pro', 50, 'Inactive', '2026-01-18 18:19:34', '2026-01-18 18:22:15');

-- --------------------------------------------------------

--
-- Table structure for table `workout_plan_library`
--

CREATE TABLE `workout_plan_library` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `category` enum('Strength','Cardio','General','Athletic') NOT NULL DEFAULT 'General',
  `difficulty` enum('Beginner','Intermediate','Advanced') NOT NULL DEFAULT 'Beginner',
  `duration_weeks` int(11) NOT NULL DEFAULT 8,
  `exercises` text DEFAULT NULL,
  `created_by` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `workout_plan_library`
--

INSERT INTO `workout_plan_library` (`id`, `name`, `description`, `category`, `difficulty`, `duration_weeks`, `exercises`, `created_by`, `created_at`) VALUES
(1, 'Strength Training Pro', 'A comprehensive strength-building program targeting all major muscle groups with progressive overload.', 'Strength', 'Advanced', 8, 'Bench Press: 4x8, Squat: 4x8, Deadlift: 3x6, Pull-ups: 3x10, Overhead Press: 3x8', 'trainer@gmail.com', '2026-03-14 04:24:54'),
(2, 'Fat Burn Challenge', 'High-intensity interval training combined with metabolic conditioning for maximum fat loss.', 'Cardio', 'Intermediate', 6, 'Burpees: 3x15, Jump Rope: 3x3min, Box Jumps: 3x12, Mountain Climbers: 3x20, Sprints: 5x100m', 'trainer@gmail.com', '2026-03-14 04:24:54'),
(3, 'Balanced Fitness', 'A well-rounded program combining strength, cardio, and flexibility for overall health.', 'General', 'Beginner', 12, 'Goblet Squat: 3x12, Push-ups: 3x10-15, Dumbbell Rows: 3x12, Plank: 3x30-45s', 'trainer@gmail.com', '2026-03-14 04:24:54'),
(4, 'Women\'s Strength', 'Designed specifically for women looking to build lean muscle and improve body composition.', 'Strength', 'Intermediate', 10, 'Hip Thrust: 4x12, Romanian Deadlift: 3x10, Lat Pulldown: 3x12, Lunges: 3x12, Cable Row: 3x12', 'trainer@gmail.com', '2026-03-14 04:24:54'),
(5, 'Sports Performance', 'Athletic performance training with focus on power, agility, and sport-specific movements.', 'Athletic', 'Advanced', 8, 'Power Clean: 4x5, Box Jump: 4x6, Sprint Drills: 5x40m, Agility Ladder: 3x, Medicine Ball: 3x10', 'trainer@gmail.com', '2026-03-14 04:24:54');

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
-- Indexes for table `equipment`
--
ALTER TABLE `equipment`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `members`
--
ALTER TABLE `members`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `member_id` (`member_id`);

--
-- Indexes for table `payment_reminders`
--
ALTER TABLE `payment_reminders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `member_id` (`member_id`);

--
-- Indexes for table `progress_reports`
--
ALTER TABLE `progress_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `member_id` (`member_id`);

--
-- Indexes for table `staff`
--
ALTER TABLE `staff`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `trainer_sessions`
--
ALTER TABLE `trainer_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `member_id` (`member_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `weight_progress`
--
ALTER TABLE `weight_progress`
  ADD PRIMARY KEY (`id`),
  ADD KEY `member_id` (`member_id`);

--
-- Indexes for table `workout_plans`
--
ALTER TABLE `workout_plans`
  ADD PRIMARY KEY (`id`),
  ADD KEY `member_id` (`member_id`);

--
-- Indexes for table `workout_plan_library`
--
ALTER TABLE `workout_plan_library`
  ADD PRIMARY KEY (`id`);

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
-- AUTO_INCREMENT for table `equipment`
--
ALTER TABLE `equipment`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `members`
--
ALTER TABLE `members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `payment_reminders`
--
ALTER TABLE `payment_reminders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `progress_reports`
--
ALTER TABLE `progress_reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `staff`
--
ALTER TABLE `staff`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `trainer_sessions`
--
ALTER TABLE `trainer_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `weight_progress`
--
ALTER TABLE `weight_progress`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `workout_plans`
--
ALTER TABLE `workout_plans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `workout_plan_library`
--
ALTER TABLE `workout_plan_library`
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
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payment_reminders`
--
ALTER TABLE `payment_reminders`
  ADD CONSTRAINT `payment_reminders_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `progress_reports`
--
ALTER TABLE `progress_reports`
  ADD CONSTRAINT `progress_reports_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `trainer_sessions`
--
ALTER TABLE `trainer_sessions`
  ADD CONSTRAINT `trainer_sessions_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `weight_progress`
--
ALTER TABLE `weight_progress`
  ADD CONSTRAINT `weight_progress_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `workout_plans`
--
ALTER TABLE `workout_plans`
  ADD CONSTRAINT `workout_plans_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
