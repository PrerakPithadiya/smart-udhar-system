<?php
require_once 'core/database.php';

$conn = getDBConnection();

$result = $conn->query("SHOW TABLES LIKE 'customers'");

if ($result->num_rows > 0) {
    echo "Customers table exists.";
} else {
    echo "Customers table does not exist.";
}

$conn->close();
?>