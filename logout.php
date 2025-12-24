<?php
// File: smart-udhar-system/logout.php

require_once 'config/database.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['user_id'])) {
    $conn = getDBConnection();
    $user_id = $_SESSION['user_id'];
    $ip_address = $_SERVER['REMOTE_ADDR'];

    // Update the logout_at timestamp
    $update_sql = "UPDATE users SET logout_at = NOW() WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("i", $user_id);
    $update_stmt->execute();
    $update_stmt->close();

    $log_sql = "INSERT INTO user_logs (user_id, activity, ip_address) VALUES (?, 'logout', ?)";
    $log_stmt = $conn->prepare($log_sql);
    $log_stmt->bind_param("is", $user_id, $ip_address);
    $log_stmt->execute();
    $log_stmt->close();
    $conn->close();
}


// Unset all of the session variables
$_SESSION = array();

// Destroy the session.
session_destroy();

// Redirect to login page
header('Location: index.php');
exit();
