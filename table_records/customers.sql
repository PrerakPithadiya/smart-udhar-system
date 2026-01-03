-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3307
-- Generation Time: Jan 03, 2026 at 04:54 PM
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
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `mobile` varchar(15) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `total_udhar` decimal(10,2) DEFAULT 0.00,
  `total_paid` decimal(10,2) DEFAULT 0.00,
  `balance` decimal(10,2) DEFAULT 0.00,
  `last_transaction_date` date DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`id`, `user_id`, `name`, `mobile`, `email`, `address`, `total_udhar`, `total_paid`, `balance`, `last_transaction_date`, `status`, `created_at`, `updated_at`) VALUES
(26, 5, 'Rohan Mehta', '9820012345', 'rohan.mehta@example.com', 'Flat 202, Gokul Dham, Malad East, Mumbai, MH', 0.00, 0.00, 0.00, NULL, 'active', '2025-12-25 03:31:52', '2025-12-25 03:31:52'),
(27, 5, 'Aditi Rao', '9900054321', 'aditi.rao@webmail.in', 'No. 12, 4th Cross, Indiranagar, Bangalore, KA', 0.00, 0.00, 0.00, NULL, 'active', '2025-12-25 03:31:52', '2025-12-25 03:31:52'),
(28, 5, 'Sandeep Pillai', '9444098765', 's.pillai@outlook.com', 'Apt 5A, Lotus Greens, Anna Nagar, Chennai, TN', 0.00, 0.00, 0.00, NULL, 'active', '2025-12-25 03:31:52', '2025-12-25 03:31:52'),
(29, 5, 'Ishita Das', '9830067890', 'ishita.das@provider.com', '32/B, Lake View Road, Salt Lake, Kolkata, WB', 0.00, 0.00, 0.00, NULL, 'active', '2025-12-25 03:31:52', '2025-12-25 03:31:52'),
(30, 5, 'Amit Sharma', '9123456789', 'sharma.amit@gmail.com', 'H-45, Sector 15, Rohini, New Delhi, DL', 0.00, 0.00, 0.00, NULL, 'active', '2025-12-25 03:31:52', '2025-12-25 03:31:52'),
(31, 5, 'Sneha Kulkarni', '9892011223', 'sneha.k@icloud.com', 'Plot 88, Sahakar Nagar, Pune, MH', 0.00, 0.00, 0.00, NULL, 'active', '2025-12-25 03:31:52', '2025-12-25 03:31:52'),
(32, 5, 'Harpreet Singh', '9814055443', 'h.singh@live.com', 'House 212, Sector 35-C, Chandigarh, CH', 0.00, 0.00, 0.00, NULL, 'active', '2025-12-25 03:31:52', '2025-12-25 03:31:52'),
(33, 5, 'Kavita Reddy', '6300011998', 'kavita.r@tata.com', 'Villa 7, Jubilee Hills, Hyderabad, TS', 0.00, 0.00, 0.00, NULL, 'active', '2025-12-25 03:31:52', '2025-12-25 03:31:52'),
(34, 5, 'Deepak Verma', '7000122334', 'verma.deepak@rediff.com', 'Shanti Kunj, Arera Colony, Bhopal, MP', 0.00, 0.00, 0.00, NULL, 'active', '2025-12-25 03:31:52', '2025-12-25 03:31:52'),
(35, 5, 'Meera Nair', '9447088776', 'meera.nair@work.in', 'Sree Nilayam, MG Road, Kochi, KL', 0.00, 0.00, 0.00, NULL, 'active', '2025-12-25 03:31:52', '2025-12-25 03:31:52'),
(36, 5, 'Vikram Singh Negi', '9816012344', 'vikram.negi@example.com', 'Village P.O. Kotdwar, Pauri Garhwal, UK', 0.00, 0.00, 0.00, NULL, 'active', '2025-12-25 03:34:26', '2025-12-25 03:34:26'),
(37, 5, 'Ananya Deshpande', '9766055441', 'ananya.d@gmail.com', 'Flat 402, Shivneri Appts, Kothrud, Pune, MH', 0.00, 0.00, 0.00, NULL, 'active', '2025-12-25 03:34:26', '2025-12-25 03:34:26'),
(38, 5, 'Suresh Goud', '8008011223', 'suresh.goud@outlook.in', 'H.No 4-12, Kukatpally Housing Board, Hyderabad, TS', 0.00, 0.00, 0.00, NULL, 'active', '2025-12-25 03:34:26', '2025-12-25 03:34:26'),
(39, 5, 'Pallavi Majumdar', '9831044556', 'p.majumdar@webmail.com', '12/1, Ballygunge Circular Road, Kolkata, WB', 0.00, 0.00, 0.00, NULL, 'active', '2025-12-25 03:34:26', '2025-12-25 03:34:26'),
(40, 5, 'Nitin Gadkari', '9422188990', 'nitin.g@provider.com', 'Civil Lines, Near High Court, Nagpur, MH', 0.00, 0.00, 0.00, NULL, 'active', '2025-12-25 03:34:26', '2025-12-25 03:34:26'),
(41, 5, 'Jasminder Gill', '9872033445', 'j.gill@live.com', 'Patti Road, Barnala, Sangrur, PB', 0.00, 0.00, 0.00, NULL, 'active', '2025-12-25 03:34:26', '2025-12-25 03:34:26'),
(42, 5, 'Ramyakrishnan S.', '9444511229', 'ramyak.s@tata.com', '22, Thiru-vi-ka Nagar, Perambur, Chennai, TN', 0.00, 0.00, 0.00, NULL, 'active', '2025-12-25 03:34:26', '2025-12-25 03:34:26'),
(43, 5, 'Manish Tiwari', '7007055667', 'tiwari.manish@rediff.com', '32, Gomti Nagar Extension, Lucknow, UP', 0.00, 0.00, 0.00, NULL, 'active', '2025-12-25 03:34:26', '2025-12-25 03:34:26'),
(44, 5, 'Bhavna Patel', '9924011220', 'bhavna.p@icloud.com', 'B-101, Satyam Villa, Satellite Area, Ahmedabad, GJ', 0.00, 0.00, 0.00, NULL, 'active', '2025-12-25 03:34:26', '2025-12-25 03:34:26'),
(45, 5, 'Abhijit Barua', '9864011882', 'a.barua@assamservices.in', 'Zoo Road, Tiniali, Guwahati, AS', 0.00, 0.00, 0.00, NULL, 'active', '2025-12-25 03:34:26', '2025-12-25 03:34:26'),
(46, 5, 'Sanjay Rathore', '9829011223', 'sanjay.r@example.com', 'Plot 42, Vaishali Nagar, Jaipur, RJ', 0.00, 1000.00, -1000.00, NULL, 'active', '2025-12-25 18:53:44', '2025-12-25 23:51:53'),
(47, 5, 'Laxmi Narayan', '9440155667', 'laxmi.n@webmail.in', 'D.No 12-5, Main Bazar, Vijayawada, AP', 0.00, 2597.00, -2597.00, NULL, 'active', '2025-12-25 18:53:44', '2025-12-25 23:51:53'),
(48, 5, 'Arindam Das', '9836077889', 'arindam.das@outlook.com', 'Flat 2C, Greenfield Heights, New Town, Kolkata, WB', 0.00, 500.00, -500.00, NULL, 'active', '2025-12-25 18:53:44', '2025-12-25 23:51:53'),
(49, 5, 'Sunita Williams', '9123443210', 'sunita.w@gmail.com', 'H-102, Shanti Kunj, Arera Colony, Bhopal, MP', 0.00, 3392.00, -3392.00, NULL, 'active', '2025-12-25 18:53:44', '2025-12-25 23:51:53'),
(50, 5, 'Karthik Raja', '9840011224', 'karthik.raja@live.com', 'No. 5, 2nd Street, T.Nagar, Chennai, TN', 0.00, 1500.00, -1500.00, NULL, 'active', '2025-12-25 18:53:44', '2025-12-25 23:51:53'),
(51, 5, 'Priyanka Maurya', '7007011992', 'p.maurya@rediff.com', 'Sector 4, Vikas Nagar, Lucknow, UP', 0.00, 1749.00, -1749.00, NULL, 'active', '2025-12-25 18:53:44', '2025-12-25 23:51:53'),
(52, 5, 'Rajinder Kohli', '9815044332', 'r.kohli@provider.com', '241-B, Model Town, Jalandhar, PB', 0.00, 1000.00, -1000.00, NULL, 'active', '2025-12-25 18:53:44', '2025-12-25 23:51:53'),
(53, 5, 'Zoya Ahmed', '9163000551', 'zoya.ahmed@icloud.com', 'Lane 4, Abids Road, Hyderabad, TS', 0.00, 3816.00, -3816.00, NULL, 'active', '2025-12-25 18:53:44', '2025-12-25 23:51:53'),
(54, 5, 'Madhavan Nair', '9447011228', 'm.nair@work.in', 'House No. 8, Kowdiar, Thiruvananthapuram, KL', 0.00, 800.00, -800.00, NULL, 'active', '2025-12-25 18:53:44', '2025-12-25 23:51:53'),
(55, 5, 'Binod Kumar', '7004011225', 'binod.k@tata.com', 'Qtr No 45, Sector 2, Bokaro Steel City, JH', 0.00, 2438.00, -2438.00, NULL, 'active', '2025-12-25 18:53:44', '2025-12-25 23:51:53'),
(56, 7, 'Alice', '1234567890', 'alice@example.com', 'Wonderland', 0.00, 0.00, 0.00, NULL, 'active', '2025-12-27 05:03:09', '2025-12-27 05:03:09'),
(61, 5, 'Prerak Pithadiya', '9106180772', 'prerakpithadiya@gmail.com', '101 Shyamal Apartment, Near Alpha International School, Madhavnagar, Madhuram, Timbavadi, Junagadh', 0.00, 0.00, 0.00, NULL, 'active', '2026-01-03 15:26:35', '2026-01-03 15:26:35');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_customer_user` (`user_id`),
  ADD KEY `idx_customer_mobile` (`mobile`),
  ADD KEY `idx_customers_balance` (`balance`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `customers`
--
ALTER TABLE `customers`
  ADD CONSTRAINT `customers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
