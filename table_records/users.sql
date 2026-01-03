-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3307
-- Generation Time: Jan 03, 2026 at 04:55 PM
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
-- Database: `smart_udhar_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `shop_name` varchar(150) DEFAULT NULL,
  `mobile` varchar(15) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `role` enum('admin','staff') DEFAULT 'admin',
  `status` enum('active','inactive') DEFAULT 'active',
  `last_login_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `logout_at` datetime DEFAULT NULL,
  `last_login` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `full_name`, `email`, `shop_name`, `mobile`, `address`, `role`, `status`, `last_login_at`, `created_at`, `logout_at`, `last_login`) VALUES
(4, 'prerak', '$2y$10$AQWFgxqtE866O4cXpur/iuqlYYtVzjeTkrkUJJ6c7scUmuT6JQ/D2', 'Prerak Pithadiya', 'prerakpithadiya@gmail.com', '', '09106180772', '101 Shyamal Apartment, Near Alpha International School, Madhavnagar, Madhuram, Timbavadi, Junagadh', 'admin', 'active', '2025-12-24 20:40:01', '2025-12-24 15:09:31', '2025-12-24 20:40:15', NULL),
(5, 'admin', '$2y$10$0racukO87yXPCJoBL9NzaOPKu17wE4NAmQt/nGn7fWYYk1rcmJso6', 'Admin User', 'admin@gmail.com', '', '', '', 'admin', 'active', '2025-12-25 01:48:08', '2025-12-24 20:18:01', '2025-12-26 05:42:42', '2026-01-03 20:34:55'),
(6, 'testuser', '$2y$10$XTCTS6OHMIt7nmOkSX2Q1eRpF7RIqPCHD8FVTvGixK6B8mk0tpfFe', 'Test User', 'test@example.com', '', '', '', 'admin', 'active', NULL, '2025-12-25 19:04:42', NULL, '2025-12-26 00:35:39'),
(7, 'tester', '$2y$10$4BqcKWPPPow6wsmXZLW7t.b1NZMfW.Ow1WRVF7HejAJSssYhVo8ga', 'Tester User', 'tester@example.com', 'Test Shop', '1234567890', 'Test Address', 'admin', 'active', NULL, '2025-12-27 05:02:11', NULL, '2025-12-27 10:32:39');

--
-- Indexes for dumped tables
--

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
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
