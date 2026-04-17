-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 26, 2026 at 03:13 PM
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
-- Database: `editing`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `service` varchar(50) NOT NULL,
  `message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `booking_date` date NOT NULL,
  `booking_time` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `full_name`, `email`, `phone`, `service`, `message`, `created_at`, `booking_date`, `booking_time`) VALUES
(5, 'JAIMIN HARESHBHAI PARMAR', 'jaiminparmar3110@gmail.com', '08401613110', 'video', 'Nothing', '2025-08-21 14:17:43', '2025-08-22', '21:47:00'),
(6, 'JAIMIN HARESHBHAI PARMAR', 'jaiminparmar3110@gmail.com', '08401613110', 'photo - color correction', '', '2025-11-09 06:34:31', '2025-08-22', '14:06:00'),
(7, 'JAIMIN HARESHBHAI PARMAR', 'jaiminparmar3110@gmail.com', '08401613110', 'photo - background_change', '', '2025-11-09 06:43:57', '2025-08-22', '12:13:00'),
(8, 'JAIMIN HARESHBHAI PARMAR', 'jaiminparmar3110@gmail.com', '08401613110', 'photo - composite', '', '2025-11-09 06:48:39', '2025-08-22', '12:20:00'),
(9, 'JAIMIN HARESHBHAI PARMAR', 'jaiminparmar3110@gmail.com', '08401613110', 'video - color_grading, editing, effects', '', '2025-11-09 06:58:00', '2025-11-10', '13:27:00'),
(10, 'JAIMIN HARESHBHAI PARMAR', 'jaiminparmar3110@gmail.com', '08401613110', 'photo - retouch, color_correction, background_chan', '', '2025-11-09 07:08:06', '2025-11-10', '12:40:00');

-- --------------------------------------------------------

--
-- Table structure for table `chat_messages`
--

CREATE TABLE `chat_messages` (
  `id` int(11) NOT NULL,
  `sender` varchar(50) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `chat_messages`
--

INSERT INTO `chat_messages` (`id`, `sender`, `message`, `created_at`) VALUES
(1, 'user', 'hii', '2025-08-31 06:16:23'),
(2, 'bot', 'Hello! How can I assist you today?', '2025-08-31 06:16:23');

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contact_messages`
--

INSERT INTO `contact_messages` (`id`, `name`, `email`, `message`, `created_at`) VALUES
(1, 'Aarti Rajput', 'Aartirajpur1810@gmail.com', 'contect me arggent...', '2025-08-11 03:52:03'),
(2, 'JAIMIN HARESHBHAI PARMAR', 'jaiminparmar3110@gmail.com', 'nothing', '2025-08-11 05:45:58'),
(3, 'Vishva Sarvaiya', 'Vishva@gmail.com', 'Nothing', '2025-08-11 05:46:13'),
(4, 'JAIMIN HARESHBHAI PARMAR', 'jaiminparmar3110@gmail.com', 'none', '2025-08-19 14:08:58');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `chat_messages`
--
ALTER TABLE `chat_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
