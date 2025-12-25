<?php
// File: smart-udhar-system/index.php

// Include database configuration and functions
require_once 'core/database.php';

// Get database connection
$conn = getDBConnection();

// Check if already logged in
redirectIfLoggedIn();

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

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // Verify password
            if (password_verify($password, $user['password'])) {
                // Login successful
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['shop_name'] = $user['shop_name'];

                // Update last login
                $update_sql = "UPDATE users SET last_login = NOW() WHERE id = ?";
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->bind_param("i", $user['id']);
                $update_stmt->execute();
                $update_stmt->close();

                // Redirect to dashboard
                header('Location: dashboard.php');
                exit();
            } else {
                $error = 'Invalid username or password!';
            }
        } else {
            $error = 'Invalid username or password!';
        }

        $stmt->close();
    }
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Smart Udhar System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #007bff;
            --primary-color-darker: #0069d9;
            --secondary-color: #6c757d;
            --background-color: #f4f7fc;
            --text-color: #495057;
            --card-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--background-color);
            color: var(--text-color);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
        }

        .main-container {
            display: flex;
            width: 100%;
            max-width: 1200px;
            min-height: 700px;
            background: #fff;
            border-radius: 20px;
            box-shadow: var(--card-shadow);
            overflow: hidden;
        }

        .branding-section {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: #fff;
            padding: 60px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            width: 50%;
        }

        .branding-section .logo-icon {
            font-size: 80px;
            margin-bottom: 20px;
        }

        .branding-section h1 {
            font-weight: 600;
            font-size: 2.5rem;
            margin-bottom: 15px;
        }

        .branding-section p {
            font-size: 1.1rem;
            max-width: 350px;
            line-height: 1.6;
        }

        .form-section {
            padding: 60px 50px;
            width: 50%;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .form-section h2 {
            font-weight: 600;
            font-size: 2rem;
            margin-bottom: 10px;
            color: #343a40;
        }

        .form-section .lead {
            font-size: 1.1rem;
            color: var(--secondary-color);
            margin-bottom: 30px;
        }

        .form-control {
            border-radius: 8px;
            padding: 12px 15px;
            border: 1px solid #ced4da;
            transition: border-color .15s ease-in-out, box-shadow .15s ease-in-out;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgb(0 123 255 / 25%);
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            border-radius: 8px;
            padding: 12px;
            font-weight: 500;
            transition: background-color .15s ease-in-out, border-color .15s ease-in-out;
        }

        .btn-primary:hover {
            background-color: var(--primary-color-darker);
            border-color: var(--primary-color-darker);
        }

        .form-check-label {
            user-select: none;
        }

        .footer-text {
            text-align: center;
            margin-top: 30px;
        }

        .footer-text a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
        }

        .footer-text a:hover {
            text-decoration: underline;
        }

        @media (max-width: 992px) {
            .main-container {
                flex-direction: column;
                min-height: auto;
            }

            .branding-section,
            .form-section {
                width: 100%;
                padding: 40px;
            }
        }
    </style>
</head>

<body>
    <div class="main-container">
        <div class="branding-section">
            <i class="bi bi-wallet2 logo-icon"></i>
            <h1>Smart Udhar System</h1>
            <p>Your trusted partner for seamless digital credit management. Track dues, manage payments, and grow your business with confidence.</p>
        </div>
        <div class="form-section">
            <h2>Welcome Back!</h2>
            <p class="lead">Login to access your dashboard.</p>

            <?php if ($error): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="index.php">
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" name="username" placeholder="e.g., john.doe" required>
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" placeholder="••••••••" required>
                </div>

                <div class="mb-4 form-check">
                    <input type="checkbox" class="form-check-input" id="showPassword">
                    <label class="form-check-label" for="showPassword">Show Password</label>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="bi bi-box-arrow-in-right"></i> Login
                    </button>
                </div>
            </form>

            <div class="footer-text">
                <p>
                    Don't have an account? <a href="register.php">Sign up now</a>
                </p>
                <small class="text-muted">&copy; <?php echo date('Y'); ?> Smart Udhar System</small>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('showPassword').addEventListener('change', function() {
            document.getElementById('password').type = this.checked ? 'text' : 'password';
        });
        document.getElementById('username').focus();
    </script>
</body>

</html>