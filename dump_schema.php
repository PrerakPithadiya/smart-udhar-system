<?php
require 'config/database.php';
$conn = getDBConnection();
$res = $conn->query('DESCRIBE customers');
while ($row = $res->fetch_assoc()) {
    print_r($row);
}
?>