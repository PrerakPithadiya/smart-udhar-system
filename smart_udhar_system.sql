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
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`id`, `user_id`, `name`, `mobile`, `email`, `address`, `total_udhar`, `total_paid`, `balance`, `last_transaction_date`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 'Rajesh Kumar', '9876543210', 'rajesh@example.com', '123 Main Street,', 5000.00, 3000.00, 2000.00, '2025-12-20', 'active', '2025-12-20 02:30:00', '2025-12-21 11:09:42'),
(2, 1, 'Priya Sharma', '9876543211', 'priya@example.com', '456 Park Road, Delhi', 3000.00, 6000.00, -3000.00, '2025-12-19', 'active', '2025-12-20 02:30:00', '2025-12-21 10:55:11'),
(3, 1, 'Amit Patel', '9876543212', 'amit@example.com', '789 Market Lane, Ahmedabad', 7500.00, 7500.00, 0.00, '2025-12-21', 'active', '2025-12-20 02:30:00', '2025-12-21 10:55:11'),
(4, 1, 'Sneha Reddy', '9876543213', 'sneha@example.com', '321 Garden Street, Hyderabad', 4000.00, 8000.00, -4000.00, '2025-12-18', 'active', '2025-12-20 02:30:00', '2025-12-21 10:55:11'),
(5, 1, 'Vikram Singh', '9876543214', 'vikram@example.com', '654 Temple Road, Jaipur', 6000.00, 5000.00, 1000.00, '2025-12-21', 'active', '2025-12-20 02:30:00', '2025-12-21 10:55:11');

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
(1, 1, 'Rice Bag', 'RIC001', '1001', 1200.00, 5.00, 5.00, 0.00, 'BAG', 'Premium Basmati Rice 25kg', 'active', '2025-12-21 10:55:11', '2025-12-21 10:55:11', 'Grains'),
(2, 1, 'Wheat Flour', 'WHE002', '1101', 600.00, 5.00, 5.00, 0.00, 'BAG', 'Fine Wheat Flour 10kg', 'active', '2025-12-21 10:55:11', '2025-12-21 10:55:11', 'Flour'),
(3, 1, 'Sugar', 'SUG003', '1701', 45.00, 5.00, 5.00, 0.00, 'KG', 'Refined Sugar', 'active', '2025-12-21 10:55:11', '2025-12-21 10:55:11', 'Groceries'),
(4, 1, 'Toothpaste', 'TP004', '3306', 80.00, 9.00, 9.00, 0.00, 'PCS', 'Mint Flavored Toothpaste', 'active', '2025-12-21 10:55:11', '2025-12-21 10:55:11', 'Personal Care'),
(5, 1, 'Soap', 'SOP005', '3401', 30.00, 9.00, 9.00, 0.00, 'PCS', 'Bathing Soap', 'active', '2025-12-21 10:55:11', '2025-12-21 10:55:11', 'Personal Care'),
(6, 1, 'Cooking Oil', 'OIL006', '1509', 180.00, 5.00, 5.00, 0.00, 'LTR', 'Sunflower Oil', 'active', '2025-12-21 10:55:11', '2025-12-21 10:55:11', 'Oil'),
(7, 1, 'Tea Powder', 'TEA007', '0902', 300.00, 5.00, 5.00, 0.00, 'KG', 'Premium Tea Leaves', 'active', '2025-12-21 10:55:11', '2025-12-21 10:55:11', 'Beverages'),
(8, 1, 'Detergent', 'DET008', '3402', 250.00, 9.00, 9.00, 0.00, 'PCS', 'Washing Powder 1kg', 'active', '2025-12-21 10:55:11', '2025-12-21 10:55:11', 'Cleaning'),
(9, 1, 'Biscuit', 'BIS009', '1905', 35.00, 9.00, 9.00, 0.00, 'PACK', 'Cream Biscuits 200g', 'active', '2025-12-21 10:55:11', '2025-12-21 11:10:00', 'Snacks'),
(10, 1, 'Shampoo', 'SHA010', '3305', 120.00, 9.00, 9.00, 0.00, 'PCS', 'Hair Care Shampoo 200ml', 'active', '2025-12-21 10:55:11', '2025-12-21 10:55:11', 'Personal Care'),
(11, 1, 'yuriya', '1', '213125', 253.80, 2.50, 2.50, 0.00, 'PACK', '', 'active', '2025-12-21 09:06:21', '2025-12-21 10:12:28', NULL);

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
(1, 1, '2025-12-18', 1000.00, 'cash', 'CASH001', 'Part payment for BILL001', '2025-12-21 10:55:11', 'Rajesh Kumar', 0.00, 1000.00, 1, '2025-12-18', '2025-12-21 10:55:11'),
(2, 2, '2025-12-20', 2000.00, 'upi', 'UPI123456', 'Full payment for BILL003', '2025-12-21 10:55:11', 'Priya Sharma', 0.00, 1500.00, 1, '2025-12-20', '2025-12-23 19:30:02'),
(3, 2, '2025-12-15', 1500.00, 'bank_transfer', 'TRF789012', 'Payment for BILL004', '2025-12-21 10:55:11', 'Priya Sharma', 0.00, 1500.00, 1, '2025-12-15', '2025-12-21 10:55:11'),
(4, 3, '2025-12-20', 2500.00, 'cash', 'CASH002', 'Part payment for BILL005', '2025-12-21 10:55:11', 'Amit Patel', 0.00, 2500.00, 1, '2025-12-20', '2025-12-21 10:55:11'),
(5, 4, '2025-12-20', 2500.00, 'cheque', 'CHQ456789', 'Payment for BILL007', '2025-12-21 10:55:11', 'Sneha Reddy', 0.00, 2500.00, 1, '2025-12-20', '2025-12-21 10:55:11'),
(6, 4, '2025-12-15', 1500.00, 'upi', 'UPI654321', 'Payment for BILL008', '2025-12-21 10:55:11', 'Sneha Reddy', 0.00, 1500.00, 1, '2025-12-15', '2025-12-21 10:55:11'),
(7, 5, '2025-12-20', 2000.00, 'cash', 'CASH003', 'Part payment for BILL009', '2025-12-21 10:55:11', 'Vikram Singh', 0.00, 2000.00, 1, '2025-12-20', '2025-12-21 10:55:11');

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
(1, 1, 1, 1000.00, '2025-12-21 10:55:11'),
(2, 2, 3, 1500.00, '2025-12-21 10:55:11'),
(3, 3, 4, 1500.00, '2025-12-21 10:55:11'),
(4, 4, 5, 2500.00, '2025-12-21 10:55:11'),
(5, 5, 7, 2500.00, '2025-12-21 10:55:11'),
(6, 6, 8, 1500.00, '2025-12-21 10:55:11'),
(7, 7, 9, 2000.00, '2025-12-21 10:55:11');

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
(1, 1, 1, 'Rice Bag', '1001', 'BAG', 1.00, 1200.00, 5.00, 5.00, 0.00, 60.00, 60.00, 0.00, 1320.00, '2025-12-21 10:55:11'),
(2, 1, 3, 'Sugar', '1701', 'KG', 10.00, 45.00, 5.00, 5.00, 0.00, 22.50, 22.50, 0.00, 495.00, '2025-12-21 10:55:11'),
(3, 1, 6, 'Cooking Oil', '1509', 'LTR', 2.00, 180.00, 5.00, 5.00, 0.00, 9.00, 9.00, 0.00, 378.00, '2025-12-21 10:55:11'),
(4, 2, 7, 'Tea Powder', '0902', 'KG', 5.00, 300.00, 5.00, 5.00, 0.00, 75.00, 75.00, 0.00, 1650.00, '2025-12-21 10:55:11'),
(5, 2, 8, 'Detergent', '3402', 'PCS', 3.00, 250.00, 9.00, 9.00, 0.00, 67.50, 67.50, 0.00, 885.00, '2025-12-21 10:55:11'),
(6, 2, 10, 'Shampoo', '3305', 'PCS', 5.00, 120.00, 9.00, 9.00, 0.00, 54.00, 54.00, 0.00, 726.00, '2025-12-21 10:55:11'),
(7, 3, 2, 'Wheat Flour', '1101', 'BAG', 2.00, 600.00, 5.00, 5.00, 0.00, 60.00, 60.00, 0.00, 1320.00, '2025-12-21 10:55:11'),
(8, 3, 9, 'Biscuits', '1905', 'PACK', 5.00, 35.00, 9.00, 9.00, 0.00, 15.75, 15.75, 0.00, 210.00, '2025-12-21 10:55:11'),
(9, 5, 1, 'Rice Bag', '1001', 'BAG', 3.00, 1200.00, 5.00, 5.00, 0.00, 180.00, 180.00, 0.00, 3960.00, '2025-12-21 10:55:11'),
(10, 5, 4, 'Toothpaste', '3306', 'PCS', 6.00, 80.00, 9.00, 9.00, 0.00, 43.20, 43.20, 0.00, 576.00, '2025-12-21 10:55:11'),
(11, 9, 6, 'Cooking Oil', '1509', 'LTR', 10.00, 180.00, 5.00, 5.00, 0.00, 90.00, 90.00, 0.00, 1980.00, '2025-12-21 10:55:11'),
(12, 9, 7, 'Tea Powder', '0902', 'KG', 5.00, 300.00, 5.00, 5.00, 0.00, 75.00, 75.00, 0.00, 1650.00, '2025-12-21 10:55:11'),
(13, 9, 3, 'Sugar', '1701', 'KG', 10.00, 45.00, 5.00, 5.00, 0.00, 22.50, 22.50, 0.00, 495.00, '2025-12-21 10:55:11'),
(15, 13, 2, 'Wheat Flour', '', 'PCS', 1.00, 600.00, 5.00, 5.00, 0.00, 30.00, 30.00, 0.00, 600.00, '2025-12-21 11:30:36');

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
-- Dumping data for table `udhar_transactions`
--

