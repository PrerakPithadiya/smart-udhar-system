<?php
require_once 'config/database.php';

$conn = getDBConnection();

$sql = "CREATE TABLE `users` (
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

ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

INSERT INTO `users` (`id`, `username`, `password`, `full_name`, `email`, `shop_name`, `mobile`, `address`, `role`, `status`, `last_login`, `created_at`) VALUES
(1, 'admin', 'admin123', 'Admin User', 'admin@myshop.com', 'My Shop', '9876543210', NULL, 'admin', 'active', '2025-12-24 00:51:53', '2025-12-19 05:17:09'),
(2, 'staff1', 'staff123', 'Staff Member', 'staff@myshop.com', 'My Shop', '9876543211', 'Shop Address', 'staff', 'active', '2025-12-20 10:00:00', '2025-12-19 23:30:00');";

if ($conn->multi_query($sql)) {
  echo "Users table created successfully.";
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