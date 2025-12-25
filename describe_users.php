<?php
require_once 'core/database.php';

$conn = getDBConnection();

$result = $conn->query("DESCRIBE users");

if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo $row['Field'] . " - " . $row['Type'] . " - " . $row['Key'] . "\n";
    }
} else {
    echo "Error: " . $conn->error;
}

$conn->close();
?>