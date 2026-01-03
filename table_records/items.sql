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
-- Table structure for table `items`
--

CREATE TABLE `items` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `item_name` varchar(200) NOT NULL,
  `item_code` varchar(50) DEFAULT NULL,
  `hsn_code` varchar(50) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `cgst_rate` decimal(5,2) DEFAULT 0.00,
  `sgst_rate` decimal(5,2) DEFAULT 0.00,
  `igst_rate` decimal(5,2) DEFAULT 0.00,
  `unit` varchar(20) DEFAULT 'PCS',
  `description` text DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `category` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `items`
--

INSERT INTO `items` (`id`, `user_id`, `item_name`, `item_code`, `hsn_code`, `price`, `cgst_rate`, `sgst_rate`, `igst_rate`, `unit`, `description`, `status`, `created_at`, `updated_at`, `category`) VALUES
(275, 5, 'Green Moong Dal', 'DAL001', '0713', 120.00, 0.00, 0.00, 0.00, 'KG', 'Organic moong dal', 'active', '2025-12-25 03:21:27', '2025-12-25 03:21:27', 'Groceries'),
(276, 5, 'Sunflower Oil (5L)', 'OIL002', '1512', 850.00, 2.50, 2.50, 0.00, 'LTR', 'Refined sunflower oil', 'active', '2025-12-25 03:21:27', '2025-12-25 03:21:27', 'Oil'),
(277, 5, 'Bathing Scrubber', 'SCR001', '9603', 45.00, 6.00, 6.00, 0.00, 'PCS', 'Exfoliating body scrubber', 'active', '2025-12-25 03:21:27', '2025-12-25 03:21:27', 'Personal Care'),
(278, 5, 'DAP Fertilizer', 'FER-DAP-01', '3105', 1350.00, 2.50, 2.50, 0.00, 'BAG', 'Di-ammonium Phosphate 50kg', 'active', '2025-12-25 03:21:27', '2025-12-25 03:21:27', 'Fertilizers'),
(279, 5, 'NPK 19:19:19', 'FER-NPK-19', '3105', 180.00, 2.50, 2.50, 5.00, 'KG', 'Water-soluble balanced fertilizer', 'active', '2025-12-25 03:28:30', '2025-12-25 03:28:30', 'Fertilizers'),
(280, 5, 'Hybrid Wheat Seeds', 'SED-WHT-50', '1001', 950.00, 0.00, 0.00, 0.00, 'BAG', 'High-yield Shriram Super seeds', 'active', '2025-12-25 03:28:30', '2025-12-25 03:28:30', 'Seeds'),
(281, 5, 'Chilli Seeds (Teja)', 'SED-CHL-01', '1209', 450.00, 0.00, 0.00, 0.00, 'PKT', 'Dry Red Chilli seeds - 50g', 'active', '2025-12-25 03:28:30', '2025-12-25 03:28:30', 'Seeds'),
(282, 5, 'Neem Oil (1L)', 'INS-NEM-01', '3808', 550.00, 6.00, 6.00, 12.00, 'LTR', 'Cold pressed organic insecticide', 'active', '2025-12-25 03:28:30', '2025-12-25 03:28:30', 'Insecticides'),
(283, 5, 'Monocrotophos', 'INS-MON-36', '3808', 420.00, 9.00, 9.00, 18.00, 'BTL', 'Broad spectrum insecticide for crops', 'active', '2025-12-25 03:28:30', '2025-12-25 03:28:30', 'Insecticides'),
(284, 5, 'Zinc Sulphate', 'FER-ZNC-21', '2833', 380.00, 6.00, 6.00, 12.00, 'PKT', 'Agricultural grade 21% Zinc', 'active', '2025-12-25 03:28:30', '2025-12-25 03:28:30', 'Fertilizers'),
(285, 5, 'Spray Pump (16L)', 'OTH-PMP-01', '8424', 2100.00, 6.00, 6.00, 12.00, 'PCS', 'Manual knapsack sprayer pump', 'active', '2025-12-25 03:28:30', '2025-12-25 03:28:30', 'Others'),
(286, 5, 'Drip Tape Roll', 'OTH-DRP-20', '3917', 3200.00, 6.00, 6.00, 12.00, 'ROL', '16mm drip lateral (400mtr)', 'active', '2025-12-25 03:28:30', '2025-12-25 03:28:30', 'Others'),
(287, 5, 'Mustard Seeds', 'SED-MST-05', '1209', 180.00, 0.00, 0.00, 0.00, 'KG', 'Black mustard seeds for sowing', 'active', '2025-12-25 03:28:30', '2025-12-25 03:28:30', 'Seeds'),
(288, 5, 'Organic Manure', 'FER-ORG-50', '3101', 250.00, 2.50, 2.50, 5.00, 'BAG', 'Decomposed organic soil conditioner', 'active', '2025-12-25 03:28:30', '2025-12-25 03:28:30', 'Fertilizers'),
(289, 5, 'DAP Fertilizer (50kg)', 'FER-DAP-50', '3105', 1350.00, 2.50, 2.50, 5.00, 'BAG', 'Di-Ammonium Phosphate for root development', 'active', '2025-12-25 03:29:14', '2025-12-25 03:29:14', 'Fertilizers'),
(291, 5, 'F1 Hybrid Watermelon', 'SED-WML-10', '1209', 650.00, 0.00, 0.00, 0.00, 'PKT', 'Sugar Queen variety - 50g pack', 'active', '2025-12-25 03:29:14', '2025-12-25 03:29:14', 'Seeds'),
(292, 5, 'Cabbage Seeds', 'SED-CAB-05', '1209', 280.00, 0.00, 0.00, 0.00, 'PKT', 'High-quality winter cabbage seeds', 'active', '2025-12-25 03:29:14', '2025-12-25 03:29:14', 'Seeds'),
(293, 5, 'Chlorpyrifos 20% EC', 'INS-CHL-20', '3808', 390.00, 9.00, 9.00, 18.00, 'BTL', 'Powerful insecticide for termites and borers', 'active', '2025-12-25 03:29:14', '2025-12-25 03:29:14', 'Insecticides'),
(294, 5, 'Fungicide (Mancozeb)', 'INS-MAN-75', '3808', 215.00, 6.00, 6.00, 12.00, 'PKT', 'Broad-spectrum contact fungicide', 'active', '2025-12-25 03:29:14', '2025-12-25 03:29:14', 'Insecticides'),
(295, 5, 'Cypermethrin 10% EC', 'INS-CYP-10', '3808', 320.00, 9.00, 9.00, 18.00, 'BTL', 'Effective against bollworms and fruit borers', 'active', '2025-12-25 03:29:14', '2025-12-25 03:29:14', 'Insecticides'),
(296, 5, 'Green Shade Net (50%)', 'OTH-SNET-01', '5608', 4500.00, 6.00, 6.00, 12.00, 'ROL', 'UV stabilized net for nursery protection', 'active', '2025-12-25 03:29:14', '2025-12-25 03:29:14', 'Others'),
(297, 5, 'Gardening Scissor', 'OTH-GSC-02', '8201', 185.00, 6.00, 6.00, 12.00, 'PCS', 'Stainless steel pruning shears', 'active', '2025-12-25 03:29:14', '2025-12-25 03:29:14', 'Others'),
(298, 5, 'Plastic Seedling Tray', 'OTH-TRY-50', '3926', 45.00, 9.00, 9.00, 18.00, 'PCS', '50-cell cavity tray for nursery', 'active', '2025-12-25 03:29:14', '2025-12-25 03:29:14', 'Others'),
(299, 5, 'Boron 20% (Micronutrient)', 'FER-BOR-01', '2810', 290.00, 6.00, 6.00, 12.00, 'PKT', 'Essential for flower and fruit setting', 'active', '2025-12-25 18:27:18', '2025-12-25 18:27:18', 'Fertilizers'),
(300, 5, 'Cauliflower Seeds', 'SED-CAU-10', '1209', 350.00, 0.00, 0.00, 0.00, 'PKT', 'F1 Hybrid early variety seeds', 'active', '2025-12-25 18:27:18', '2025-12-25 18:27:18', 'Seeds'),
(301, 5, 'Acephate 75% SP', 'INS-ACP-75', '3808', 520.00, 9.00, 9.00, 18.00, 'BTL', 'Systemic insecticide for jassids and thrips', 'active', '2025-12-25 18:27:18', '2025-12-25 18:27:18', 'Insecticides'),
(302, 5, 'Okra Seeds (Bhendi)', 'SED-OKR-05', '1209', 120.00, 0.00, 0.00, 0.00, 'PKT', 'High-yield virus resistant variety', 'active', '2025-12-25 18:27:18', '2025-12-25 18:27:18', 'Seeds'),
(303, 5, 'Yellow Sticky Traps', 'OTH-TRP-25', '3808', 450.00, 6.00, 6.00, 12.00, 'PCS', 'Pack of 25 traps for pest monitoring', 'active', '2025-12-25 18:27:18', '2025-12-25 18:27:18', 'Others'),
(304, 5, 'Humic Acid Liquid', 'FER-HUM-01', '3824', 380.00, 6.00, 6.00, 12.00, 'LTR', 'Soil conditioner and growth stimulant', 'active', '2025-12-25 18:27:18', '2025-12-25 18:27:18', 'Fertilizers'),
(305, 5, 'Pruning Saw', 'OTH-SAW-12', '8202', 310.00, 6.00, 6.00, 12.00, 'PCS', 'Manual curved blade saw for branches', 'active', '2025-12-25 18:27:18', '2025-12-25 18:27:18', 'Others');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `items`
--
ALTER TABLE `items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_item_user` (`user_id`,`item_name`),
  ADD KEY `idx_items_user` (`user_id`),
  ADD KEY `idx_items_name` (`item_name`),
  ADD KEY `idx_items_category` (`category`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `items`
--
ALTER TABLE `items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=306;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `items`
--
ALTER TABLE `items`
  ADD CONSTRAINT `items_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
