<?php
// File: smart-udhar-system/dashboard.php

require_once 'config/database.php';
requireLogin();

// Get current user
$user = getCurrentUser();
$conn = getDBConnection();

// Get dashboard statistics
$stats = [];

// Total customers
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM customers WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$stats['total_customers'] = $result->fetch_assoc()['total'];
$stmt->close();

// Total udhar
$stmt = $conn->prepare("SELECT SUM(amount) as total FROM udhar_transactions WHERE customer_id IN (SELECT id FROM customers WHERE user_id = ?)");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$stats['total_udhar'] = $result->fetch_assoc()['total'] ?? 0;
$stmt->close();

// Total received
$stmt = $conn->prepare("SELECT SUM(amount) as total FROM payments WHERE customer_id IN (SELECT id FROM customers WHERE user_id = ?)");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$stats['total_received'] = $result->fetch_assoc()['total'] ?? 0;
$stmt->close();

// Pending amount
$stats['pending_amount'] = $stats['total_udhar'] - $stats['total_received'];

// Today's udhar
$today = date('Y-m-d');
$stmt = $conn->prepare("SELECT SUM(amount) as total FROM udhar_transactions WHERE customer_id IN (SELECT id FROM customers WHERE user_id = ?) AND transaction_date = ?");
$stmt->bind_param("is", $_SESSION['user_id'], $today);
$stmt->execute();
$result = $stmt->get_result();
$stats['today_udhar'] = $result->fetch_assoc()['total'] ?? 0;
$stmt->close();

// Today's collection
$stmt = $conn->prepare("SELECT SUM(amount) as total FROM payments WHERE customer_id IN (SELECT id FROM customers WHERE user_id = ?) AND payment_date = ?");
$stmt->bind_param("is", $_SESSION['user_id'], $today);
$stmt->execute();
$result = $stmt->get_result();
$stats['today_collection'] = $result->fetch_assoc()['total'] ?? 0;
$stmt->close();

