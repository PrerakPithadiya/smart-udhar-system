<?php
require_once 'config/database.php';

$conn = getDBConnection();

$result = $conn->query("SHOW TABLES");

if ($result) {
    while ($row = $result->fetch_array()) {
        echo $row[0] . "\n";
    }
} else {
    echo "Error: " . $conn->error;
}

$conn->close();
?>