<?php
require_once 'config/database.php';

$conn = getDBConnection();

$sql = "ALTER TABLE users
ADD COLUMN logout_at DATETIME DEFAULT NULL AFTER last_login,
ADD COLUMN last_login_at DATETIME DEFAULT NULL AFTER logout_at,
CHANGE last_login last_login_at DATETIME DEFAULT NULL;
";

if ($conn->multi_query($sql)) {
    echo "Table 'users' altered successfully.";
} else {
    echo "Error altering table: " . $conn->error;
}

$conn->close();
?>
