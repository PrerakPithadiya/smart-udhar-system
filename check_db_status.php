<?php
require 'config/database.php';
$conn = getDBConnection();
$res = $conn->query('SELECT * FROM customers ORDER BY id DESC LIMIT 5');
while ($row = $res->fetch_assoc()) {
    echo "ID: " . $row['id'] . " | Name: " . $row['name'] . " | UserID: " . $row['user_id'] . " | Created: " . $row['created_at'] . "\n";
}
echo "Current Session UserID: " . ($_SESSION['user_id'] ?? 'NOT SET') . "\n";
?>