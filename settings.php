<?php
require_once 'config/database.php';
requireLogin();

$user = getCurrentUser();

if (!$user) {
    header('Location: index.php');
    exit();
}

// Get current user settings
$conn = getDBConnection();
$stmt = $conn->prepare("SELECT setting_key, setting_value FROM user_settings WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

$user_settings = [];
while ($row = $result->fetch_assoc()) {
    $user_settings[$row['setting_key']] = $row['setting_value'];
}

// Handle settings update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_settings'])) {
    $settings = [
        'default_report_type' => sanitizeInput($_POST['default_report_type']),
        'default_date_range' => sanitizeInput($_POST['default_date_range']),
        'chart_type' => sanitizeInput($_POST['chart_type']),
        'export_format' => sanitizeInput($_POST['export_format']),
        'print_format' => sanitizeInput($_POST['print_format']),
        'theme_preference' => sanitizeInput($_POST['theme_preference'])
    ];

    $conn = getDBConnection();
    
    foreach ($settings as $key => $value) {
        // Check if setting already exists
        $check_stmt = $conn->prepare("SELECT id FROM user_settings WHERE user_id = ? AND setting_key = ?");
        $check_stmt->bind_param("is", $_SESSION['user_id'], $key);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            // Update existing setting
            $update_stmt = $conn->prepare("UPDATE user_settings SET setting_value = ? WHERE user_id = ? AND setting_key = ?");
            $update_stmt->bind_param("sis", $value, $_SESSION['user_id'], $key);
            $update_stmt->execute();
        } else {
            // Insert new setting
            $insert_stmt = $conn->prepare("INSERT INTO user_settings (user_id, setting_key, setting_value) VALUES (?, ?, ?)");
            $insert_stmt->bind_param("iss", $_SESSION['user_id'], $key, $value);
            $insert_stmt->execute();
        }
    }
    
    setMessage('Settings updated successfully!', 'success');
    header('Location: settings.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Smart Udhar System</title>
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
                    <a class="nav-link" href="profile.php"><i class="bi bi-person-circle"></i> Profile</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="settings.php"><i class="bi bi-gear-fill"></i> Settings</a>
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
                        <span class="navbar-brand mb-0 h1">System Settings</span>
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
                    <div class="col-lg-10 mx-auto">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="bi bi-gear-wide-connected"></i> Application Settings</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="">
                                    <div class="row">
                                        <div class="col-md-6 mb-4">
                                            <h6 class="text-primary"><i class="bi bi-graph-up"></i> Report Settings</h6>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Default Report Type</label>
                                                <select class="form-select" name="default_report_type">
                                                    <option value="dashboard" <?php echo (isset($user_settings['default_report_type']) && $user_settings['default_report_type'] === 'dashboard') ? 'selected' : ''; ?>>Dashboard</option>
                                                    <option value="sales" <?php echo (isset($user_settings['default_report_type']) && $user_settings['default_report_type'] === 'sales') ? 'selected' : ''; ?>>Sales Report</option>
                                                    <option value="payments" <?php echo (isset($user_settings['default_report_type']) && $user_settings['default_report_type'] === 'payments') ? 'selected' : ''; ?>>Payments Report</option>
                                                    <option value="customers" <?php echo (isset($user_settings['default_report_type']) && $user_settings['default_report_type'] === 'customers') ? 'selected' : ''; ?>>Customers Report</option>
                                                </select>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Default Date Range</label>
                                                <select class="form-select" name="default_date_range">
                                                    <option value="today" <?php echo (isset($user_settings['default_date_range']) && $user_settings['default_date_range'] === 'today') ? 'selected' : ''; ?>>Today</option>
                                                    <option value="week" <?php echo (isset($user_settings['default_date_range']) && $user_settings['default_date_range'] === 'week') ? 'selected' : ''; ?>>This Week</option>
                                                    <option value="month" <?php echo (isset($user_settings['default_date_range']) && $user_settings['default_date_range'] === 'month') ? 'selected' : ''; ?>>This Month</option>
                                                    <option value="year" <?php echo (isset($user_settings['default_date_range']) && $user_settings['default_date_range'] === 'year') ? 'selected' : ''; ?>>This Year</option>
                                                    <option value="custom" <?php echo (isset($user_settings['default_date_range']) && $user_settings['default_date_range'] === 'custom') ? 'selected' : ''; ?>>Custom</option>
                                                </select>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Chart Type</label>
                                                <select class="form-select" name="chart_type">
                                                    <option value="bar" <?php echo (isset($user_settings['chart_type']) && $user_settings['chart_type'] === 'bar') ? 'selected' : ''; ?>>Bar Chart</option>
                                                    <option value="line" <?php echo (isset($user_settings['chart_type']) && $user_settings['chart_type'] === 'line') ? 'selected' : ''; ?>>Line Chart</option>
                                                    <option value="pie" <?php echo (isset($user_settings['chart_type']) && $user_settings['chart_type'] === 'pie') ? 'selected' : ''; ?>>Pie Chart</option>
                                                    <option value="area" <?php echo (isset($user_settings['chart_type']) && $user_settings['chart_type'] === 'area') ? 'selected' : ''; ?>>Area Chart</option>
                                                </select>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6 mb-4">
                                            <h6 class="text-primary"><i class="bi bi-printer"></i> Print & Export Settings</h6>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Export Format</label>
                                                <select class="form-select" name="export_format">
                                                    <option value="excel" <?php echo (isset($user_settings['export_format']) && $user_settings['export_format'] === 'excel') ? 'selected' : ''; ?>>Excel (.xlsx)</option>
                                                    <option value="pdf" <?php echo (isset($user_settings['export_format']) && $user_settings['export_format'] === 'pdf') ? 'selected' : ''; ?>>PDF (.pdf)</option>
                                                    <option value="csv" <?php echo (isset($user_settings['export_format']) && $user_settings['export_format'] === 'csv') ? 'selected' : ''; ?>>CSV (.csv)</option>
                                                    <option value="all" <?php echo (isset($user_settings['export_format']) && $user_settings['export_format'] === 'all') ? 'selected' : ''; ?>>All Formats</option>
                                                </select>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Print Format</label>
                                                <select class="form-select" name="print_format">
                                                    <option value="standard" <?php echo (isset($user_settings['print_format']) && $user_settings['print_format'] === 'standard') ? 'selected' : ''; ?>>Standard</option>
                                                    <option value="compact" <?php echo (isset($user_settings['print_format']) && $user_settings['print_format'] === 'compact') ? 'selected' : ''; ?>>Compact</option>
                                                    <option value="detailed" <?php echo (isset($user_settings['print_format']) && $user_settings['print_format'] === 'detailed') ? 'selected' : ''; ?>>Detailed</option>
                                                </select>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Theme Preference</label>
                                                <select class="form-select" name="theme_preference">
                                                    <option value="light" <?php echo (isset($user_settings['theme_preference']) && $user_settings['theme_preference'] === 'light') ? 'selected' : ''; ?>>Light</option>
                                                    <option value="dark" <?php echo (isset($user_settings['theme_preference']) && $user_settings['theme_preference'] === 'dark') ? 'selected' : ''; ?>>Dark</option>
                                                    <option value="auto" <?php echo (isset($user_settings['theme_preference']) && $user_settings['theme_preference'] === 'auto') ? 'selected' : ''; ?>>System Default</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <hr class="my-4">
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-4">
                                            <h6 class="text-primary"><i class="bi bi-currency-rupee"></i> Business Settings</h6>
                                            
                                            <div class="mb-3 form-check">
                                                <input type="checkbox" class="form-check-input" id="auto_calculate_tax" <?php echo (isset($user_settings['auto_calculate_tax']) && $user_settings['auto_calculate_tax'] === '1') ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="auto_calculate_tax">Auto Calculate Tax</label>
                                            </div>
                                            
                                            <div class="mb-3 form-check">
                                                <input type="checkbox" class="form-check-input" id="round_off_amounts" <?php echo (isset($user_settings['round_off_amounts']) && $user_settings['round_off_amounts'] === '1') ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="round_off_amounts">Round Off Amounts</label>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Default Discount Type</label>
                                                <select class="form-select" name="default_discount_type">
                                                    <option value="fixed" <?php echo (isset($user_settings['default_discount_type']) && $user_settings['default_discount_type'] === 'fixed') ? 'selected' : ''; ?>>Fixed Amount</option>
                                                    <option value="percentage" <?php echo (isset($user_settings['default_discount_type']) && $user_settings['default_discount_type'] === 'percentage') ? 'selected' : ''; ?>>Percentage</option>
                                                </select>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6 mb-4">
                                            <h6 class="text-primary"><i class="bi bi-bell"></i> Notification Settings</h6>
                                            
                                            <div class="mb-3 form-check">
                                                <input type="checkbox" class="form-check-input" id="email_notifications" <?php echo (isset($user_settings['email_notifications']) && $user_settings['email_notifications'] === '1') ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="email_notifications">Email Notifications</label>
                                            </div>
                                            
                                            <div class="mb-3 form-check">
                                                <input type="checkbox" class="form-check-input" id="sms_notifications" <?php echo (isset($user_settings['sms_notifications']) && $user_settings['sms_notifications'] === '1') ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="sms_notifications">SMS Notifications</label>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Reminder Days</label>
                                                <input type="number" class="form-control" name="reminder_days" value="<?php echo isset($user_settings['reminder_days']) ? htmlspecialchars($user_settings['reminder_days']) : '3'; ?>" min="1" max="30">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex justify-content-end">
                                        <button type="submit" name="update_settings" class="btn btn-primary">
                                            <i class="bi bi-save"></i> Save Settings
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        
                        <!-- System Information -->
                        <div class="card mt-4">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="bi bi-info-circle"></i> System Information</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Application Version:</strong> 1.0.0</p>
                                        <p><strong>Database:</strong> MySQL</p>
                                        <p><strong>PHP Version:</strong> <?php echo phpversion(); ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Server:</strong> <?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?></p>
                                        <p><strong>System:</strong> <?php echo php_uname('s') . ' ' . php_uname('r'); ?></p>
                                        <p><strong>Uptime:</strong> <?php echo floor((time() - $_SERVER['REQUEST_TIME']) / 60); ?> minutes</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/dashboard.js"></script>
</body>
</html>