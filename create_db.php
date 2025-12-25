<?php
// Connect without database
$conn = new mysqli('127.0.0.1', 'root', '', '', 3307);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "CREATE DATABASE IF NOT EXISTS smart_udhar_system";

if ($conn->query($sql)) {
    echo "Database created successfully.";
} else {
    echo "Error creating database: " . $conn->error;
}

$conn->close();
?>