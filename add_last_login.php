<?php
require_once 'config/database.php';

$conn = getDBConnection();

$sql = "ALTER TABLE users ADD COLUMN last_login DATETIME DEFAULT NULL;";

if ($conn->query($sql)) {
    echo "Column 'last_login' added successfully.";
} else {
    echo "Error adding column: " . $conn->error;
}

$conn->close();
?>