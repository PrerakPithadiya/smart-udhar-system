<?php
// Include the database configuration
// We need to be careful about paths. Assuming we run this from project root or core.
// Let's put this file in the root directory for easy execution.

require_once 'core/database.php';

echo "Testing Database Connection...\n";
echo "Host: " . DB_HOST . "\n";
echo "Port: " . DB_PORT . "\n";
echo "User: " . DB_USER . "\n";
echo "DB Name: " . DB_NAME . "\n";

try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

    if ($conn->connect_error) {
        echo "Connection Failed: " . $conn->connect_error . "\n";
    } else {
        echo "Connection Successful!\n";
        $conn->close();
    }
} catch (Exception $e) {
    echo "Exception Caught: " . $e->getMessage() . "\n";
}
