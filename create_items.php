<?php
require_once 'core/database.php';

$conn = getDBConnection();

$sql = "CREATE TABLE `items` (
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

ALTER TABLE `items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_item_user` (`user_id`,`item_name`),
  ADD KEY `idx_items_user` (`user_id`),
  ADD KEY `idx_items_name` (`item_name`),
  ADD KEY `idx_items_category` (`category`);

ALTER TABLE `items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

ALTER TABLE `items`
  ADD CONSTRAINT `items_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

INSERT INTO `items` (`id`, `user_id`, `item_name`, `item_code`, `hsn_code`, `price`, `cgst_rate`, `sgst_rate`, `igst_rate`, `unit`, `description`, `status`, `created_at`, `updated_at`, `category`) VALUES
(1, 1, 'Rice', 'RICE001', '1001', 50.00, 2.50, 2.50, 0.00, 'KG', 'Premium quality rice', 'active', '2025-12-19 05:30:00', '2025-12-19 05:30:00', 'Grains'),
(2, 1, 'Sugar', 'SUGAR001', '1701', 40.00, 2.50, 2.50, 0.00, 'KG', 'White sugar', 'active', '2025-12-19 05:30:00', '2025-12-19 05:30:00', 'Groceries'),
(3, 1, 'Wheat Flour', 'FLOUR001', '1101', 30.00, 2.50, 2.50, 0.00, 'KG', 'Whole wheat flour', 'active', '2025-12-19 05:30:00', '2025-12-19 05:30:00', 'Grains'),
(4, 1, 'Pulses Mix', 'PULSE001', '0713', 80.00, 2.50, 2.50, 0.00, 'KG', 'Mixed pulses', 'active', '2025-12-19 05:30:00', '2025-12-19 05:30:00', 'Pulses'),
(5, 1, 'Tea Leaves', 'TEA001', '0902', 120.00, 2.50, 2.50, 0.00, 'KG', 'Premium tea leaves', 'active', '2025-12-19 05:30:00', '2025-12-19 05:30:00', 'Beverages'),
(6, 1, 'Coffee', 'COFFEE001', '0901', 200.00, 2.50, 2.50, 0.00, 'KG', 'Ground coffee', 'active', '2025-12-19 05:30:00', '2025-12-19 05:30:00', 'Beverages'),
(7, 1, 'Oil', 'OIL001', '1507', 120.00, 2.50, 2.50, 0.00, 'L', 'Cooking oil', 'active', '2025-12-19 05:30:00', '2025-12-19 05:30:00', 'Oils'),
(8, 1, 'Ghee', 'GHEE001', '0405', 400.00, 2.50, 2.50, 0.00, 'KG', 'Pure ghee', 'active', '2025-12-19 05:30:00', '2025-12-19 05:30:00', 'Dairy'),
(9, 1, 'Spices Set', 'SPICE001', '0910', 150.00, 2.50, 2.50, 0.00, 'SET', 'Basic spices set', 'active', '2025-12-19 05:30:00', '2025-12-19 05:30:00', 'Spices'),
(10, 1, 'Honey', 'HONEY001', '0409', 250.00, 2.50, 2.50, 0.00, 'KG', 'Natural honey', 'active', '2025-12-19 05:30:00', '2025-12-19 05:30:00', 'Sweeteners'),
(11, 1, 'Milk Powder', 'MILK001', '0402', 300.00, 2.50, 2.50, 0.00, 'KG', 'Full cream milk powder', 'active', '2025-12-19 05:30:00', '2025-12-19 05:30:00', 'Dairy'),
(12, 1, 'Biscuits', 'BISCUIT001', '1905', 60.00, 2.50, 2.50, 0.00, 'PACK', 'Cream biscuits', 'active', '2025-12-19 05:30:00', '2025-12-19 05:30:00', 'Snacks'),
(13, 1, 'Soap', 'SOAP001', '3401', 25.00, 9.00, 9.00, 0.00, 'PCS', 'Bathing soap', 'active', '2025-12-19 05:30:00', '2025-12-19 05:30:00', 'Personal Care'),
(14, 1, 'Detergent', 'DETERGENT001', '3402', 80.00, 9.00, 9.00, 0.00, 'KG', 'Washing detergent', 'active', '2025-12-19 05:30:00', '2025-12-19 05:30:00', 'Household'),
(15, 1, 'Dry Fruits', 'DRYFRUIT001', '0801', 500.00, 2.50, 2.50, 0.00, 'KG', 'Mixed dry fruits', 'active', '2025-12-19 05:30:00', '2025-12-19 05:30:00', 'Dry Fruits'),
(16, 1, 'Cleaning Supplies', 'CLEAN001', '3402', 100.00, 9.00, 9.00, 0.00, 'SET', 'Basic cleaning set', 'active', '2025-12-19 05:30:00', '2025-12-19 05:30:00', 'Household'),
(17, 1, 'Salt', 'SALT001', '2501', 15.00, 2.50, 2.50, 0.00, 'KG', 'Iodized salt', 'active', '2025-12-19 05:30:00', '2025-12-19 05:30:00', 'Groceries'),
(18, 1, 'Turmeric', 'TURMERIC001', '0910', 200.00, 2.50, 2.50, 0.00, 'KG', 'Pure turmeric powder', 'active', '2025-12-19 05:30:00', '2025-12-19 05:30:00', 'Spices'),
(19, 1, 'Chili Powder', 'CHILI001', '0910', 180.00, 2.50, 2.50, 0.00, 'KG', 'Red chili powder', 'active', '2025-12-19 05:30:00', '2025-12-19 05:30:00', 'Spices'),
(20, 1, 'Coriander', 'CORIANDER001', '0910', 120.00, 2.50, 2.50, 0.00, 'KG', 'Coriander powder', 'active', '2025-12-19 05:30:00', '2025-12-19 05:30:00', 'Spices'),
(21, 1, 'Cumin', 'CUMIN001', '0910', 150.00, 2.50, 2.50, 0.00, 'KG', 'Cumin seeds', 'active', '2025-12-19 05:30:00', '2025-12-19 05:30:00', 'Spices'),
(22, 1, 'Mustard Oil', 'MOIL001', '1507', 140.00, 2.50, 2.50, 0.00, 'L', 'Mustard cooking oil', 'active', '2025-12-19 05:30:00', '2025-12-19 05:30:00', 'Oils'),
(23, 1, 'Groundnut Oil', 'GOIL001', '1507', 160.00, 2.50, 2.50, 0.00, 'L', 'Groundnut oil', 'active', '2025-12-19 05:30:00', '2025-12-19 05:30:00', 'Oils'),
(24, 1, 'Jaggery', 'JAGGERY001', '1702', 60.00, 2.50, 2.50, 0.00, 'KG', 'Natural jaggery', 'active', '2025-12-19 05:30:00', '2025-12-19 05:30:00', 'Sweeteners'),
(25, 1, 'Pickles', 'PICKLE001', '2001', 80.00, 2.50, 2.50, 0.00, 'JAR', 'Mixed pickles', 'active', '2025-12-19 05:30:00', '2025-12-19 05:30:00', 'Pickles'),
(26, 1, 'Noodles', 'NOODLES001', '1902', 45.00, 2.50, 2.50, 0.00, 'PACK', 'Instant noodles', 'active', '2025-12-19 05:30:00', '2025-12-19 05:30:00', 'Snacks'),
(27, 1, 'Chocolates', 'CHOCO001', '1806', 100.00, 9.00, 9.00, 0.00, 'PACK', 'Milk chocolates', 'active', '2025-12-19 05:30:00', '2025-12-19 05:30:00', 'Confectionery'),
(28, 1, 'Toothpaste', 'TOOTHPASTE001', '3306', 50.00, 9.00, 9.00, 0.00, 'TUBE', 'Fluoride toothpaste', 'active', '2025-12-19 05:30:00', '2025-12-19 05:30:00', 'Personal Care'),
(29, 1, 'Shampoo', 'SHAMPOO001', '3305', 120.00, 9.00, 9.00, 0.00, 'BOTTLE', 'Herbal shampoo', 'active', '2025-12-19 05:30:00', '2025-12-19 05:30:00', 'Personal Care'),
(30, 1, 'Face Wash', 'FACEWASH001', '3304', 150.00, 9.00, 9.00, 0.00, 'TUBE', 'Gentle face wash', 'active', '2025-12-19 05:30:00', '2025-12-19 05:30:00', 'Personal Care'),
(31, 1, 'Deodorant', 'DEO001', '3307', 180.00, 9.00, 9.00, 0.00, 'CAN', 'Men\'s deodorant', 'active', '2025-12-19 05:30:00', '2025-12-19 05:30:00', 'Personal Care'),
(32, 1, 'Perfume', 'PERFUME001', '3303', 250.00, 9.00, 9.00, 0.00, 'BOTTLE', 'Designer perfume', 'active', '2025-12-19 05:30:00', '2025-12-19 05:30:00', 'Personal Care'),
(33, 1, 'Washing Powder', 'WASHPOWDER001', '3401', 120.00, 9.00, 9.00, 0.00, 'KG', 'Clothes washing powder', 'active', '2025-12-19 05:30:00', '2025-12-19 05:30:00', 'Household'),
(34, 1, 'Dish Soap', 'DISHSOAP001', '3401', 40.00, 9.00, 9.00, 0.00, 'BOTTLE', 'Dish washing liquid', 'active', '2025-12-19 05:30:00', '2025-12-19 05:30:00', 'Household'),
(35, 1, 'Air Freshener', 'AIRFRESH001', '3307', 90.00, 9.00, 9.00, 0.00, 'CAN', 'Room air freshener', 'active', '2025-12-19 05:30:00', '2025-12-19 05:30:00', 'Household');";

if ($conn->multi_query($sql)) {
    echo "Items table created successfully.";
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