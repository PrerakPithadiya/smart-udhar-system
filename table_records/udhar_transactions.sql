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
-- Table structure for table `udhar_transactions`
--

CREATE TABLE `udhar_transactions` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `transaction_date` date NOT NULL,
  `description` text DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `due_date` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('pending','partially_paid','paid') DEFAULT 'pending',
  `remaining_amount` decimal(10,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `bill_no` int(11) DEFAULT NULL,
  `total_amount` decimal(10,2) DEFAULT 0.00,
  `cgst_amount` decimal(10,2) DEFAULT 0.00,
  `sgst_amount` decimal(10,2) DEFAULT 0.00,
  `igst_amount` decimal(10,2) DEFAULT 0.00,
  `grand_total` decimal(10,2) DEFAULT 0.00,
  `discount` decimal(10,2) DEFAULT 0.00,
  `discount_type` enum('percentage','fixed') DEFAULT 'fixed',
  `round_off` decimal(10,2) DEFAULT 0.00,
  `transportation_charge` decimal(10,2) DEFAULT 0.00,
  `bill_notes` text DEFAULT NULL,
  `print_count` int(11) DEFAULT 0,
  `revision_number` int(11) DEFAULT 1,
  `last_edited_by` int(11) DEFAULT NULL,
  `last_edited_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `udhar_transactions`
--

INSERT INTO `udhar_transactions` (`id`, `customer_id`, `transaction_date`, `description`, `amount`, `due_date`, `notes`, `status`, `remaining_amount`, `created_at`, `bill_no`, `total_amount`, `cgst_amount`, `sgst_amount`, `igst_amount`, `grand_total`, `discount`, `discount_type`, `round_off`, `transportation_charge`, `bill_notes`, `print_count`, `revision_number`, `last_edited_by`, `last_edited_at`) VALUES
(1, 37, '2025-12-26', NULL, 2650.00, NULL, NULL, 'pending', 0.00, '2025-12-25 21:26:20', 1, 2650.00, 0.00, 0.00, 0.00, 2809.00, 0.00, 'fixed', 0.00, 0.00, NULL, 8, 1, NULL, NULL),
(2, 38, '2025-12-26', NULL, 3120.00, NULL, NULL, 'pending', 0.00, '2025-12-25 21:26:20', 2, 3120.00, 0.00, 0.00, 0.00, 3315.60, 0.00, 'fixed', 0.00, 0.00, NULL, 1, 1, NULL, NULL),
(3, 39, '2025-12-26', NULL, 1980.00, NULL, NULL, 'pending', 0.00, '2025-12-25 21:26:20', 3, 1980.00, 0.00, 0.00, 0.00, 2125.20, 0.00, 'fixed', 0.00, 0.00, NULL, 0, 1, NULL, NULL),
(4, 40, '2025-12-26', NULL, 4450.00, NULL, NULL, 'pending', 0.00, '2025-12-25 21:26:20', 4, 4450.00, 0.00, 0.00, 0.00, 4820.00, 0.00, 'fixed', 0.00, 0.00, NULL, 0, 1, NULL, NULL),
(5, 41, '2025-12-26', NULL, 2200.00, NULL, NULL, 'pending', 0.00, '2025-12-25 21:26:20', 5, 2200.00, 0.00, 0.00, 0.00, 2354.00, 0.00, 'fixed', 0.00, 0.00, NULL, 0, 1, NULL, NULL),
(6, 42, '2025-12-26', NULL, 1580.00, NULL, NULL, 'pending', 0.00, '2025-12-25 21:26:20', 6, 1580.00, 0.00, 0.00, 0.00, 1659.00, 0.00, 'fixed', 0.00, 0.00, NULL, 0, 1, NULL, NULL),
(7, 43, '2025-12-26', NULL, 2890.00, NULL, NULL, 'pending', 0.00, '2025-12-25 21:26:20', 7, 2890.00, 0.00, 0.00, 0.00, 3105.00, 0.00, 'fixed', 0.00, 0.00, NULL, 0, 1, NULL, NULL),
(8, 44, '2025-12-26', NULL, 3400.00, NULL, NULL, 'pending', 0.00, '2025-12-25 21:26:20', 8, 3400.00, 0.00, 0.00, 0.00, 3700.00, 0.00, 'fixed', 0.00, 0.00, NULL, 0, 1, NULL, NULL),
(9, 45, '2025-12-26', NULL, 1250.00, NULL, NULL, 'pending', 0.00, '2025-12-25 21:26:20', 9, 1250.00, 0.00, 0.00, 0.00, 1312.50, 0.00, 'fixed', 0.00, 0.00, NULL, 0, 1, NULL, NULL),
(10, 46, '2025-12-26', NULL, 2100.00, NULL, NULL, 'pending', 0.00, '2025-12-25 21:26:20', 10, 2100.00, 0.00, 0.00, 0.00, 2268.00, 0.00, 'fixed', 0.00, 0.00, NULL, 0, 1, NULL, NULL),
(21, 46, '2025-12-26', NULL, 1800.00, NULL, NULL, 'partially_paid', 908.00, '2025-12-25 23:51:42', 11, 1800.00, 0.00, 0.00, 0.00, 1908.00, 0.00, 'fixed', 0.00, 0.00, NULL, 0, 1, NULL, NULL),
(22, 47, '2025-12-26', NULL, 2450.00, NULL, NULL, 'paid', 0.00, '2025-12-25 23:51:42', 12, 2450.00, 0.00, 0.00, 0.00, 2597.00, 0.00, 'fixed', 0.00, 0.00, NULL, 0, 1, NULL, NULL),
(23, 48, '2025-12-26', NULL, 1350.00, NULL, NULL, 'partially_paid', 931.00, '2025-12-25 23:51:42', 13, 1350.00, 0.00, 0.00, 0.00, 1431.00, 0.00, 'fixed', 0.00, 0.00, NULL, 0, 1, NULL, NULL),
(24, 49, '2025-12-26', NULL, 3200.00, NULL, NULL, 'paid', 0.00, '2025-12-25 23:51:42', 14, 3200.00, 0.00, 0.00, 0.00, 3392.00, 0.00, 'fixed', 0.00, 0.00, NULL, 0, 1, NULL, NULL),
(25, 50, '2025-12-26', NULL, 2900.00, NULL, NULL, 'partially_paid', 1574.00, '2025-12-25 23:51:42', 15, 2900.00, 0.00, 0.00, 0.00, 3074.00, 0.00, 'fixed', 0.00, 0.00, NULL, 0, 1, NULL, NULL),
(26, 51, '2025-12-26', NULL, 1650.00, NULL, NULL, 'paid', 0.00, '2025-12-25 23:51:42', 16, 1650.00, 0.00, 0.00, 0.00, 1749.00, 0.00, 'fixed', 0.00, 0.00, NULL, 0, 1, NULL, NULL),
(27, 52, '2025-12-26', NULL, 2150.00, NULL, NULL, 'partially_paid', 1279.00, '2025-12-25 23:51:42', 17, 2150.00, 0.00, 0.00, 0.00, 2279.00, 0.00, 'fixed', 0.00, 0.00, NULL, 0, 1, NULL, NULL),
(28, 53, '2025-12-26', NULL, 3600.00, NULL, NULL, 'paid', 0.00, '2025-12-25 23:51:42', 18, 3600.00, 0.00, 0.00, 0.00, 3816.00, 0.00, 'fixed', 0.00, 0.00, NULL, 0, 1, NULL, NULL),
(29, 54, '2025-12-26', NULL, 1750.00, NULL, NULL, 'partially_paid', 1055.00, '2025-12-25 23:51:42', 19, 1750.00, 0.00, 0.00, 0.00, 1855.00, 0.00, 'fixed', 0.00, 0.00, NULL, 0, 1, NULL, NULL),
(30, 55, '2025-12-26', NULL, 2300.00, NULL, NULL, 'paid', 0.00, '2025-12-25 23:51:42', 20, 2300.00, 0.00, 0.00, 0.00, 2438.00, 0.00, 'fixed', 0.00, 0.00, NULL, 3, 1, NULL, NULL);

--
-- Triggers `udhar_transactions`
--
DELIMITER $$
CREATE TRIGGER `trg_auto_bill_no` BEFORE INSERT ON `udhar_transactions` FOR EACH ROW BEGIN
    IF NEW.bill_no IS NULL OR NEW.bill_no = 0 THEN
        SET NEW.bill_no = (SELECT IFNULL(MAX(bill_no), 0) + 1 FROM udhar_transactions);
    END IF;
END
$$
DELIMITER ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `udhar_transactions`
--
ALTER TABLE `udhar_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_udhar_customer` (`customer_id`),
  ADD KEY `idx_udhar_transaction_date` (`transaction_date`),
  ADD KEY `idx_udhar_status` (`status`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `udhar_transactions`
--
ALTER TABLE `udhar_transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `udhar_transactions`
--
ALTER TABLE `udhar_transactions`
  ADD CONSTRAINT `udhar_transactions_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
