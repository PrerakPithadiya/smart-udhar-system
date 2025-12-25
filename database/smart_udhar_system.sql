-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 23, 2025 at 09:06 PM
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
-- Database: `smart_udhar_system`
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
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_customer_user` (`user_id`),
  ADD KEY `idx_customer_mobile` (`mobile`),
  ADD KEY `idx_customers_balance` (`balance`);

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
-- Indexes for table `items`
--
ALTER TABLE `items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_item_user` (`user_id`,`item_name`),
  ADD KEY `idx_items_user` (`user_id`),
  ADD KEY `idx_items_name` (`item_name`),
  ADD KEY `idx_items_category` (`category`);

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
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_payment_customer` (`customer_id`),
  ADD KEY `idx_payments_customer_date` (`customer_id`,`payment_date`),
  ADD KEY `idx_payments_allocated` (`is_allocated`),
  ADD KEY `idx_payments_payment_date` (`payment_date`),
  ADD KEY `idx_payments_payment_mode` (`payment_mode`);

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
-- Indexes for table `payment_allocations`
--
ALTER TABLE `payment_allocations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `payment_id` (`payment_id`),
  ADD KEY `udhar_transaction_id` (`udhar_transaction_id`);

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
-- Indexes for table `udhar_items`
--
ALTER TABLE `udhar_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_udhar_items_udhar` (`udhar_id`),
  ADD KEY `idx_udhar_items_item` (`item_id`);

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
  `bill_no` varchar(50) DEFAULT NULL,
  `total_amount` decimal(10,2) DEFAULT 0.00,
  `cgst_amount` decimal(10,2) DEFAULT 0.00,
  `sgst_amount` decimal(10,2) DEFAULT 0.00,
  `igst_amount` decimal(10,2) DEFAULT 0.00,
  `grand_total` decimal(10,2) DEFAULT 0.00,
  `discount` decimal(10,2) DEFAULT 0.00,
  `discount_type` enum('percentage','fixed') DEFAULT 'fixed',
  `round_off` decimal(10,2) DEFAULT 0.00,
  `bill_notes` text DEFAULT NULL,
  `print_count` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for table `udhar_transactions`
--
ALTER TABLE `udhar_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_udhar_customer` (`customer_id`),
  ADD KEY `idx_udhar_transaction_date` (`transaction_date`),
  ADD KEY `idx_udhar_status` (`status`);

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
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

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

--
-- Indexes for table `user_settings`
--
ALTER TABLE `user_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_setting` (`user_id`,`setting_key`);

-- --------------------------------------------------------

--
-- Structure for view `customer_balance_history`
--
DROP TABLE IF EXISTS `customer_balance_history`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `customer_balance_history`  AS SELECT `c`.`id` AS `customer_id`, `c`.`name` AS `customer_name`, cast`ut`.`transaction_date` as date) AS `date`, 'UDHAR' AS `type`, `ut`.`amount` AS `debit`, 0 AS `credit`, `ut`.`description` AS `remarks`, `ut`.`bill_no` AS `reference` FROM `customers` `c` join `udhar_transactions` `ut` on`c`.`id` = `ut`.`customer_id`))union all select `c`.`id` AS `customer_id`,`c`.`name` AS `customer_name`,cast`p`.`payment_date` as date) AS `date`,'PAYMENT' AS `type`,0 AS `debit`,`p`.`amount` AS `credit`,`p`.`notes` AS `remarks`,`p`.`reference_no` AS `reference` from `customers` `c` join `payments` `p` on`c`.`id` = `p`.`customer_id`)) order by `customer_id`,`date` desc  ;

-- --------------------------------------------------------

--
-- Structure for view `daily_summary`
--
DROP TABLE IF EXISTS `daily_summary`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `daily_summary`  AS SELECT cast`ut`.`transaction_date` as date) AS `report_date`, countdistinct `ut`.`id`) AS `total_bills`, countdistinct `ut`.`customer_id`) AS `unique_customers`, sum`ut`.`amount`) AS `total_udhar`, sum`ut`.`total_amount`) AS `total_sales`, sum`ut`.`cgst_amount` + `ut`.`sgst_amount` + `ut`.`igst_amount`) AS `total_tax`, sum`p`.`amount`) AS `total_payments`, sum`p`.`amount`) - sum`ut`.`amount`) AS `net_balance` FROM `udhar_transactions` `ut` left join `payments` `p` oncast`p`.`payment_date` as date) = cast`ut`.`transaction_date` as date))) GROUP BY cast`ut`.`transaction_date` as date) ORDER BY cast`ut`.`transaction_date` as date) DESC ;

-- --------------------------------------------------------

--
-- Structure for view `item_performance`
--
DROP TABLE IF EXISTS `item_performance`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `item_performance`  AS SELECT `i`.`id` AS `id`, `i`.`item_name` AS `item_name`, `i`.`hsn_code` AS `hsn_code`, `i`.`unit` AS `unit`, countdistinct `ui`.`udhar_id`) AS `bill_count`, sum`ui`.`quantity`) AS `total_quantity`, sum`ui`.`total_amount`) AS `total_sales`, avg`ui`.`unit_price`) AS `avg_price`, max`ui`.`unit_price`) AS `max_price`, min`ui`.`unit_price`) AS `min_price`, max`ut`.`transaction_date`) AS `last_sale_date`, countdistinct `ut`.`customer_id`) AS `unique_customers` FROM `items` `i` join `udhar_items` `ui` on`i`.`id` = `ui`.`item_id`)) join `udhar_transactions` `ut` on`ui`.`udhar_id` = `ut`.`id`)) GROUP BY `i`.`id`, `i`.`item_name`, `i`.`hsn_code`, `i`.`unit` ORDER BY sum`ui`.`total_amount`) DESC ;

-- --------------------------------------------------------

--
-- Structure for view `payment_summary`
--
DROP TABLE IF EXISTS `payment_summary`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `payment_summary`  AS SELECT `p`.`id` AS `id`, `p`.`payment_date` AS `payment_date`, `p`.`customer_id` AS `customer_id`, `p`.`customer_name` AS `customer_name`, `p`.`amount` AS `amount`, `p`.`payment_mode` AS `payment_mode`, `p`.`reference_no` AS `reference_no`, `p`.`is_allocated` AS `is_allocated`, `p`.`allocated_amount` AS `allocated_amount`, `p`.`remaining_amount` AS `remaining_amount`, `p`.`notes` AS `notes`, `c`.`balance` AS `customer_balance`, `c`.`total_udhar` AS `total_udhar`, `c`.`total_paid` AS `total_paid` FROM `payments` `p` join `customers` `c` on`p`.`customer_id` = `c`.`id`)) ;

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

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `items`
--
ALTER TABLE `items`
  MODIFY `id` int11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `payment_allocations`
--
ALTER TABLE `payment_allocations`
  MODIFY `id` int11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `udhar_items`
--
ALTER TABLE `udhar_items`
  MODIFY `id` int11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `udhar_transactions`
--
ALTER TABLE `udhar_transactions`
  MODIFY `id` int11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `user_settings`
--
ALTER TABLE `user_settings`
  MODIFY `id` int11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `customers`
--
ALTER TABLE `customers`
  ADD CONSTRAINT `customers_ibfk_1` FOREIGN KEY `user_id`) REFERENCES `users` `id`) ON DELETE CASCADE;

--
-- Constraints for table `items`
--
ALTER TABLE `items`
  ADD CONSTRAINT `items_ibfk_1` FOREIGN KEY `user_id`) REFERENCES `users` `id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY `customer_id`) REFERENCES `customers` `id`) ON DELETE CASCADE;

