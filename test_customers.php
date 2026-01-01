<?php
// File: test_customers.php
require_once 'config/database.php';
requireLogin();

$conn = getDBConnection();
$stmt = $conn->prepare("SELECT COUNT(*) as count, GROUP_CONCAT(name) as names FROM customers WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

echo "<h2>Customer Test</h2>";
echo "<p>Total customers: " . $data['count'] . "</p>";
if ($data['count'] > 0) {
    echo "<p>Customer names: " . $data['names'] . "</p>";
}
echo "<p>User ID: " . $_SESSION['user_id'] . "</p>";

// Test the API endpoint
echo "<h3>API Test</h3>";
echo "<p><a href='/api/search_customers.php?q=test' target='_blank'>Test API</a></p>";
