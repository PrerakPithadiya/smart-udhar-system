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
-- Table structure for table `udhar_items`
--

CREATE TABLE `udhar_items` (
  `id` int(11) NOT NULL,
  `udhar_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `item_name` varchar(200) NOT NULL,
  `hsn_code` varchar(50) DEFAULT NULL,
  `unit` varchar(20) DEFAULT 'PCS',
  `quantity` decimal(10,2) NOT NULL DEFAULT 1.00,
  `unit_price` decimal(10,2) NOT NULL,
  `cgst_rate` decimal(5,2) DEFAULT 0.00,
  `sgst_rate` decimal(5,2) DEFAULT 0.00,
  `igst_rate` decimal(5,2) DEFAULT 0.00,
  `cgst_amount` decimal(10,2) DEFAULT 0.00,
  `sgst_amount` decimal(10,2) DEFAULT 0.00,
  `igst_amount` decimal(10,2) DEFAULT 0.00,
  `total_amount` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `udhar_items`
--

INSERT INTO `udhar_items` (`id`, `udhar_id`, `item_id`, `item_name`, `hsn_code`, `unit`, `quantity`, `unit_price`, `cgst_rate`, `sgst_rate`, `igst_rate`, `cgst_amount`, `sgst_amount`, `igst_amount`, `total_amount`, `created_at`) VALUES
(201, 1, 278, 'DAP Fertilizer', '3105', 'BAG', 1.00, 1350.00, 2.50, 2.50, 0.00, 33.75, 33.75, 0.00, 1417.50, '2025-12-25 21:27:25'),
(202, 1, 276, 'Sunflower Oil (5L)', '1512', 'LTR', 1.00, 850.00, 2.50, 2.50, 0.00, 21.25, 21.25, 0.00, 892.50, '2025-12-25 21:27:25'),
(203, 1, 275, 'Green Moong Dal', '0713', 'KG', 1.00, 120.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 120.00, '2025-12-25 21:27:25'),
(204, 1, 302, 'Okra Seeds (Bhendi)', '1209', 'PKT', 1.00, 120.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 120.00, '2025-12-25 21:27:25'),
(205, 1, 277, 'Bathing Scrubber', '9603', 'PCS', 2.00, 45.00, 6.00, 6.00, 0.00, 5.40, 5.40, 0.00, 100.00, '2025-12-25 21:27:25'),
(206, 2, 278, 'DAP Fertilizer', '3105', 'BAG', 2.00, 1350.00, 2.50, 2.50, 0.00, 67.50, 67.50, 0.00, 2835.00, '2025-12-25 21:27:25'),
(207, 2, 275, 'Green Moong Dal', '0713', 'KG', 1.00, 120.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 120.00, '2025-12-25 21:27:25'),
(208, 2, 302, 'Okra Seeds (Bhendi)', '1209', 'PKT', 1.00, 120.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 120.00, '2025-12-25 21:27:25'),
(209, 2, 277, 'Bathing Scrubber', '9603', 'PCS', 0.89, 45.00, 6.00, 6.00, 0.00, 2.40, 2.40, 0.00, 45.00, '2025-12-25 21:27:25'),
(210, 2, 305, 'Pruning Saw', '8202', 'PCS', 0.00, 310.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, '2025-12-25 21:27:25'),
(211, 3, 276, 'Sunflower Oil (5L)', '1512', 'LTR', 2.00, 850.00, 2.50, 2.50, 0.00, 42.50, 42.50, 0.00, 1785.00, '2025-12-25 21:27:25'),
(212, 3, 275, 'Green Moong Dal', '0713', 'KG', 1.00, 120.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 120.00, '2025-12-25 21:27:25'),
(213, 3, 302, 'Okra Seeds (Bhendi)', '1209', 'PKT', 0.42, 120.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 50.00, '2025-12-25 21:27:25'),
(214, 3, 277, 'Bathing Scrubber', '9603', 'PCS', 0.44, 45.00, 6.00, 6.00, 0.00, 1.20, 1.20, 0.00, 25.00, '2025-12-25 21:27:25'),
(215, 3, 300, 'Cauliflower Seeds', '1209', 'PKT', 0.00, 350.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, '2025-12-25 21:27:25'),
(216, 4, 278, 'DAP Fertilizer', '3105', 'BAG', 3.00, 1350.00, 2.50, 2.50, 0.00, 101.25, 101.25, 0.00, 4252.50, '2025-12-25 21:27:25'),
(217, 4, 275, 'Green Moong Dal', '0713', 'KG', 1.00, 120.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 120.00, '2025-12-25 21:27:25'),
(218, 4, 302, 'Okra Seeds (Bhendi)', '1209', 'PKT', 0.50, 120.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 60.00, '2025-12-25 21:27:25'),
(219, 4, 277, 'Bathing Scrubber', '9603', 'PCS', 0.35, 45.00, 6.00, 6.00, 0.00, 0.95, 0.95, 0.00, 17.50, '2025-12-25 21:27:25'),
(220, 4, 305, 'Pruning Saw', '8202', 'PCS', 0.00, 310.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, '2025-12-25 21:27:25'),
(221, 5, 276, 'Sunflower Oil (5L)', '1512', 'LTR', 2.00, 850.00, 2.50, 2.50, 0.00, 42.50, 42.50, 0.00, 1785.00, '2025-12-25 21:27:25'),
(222, 5, 275, 'Green Moong Dal', '0713', 'KG', 2.00, 120.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 240.00, '2025-12-25 21:27:25'),
(223, 5, 302, 'Okra Seeds (Bhendi)', '1209', 'PKT', 1.00, 120.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 120.00, '2025-12-25 21:27:25'),
(224, 5, 277, 'Bathing Scrubber', '9603', 'PCS', 1.00, 45.00, 6.00, 6.00, 0.00, 2.70, 2.70, 0.00, 50.40, '2025-12-25 21:27:25'),
(225, 5, 305, 'Pruning Saw', '8202', 'PCS', 0.02, 310.00, 6.00, 6.00, 0.00, 0.28, 0.28, 0.00, 4.60, '2025-12-25 21:27:25'),
(226, 6, 276, 'Sunflower Oil (5L)', '1512', 'LTR', 1.00, 850.00, 2.50, 2.50, 0.00, 21.25, 21.25, 0.00, 892.50, '2025-12-25 21:27:25'),
(227, 6, 275, 'Green Moong Dal', '0713', 'KG', 3.00, 120.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 360.00, '2025-12-25 21:27:25'),
(228, 6, 300, 'Cauliflower Seeds', '1209', 'PKT', 0.50, 350.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 175.00, '2025-12-25 21:27:25'),
(229, 6, 302, 'Okra Seeds (Bhendi)', '1209', 'PKT', 1.00, 120.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 120.00, '2025-12-25 21:27:25'),
(230, 6, 277, 'Bathing Scrubber', '9603', 'PCS', 0.58, 45.00, 6.00, 6.00, 0.00, 1.57, 1.57, 0.00, 32.50, '2025-12-25 21:27:25'),
(231, 7, 278, 'DAP Fertilizer', '3105', 'BAG', 2.00, 1350.00, 2.50, 2.50, 0.00, 67.50, 67.50, 0.00, 2835.00, '2025-12-25 21:27:25'),
(232, 7, 275, 'Green Moong Dal', '0713', 'KG', 0.30, 120.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 36.00, '2025-12-25 21:27:25'),
(233, 7, 302, 'Okra Seeds (Bhendi)', '1209', 'PKT', 0.10, 120.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 12.00, '2025-12-25 21:27:25'),
(234, 7, 277, 'Bathing Scrubber', '9603', 'PCS', 0.10, 45.00, 6.00, 6.00, 0.00, 0.27, 0.27, 0.00, 5.00, '2025-12-25 21:27:25'),
(235, 7, 305, 'Pruning Saw', '8202', 'PCS', 0.01, 310.00, 6.00, 6.00, 0.00, 0.11, 0.11, 0.00, 2.00, '2025-12-25 21:27:25'),
(236, 8, 278, 'DAP Fertilizer', '3105', 'BAG', 2.00, 1350.00, 2.50, 2.50, 0.00, 67.50, 67.50, 0.00, 2835.00, '2025-12-25 21:27:25'),
(237, 8, 300, 'Cauliflower Seeds', '1209', 'PKT', 1.00, 350.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 350.00, '2025-12-25 21:27:25'),
(238, 8, 275, 'Green Moong Dal', '0713', 'KG', 1.00, 120.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 120.00, '2025-12-25 21:27:25'),
(239, 8, 302, 'Okra Seeds (Bhendi)', '1209', 'PKT', 0.50, 120.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 60.00, '2025-12-25 21:27:25'),
(240, 8, 277, 'Bathing Scrubber', '9603', 'PCS', 0.77, 45.00, 6.00, 6.00, 0.00, 2.08, 2.08, 0.00, 35.00, '2025-12-25 21:27:25'),
(241, 9, 276, 'Sunflower Oil (5L)', '1512', 'LTR', 1.00, 850.00, 2.50, 2.50, 0.00, 21.25, 21.25, 0.00, 892.50, '2025-12-25 21:27:25'),
(242, 9, 275, 'Green Moong Dal', '0713', 'KG', 1.00, 120.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 120.00, '2025-12-25 21:27:25'),
(243, 9, 302, 'Okra Seeds (Bhendi)', '1209', 'PKT', 1.00, 120.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 120.00, '2025-12-25 21:27:25'),
(244, 9, 277, 'Bathing Scrubber', '9603', 'PCS', 2.00, 45.00, 6.00, 6.00, 0.00, 5.40, 5.40, 0.00, 100.80, '2025-12-25 21:27:25'),
(245, 9, 305, 'Pruning Saw', '8202', 'PCS', 0.05, 310.00, 6.00, 6.00, 0.00, 0.93, 0.93, 0.00, 16.70, '2025-12-25 21:27:25'),
(246, 10, 278, 'DAP Fertilizer', '3105', 'BAG', 1.00, 1350.00, 2.50, 2.50, 0.00, 33.75, 33.75, 0.00, 1417.50, '2025-12-25 21:27:25'),
(247, 10, 300, 'Cauliflower Seeds', '1209', 'PKT', 1.00, 350.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 350.00, '2025-12-25 21:27:25'),
(248, 10, 275, 'Green Moong Dal', '0713', 'KG', 1.00, 120.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 120.00, '2025-12-25 21:27:25'),
(249, 10, 302, 'Okra Seeds (Bhendi)', '1209', 'PKT', 1.00, 120.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 120.00, '2025-12-25 21:27:25'),
(250, 10, 277, 'Bathing Scrubber', '9603', 'PCS', 2.00, 45.00, 6.00, 6.00, 0.00, 5.40, 5.40, 0.00, 92.50, '2025-12-25 21:27:25');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `udhar_items`
--
ALTER TABLE `udhar_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_udhar_items_udhar` (`udhar_id`),
  ADD KEY `idx_udhar_items_item` (`item_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `udhar_items`
--
ALTER TABLE `udhar_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=301;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `udhar_items`
--
ALTER TABLE `udhar_items`
  ADD CONSTRAINT `udhar_items_ibfk_1` FOREIGN KEY (`udhar_id`) REFERENCES `udhar_transactions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `udhar_items_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
