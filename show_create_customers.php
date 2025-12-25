<?php
require_once 'core/database.php';

$conn = getDBConnection();

$result = $conn->query("SHOW CREATE TABLE customers");

if ($result) {
    $row = $result->fetch_assoc();
    echo $row['Create Table'];
} else {
    echo "Error: " . $conn->error;
}

$conn->close();
?>