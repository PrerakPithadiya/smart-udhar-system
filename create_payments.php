<?php
require_once 'core/database.php';

$conn = getDBConnection();

$sql = "CREATE TABLE `payments` (
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

ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_payment_customer` (`customer_id`),
  ADD KEY `idx_payment_date` (`payment_date`);

ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE;

INSERT INTO `payments` (`id`, `customer_id`, `payment_date`, `amount`, `payment_mode`, `reference_no`, `notes`, `created_at`, `customer_name`, `remaining_amount`, `allocated_amount`, `is_allocated`, `allocation_date`, `updated_at`) VALUES
(1, 1, '2025-12-21', 300.00, 'cash', NULL, 'Partial payment', '2025-12-21 11:00:00', 'Rajesh Kumar', 0.00, 300.00, 1, '2025-12-21', '2025-12-21 11:00:00'),
(2, 2, '2025-12-20', 600.00, 'upi', 'UPI123', 'Full payment', '2025-12-20 14:30:00', 'Priya Sharma', 0.00, 600.00, 1, '2025-12-20', '2025-12-20 14:30:00'),
(3, 3, '2025-12-21', 750.00, 'bank_transfer', 'BT456', 'Complete settlement', '2025-12-21 10:15:00', 'Amit Patel', 0.00, 750.00, 1, '2025-12-21', '2025-12-21 10:15:00'),
(4, 4, '2025-12-19', 800.00, 'cash', NULL, 'Payment received', '2025-12-19 16:20:00', 'Sneha Reddy', 0.00, 800.00, 1, '2025-12-19', '2025-12-19 16:20:00'),
(5, 5, '2025-12-21', 500.00, 'cheque', 'CH789', 'Cheque payment', '2025-12-21 09:45:00', 'Vikram Singh', 0.00, 500.00, 1, '2025-12-21', '2025-12-21 09:45:00'),
(6, 1, '2025-12-20', 200.00, 'cash', NULL, 'Additional payment', '2025-12-20 15:30:00', 'Rajesh Kumar', 0.00, 200.00, 1, '2025-12-20', '2025-12-20 15:30:00'),
(7, 2, '2025-12-19', 400.00, 'upi', 'UPI456', 'Partial payment', '2025-12-19 13:00:00', 'Priya Sharma', 0.00, 400.00, 1, '2025-12-19', '2025-12-19 13:00:00'),
(8, 3, '2025-12-21', 500.00, 'cash', NULL, 'Advance payment', '2025-12-21 14:00:00', 'Amit Patel', 0.00, 500.00, 1, '2025-12-21', '2025-12-21 14:00:00'),
(9, 4, '2025-12-18', 200.00, 'bank_transfer', 'BT789', 'Partial settlement', '2025-12-18 10:45:00', 'Sneha Reddy', 0.00, 200.00, 1, '2025-12-18', '2025-12-18 10:45:00'),
(10, 5, '2025-12-21', 300.00, 'cash', NULL, 'Payment towards udhar', '2025-12-21 16:30:00', 'Vikram Singh', 0.00, 300.00, 1, '2025-12-21', '2025-12-21 16:30:00');";

if ($conn->multi_query($sql)) {
    echo "Payments table created successfully.";
    do {
        if ($result = $conn->store_result()) {
            $result->free();
        }
    } while ($conn->more_results() && $conn->next_result());
} else {
    echo "Error: " . $conn->error;
}

$conn->close();
?>