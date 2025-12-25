<?php
require_once 'core/database.php';

$conn = getDBConnection();

$sql = "CREATE TABLE `udhar_transactions` (
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

ALTER TABLE `udhar_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_udhar_customer` (`customer_id`),
  ADD KEY `idx_udhar_transaction_date` (`transaction_date`),
  ADD KEY `idx_udhar_status` (`status`);

ALTER TABLE `udhar_transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

ALTER TABLE `udhar_transactions`
  ADD CONSTRAINT `udhar_transactions_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE;

INSERT INTO `udhar_transactions` (`id`, `customer_id`, `transaction_date`, `description`, `amount`, `due_date`, `notes`, `status`, `remaining_amount`, `created_at`, `bill_no`, `total_amount`, `cgst_amount`, `sgst_amount`, `igst_amount`, `grand_total`, `discount`, `discount_type`, `round_off`, `bill_notes`, `print_count`) VALUES
(1, 1, '2025-12-20', 'Rice 5kg, Sugar 2kg', 500.00, '2025-12-27', 'First purchase', 'partially_paid', 200.00, '2025-12-20 02:30:00', 'BILL-001', 500.00, 22.50, 22.50, 0.00, 545.00, 0.00, 'fixed', 0.00, NULL, 1),
(2, 2, '2025-12-19', 'Wheat flour 10kg', 300.00, '2025-12-26', NULL, 'paid', 0.00, '2025-12-19 14:20:00', 'BILL-002', 300.00, 13.50, 13.50, 0.00, 327.00, 0.00, 'fixed', 0.00, NULL, 0),
(3, 3, '2025-12-21', 'Pulses mix 5kg', 750.00, '2025-12-28', 'Bulk purchase', 'pending', 750.00, '2025-12-21 10:15:00', 'BILL-003', 750.00, 33.75, 33.75, 0.00, 817.50, 0.00, 'fixed', 0.00, NULL, 0),
(4, 4, '2025-12-18', 'Spices set', 400.00, '2025-12-25', NULL, 'paid', 0.00, '2025-12-18 16:45:00', 'BILL-004', 400.00, 18.00, 18.00, 0.00, 436.00, 0.00, 'fixed', 0.00, NULL, 0),
(5, 5, '2025-12-21', 'Tea leaves 1kg, Coffee 500g', 600.00, '2025-12-28', NULL, 'partially_paid', 100.00, '2025-12-21 09:30:00', 'BILL-005', 600.00, 27.00, 27.00, 0.00, 654.00, 0.00, 'fixed', 0.00, NULL, 0),
(6, 1, '2025-12-20', 'Oil 2L, Ghee 1kg', 450.00, '2025-12-27', NULL, 'pending', 450.00, '2025-12-20 11:00:00', 'BILL-006', 450.00, 20.25, 20.25, 0.00, 490.50, 0.00, 'fixed', 0.00, NULL, 0),
(7, 2, '2025-12-19', 'Milk powder 2kg', 280.00, '2025-12-26', NULL, 'paid', 0.00, '2025-12-19 13:15:00', 'BILL-007', 280.00, 12.60, 12.60, 0.00, 305.20, 0.00, 'fixed', 0.00, NULL, 0),
(8, 3, '2025-12-21', 'Dry fruits 2kg', 1200.00, '2025-12-28', 'Premium quality', 'pending', 1200.00, '2025-12-21 14:20:00', 'BILL-008', 1200.00, 54.00, 54.00, 0.00, 1308.00, 0.00, 'fixed', 0.00, NULL, 0),
(9, 4, '2025-12-18', 'Honey 2 jars', 320.00, '2025-12-25', NULL, 'paid', 0.00, '2025-12-18 10:30:00', 'BILL-009', 320.00, 14.40, 14.40, 0.00, 348.80, 0.00, 'fixed', 0.00, NULL, 0),
(10, 5, '2025-12-21', 'Biscuits pack', 150.00, '2025-12-28', NULL, 'pending', 150.00, '2025-12-21 16:45:00', 'BILL-010', 150.00, 6.75, 6.75, 0.00, 163.50, 0.00, 'fixed', 0.00, NULL, 0),
(11, 1, '2025-12-20', 'Soap bars 6pcs', 120.00, '2025-12-27', NULL, 'partially_paid', 60.00, '2025-12-20 15:20:00', 'BILL-011', 120.00, 5.40, 5.40, 0.00, 130.80, 0.00, 'fixed', 0.00, NULL, 0),
(12, 2, '2025-12-19', 'Detergent 2kg', 180.00, '2025-12-26', NULL, 'paid', 0.00, '2025-12-19 12:10:00', 'BILL-012', 180.00, 8.10, 8.10, 0.00, 196.20, 0.00, 'fixed', 0.00, NULL, 0),
(13, 3, '2025-12-21', 'Cleaning supplies', 350.00, '2025-12-28', NULL, 'pending', 350.00, '2025-12-21 11:55:00', 'BILL-013', 350.00, 15.75, 15.75, 0.00, 381.50, 0.00, 'fixed', 0.00, NULL, 0);";

if ($conn->multi_query($sql)) {
    echo "Udhar transactions table created successfully.";
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