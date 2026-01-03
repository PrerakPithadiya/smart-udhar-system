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
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `payment_date` date NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_mode` enum('cash','bank_transfer','upi','cheque','other') DEFAULT 'cash',
  `reference_no` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `customer_name` varchar(100) DEFAULT NULL,
  `remaining_amount` decimal(10,2) DEFAULT 0.00,
  `allocated_amount` decimal(10,2) DEFAULT 0.00,
  `is_allocated` tinyint(1) DEFAULT 0,
  `allocation_date` date DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `customer_id`, `payment_date`, `amount`, `payment_mode`, `reference_no`, `notes`, `created_at`, `customer_name`, `remaining_amount`, `allocated_amount`, `is_allocated`, `allocation_date`, `updated_at`) VALUES
(8, 46, '2025-12-26', 1000.00, 'cash', 'CASH-001', 'Partial payment for bill 21', '2025-12-25 23:45:18', NULL, 0.00, 0.00, 1, NULL, '2025-12-25 23:45:18'),
(9, 47, '2025-12-26', 2597.00, 'upi', 'TXN9928374', 'Full payment for bill 22', '2025-12-25 23:45:18', NULL, 0.00, 0.00, 1, NULL, '2025-12-25 23:45:18'),
(10, 48, '2025-12-26', 500.00, 'cash', NULL, 'Small advance', '2025-12-25 23:45:18', NULL, 0.00, 0.00, 1, NULL, '2025-12-25 23:45:18'),
(11, 49, '2025-12-26', 3392.00, 'upi', 'TXN1122334', 'Settled bill 24', '2025-12-25 23:45:18', NULL, 0.00, 0.00, 1, NULL, '2025-12-25 23:45:18'),
(12, 50, '2025-12-26', 1500.00, '', 'REF-KR-99', 'Partial payment', '2025-12-25 23:45:18', NULL, 0.00, 0.00, 1, NULL, '2025-12-25 23:45:18'),
(13, 51, '2025-12-26', 1749.00, 'cash', 'CASH-002', 'Full settlement', '2025-12-25 23:45:18', NULL, 0.00, 0.00, 1, NULL, '2025-12-25 23:45:18'),
(14, 52, '2025-12-26', 1000.00, 'upi', 'TXN5566778', 'Partial payment', '2025-12-25 23:45:18', NULL, 0.00, 0.00, 1, NULL, '2025-12-25 23:45:18'),
(15, 53, '2025-12-26', 3816.00, '', 'REF-ZA-102', 'Full payment', '2025-12-25 23:45:18', NULL, 0.00, 0.00, 1, NULL, '2025-12-25 23:45:18'),
(16, 54, '2025-12-26', 800.00, 'cash', NULL, 'Partial payment', '2025-12-25 23:45:18', NULL, 0.00, 0.00, 1, NULL, '2025-12-25 23:45:18'),
(17, 55, '2025-12-26', 2438.00, 'upi', 'TXN8877665', 'Cleared bill 30', '2025-12-25 23:45:18', NULL, 0.00, 0.00, 1, NULL, '2025-12-25 23:45:18'),
(18, 46, '2025-12-26', 1000.00, 'cash', 'CASH-001', 'Partial payment', '2025-12-25 23:48:55', NULL, 0.00, 0.00, 1, NULL, '2025-12-25 23:48:55'),
(19, 47, '2025-12-26', 2597.00, 'upi', 'UPI-001', 'Full payment', '2025-12-25 23:48:55', NULL, 0.00, 0.00, 1, NULL, '2025-12-25 23:48:55'),
(20, 48, '2025-12-26', 500.00, 'cash', 'CASH-002', 'Partial payment', '2025-12-25 23:48:55', NULL, 0.00, 0.00, 1, NULL, '2025-12-25 23:48:55'),
(21, 49, '2025-12-26', 3392.00, 'upi', 'UPI-002', 'Full payment', '2025-12-25 23:48:55', NULL, 0.00, 0.00, 1, NULL, '2025-12-25 23:48:55'),
(22, 50, '2025-12-26', 1500.00, '', 'BANK-001', 'Partial payment', '2025-12-25 23:48:55', NULL, 0.00, 0.00, 1, NULL, '2025-12-25 23:48:55'),
(23, 51, '2025-12-26', 1749.00, 'cash', 'CASH-003', 'Full payment', '2025-12-25 23:48:55', NULL, 0.00, 0.00, 1, NULL, '2025-12-25 23:48:55'),
(24, 52, '2025-12-26', 1000.00, 'upi', 'UPI-003', 'Partial payment', '2025-12-25 23:48:55', NULL, 0.00, 0.00, 1, NULL, '2025-12-25 23:48:55'),
(25, 53, '2025-12-26', 3816.00, '', 'BANK-002', 'Full payment', '2025-12-25 23:48:55', NULL, 0.00, 0.00, 1, NULL, '2025-12-25 23:48:55'),
(26, 54, '2025-12-26', 800.00, 'cash', 'CASH-004', 'Partial payment', '2025-12-25 23:48:55', NULL, 0.00, 0.00, 1, NULL, '2025-12-25 23:48:55'),
(27, 55, '2025-12-26', 2438.00, 'upi', 'UPI-004', 'Full payment', '2025-12-25 23:48:55', NULL, 0.00, 0.00, 1, NULL, '2025-12-25 23:48:55');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_payment_customer` (`customer_id`),
  ADD KEY `idx_payments_customer_date` (`customer_id`,`payment_date`),
  ADD KEY `idx_payments_allocated` (`is_allocated`),
  ADD KEY `idx_payments_payment_date` (`payment_date`),
  ADD KEY `idx_payments_payment_mode` (`payment_mode`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
