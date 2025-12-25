<?php
$conn = new mysqli('127.0.0.1', 'root', '', '', 3306);
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}
$conn->query('DROP DATABASE IF EXISTS smart_udhar_db');
$conn->query('CREATE DATABASE smart_udhar_db');
echo 'Database recreated as smart_udhar_db on port 3306';
$conn->close();
?>