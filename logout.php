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

    // Check if user exists
    $check_sql = "SELECT id FROM users WHERE id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $user_id);
    $check_stmt->execute();
    $check_stmt->store_result();
    if ($check_stmt->num_rows > 0) {
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
    } else {
        error_log("Logout attempted for non-existent user_id: $user_id");
    }
    $check_stmt->close();
    $conn->close();
}


// Unset all of the session variables
$_SESSION = array();

// Destroy the session.
session_destroy();

// Redirect to login page
header('Location: index.php');
exit();
