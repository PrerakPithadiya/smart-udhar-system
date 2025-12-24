<?php
// File: smart-udhar-system/fix_unit_column.php

require_once 'config/database.php';

$conn = getDBConnection();

echo "<h3>Fixing Database Issues...</h3>";

// 1. Add unit column to udhar_items table
$sql1 = "ALTER TABLE udhar_items ADD COLUMN IF NOT EXISTS unit VARCHAR(20) DEFAULT 'PCS' AFTER hsn_code";
if ($conn->query($sql1)) {
    echo "✓ Added unit column to udhar_items table<br>";
} else {
    echo "✗ Error adding unit column: " . $conn->error . "<br>";
}

// 2. Update existing records
$sql2 = "UPDATE udhar_items SET unit = 'PCS' WHERE unit IS NULL";
if ($conn->query($sql2)) {
    echo "✓ Updated existing records with default unit value<br>";
} else {
    echo "✗ Error updating records: " . $conn->error . "<br>";
}

// 3. Check and add missing columns to udhar_transactions
$columns_to_add = [
    "bill_no VARCHAR(50)",
    "total_amount DECIMAL(10,2) DEFAULT 0",
    "cgst_amount DECIMAL(10,2) DEFAULT 0",
    "sgst_amount DECIMAL(10,2) DEFAULT 0",
    "igst_amount DECIMAL(10,2) DEFAULT 0",
    "grand_total DECIMAL(10,2) DEFAULT 0",
    "discount DECIMAL(10,2) DEFAULT 0",
    "discount_type ENUM('percentage', 'fixed') DEFAULT 'fixed'",
    "round_off DECIMAL(10,2) DEFAULT 0",
    "bill_notes TEXT",
    "print_count INT DEFAULT 0"
];

foreach ($columns_to_add as $column_def) {
    $column_name = explode(' ', $column_def)[0];

    // Check if column exists
    $check_sql = "SHOW COLUMNS FROM udhar_transactions LIKE '$column_name'";
    $result = $conn->query($check_sql);

    if ($result->num_rows == 0) {
        $add_sql = "ALTER TABLE udhar_transactions ADD COLUMN $column_def";
        if ($conn->query($add_sql)) {
            echo "✓ Added column '$column_name' to udhar_transactions<br>";
        } else {
            echo "✗ Error adding column '$column_name': " . $conn->error . "<br>";
        }
    } else {
        echo "✓ Column '$column_name' already exists<br>";
    }
}

echo "<h3 style='color: green;'>Database fixes completed!</h3>";
echo "<p>You can now <a href='udhar.php'>go back to Udhar Management</a> and create bills.</p>";

$conn->close();
