<?php
require_once 'core/database.php';

$conn = getDBConnection();

$sql = "CREATE TABLE `customers` (
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

ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_customer_user` (`user_id`),
  ADD KEY `idx_customer_status` (`status`);

ALTER TABLE `customers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

ALTER TABLE `customers`
  ADD CONSTRAINT `customers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

INSERT INTO `customers` (`id`, `user_id`, `name`, `mobile`, `email`, `address`, `total_udhar`, `total_paid`, `balance`, `last_transaction_date`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 'Rajesh Kumar', '9876543210', 'rajesh@example.com', '123 Main Street,', 5000.00, 3000.00, 2000.00, '2025-12-20', 'active', '2025-12-20 02:30:00', '2025-12-21 11:09:42'),
(2, 1, 'Priya Sharma', '9876543211', 'priya@example.com', '456 Park Road, Delhi', 3000.00, 6000.00, -3000.00, '2025-12-19', 'active', '2025-12-20 02:30:00', '2025-12-21 10:55:11'),
(3, 1, 'Amit Patel', '9876543212', 'amit@example.com', '789 Market Lane, Ahmedabad', 7500.00, 7500.00, 0.00, '2025-12-21', 'active', '2025-12-20 02:30:00', '2025-12-21 10:55:11'),
(4, 1, 'Sneha Reddy', '9876543213', 'sneha@example.com', '321 Garden Street, Hyderabad', 4000.00, 8000.00, -4000.00, '2025-12-18', 'active', '2025-12-20 02:30:00', '2025-12-21 10:55:11'),
(5, 1, 'Vikram Singh', '9876543214', 'vikram@example.com', '654 Temple Road, Jaipur', 6000.00, 5000.00, 1000.00, '2025-12-21', 'active', '2025-12-20 02:30:00', '2025-12-21 10:55:11');";

if ($conn->multi_query($sql)) {
    echo "Customers table created successfully.";
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