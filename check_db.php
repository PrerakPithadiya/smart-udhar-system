<?php
require_once 'config/database.php';
$conn = getDBConnection();
$result = $conn->query("SELECT COUNT(*) as count FROM customers");
$row = $result->fetch_assoc();
echo "Total customers in DB: " . $row['count'] . "\n";

$result = $conn->query("SELECT * FROM users LIMIT 1");
$user = $result->fetch_assoc();
if ($user) {
    echo "First user ID: " . $user['id'] . "\n";
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM customers WHERE user_id = ?");
    $stmt->bind_param("i", $user['id']);
    $stmt->execute();
    $res = $stmt->get_result();
    $r = $res->fetch_assoc();
    echo "Customers for first user: " . $r['count'] . "\n";
}
?>