<?php
require_once 'core/database.php';

$conn = getDBConnection();

// Hash the passwords
$admin_hash = password_hash('admin123', PASSWORD_DEFAULT);
$staff_hash = password_hash('staff123', PASSWORD_DEFAULT);

$sql = "UPDATE users SET password = ? WHERE username = 'admin';
UPDATE users SET password = ? WHERE username = 'staff1';";

$stmt = $conn->prepare("UPDATE users SET password = ? WHERE username = 'admin'");
$stmt->bind_param("s", $admin_hash);
$stmt->execute();

$stmt2 = $conn->prepare("UPDATE users SET password = ? WHERE username = 'staff1'");
$stmt2->bind_param("s", $staff_hash);
$stmt2->execute();

echo "Passwords hashed successfully.";

$conn->close();
?>