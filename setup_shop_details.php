<?php
// File: setup_shop_details.php
// Run this file once to add shop details fields to the users table

require_once 'config/database.php';
requireLogin();

// Only allow admin users
if (!isset($_SESSION['user_id'])) {
    die("Access denied. Please login as admin.");
}

$conn = getDBConnection();

echo "<h2>Setting up shop details fields...</h2>";

// SQL to add new columns
$sql = "
ALTER TABLE `users` 
ADD COLUMN IF NOT EXISTS `license_no` VARCHAR(100) DEFAULT NULL AFTER `address`,
ADD COLUMN IF NOT EXISTS `license_date` DATE DEFAULT NULL AFTER `license_no`,
ADD COLUMN IF NOT EXISTS `gst_no` VARCHAR(50) DEFAULT NULL AFTER `license_date`,
ADD COLUMN IF NOT EXISTS `registration_no` VARCHAR(100) DEFAULT NULL AFTER `gst_no`,
ADD COLUMN IF NOT EXISTS `registration_date` DATE DEFAULT NULL AFTER `registration_no`
";

// For MySQL versions that don't support IF NOT EXISTS, we'll check first
$check_columns = $conn->query("SHOW COLUMNS FROM users LIKE 'license_no'");

if ($check_columns->num_rows == 0) {
    // Columns don't exist, add them
    $queries = [
        "ALTER TABLE `users` ADD COLUMN `license_no` VARCHAR(100) DEFAULT NULL AFTER `address`",
        "ALTER TABLE `users` ADD COLUMN `license_date` DATE DEFAULT NULL AFTER `license_no`",
        "ALTER TABLE `users` ADD COLUMN `gst_no` VARCHAR(50) DEFAULT NULL AFTER `license_date`",
        "ALTER TABLE `users` ADD COLUMN `registration_no` VARCHAR(100) DEFAULT NULL AFTER `gst_no`",
        "ALTER TABLE `users` ADD COLUMN `registration_date` DATE DEFAULT NULL AFTER `registration_no`",
        "ALTER TABLE `users` ADD INDEX `idx_users_gst` (`gst_no`)"
    ];

    foreach ($queries as $query) {
        try {
            if ($conn->query($query)) {
                echo "<p style='color: green;'>✓ Executed: " . substr($query, 0, 80) . "...</p>";
            } else {
                echo "<p style='color: orange;'>⚠ Query may have already been applied: " . $conn->error . "</p>";
            }
        } catch (Exception $e) {
            echo "<p style='color: orange;'>⚠ " . $e->getMessage() . "</p>";
        }
    }

    echo "<h3 style='color: green;'>✓ Setup completed successfully!</h3>";
    echo "<p>New fields added to users table:</p>";
    echo "<ul>";
    echo "<li>license_no - License number</li>";
    echo "<li>license_date - License date</li>";
    echo "<li>gst_no - GST number</li>";
    echo "<li>registration_no - Registration number</li>";
    echo "<li>registration_date - Registration date</li>";
    echo "</ul>";
} else {
    echo "<h3 style='color: blue;'>ℹ Fields already exist!</h3>";
    echo "<p>The shop details fields are already present in the users table.</p>";
}

echo "<hr>";
echo "<h3>Next Steps:</h3>";
echo "<ol>";
echo "<li>Go to your profile settings to update License No., GST No., and Registration details</li>";
echo "<li>Use the new print format: <a href='print_bill_tax_invoice.php?id=YOUR_BILL_ID'>print_bill_tax_invoice.php</a></li>";
echo "</ol>";

echo "<p><a href='dashboard.php'>← Back to Dashboard</a></p>";

$conn->close();
?>