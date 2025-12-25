<?php
require_once 'core/database.php';

$conn = getDBConnection();

$result = $conn->query("SHOW TABLES LIKE 'users'");

if ($result->num_rows > 0) {
    echo "Users table exists.";
} else {
    echo "Users table does not exist.";
}

$conn->close();
?>