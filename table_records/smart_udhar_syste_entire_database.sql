-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3307
-- Generation Time: Jan 05, 2026 at 01:16 PM
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
-- Table structure for table `bill_revisions`
--

CREATE TABLE `bill_revisions` (
  `id` int(11) NOT NULL,
  `udhar_id` int(11) NOT NULL,
  `revision_number` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `bill_no` varchar(50) NOT NULL,
  `transaction_date` date NOT NULL,
  `due_date` date DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `cgst_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `sgst_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `igst_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `discount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `discount_type` enum('fixed','percentage') DEFAULT 'fixed',
  `round_off` decimal(10,2) NOT NULL DEFAULT 0.00,
  `grand_total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `description` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('pending','partially_paid','paid') DEFAULT 'pending',
  `category` varchar(100) DEFAULT NULL,
  `items_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`items_data`)),
  `change_reason` text DEFAULT NULL,
  `changed_by` int(11) NOT NULL,
  `changed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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

-- --------------------------------------------------------

--
-- Stand-in structure for view `customer_balance_history`
-- (See below for the actual view)
--
CREATE TABLE `customer_balance_history` (
`customer_id` int(11)
,`customer_name` varchar(100)
,`date` date
,`type` varchar(7)
,`debit` decimal(10,2)
,`credit` decimal(10,2)
,`remarks` mediumtext
,`reference` varchar(100)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `daily_summary`
-- (See below for the actual view)
--
CREATE TABLE `daily_summary` (
`report_date` date
,`total_bills` bigint(21)
,`unique_customers` bigint(21)
,`total_udhar` decimal(32,2)
,`total_sales` decimal(32,2)
,`total_tax` decimal(34,2)
,`total_payments` decimal(32,2)
,`net_balance` decimal(33,2)
);

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

-- --------------------------------------------------------

--
-- Stand-in structure for view `item_performance`
-- (See below for the actual view)
--
CREATE TABLE `item_performance` (
`id` int(11)
,`item_name` varchar(200)
,`hsn_code` varchar(50)
,`unit` varchar(20)
,`bill_count` bigint(21)
,`total_quantity` decimal(32,2)
,`total_sales` decimal(32,2)
,`avg_price` decimal(14,6)
,`max_price` decimal(10,2)
,`min_price` decimal(10,2)
,`last_sale_date` date
,`unique_customers` bigint(21)
);

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

-- --------------------------------------------------------

--
-- Table structure for table `payment_allocations`
--

CREATE TABLE `payment_allocations` (
  `id` int(11) NOT NULL,
  `payment_id` int(11) NOT NULL,
  `udhar_transaction_id` int(11) NOT NULL,
  `allocated_amount` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment_allocations`
--

INSERT INTO `payment_allocations` (`id`, `payment_id`, `udhar_transaction_id`, `allocated_amount`, `created_at`) VALUES
(39, 8, 21, 1000.00, '2025-12-25 23:51:53'),
(40, 19, 22, 2597.00, '2025-12-25 23:51:53'),
(41, 13, 23, 500.00, '2025-12-25 23:51:53'),
(42, 21, 24, 3392.00, '2025-12-25 23:51:53'),
(43, 22, 25, 1500.00, '2025-12-25 23:51:53'),
(44, 23, 26, 1749.00, '2025-12-25 23:51:53'),
(45, 24, 27, 1000.00, '2025-12-25 23:51:53'),
(46, 25, 28, 3816.00, '2025-12-25 23:51:53'),
(47, 26, 29, 800.00, '2025-12-25 23:51:53'),
(48, 27, 30, 2438.00, '2025-12-25 23:51:53');

--
-- Triggers `payment_allocations`
--
DELIMITER $$
CREATE TRIGGER `after_payment_allocation_insert` AFTER INSERT ON `payment_allocations` FOR EACH ROW BEGIN
    -- Update customer balance
    UPDATE customers c
    JOIN udhar_transactions ut ON c.id = ut.customer_id
    SET c.balance = c.balance - NEW.allocated_amount,
        c.total_paid = c.total_paid + NEW.allocated_amount,
        c.updated_at = NOW()
    WHERE ut.id = NEW.udhar_transaction_id;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Stand-in structure for view `payment_summary`
