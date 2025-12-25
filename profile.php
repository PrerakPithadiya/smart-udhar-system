<?php
require_once 'config/database.php';
requireLogin();

$user = getCurrentUser();

if (!$user) {
    header('Location: index.php');
    exit();
}

// Handle account deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_account'])) {
    $conn = getDBConnection();
    $user_id = $_SESSION['user_id'];
    // Delete user from users table (CASCADE will remove user_logs)
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();
    $conn->close();
    // Destroy session
    $_SESSION = array();
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
    }
    session_destroy();
    header('Location: index.php?account_deleted=1');
    exit();
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $full_name = sanitizeInput($_POST['full_name']);
    $email = sanitizeInput($_POST['email']);
    $shop_name = sanitizeInput($_POST['shop_name']);
    $mobile = sanitizeInput($_POST['mobile']);
    $address = sanitizeInput($_POST['address']);

    $conn = getDBConnection();
    $stmt = $conn->prepare("UPDATE users SET full_name = ?, email = ?, shop_name = ?, mobile = ?, address = ? WHERE id = ?");
    $stmt->bind_param("sssssi", $full_name, $email, $shop_name, $mobile, $address, $_SESSION['user_id']);

    if ($stmt->execute()) {
        setMessage('Profile updated successfully!', 'success');
        // Refresh user data
        $user = getCurrentUser();
    } else {
        setMessage('Error updating profile!', 'danger');
    }
    
    header('Location: profile.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Smart Udhar System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <div class="sidebar-header-content">
                    <h3 class="text-light mb-0"><i class="bi bi-cash-coin"></i> Smart Udhar</h3>
                </div>
                <button class="sidebar-toggle-btn" id="sidebarToggle">
                    <i class="bi bi-chevron-left"></i>
                </button>
            </div>
            
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link" href="dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="customers.php"><i class="bi bi-people"></i> Customers</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="udhar.php"><i class="bi bi-cash-stack"></i> Udhar</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="items.php"><i class="bi bi-box"></i> Items</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="payments.php"><i class="bi bi-currency-rupee"></i> Payments</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="reports.php"><i class="bi bi-file-earmark-bar-graph"></i> Reports</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="reminders.php"><i class="bi bi-bell"></i> Reminders</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="profile.php"><i class="bi bi-person-circle"></i> Profile</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="settings.php"><i class="bi bi-gear-fill"></i> Settings</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
                </li>
            </ul>
            
            <div class="sidebar-footer text-center mt-4">
                <small class="text-light">© 2025 Smart Udhar System</small>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <nav class="navbar navbar-expand-lg navbar-light bg-light">
                <div class="container-fluid">
                    <div class="d-flex align-items-center">
                        <button class="btn" id="sidebarToggleTop">
                            <i class="bi bi-list"></i>
                        </button>
                        <span class="navbar-brand mb-0 h1">Profile Management</span>
                    </div>
                    
                    <div class="d-flex align-items-center">
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="userMenu" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($user['username']); ?>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person"></i> Profile</a></li>
                                <li><a class="dropdown-item" href="settings.php"><i class="bi bi-gear"></i> Settings</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </nav>

            <div class="container-fluid mt-4">
                <?php displayMessage(); ?>
                
                <div class="row">
                    <div class="col-lg-8 mx-auto">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="bi bi-person-lines-fill"></i> Profile Information</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Username</label>
                                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" readonly>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Email</label>
                                            <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($user['email']); ?>">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Mobile</label>
                                            <input type="text" class="form-control" name="mobile" value="<?php echo htmlspecialchars($user['mobile']); ?>">
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Shop Name</label>
                                        <input type="text" class="form-control" name="shop_name" value="<?php echo htmlspecialchars($user['shop_name']); ?>">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Address</label>
                                        <textarea class="form-control" name="address" rows="3"><?php echo htmlspecialchars($user['address']); ?></textarea>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between">
                                        <button type="submit" name="update_profile" class="btn btn-primary">
                                            <i class="bi bi-save"></i> Update Profile
                                        </button>
                                        <form method="POST" action="" onsubmit="return confirm('Are you sure you want to delete your account? This action cannot be undone.');" style="display:inline-block; margin-left:10px;">
                                            <button type="submit" name="delete_account" class="btn btn-danger">
                                                <i class="bi bi-trash"></i> Delete Account
                                            </button>
                                        </form>
                                    </div>
                                </form>
                            </div>
                        </div>
                        
                        <!-- Change Password Section -->
                        <div class="card mt-4">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="bi bi-key"></i> Change Password</h5>
                            </div>
                            <div class="card-body">
                                <form id="changePasswordForm">
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Current Password <span class="text-danger">*</span></label>
                                            <input type="password" class="form-control" id="current_password" required>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">New Password <span class="text-danger">*</span></label>
                                            <input type="password" class="form-control" id="new_password" required>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Confirm New Password <span class="text-danger">*</span></label>
                                            <input type="password" class="form-control" id="confirm_password" required>
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex justify-content-end">
                                        <button type="submit" class="btn btn-warning">
                                            <i class="bi bi-key"></i> Change Password
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/dashboard.js"></script>
    
    <script>
        document.getElementById('changePasswordForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const currentPassword = document.getElementById('current_password').value;
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (newPassword !== confirmPassword) {
                alert('New password and confirm password do not match!');
                return;
            }
            
            if (newPassword.length < 6) {
                alert('New password must be at least 6 characters long!');
                return;
            }
            
            // In a real application, you would send an AJAX request to update the password
            // For now, showing a success message
            alert('Password change functionality would be implemented here. In a real application, this would send a request to the server.');
        });
    </script>
</body>
</html>