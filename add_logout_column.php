<?php
require_once 'core/database.php';

$conn = getDBConnection();

// Check if the logout_at column already exists
$check_column_sql = "SHOW COLUMNS FROM users LIKE 'logout_at'";
$result = $conn->query($check_column_sql);

if ($result->num_rows == 0) {
    // Column doesn't exist, so add it
    $alter_sql = "ALTER TABLE users ADD COLUMN logout_at DATETIME DEFAULT NULL AFTER last_login";
    
    if ($conn->query($alter_sql) === TRUE) {
        echo "Column 'logout_at' added successfully to the users table.\n";
    } else {
        echo "Error adding column: " . $conn->error . "\n";
    }
} else {
    echo "Column 'logout_at' already exists in the users table.\n";
}

$conn->close();
?>