INSERT INTO `udhar_transactions` (`id`, `customer_id`, `transaction_date`, `description`, `amount`, `due_date`, `notes`, `status`, `remaining_amount`, `created_at`, `bill_no`, `total_amount`, `cgst_amount`, `sgst_amount`, `igst_amount`, `grand_total`, `discount`, `discount_type`, `round_off`, `bill_notes`, `print_count`) VALUES
(1, 1, '2025-12-15', 'Monthly grocery purchase', 2000.00, '2025-12-30', 'Regular customer', 'partially_paid', 1000.00, '2025-12-21 10:55:11', 'BILL001', 2000.00, 90.48, 90.48, 0.00, 2180.96, 20.00, 'fixed', -0.96, 'Thank you for shopping', 2),
(2, 1, '2025-12-20', 'Festival purchase', 3000.00, '2026-01-05', 'Diwali shopping', 'pending', 3000.00, '2025-12-21 10:55:11', 'BILL002', 3000.00, 135.71, 135.71, 0.00, 3271.42, 0.00, 'fixed', -0.42, 'Happy Diwali', 1),
(3, 2, '2025-12-19', 'Regular purchase', 1500.00, '2025-12-29', 'Paid in full', 'paid', 0.00, '2025-12-21 10:55:11', 'BILL003', 1500.00, 67.86, 67.86, 0.00, 1635.72, 50.00, 'fixed', 0.28, NULL, 1),
(4, 2, '2025-12-10', 'Bulk order', 1500.00, '2025-12-25', 'Wholesale purchase', 'paid', 0.00, '2025-12-21 10:55:11', 'BILL004', 1500.00, 67.86, 67.86, 0.00, 1635.72, 0.00, 'fixed', 0.28, NULL, 1),
(5, 3, '2025-12-18', 'Office supplies', 4500.00, '2026-01-10', 'For office cafeteria', 'partially_paid', 2000.00, '2025-12-21 10:55:11', 'BILL005', 4500.00, 203.57, 203.57, 0.00, 4907.14, 100.00, 'fixed', -0.14, 'Office order', 1),
(6, 3, '2025-12-21', 'Restaurant order', 3000.00, '2026-01-15', 'For restaurant kitchen', 'pending', 3000.00, '2025-12-21 10:55:11', 'BILL006', 3000.00, 135.71, 135.71, 0.00, 3271.42, 0.00, 'fixed', -0.42, 'Restaurant supply', 4),
(7, 4, '2025-12-18', 'Household items', 2500.00, '2025-12-28', 'Home use', 'paid', 0.00, '2025-12-21 10:55:11', 'BILL007', 2500.00, 113.10, 113.10, 0.00, 2726.20, 0.00, 'fixed', -0.20, NULL, 1),
(8, 4, '2025-12-12', 'Monthly stock', 1500.00, '2025-12-22', 'Regular monthly purchase', 'paid', 0.00, '2025-12-21 10:55:11', 'BILL008', 1500.00, 67.86, 67.86, 0.00, 1635.72, 0.00, 'fixed', 0.28, NULL, 1),
(9, 5, '2025-12-16', 'Hotel supplies', 4000.00, '2026-01-05', 'For hotel kitchen', 'partially_paid', 2000.00, '2025-12-21 10:55:11', 'BILL009', 4000.00, 181.82, 181.82, 0.00, 4363.64, 0.00, 'fixed', 0.36, 'Hotel order', 1),
(10, 5, '2025-12-21', 'Catering order', 2000.00, '2026-01-10', 'For wedding catering', 'pending', 2000.00, '2025-12-21 10:55:11', 'BILL010', 2000.00, 90.48, 90.48, 0.00, 2180.96, 20.00, 'fixed', -0.96, 'Wedding order', 3),
(13, 2, '2025-12-21', '', 660.00, '0000-00-00', '', 'pending', 660.00, '2025-12-21 11:30:36', 'BILL-202512-0001', 600.00, 30.00, 30.00, 0.00, 660.00, 0.00, '', 0.00, '0', 2);

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
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `full_name`, `email`, `shop_name`, `mobile`, `address`, `role`, `status`, `last_login`, `created_at`) VALUES
(1, 'admin', 'admin123', 'Admin User', 'admin@myshop.com', 'My Shop', '9876543210', NULL, 'admin', 'active', '2025-12-24 00:51:53', '2025-12-19 05:17:09'),
(2, 'staff1', 'staff123', 'Staff Member', 'staff@myshop.com', 'My Shop', '9876543211', 'Shop Address', 'staff', 'active', '2025-12-20 10:00:00', '2025-12-19 23:30:00');

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
-- Dumping data for table `user_settings`
--

