-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 18, 2026 at 08:23 PM
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
(4, 4, 'Strength & Toning', 'Women\'s Strength', 30, 'Pending', '2026-01-18 18:19:34', '2026-01-18 18:19:34'),
(5, 5, 'Weight Loss', 'Strength Training Pro', 50, 'Inactive', '2026-01-18 18:19:34', '2026-01-18 18:22:15');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `members`
--
ALTER TABLE `members`
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
-- AUTO_INCREMENT for table `members`
--
ALTER TABLE `members`
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
-- Constraints for table `workout_plans`
--
ALTER TABLE `workout_plans`
  ADD CONSTRAINT `workout_plans_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