// Recent udhar transactions
$stmt = $conn->prepare("
    SELECT ut.*, c.name as customer_name 
    FROM udhar_transactions ut 
    JOIN customers c ON ut.customer_id = c.id 
    WHERE c.user_id = ? 
    ORDER BY ut.created_at DESC 
    LIMIT 5
");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$recent_transactions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Recent payments
$stmt = $conn->prepare("
    SELECT p.*, c.name as customer_name 
    FROM payments p 
    JOIN customers c ON p.customer_id = c.id 
    WHERE c.user_id = ? 
    ORDER BY p.created_at DESC 
    LIMIT 5
");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$recent_payments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Top customers with highest balance
$stmt = $conn->prepare("
    SELECT name, mobile, balance 
    FROM customers 
    WHERE user_id = ? AND balance > 0 
    ORDER BY balance DESC 
    LIMIT 5
");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$top_customers = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Smart Udhar System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
</head>

<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-header-content">
                <h4><i class="bi bi-wallet2"></i> Smart Udhar</h4>
                <div class="shop-name">
                    <?php echo htmlspecialchars($_SESSION['shop_name']); ?>
                </div>
            </div>
            <button class="sidebar-toggle-btn" id="sidebarToggle">
                <i class="bi bi-chevron-left"></i>
            </button>
        </div>

        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link active" href="dashboard.php">
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
                    <i class="bi bi-people-fill"></i> Items
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
                <a class="nav-link" href="reminders.php">
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
        <!-- Floating Toggle Button (visible when sidebar is closed) -->
        <button class="floating-toggle-btn" id="floatingToggle">
            <i class="bi bi-chevron-right"></i>
        </button>

        <!-- Top Navbar -->
        <nav class="navbar navbar-expand-lg">
            <div class="container-fluid">
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

        <!-- Welcome Message -->
        <div class="welcome-card">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h3>Welcome back, <?php echo htmlspecialchars($_SESSION['full_name']); ?>!</h3>
                    <p class="mb-0">Here's what's happening with your business today.</p>
                </div>
                <div class="col-md-4 text-end">
                    <span class="badge bg-light text-dark">
                        <i class="bi bi-calendar-check"></i> <?php echo date('F j, Y'); ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row">
            <div class="col-md-3 col-sm-6">
                <div class="card stat-card border-primary">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <div class="stat-value text-primary">
                                    ₹<?php echo number_format($stats['pending_amount'], 2); ?>
                                </div>
                                <div class="stat-label">Pending Amount</div>
                            </div>
                            <div>
                                <i class="bi bi-cash-coin text-primary"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6">
                <div class="card stat-card border-success">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <div class="stat-value text-success">
                                    ₹<?php echo number_format($stats['total_received'], 2); ?>
                                </div>
                                <div class="stat-label">Total Received</div>
                            </div>
                            <div>
                                <i class="bi bi-cash-stack text-success"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6">
                <div class="card stat-card border-info">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <div class="stat-value text-info">
                                    <?php echo number_format($stats['total_customers']); ?>
                                </div>
                                <div class="stat-label">Total Customers</div>
                            </div>
                            <div>
                                <i class="bi bi-people-fill text-info"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6">
                <div class="card stat-card border-warning">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <div class="stat-value text-warning">
                                    ₹<?php echo number_format($stats['today_collection'], 2); ?>
                                </div>
                                <div class="stat-label">Today's Collection</div>
                            </div>
                            <div>
                                <i class="bi bi-wallet2 text-warning"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Recent Udhar Transactions -->
            <div class="col-lg-6">
                <div class="recent-table">
                    <h5><i class="bi bi-clock-history"></i> Recent Udhar Transactions</h5>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Customer</th>
                                    <th>Amount</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($recent_transactions)): ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">
                                            No recent transactions
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($recent_transactions as $transaction): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($transaction['customer_name']); ?></td>
                                            <td class="fw-bold">₹<?php echo number_format($transaction['amount'], 2); ?></td>
                                            <td><?php echo date('d M Y', strtotime($transaction['transaction_date'])); ?></td>
                                            <td>
                                                <?php
                                                $status_class = '';
                                                switch ($transaction['status']) {
                                                    case 'paid':
                                                        $status_class = 'badge-paid';
                                                        break;
                                                    case 'partially_paid':
                                                        $status_class = 'badge-partial';
                                                        break;
                                                    default:
                                                        $status_class = 'badge-pending';
                                                }
                                                ?>
                                                <span class="status-badge <?php echo $status_class; ?>">
                                                    <?php echo ucfirst(str_replace('_', ' ', $transaction['status'])); ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-end">
                        <a href="udhar.php" class="btn btn-sm btn-outline-primary">
                            View All <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Recent Payments -->
            <div class="col-lg-6">
                <div class="recent-table">
                    <h5><i class="bi bi-cash-coin"></i> Recent Payments</h5>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Customer</th>
                                    <th>Amount</th>
                                    <th>Date</th>
                                    <th>Mode</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($recent_payments)): ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">
                                            No recent payments
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($recent_payments as $payment): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($payment['customer_name']); ?></td>
                                            <td class="fw-bold text-success">₹<?php echo number_format($payment['amount'], 2); ?></td>
                                            <td><?php echo date('d M Y', strtotime($payment['payment_date'])); ?></td>
                                            <td>
                                                <span class="badge bg-light text-dark">
                                                    <?php echo ucfirst($payment['payment_mode']); ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-end">
                        <a href="payments.php" class="btn btn-sm btn-outline-primary">
                            View All <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Customers -->
        <div class="row">
            <div class="col-lg-6">
                <div class="recent-table">
                    <h5><i class="bi bi-trophy"></i> Top Customers (Highest Balance)</h5>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Customer Name</th>
                                    <th>Mobile</th>
                                    <th>Balance</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($top_customers)): ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">
                                            No customers with balance
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($top_customers as $index => $customer): ?>
                                        <tr>
                                            <td><?php echo $index + 1; ?></td>
                                            <td><?php echo htmlspecialchars($customer['name']); ?></td>
                                            <td><?php echo htmlspecialchars($customer['mobile']); ?></td>
                                            <td class="fw-bold text-danger">₹<?php echo number_format($customer['balance'], 2); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="col-lg-6">
                <div class="recent-table">
                    <h5><i class="bi bi-lightning-charge"></i> Quick Actions</h5>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <a href="customers.php?action=add" class="btn btn-outline-primary w-100 py-3">
                                <i class="bi bi-person-plus-fill fs-4"></i><br>
                                Add New Customer
                            </a>
                        </div>
                        <div class="col-md-6">
                            <a href="udhar.php?action=add" class="btn btn-outline-success w-100 py-3">
                                <i class="bi bi-plus-circle-fill fs-4"></i><br>
                                New Udhar Entry
                            </a>
                        </div>
                        <div class="col-md-6">
                            <a href="payments.php?action=add" class="btn btn-outline-info w-100 py-3">
                                <i class="bi bi-cash-coin fs-4"></i><br>
                                Receive Payment
                            </a>
                        </div>
                        <div class="col-md-6">
                            <a href="reports.php" class="btn btn-outline-warning w-100 py-3">
                                <i class="bi bi-file-earmark-bar-graph fs-4"></i><br>
                                Generate Reports
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/dashboard.js"></script>
</body>

</html>