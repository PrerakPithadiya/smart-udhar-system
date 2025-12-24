<?php
// File: smart-udhar-system/index.php

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include the central database configuration and functions
require_once 'config/database.php';

// Get the database connection
$conn = getDBConnection();

// Check if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}

$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Input validation
    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password';
    } else {
        // Prepare SQL statement to prevent SQL injection
        $sql = "SELECT id, username, password, full_name, shop_name FROM users WHERE username = ? AND status = 'active'";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $user = null;

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (!password_verify($password, $user['password'])) {
                $error = 'Invalid password!';
                $user = null;
            }
        } else {
            $error = 'Invalid username or user not found!';
        }
        
        if ($user) {
            // Login successful
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['shop_name'] = $user['shop_name'];
            
            // Update last login
            $update_sql = "UPDATE users SET last_login_at = NOW() WHERE id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("i", $user['id']);
            $update_stmt->execute();
            $update_stmt->close();

            // Log user activity
            $log_sql = "INSERT INTO user_logs (user_id, activity, ip_address) VALUES (?, 'login', ?)";
            $log_stmt = $conn->prepare($log_sql);
            $ip_address = $_SERVER['REMOTE_ADDR'];
            $log_stmt->bind_param("is", $user['id'], $ip_address);
            $log_stmt->execute();
            $log_stmt->close();

            // Redirect to dashboard
            header('Location: dashboard.php');
            exit();
        }
        
        $stmt->close();
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Smart Udhar System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .login-container {
            max-width: 400px;
            width: 100%;
            margin: 0 auto;
        }
        .login-card {
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        .login-header {
            background: #2c3e50;
            color: white;
            padding: 25px;
            text-align: center;
        }
        .login-body {
            padding: 30px;
            background: white;
        }
        .prefilled-info {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            margin-top: 15px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="login-card">
                <div class="login-header">
                    <h3><i class="bi bi-wallet2"></i> Smart Udhar System</h3>
                    <p class="mb-0">Digital Credit Management</p>
                </div>
                <div class="login-body">
                    <h4 class="text-center mb-4">Login to Your Account</h4>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($error); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <input type="hidden" name="login" value="1">
                        <div class="mb-3">
                            <label for="username" class="form-label">
                                <i class="bi bi-person-fill"></i> Username
                            </label>
                            <input type="text" 
                                   class="form-control" 
                                   id="username" 
                                   name="username" 
                                   placeholder="Enter username" 
                                   value="admin"
                                   required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">
                                <i class="bi bi-lock-fill"></i> Password
                            </label>
                            <input type="password" 
                                   class="form-control" 
                                   id="password" 
                                   name="password" 
                                   placeholder="Enter password" 
                                   value="admin123"
                                   required>
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="showPassword">
                            <label class="form-check-label" for="showPassword">
                                Show Password
                            </label>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-box-arrow-in-right"></i> Login
                            </button>
                        </div>
                    </form>
                    
                    <div class="text-center mt-3">
                        <a href="register.php">Don't have an account? Create one</a>
                    </div>
                    
                    <div class="prefilled-info">
                        <div class="text-center">
                            <strong>Test Credentials:</strong><br>
                            Username: <code>admin</code><br>
                            Password: <code>admin123</code>
                        </div>
                    </div>
                    
                    <div class="text-center mt-4">
                        <small class="text-muted">
                            &copy; <?php echo date('Y'); ?> Smart Udhar System
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Show/Hide Password
        document.getElementById('showPassword').addEventListener('change', function() {
            const passwordInput = document.getElementById('password');
            passwordInput.type = this.checked ? 'text' : 'password';
        });
        
        // Auto focus on username field
        document.getElementById('username').focus();
    </script>
</body>
</html>