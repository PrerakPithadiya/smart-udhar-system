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

--
-- Indexes for dumped tables
--

--
-- Indexes for table `payment_allocations`
--
ALTER TABLE `payment_allocations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `payment_id` (`payment_id`),
  ADD KEY `udhar_transaction_id` (`udhar_transaction_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `payment_allocations`
--
ALTER TABLE `payment_allocations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `payment_allocations`
--
ALTER TABLE `payment_allocations`
  ADD CONSTRAINT `payment_allocations_ibfk_1` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payment_allocations_ibfk_2` FOREIGN KEY (`udhar_transaction_id`) REFERENCES `udhar_transactions` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