INSERT INTO `user_settings` (`id`, `user_id`, `setting_key`, `setting_value`, `created_at`, `updated_at`) VALUES
(1, 1, 'default_report_type', 'dashboard', '2025-12-20 08:41:11', '2025-12-20 08:41:11'),
(2, 1, 'default_date_range', 'month', '2025-12-20 08:41:11', '2025-12-20 08:41:11'),
(3, 1, 'chart_type', 'bar', '2025-12-20 08:41:11', '2025-12-20 08:41:11'),
(4, 1, 'export_format', 'excel', '2025-12-20 08:41:11', '2025-12-20 08:41:11');

-- --------------------------------------------------------

--
-- Structure for view `customer_balance_history`
--
DROP TABLE IF EXISTS `customer_balance_history`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `customer_balance_history`  AS SELECT `c`.`id` AS `customer_id`, `c`.`name` AS `customer_name`, cast(`ut`.`transaction_date` as date) AS `date`, 'UDHAR' AS `type`, `ut`.`amount` AS `debit`, 0 AS `credit`, `ut`.`description` AS `remarks`, `ut`.`bill_no` AS `reference` FROM (`customers` `c` join `udhar_transactions` `ut` on(`c`.`id` = `ut`.`customer_id`))union all select `c`.`id` AS `customer_id`,`c`.`name` AS `customer_name`,cast(`p`.`payment_date` as date) AS `date`,'PAYMENT' AS `type`,0 AS `debit`,`p`.`amount` AS `credit`,`p`.`notes` AS `remarks`,`p`.`reference_no` AS `reference` from (`customers` `c` join `payments` `p` on(`c`.`id` = `p`.`customer_id`)) order by `customer_id`,`date` desc  ;

