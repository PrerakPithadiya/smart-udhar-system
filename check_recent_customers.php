<?php
require 'config/database.php';
$conn = getDBConnection();
$res = $conn->query('SELECT * FROM customers ORDER BY created_at DESC LIMIT 5');
while ($row = $res->fetch_assoc()) {
    echo "ID: " . $row['id'] . " | Name: " . $row['name'] . " | Created: " . $row['created_at'] . "\n";
}
?>