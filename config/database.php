<?php
// File: smart-udhar-system/config/database.php

// Database configuration
define('DB_HOST', '127.0.0.1');
define('DB_PORT', 3307);
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'smart_udhar_db');
define('SITE_URL', 'http://localhost/smart-udhar-system-2/');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Updated database connection function
function getDBConnection()
{
    static $conn = null;

    if ($conn === null) {
        try {
            $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

            if ($conn->connect_error) {
                throw new Exception("Connection failed: " . $conn->connect_error);
            }

            $conn->set_charset("utf8mb4");
        } catch (Exception $e) {
            error_log("Database connection error: " . $e->getMessage());
            die("Database connection error. Please check your database configuration or contact support.");
        }
    }

    return $conn;
}

// Sanitize input function - Updated to avoid double-encoding
function sanitizeInput($data)
{
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }

    if ($data === null)
        return '';

    $data = trim($data);
    // Only strip slashes if magic quotes was a thing or if specifically needed
    // In modern PHP, we usually don't need this if using prepared statements
    // But we'll keep it for legacy compatibility if needed
    // $data = stripslashes($data); 

    return $data;
}

// Check if user is logged in
function isLoggedIn()
{
    return isset($_SESSION['user_id']) && isset($_SESSION['username']);
}

// Redirect if not logged in
function requireLogin()
{
    if (!isLoggedIn()) {
        $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
        header('Location: ' . SITE_URL . 'index.php');
        exit();
    }
}

// Redirect if logged in
function redirectIfLoggedIn()
{
    if (isLoggedIn()) {
        header('Location: ' . SITE_URL . 'dashboard.php');
        exit();
    }
}

// Logout function
function logout()
{
    $_SESSION = array();

    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }

    session_destroy();
    header('Location: ' . SITE_URL . 'index.php');
    exit();
}

// Get current user info
function getCurrentUser()
{
    if (!isLoggedIn()) {
        return null;
    }

    $conn = getDBConnection();
    $user_id = $_SESSION['user_id'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    return $result->fetch_assoc();
}

// Password hash function
function hashPassword($password)
{
    return password_hash($password, PASSWORD_BCRYPT);
}

// Verify password
function verifyPassword($password, $hashedPassword)
{
    return password_verify($password, $hashedPassword);
}

// Generate CSRF token
function generateCSRFToken()
{
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Verify CSRF token
function verifyCSRFToken($token)
{
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Display themed Antigravity messages
function displayMessage()
{
    if (isset($_SESSION['message'])) {
        $message = $_SESSION['message'];
        unset($_SESSION['message']);

        $type = $message['type'] ?? 'info';
        $text = $message['text'] ?? '';

        $bg = 'bg-white/80';
        $border = 'border-slate-200';
        $text_color = 'text-slate-700';
        $icon = 'solar:info-circle-bold-duotone';
        $icon_color = 'text-indigo-500';

        if ($type === 'success') {
            $border = 'border-emerald-200';
            $icon = 'solar:check-circle-bold-duotone';
            $icon_color = 'text-emerald-500';
        } elseif ($type === 'danger' || $type === 'error') {
            $border = 'border-rose-200';
            $icon = 'solar:danger-bold-duotone';
            $icon_color = 'text-rose-500';
        } elseif ($type === 'warning') {
            $border = 'border-amber-200';
            $icon = 'solar:bell-bing-bold-duotone';
            $icon_color = 'text-amber-500';
        }

        if (!empty($text)) {
            echo '<div class="message-pulse flex items-center gap-4 p-5 mb-8 rounded-3xl border-2 ' . $bg . ' ' . $border . ' backdrop-blur-xl shadow-xl shadow-slate-200/50 animate-in fade-in slide-in-from-top-4 duration-500">';
            echo '  <div class="w-12 h-12 rounded-2xl bg-white flex items-center justify-center text-3xl shadow-sm ' . $icon_color . '">';
            echo '    <iconify-icon icon="' . $icon . '"></iconify-icon>';
            echo '  </div>';
            echo '  <div class="flex-grow">';
            echo '    <p class="text-xs font-black uppercase tracking-widest text-slate-400 mb-0.5">' . strtoupper($type) . ' PROTOCOL</p>';
            echo '    <p class="text-sm font-bold ' . $text_color . '">' . $text . '</p>';
            echo '  </div>';
            echo '  <button onclick="this.parentElement.remove()" class="w-10 h-10 rounded-xl hover:bg-slate-50 flex items-center justify-center text-slate-400 transition-colors">';
            echo '    <iconify-icon icon="solar:close-circle-bold" width="20"></iconify-icon>';
            echo '  </button>';
            echo '</div>';
        }
    }
}

// Set message
function setMessage($text, $type = 'info')
{
    $_SESSION['message'] = [
        'text' => $text,
        'type' => $type
    ];
}