-- --------------------------------------------------------

--
-- Structure for view `daily_summary`
--
DROP TABLE IF EXISTS `daily_summary`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `daily_summary`  AS SELECT cast(`ut`.`transaction_date` as date) AS `report_date`, count(distinct `ut`.`id`) AS `total_bills`, count(distinct `ut`.`customer_id`) AS `unique_customers`, sum(`ut`.`amount`) AS `total_udhar`, sum(`ut`.`total_amount`) AS `total_sales`, sum(`ut`.`cgst_amount` + `ut`.`sgst_amount` + `ut`.`igst_amount`) AS `total_tax`, sum(`p`.`amount`) AS `total_payments`, sum(`p`.`amount`) - sum(`ut`.`amount`) AS `net_balance` FROM (`udhar_transactions` `ut` left join `payments` `p` on(cast(`p`.`payment_date` as date) = cast(`ut`.`transaction_date` as date))) GROUP BY cast(`ut`.`transaction_date` as date) ORDER BY cast(`ut`.`transaction_date` as date) DESC ;

-- --------------------------------------------------------

--
-- Structure for view `item_performance`
--
DROP TABLE IF EXISTS `item_performance`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `item_performance`  AS SELECT `i`.`id` AS `id`, `i`.`item_name` AS `item_name`, `i`.`hsn_code` AS `hsn_code`, `i`.`unit` AS `unit`, count(distinct `ui`.`udhar_id`) AS `bill_count`, sum(`ui`.`quantity`) AS `total_quantity`, sum(`ui`.`total_amount`) AS `total_sales`, avg(`ui`.`unit_price`) AS `avg_price`, max(`ui`.`unit_price`) AS `max_price`, min(`ui`.`unit_price`) AS `min_price`, max(`ut`.`transaction_date`) AS `last_sale_date`, count(distinct `ut`.`customer_id`) AS `unique_customers` FROM ((`items` `i` join `udhar_items` `ui` on(`i`.`id` = `ui`.`item_id`)) join `udhar_transactions` `ut` on(`ui`.`udhar_id` = `ut`.`id`)) GROUP BY `i`.`id`, `i`.`item_name`, `i`.`hsn_code`, `i`.`unit` ORDER BY sum(`ui`.`total_amount`) DESC ;