-- (See below for the actual view)
--
CREATE TABLE `payment_summary` (
`id` int(11)
,`payment_date` date
,`customer_id` int(11)
,`customer_name` varchar(100)
,`amount` decimal(10,2)
,`payment_mode` enum('cash','bank_transfer','upi','cheque','other')
,`reference_no` varchar(100)
,`is_allocated` tinyint(1)
,`allocated_amount` decimal(10,2)
,`remaining_amount` decimal(10,2)
,`notes` text
,`customer_balance` decimal(10,2)
,`total_udhar` decimal(10,2)
,`total_paid` decimal(10,2)
);

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

-- --------------------------------------------------------

--
-- Table structure for table `user_logs`
--

CREATE TABLE `user_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `activity` enum('login','logout') NOT NULL,
  `timestamp` datetime NOT NULL DEFAULT current_timestamp(),
  `ip_address` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_logs`
--

INSERT INTO `user_logs` (`id`, `user_id`, `activity`, `timestamp`, `ip_address`) VALUES
(9, 4, 'login', '2025-12-24 20:40:01', '::1'),
(10, 4, 'logout', '2025-12-24 20:40:15', '::1'),
(11, 5, 'login', '2025-12-25 01:48:08', '::1'),
(12, 5, 'logout', '2025-12-26 00:06:26', '::1'),
(13, 5, 'logout', '2025-12-26 05:03:34', '::1'),
(14, 5, 'logout', '2025-12-26 05:42:42', '::1');

-- --------------------------------------------------------

--
-- Table structure for table `user_settings`
--

