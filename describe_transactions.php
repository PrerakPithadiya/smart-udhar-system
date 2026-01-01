<?php
require_once 'config/database.php';
$conn = getDBConnection();
$result = $conn->query("DESCRIBE udhar_transactions");
echo "Column Name | Type |\n";
echo "---|---|\n";
while ($row = $result->fetch_assoc()) {
    echo $row['Field'] . " | " . $row['Type'] . "\n";
}
?>