<?php
require_once 'core/database.php';

$conn = getDBConnection();

$sql = file_get_contents('database/smart_udhar_system.sql');

if ($conn->multi_query($sql)) {
    echo "Database imported successfully.";
    // Consume all results
    do {
        if ($result = $conn->store_result()) {
            $result->free();
        }
    } while ($conn->more_results() && $conn->next_result());
} else {
    echo "Error importing database: " . $conn->error;
}

$conn->close();
?>