CREATE TABLE `user_settings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure for view `customer_balance_history`
--
DROP TABLE IF EXISTS `customer_balance_history`;

CREATE ALGORITHM=UNDEFINED SQL SECURITY INVOKER VIEW `customer_balance_history`  AS SELECT `c`.`id` AS `customer_id`, `c`.`name` AS `customer_name`, cast(`ut`.`transaction_date` as date) AS `date`, 'UDHAR' AS `type`, `ut`.`amount` AS `debit`, 0 AS `credit`, `ut`.`description` AS `remarks`, `ut`.`bill_no` AS `reference` FROM (`customers` `c` join `udhar_transactions` `ut` on(`c`.`id` = `ut`.`customer_id`))union all select `c`.`id` AS `customer_id`,`c`.`name` AS `customer_name`,cast(`p`.`payment_date` as date) AS `date`,'PAYMENT' AS `type`,0 AS `debit`,`p`.`amount` AS `credit`,`p`.`notes` AS `remarks`,`p`.`reference_no` AS `reference` from (`customers` `c` join `payments` `p` on(`c`.`id` = `p`.`customer_id`)) order by `customer_id`,`date` desc  ;

-- --------------------------------------------------------

--
-- Structure for view `daily_summary`
--
DROP TABLE IF EXISTS `daily_summary`;

CREATE ALGORITHM=UNDEFINED SQL SECURITY INVOKER VIEW `daily_summary`  AS SELECT cast(`ut`.`transaction_date` as date) AS `report_date`, count(distinct `ut`.`id`) AS `total_bills`, count(distinct `ut`.`customer_id`) AS `unique_customers`, sum(`ut`.`amount`) AS `total_udhar`, sum(`ut`.`total_amount`) AS `total_sales`, sum(`ut`.`cgst_amount` + `ut`.`sgst_amount` + `ut`.`igst_amount`) AS `total_tax`, sum(`p`.`amount`) AS `total_payments`, sum(`p`.`amount`) - sum(`ut`.`amount`) AS `net_balance` FROM (`udhar_transactions` `ut` left join `payments` `p` on(cast(`p`.`payment_date` as date) = cast(`ut`.`transaction_date` as date))) GROUP BY cast(`ut`.`transaction_date` as date) ORDER BY cast(`ut`.`transaction_date` as date) DESC ;

-- --------------------------------------------------------

--
-- Structure for view `item_performance`
--
DROP TABLE IF EXISTS `item_performance`;

CREATE ALGORITHM=UNDEFINED SQL SECURITY INVOKER VIEW `item_performance`  AS SELECT `i`.`id` AS `id`, `i`.`item_name` AS `item_name`, `i`.`hsn_code` AS `hsn_code`, `i`.`unit` AS `unit`, count(distinct `ui`.`udhar_id`) AS `bill_count`, sum(`ui`.`quantity`) AS `total_quantity`, sum(`ui`.`total_amount`) AS `total_sales`, avg(`ui`.`unit_price`) AS `avg_price`, max(`ui`.`unit_price`) AS `max_price`, min(`ui`.`unit_price`) AS `min_price`, max(`ut`.`transaction_date`) AS `last_sale_date`, count(distinct `ut`.`customer_id`) AS `unique_customers` FROM ((`items` `i` join `udhar_items` `ui` on(`i`.`id` = `ui`.`item_id`)) join `udhar_transactions` `ut` on(`ui`.`udhar_id` = `ut`.`id`)) GROUP BY `i`.`id`, `i`.`item_name`, `i`.`hsn_code`, `i`.`unit` ORDER BY sum(`ui`.`total_amount`) DESC ;

-- --------------------------------------------------------

--
-- Structure for view `payment_summary`
--
DROP TABLE IF EXISTS `payment_summary`;

CREATE ALGORITHM=UNDEFINED SQL SECURITY INVOKER VIEW `payment_summary`  AS SELECT `p`.`id` AS `id`, `p`.`payment_date` AS `payment_date`, `p`.`customer_id` AS `customer_id`, `p`.`customer_name` AS `customer_name`, `p`.`amount` AS `amount`, `p`.`payment_mode` AS `payment_mode`, `p`.`reference_no` AS `reference_no`, `p`.`is_allocated` AS `is_allocated`, `p`.`allocated_amount` AS `allocated_amount`, `p`.`remaining_amount` AS `remaining_amount`, `p`.`notes` AS `notes`, `c`.`balance` AS `customer_balance`, `c`.`total_udhar` AS `total_udhar`, `c`.`total_paid` AS `total_paid` FROM (`payments` `p` join `customers` `c` on(`p`.`customer_id` = `c`.`id`)) ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bill_revisions`
--
ALTER TABLE `bill_revisions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_udhar_revision` (`udhar_id`,`revision_number`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_customer_user` (`user_id`),
  ADD KEY `idx_customer_mobile` (`mobile`),
  ADD KEY `idx_customers_balance` (`balance`);

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
-- Indexes for table `payment_allocations`
--
ALTER TABLE `payment_allocations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `payment_id` (`payment_id`),
  ADD KEY `udhar_transaction_id` (`udhar_transaction_id`);

--
-- Indexes for table `udhar_items`
--
ALTER TABLE `udhar_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_udhar_items_udhar` (`udhar_id`),
  ADD KEY `idx_udhar_items_item` (`item_id`);

--
-- Indexes for table `udhar_transactions`
--
ALTER TABLE `udhar_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_udhar_customer` (`customer_id`),
  ADD KEY `idx_udhar_transaction_date` (`transaction_date`),
  ADD KEY `idx_udhar_status` (`status`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `user_logs`
--
ALTER TABLE `user_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `user_settings`
--
ALTER TABLE `user_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_setting` (`user_id`,`setting_key`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bill_revisions`
--
ALTER TABLE `bill_revisions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- AUTO_INCREMENT for table `items`
--
ALTER TABLE `items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=306;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `payment_allocations`
--
ALTER TABLE `payment_allocations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT for table `udhar_items`
--
ALTER TABLE `udhar_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=301;

--
-- AUTO_INCREMENT for table `udhar_transactions`
--
ALTER TABLE `udhar_transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `user_logs`
--
ALTER TABLE `user_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `user_settings`
--
ALTER TABLE `user_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `customers`
--
ALTER TABLE `customers`
  ADD CONSTRAINT `customers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `items`
--
ALTER TABLE `items`
  ADD CONSTRAINT `items_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payment_allocations`
--
ALTER TABLE `payment_allocations`
  ADD CONSTRAINT `payment_allocations_ibfk_1` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payment_allocations_ibfk_2` FOREIGN KEY (`udhar_transaction_id`) REFERENCES `udhar_transactions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `udhar_items`
--
ALTER TABLE `udhar_items`
  ADD CONSTRAINT `udhar_items_ibfk_1` FOREIGN KEY (`udhar_id`) REFERENCES `udhar_transactions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `udhar_items_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `udhar_transactions`
--
ALTER TABLE `udhar_transactions`
  ADD CONSTRAINT `udhar_transactions_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_logs`
--
ALTER TABLE `user_logs`
  ADD CONSTRAINT `user_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_settings`
--
ALTER TABLE `user_settings`
  ADD CONSTRAINT `user_settings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