--
-- Constraints for table `payment_allocations`
--
ALTER TABLE `payment_allocations`
  ADD CONSTRAINT `payment_allocations_ibfk_1` FOREIGN KEY `payment_id`) REFERENCES `payments` `id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payment_allocations_ibfk_2` FOREIGN KEY `udhar_transaction_id`) REFERENCES `udhar_transactions` `id`) ON DELETE CASCADE;

--
-- Constraints for table `udhar_items`
--
ALTER TABLE `udhar_items`
  ADD CONSTRAINT `udhar_items_ibfk_1` FOREIGN KEY `udhar_id`) REFERENCES `udhar_transactions` `id`) ON DELETE CASCADE,
  ADD CONSTRAINT `udhar_items_ibfk_2` FOREIGN KEY `item_id`) REFERENCES `items` `id`) ON DELETE CASCADE;

--
-- Constraints for table `udhar_transactions`
--
ALTER TABLE `udhar_transactions`
  ADD CONSTRAINT `udhar_transactions_ibfk_1` FOREIGN KEY `customer_id`) REFERENCES `customers` `id`) ON DELETE CASCADE;

--
-- Constraints for table `user_settings`
--
ALTER TABLE `user_settings`
  ADD CONSTRAINT `user_settings_ibfk_1` FOREIGN KEY `user_id`) REFERENCES `users` `id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
