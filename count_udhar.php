<?php
require_once 'core/database.php';

$conn = getDBConnection();

$result = $conn->query("SELECT COUNT(*) as count FROM udhar_transactions");

if ($result) {
    $row = $result->fetch_assoc();
    echo "Udhar transactions count: " . $row['count'];
} else {
    echo "Error: " . $conn->error;
}

$conn->close();
?>