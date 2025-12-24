<?php
// File: smart-udhar-system/reminders.php

require_once 'config/database.php';
requireLogin();

$conn = getDBConnection();

// Handle actions
$action = isset($_GET['action']) ? $_GET['action'] : 'list';

// Get reminder settings
$reminder_days = isset($_GET['days']) ? intval($_GET['days']) : 7;

// Query for reminders
$query = "
    SELECT 
        ut.id,
        ut.bill_no,
        ut.amount,
        ut.due_date,
        ut.transaction_date,
        ut.status,
        c.name as customer_name,
        c.mobile as customer_mobile,
        DATEDIFF(ut.due_date, CURDATE()) as days_remaining
    FROM udhar_transactions ut
    JOIN customers c ON ut.customer_id = c.id
    WHERE c.user_id = ? 
    AND ut.due_date IS NOT NULL 
    AND ut.status IN ('pending', 'partially_paid')
    AND ut.due_date <= DATE_ADD(CURDATE(), INTERVAL ? DAY)
    ORDER BY ut.due_date ASC
";

$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $_SESSION['user_id'], $reminder_days);
$stmt->execute();
$result = $stmt->get_result();
$reminders = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Count overdue
$overdue_count = 0;
$upcoming_count = 0;
foreach ($reminders as $reminder) {
    if ($reminder['days_remaining'] < 0) {
        $overdue_count++;
    } else {
        $upcoming_count++;
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reminders - Smart Udhar System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2c3e50;
            --success-color: #27ae60;
            --warning-color: #f39c12;
            --danger-color: #e74c3c;
            --light-bg: #f8f9fa;
        }

        body {
            background-color: var(--light-bg);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .sidebar {
            background-color: var(--secondary-color);
            color: white;
            min-height: 100vh;
            position: fixed;
            width: 250px;
            transition: all 0.3s;
            z-index: 1000;
        }

        .sidebar-header {
            padding: 20px;
            background-color: var(--primary-color);
        }

        .sidebar-header h4 {
            margin: 0;
            color: white;
        }

        .sidebar-header .shop-name {
            font-size: 0.9rem;
            opacity: 0.9;
        }

        .sidebar .nav-link {
            color: #b3b3b3;
            padding: 12px 20px;
            border-left: 3px solid transparent;
            transition: all 0.3s;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: white;
            background-color: rgba(255, 255, 255, 0.1);
            border-left-color: var(--primary-color);
        }

        .sidebar .nav-link i {
            width: 20px;
            margin-right: 10px;
        }

        .main-content {
            margin-left: 250px;
            padding: 20px;
            transition: all 0.3s;
        }

        @media (max-width: 768px) {
            .sidebar {
                margin-left: -250px;
            }

            .sidebar.active {
                margin-left: 0;
            }

            .main-content {
                margin-left: 0;
            }

            .main-content.active {
                margin-left: 250px;
            }
        }

        .reminder-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            border-left: 4px solid;
        }

        .reminder-overdue {
            border-left-color: var(--danger-color);
        }

        .reminder-upcoming {
            border-left-color: var(--warning-color);
        }

        .reminder-header {
            padding: 15px 20px;
            background: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
        }

        .reminder-body {
            padding: 20px;
        }

        .reminder-status {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .status-overdue {
            background-color: #f8d7da;
            color: #721c24;
        }

        .status-upcoming {
            background-color: #fff3cd;
            color: #856404;
        }

        .reminder-stats {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            flex: 1;
            text-align: center;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: bold;
            color: var(--primary-color);
        }

        .stat-label {
            color: #7f8c8d;
            font-weight: 600;
        }
    </style>
</head>

<body>
    <?php include 'includes/header.php'; ?>

    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h4><i class="bi bi-wallet2"></i> Smart Udhar</h4>
            <div class="shop-name">
                <?php echo htmlspecialchars($_SESSION['shop_name']); ?>
            </div>
        </div>

        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link" href="dashboard.php">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="customers.php">
                    <i class="bi bi-people-fill"></i> Customers
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="items.php">
                    <i class="bi bi-box-seam"></i> Items
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="udhar.php">
                    <i class="bi bi-credit-card"></i> Udhar Entry
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="payments.php">
                    <i class="bi bi-cash-stack"></i> Payments
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="reports.php">
                    <i class="bi bi-bar-chart-fill"></i> Reports
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="reminders.php">
                    <i class="bi bi-bell-fill"></i> Reminders
                </a>
            </li>
            <li class="nav-item">
                <div class="dropdown-divider"></div>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="profile.php">
                    <i class="bi bi-person-circle"></i> Profile
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="settings.php">
                    <i class="bi bi-gear-fill"></i> Settings
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-danger" href="logout.php">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </a>
            </li>
        </ul>

        <div class="sidebar-footer text-center mt-4">
            <small class="text-muted">
                Version 1.0<br>
                &copy; <?php echo date('Y'); ?>
            </small>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Navbar -->
        <nav class="navbar navbar-expand-lg">
            <div class="container-fluid">
                <button class="mobile-menu-btn" id="sidebarToggle">
                    <i class="bi bi-list"></i>
                </button>

                <div class="d-flex align-items-center ms-auto">
                    <div class="me-3">
                        <small class="text-muted">Welcome,</small>
                        <strong><?php echo htmlspecialchars($_SESSION['full_name']); ?></strong>
                    </div>
                    <div class="dropdown">
                        <button class="btn btn-outline-primary dropdown-toggle" type="button"
                            data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="profile.php">
                                    <i class="bi bi-person"></i> Profile
                                </a></li>
                            <li><a class="dropdown-item" href="settings.php">
                                    <i class="bi bi-gear"></i> Settings
                                </a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item text-danger" href="logout.php">
                                    <i class="bi bi-box-arrow-right"></i> Logout
                                </a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </nav>


        <div class="container-fluid">
            <div class="row">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-12">
                            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                                <h1 class="h2">
                                    <i class="bi bi-bell"></i> Reminders
                                </h1>
                                <div class="btn-toolbar mb-2 mb-md-0">
                                    <form method="GET" class="d-inline">
                                        <select name="days" class="form-select" onchange="this.form.submit()">
                                            <option value="1" <?php echo $reminder_days == 1 ? 'selected' : ''; ?>>Due Today</option>
                                            <option value="3" <?php echo $reminder_days == 3 ? 'selected' : ''; ?>>Due in 3 days</option>
                                            <option value="7" <?php echo $reminder_days == 7 ? 'selected' : ''; ?>>Due in 7 days</option>
                                            <option value="14" <?php echo $reminder_days == 14 ? 'selected' : ''; ?>>Due in 14 days</option>
                                            <option value="30" <?php echo $reminder_days == 30 ? 'selected' : ''; ?>>Due in 30 days</option>
                                        </select>
                                    </form>
                                </div>
                            </div>

                            <?php displayMessage(); ?>

                            <!-- Reminder Stats -->
                            <div class="reminder-stats">
                                <div class="stat-card">
                                    <div class="stat-value text-danger"><?php echo $overdue_count; ?></div>
                                    <div class="stat-label">Overdue Bills</div>
                                </div>
                                <div class="stat-card">
                                    <div class="stat-value text-warning"><?php echo $upcoming_count; ?></div>
                                    <div class="stat-label">Upcoming Due</div>
                                </div>
                                <div class="stat-card">
                                    <div class="stat-value"><?php echo count($reminders); ?></div>
                                    <div class="stat-label">Total Reminders</div>
                                </div>
                            </div>

                            <!-- Reminders List -->
                            <?php if (empty($reminders)): ?>
                                <div class="text-center py-5">
                                    <i class="bi bi-bell-slash display-1 text-muted"></i>
                                    <h4 class="mt-3 text-muted">No reminders found</h4>
                                    <p class="text-muted">All bills are up to date!</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($reminders as $reminder): ?>
                                    <div class="reminder-card <?php echo $reminder['days_remaining'] < 0 ? 'reminder-overdue' : 'reminder-upcoming'; ?>">
                                        <div class="reminder-header">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <h5 class="mb-0">
                                                    <strong><?php echo htmlspecialchars($reminder['bill_no']); ?></strong>
                                                    - <?php echo htmlspecialchars($reminder['customer_name']); ?>
                                                </h5>
                                                <span class="reminder-status <?php echo $reminder['days_remaining'] < 0 ? 'status-overdue' : 'status-upcoming'; ?>">
                                                    <?php if ($reminder['days_remaining'] < 0): ?>
                                                        Overdue by <?php echo abs($reminder['days_remaining']); ?> days
                                                    <?php else: ?>
                                                        Due in <?php echo $reminder['days_remaining']; ?> days
                                                    <?php endif; ?>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="reminder-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <p class="mb-2">
                                                        <strong>Amount:</strong> ₹<?php echo number_format($reminder['amount'], 2); ?>
                                                    </p>
                                                    <p class="mb-2">
                                                        <strong>Due Date:</strong> <?php echo date('d M Y', strtotime($reminder['due_date'])); ?>
                                                    </p>
                                                    <p class="mb-0">
                                                        <strong>Bill Date:</strong> <?php echo date('d M Y', strtotime($reminder['transaction_date'])); ?>
                                                    </p>
                                                </div>
                                                <div class="col-md-6">
                                                    <p class="mb-2">
                                                        <strong>Customer:</strong> <?php echo htmlspecialchars($reminder['customer_name']); ?>
                                                    </p>
                                                    <?php if (!empty($reminder['customer_mobile'])): ?>
                                                        <p class="mb-2">
                                                            <strong>Mobile:</strong> <?php echo htmlspecialchars($reminder['customer_mobile']); ?>
                                                        </p>
                                                    <?php endif; ?>
                                                    <p class="mb-0">
                                                        <strong>Status:</strong>
                                                        <span class="badge bg-<?php echo $reminder['status'] == 'partially_paid' ? 'warning' : 'danger'; ?>">
                                                            <?php echo ucfirst(str_replace('_', ' ', $reminder['status'])); ?>
                                                        </span>
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="mt-3">
                                                <a href="udhar.php?action=view&id=<?php echo $reminder['id']; ?>" class="btn btn-outline-primary btn-sm">
                                                    <i class="bi bi-eye"></i> View Details
                                                </a>
                                                <a href="payments.php?customer_id=<?php echo $reminder['customer_id']; ?>" class="btn btn-outline-success btn-sm">
                                                    <i class="bi bi-cash"></i> Record Payment
                                                </a>
                                                <?php if (!empty($reminder['customer_mobile'])): ?>
                                                    <a href="tel:<?php echo htmlspecialchars($reminder['customer_mobile']); ?>" class="btn btn-outline-info btn-sm">
                                                        <i class="bi bi-telephone"></i> Call Customer
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Sidebar toggle
        document.getElementById("sidebarToggle").addEventListener("click", function() {
            const sidebar = document.querySelector(".sidebar");
            const mainContent = document.querySelector(".main-content");

            if (window.innerWidth <= 768) {
                sidebar.classList.toggle("active");
            } else {
                sidebar.classList.toggle("closed");
                if (sidebar.classList.contains("closed")) {
                    mainContent.style.marginLeft = "0";
                } else {
                    mainContent.style.marginLeft = "250px";
                }
            }
        });

        // Auto-hide sidebar on mobile when clicking outside
        document.addEventListener("click", function(event) {
            const sidebar = document.querySelector(".sidebar");
            const toggleBtn = document.getElementById("sidebarToggle");

            if (
                window.innerWidth <= 768 &&
                !sidebar.contains(event.target) &&
                !toggleBtn.contains(event.target) &&
                sidebar.classList.contains("active")
            ) {
                sidebar.classList.remove("active");
            }
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>