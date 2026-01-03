<?php
require 'config/database.php';
$conn = getDBConnection();
$res = $conn->query('SELECT * FROM customers WHERE id = 58');
if ($row = $res->fetch_assoc()) {
    echo "FOUND RECORD IN DATABASE:\n";
    print_r($row);
} else {
    echo "RECORD ID 58 NOT FOUND IN DATABASE.\n";
    $res2 = $conn->query('SELECT MAX(id) as max_id FROM customers');
    $row2 = $res2->fetch_assoc();
    echo "Maximum ID in table is: " . $row2['max_id'] . "\n";
}
?>