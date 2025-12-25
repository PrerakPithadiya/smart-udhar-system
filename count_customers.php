<?php
require_once 'core/database.php';

$conn = getDBConnection();

$result = $conn->query("SELECT COUNT(*) as count FROM customers");

if ($result) {
    $row = $result->fetch_assoc();
    echo "Customers count: " . $row['count'];
} else {
    echo "Error: " . $conn->error;
}

$conn->close();
?>