-- --------------------------------------------------------

--
-- Structure for view `payment_summary`
--
DROP TABLE IF EXISTS `payment_summary`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `payment_summary`  AS SELECT `p`.`id` AS `id`, `p`.`payment_date` AS `payment_date`, `p`.`customer_id` AS `customer_id`, `p`.`customer_name` AS `customer_name`, `p`.`amount` AS `amount`, `p`.`payment_mode` AS `payment_mode`, `p`.`reference_no` AS `reference_no`, `p`.`is_allocated` AS `is_allocated`, `p`.`allocated_amount` AS `allocated_amount`, `p`.`remaining_amount` AS `remaining_amount`, `p`.`notes` AS `notes`, `c`.`balance` AS `customer_balance`, `c`.`total_udhar` AS `total_udhar`, `c`.`total_paid` AS `total_paid` FROM (`payments` `p` join `customers` `c` on(`p`.`customer_id` = `c`.`id`)) ;

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
-- Indexes for table `user_settings`
--
ALTER TABLE `user_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_setting` (`user_id`,`setting_key`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `items`
--
ALTER TABLE `items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `payment_allocations`
--
ALTER TABLE `payment_allocations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `udhar_items`
--
ALTER TABLE `udhar_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `udhar_transactions`
--
ALTER TABLE `udhar_transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

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
-- Constraints for table `user_settings`
--
ALTER TABLE `user_settings`
  ADD CONSTRAINT `user_settings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
