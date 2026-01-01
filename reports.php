<?php
// File: smart-udhar-system/reports.php

require_once 'config/database.php';
requireLogin();

$user = getCurrentUser();
$conn = getDBConnection();

// Get report filters
$report_type = isset($_GET['type']) ? sanitizeInput($_GET['type']) : 'summary';
$date_from = isset($_GET['date_from']) ? sanitizeInput($_GET['date_from']) : date('Y-m-01');
$date_to = isset($_GET['date_to']) ? sanitizeInput($_GET['date_to']) : date('Y-m-d');
$customer_filter = isset($_GET['customer']) ? intval($_GET['customer']) : 0;

// Get all customers for filter dropdown
$stmt = $conn->prepare("SELECT id, name FROM customers WHERE user_id = ? ORDER BY name ASC");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$all_customers = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Summary Report Data
if ($report_type == 'summary') {
    // Total statistics within date range
    $stmt = $conn->prepare("
        SELECT 
            COUNT(DISTINCT c.id) as total_customers,
            COALESCE(SUM(ut.amount), 0) as total_sales,
            COALESCE(SUM(p.amount), 0) as total_payments,
            (COALESCE(SUM(ut.amount), 0) - COALESCE(SUM(p.amount), 0)) as net_balance
        FROM customers c
        LEFT JOIN udhar_transactions ut ON c.id = ut.customer_id AND ut.transaction_date BETWEEN ? AND ?
        LEFT JOIN payments p ON c.id = p.customer_id AND p.payment_date BETWEEN ? AND ?
        WHERE c.user_id = ?
    ");
    $stmt->bind_param("ssssi", $date_from, $date_to, $date_from, $date_to, $_SESSION['user_id']);
    $stmt->execute();
    $summary_stats = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    // Customer-wise summary
    $where_customer = $customer_filter > 0 ? "AND c.id = $customer_filter" : "";
    $stmt = $conn->prepare("
        SELECT 
            c.id,
            c.name,
            c.mobile,
            COALESCE(SUM(ut.amount), 0) as total_sales,
            COALESCE(SUM(p.amount), 0) as total_payments,
            (COALESCE(SUM(ut.amount), 0) - COALESCE(SUM(p.amount), 0)) as balance,
            c.balance as current_balance
        FROM customers c
        LEFT JOIN udhar_transactions ut ON c.id = ut.customer_id AND ut.transaction_date BETWEEN ? AND ?
        LEFT JOIN payments p ON c.id = p.customer_id AND p.payment_date BETWEEN ? AND ?
        WHERE c.user_id = ? $where_customer
        GROUP BY c.id
        HAVING total_sales > 0 OR total_payments > 0
        ORDER BY balance DESC
    ");
    $stmt->bind_param("ssssi", $date_from, $date_to, $date_from, $date_to, $_SESSION['user_id']);
    $stmt->execute();
    $customer_summary = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

// Sales Report Data
if ($report_type == 'sales') {
    $where_customer = $customer_filter > 0 ? "AND c.id = $customer_filter" : "";
    $stmt = $conn->prepare("
        SELECT 
            ut.id,
            ut.bill_no,
            ut.transaction_date,
            c.name as customer_name,
            c.mobile,
            ut.amount,
            ut.status,
            ut.description
        FROM udhar_transactions ut
        JOIN customers c ON ut.customer_id = c.id
        WHERE c.user_id = ? 
        AND ut.transaction_date BETWEEN ? AND ?
        $where_customer
        ORDER BY ut.transaction_date DESC
    ");
    $stmt->bind_param("iss", $_SESSION['user_id'], $date_from, $date_to);
    $stmt->execute();
    $sales_data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // Calculate totals
    $sales_total = array_sum(array_column($sales_data, 'amount'));
}

// Payment Report Data
if ($report_type == 'payment') {
    $where_customer = $customer_filter > 0 ? "AND c.id = $customer_filter" : "";
    $stmt = $conn->prepare("
        SELECT 
            p.id,
            p.payment_date,
            c.name as customer_name,
            c.mobile,
            p.amount,
            p.payment_mode,
            p.reference_no,
            p.notes
        FROM payments p
        JOIN customers c ON p.customer_id = c.id
        WHERE c.user_id = ? 
        AND p.payment_date BETWEEN ? AND ?
        $where_customer
        ORDER BY p.payment_date DESC
    ");
    $stmt->bind_param("iss", $_SESSION['user_id'], $date_from, $date_to);
    $stmt->execute();
    $payment_data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // Calculate totals
    $payment_total = array_sum(array_column($payment_data, 'amount'));
}

// Outstanding Report Data
if ($report_type == 'outstanding') {
    $where_customer = $customer_filter > 0 ? "AND c.id = $customer_filter" : "";
    $stmt = $conn->prepare("
        SELECT 
            c.id,
            c.name,
            c.mobile,
            c.balance,
            MAX(ut.transaction_date) as last_transaction_date,
            MAX(p.payment_date) as last_payment_date,
            COUNT(DISTINCT ut.id) as total_bills,
            COUNT(DISTINCT p.id) as total_payments
        FROM customers c
        LEFT JOIN udhar_transactions ut ON c.id = ut.customer_id
        LEFT JOIN payments p ON c.id = p.customer_id
        WHERE c.user_id = ? 
        AND c.balance > 0
        $where_customer
        GROUP BY c.id
        ORDER BY c.balance DESC
    ");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $outstanding_data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // Calculate total outstanding
    $total_outstanding = array_sum(array_column($outstanding_data, 'balance'));
}

// Monthly Trend Data
if ($report_type == 'trend') {
    $stmt = $conn->prepare("
        SELECT 
            DATE_FORMAT(transaction_date, '%Y-%m') as month,
            SUM(amount) as total_sales,
            COUNT(*) as total_transactions
        FROM udhar_transactions ut
        JOIN customers c ON ut.customer_id = c.id
        WHERE c.user_id = ?
        AND transaction_date BETWEEN DATE_SUB(NOW(), INTERVAL 12 MONTH) AND NOW()
        GROUP BY DATE_FORMAT(transaction_date, '%Y-%m')
        ORDER BY month ASC
    ");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $sales_trend = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    $stmt = $conn->prepare("
        SELECT 
            DATE_FORMAT(payment_date, '%Y-%m') as month,
            SUM(amount) as total_payments,
            COUNT(*) as total_transactions
        FROM payments p
        JOIN customers c ON p.customer_id = c.id
        WHERE c.user_id = ?
        AND payment_date BETWEEN DATE_SUB(NOW(), INTERVAL 12 MONTH) AND NOW()
        GROUP BY DATE_FORMAT(payment_date, '%Y-%m')
        ORDER BY month ASC
    ");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $payment_trend = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

// Handle export
if (isset($_GET['export']) && $_GET['export'] == 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="report_' . $report_type . '_' . date('Y-m-d') . '.csv"');

    $output = fopen('php://output', 'w');

    if ($report_type == 'summary') {
        fputcsv($output, ['Customer Name', 'Mobile', 'Total Sales', 'Total Payments', 'Balance', 'Current Balance']);
        foreach ($customer_summary as $row) {
            fputcsv($output, [
                $row['name'],
                $row['mobile'],
                $row['total_sales'],
                $row['total_payments'],
                $row['balance'],
                $row['current_balance']
            ]);
        }
    } elseif ($report_type == 'sales') {
        fputcsv($output, ['Bill No', 'Date', 'Customer', 'Mobile', 'Amount', 'Status', 'Description']);
        foreach ($sales_data as $row) {
            fputcsv($output, [
                $row['bill_no'],
                $row['transaction_date'],
                $row['customer_name'],
                $row['mobile'],
                $row['amount'],
                $row['status'],
                $row['description']
            ]);
        }
    } elseif ($report_type == 'payment') {
        fputcsv($output, ['Date', 'Customer', 'Mobile', 'Amount', 'Payment Mode', 'Reference No', 'Notes']);
        foreach ($payment_data as $row) {
            fputcsv($output, [
                $row['payment_date'],
                $row['customer_name'],
                $row['mobile'],
                $row['amount'],
                $row['payment_mode'],
                $row['reference_no'],
                $row['notes']
            ]);
        }
    } elseif ($report_type == 'outstanding') {
        fputcsv($output, ['Customer Name', 'Mobile', 'Balance', 'Total Bills', 'Total Payments', 'Last Transaction', 'Last Payment']);
        foreach ($outstanding_data as $row) {
            fputcsv($output, [
                $row['name'],
                $row['mobile'],
                $row['balance'],
                $row['total_bills'],
                $row['total_payments'],
                $row['last_transaction_date'],
                $row['last_payment_date']
            ]);
        }
    }

    fclose($output);
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Smart Udhar System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="assets/css/reports.css">
    <link rel="stylesheet" href="assets/css/style.css">

</head>

<body>
    <?php include 'includes/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Floating Toggle Button (visible when sidebar is closed) -->
        <button class="floating-toggle-btn" id="floatingToggle">
            <i class="bi bi-chevron-right"></i>
        </button>


        <!-- Page Content -->
        <div class="container-fluid p-4">
            <!-- Page Header -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <h2><i class="bi bi-bar-chart-fill"></i> Reports & Analytics</h2>
                    <p class="text-muted">Generate and analyze various business reports</p>
                </div>
            </div>

            <!-- Report Type Selection -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <h5 class="mb-3">Select Report Type</h5>
                </div>
                <div class="col-md-2">
                    <a href="reports.php?type=summary&date_from=<?php echo $date_from; ?>&date_to=<?php echo $date_to; ?>"
                        class="report-type-btn <?php echo $report_type == 'summary' ? 'active' : ''; ?>">
                        <i class="bi bi-clipboard-data"></i>
                        <div>Summary</div>
                    </a>
                </div>
                <div class="col-md-2">
                    <a href="reports.php?type=sales&date_from=<?php echo $date_from; ?>&date_to=<?php echo $date_to; ?>"
                        class="report-type-btn <?php echo $report_type == 'sales' ? 'active' : ''; ?>">
                        <i class="bi bi-graph-up"></i>
                        <div>Sales Report</div>
                    </a>
                </div>
                <div class="col-md-2">
                    <a href="reports.php?type=payment&date_from=<?php echo $date_from; ?>&date_to=<?php echo $date_to; ?>"
                        class="report-type-btn <?php echo $report_type == 'payment' ? 'active' : ''; ?>">
                        <i class="bi bi-cash-coin"></i>
                        <div>Payment Report</div>
                    </a>
                </div>
                <div class="col-md-2">
                    <a href="reports.php?type=outstanding&date_from=<?php echo $date_from; ?>&date_to=<?php echo $date_to; ?>"
                        class="report-type-btn <?php echo $report_type == 'outstanding' ? 'active' : ''; ?>">
                        <i class="bi bi-exclamation-triangle"></i>
                        <div>Outstanding</div>
                    </a>
                </div>
                <div class="col-md-2">
                    <a href="reports.php?type=trend&date_from=<?php echo $date_from; ?>&date_to=<?php echo $date_to; ?>"
                        class="report-type-btn <?php echo $report_type == 'trend' ? 'active' : ''; ?>">
                        <i class="bi bi-graph-up-arrow"></i>
                        <div>Trend Analysis</div>
                    </a>
                </div>
            </div>

            <!-- Filters -->
            <?php if ($report_type != 'trend'): ?>
                <div class="filter-card">
                    <form method="GET" action="reports.php" class="row g-3">
                        <input type="hidden" name="type" value="<?php echo $report_type; ?>">

                        <div class="col-md-3">
                            <label class="form-label">From Date</label>
                            <input type="date" class="form-control" name="date_from" value="<?php echo $date_from; ?>">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">To Date</label>
                            <input type="date" class="form-control" name="date_to" value="<?php echo $date_to; ?>">
                        </div>

                        <?php if ($report_type != 'outstanding'): ?>
                            <div class="col-md-4">
                                <label class="form-label">Customer</label>
                                <select class="form-select" name="customer">
                                    <option value="0">All Customers</option>
                                    <?php foreach ($all_customers as $cust): ?>
                                        <option value="<?php echo $cust['id']; ?>" <?php echo $customer_filter == $cust['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($cust['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        <?php endif; ?>

                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-funnel"></i> Filter
                            </button>
                        </div>
                    </form>
                </div>
            <?php endif; ?>

            <!-- Summary Report -->
            <?php if ($report_type == 'summary'): ?>
                <!-- Statistics -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="stat-box primary">
                            <h3>₹<?php echo number_format($summary_stats['total_sales'], 2); ?></h3>
                            <p>Total Sales</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-box success">
                            <h3>₹<?php echo number_format($summary_stats['total_payments'], 2); ?></h3>
                            <p>Total Payments</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-box warning">
                            <h3>₹<?php echo number_format($summary_stats['net_balance'], 2); ?></h3>
                            <p>Net Balance</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-box info">
                            <h3><?php echo $summary_stats['total_customers']; ?></h3>
                            <p>Active Customers</p>
                        </div>
                    </div>
                </div>

                <!-- Customer Summary Table -->
                <div class="card report-card">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="bi bi-people"></i> Customer-wise Summary</h5>
                        <a href="?type=summary&date_from=<?php echo $date_from; ?>&date_to=<?php echo $date_to; ?>&export=csv"
                            class="btn btn-light btn-sm">
                            <i class="bi bi-download"></i> Export CSV
                        </a>
                    </div>
                    <div class="card-body">
                        <?php if (empty($customer_summary)): ?>
                            <div class="text-center py-5">
                                <i class="bi bi-inbox display-1 text-muted"></i>
                                <p class="text-muted mt-3">No data available for the selected period</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover report-table">
                                    <thead>
                                        <tr>
                                            <th>Customer</th>
                                            <th>Mobile</th>
                                            <th class="text-end">Total Sales</th>
                                            <th class="text-end">Total Payments</th>
                                            <th class="text-end">Period Balance</th>
                                            <th class="text-end">Current Balance</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($customer_summary as $row): ?>
                                            <tr>
                                                <td><strong><?php echo htmlspecialchars($row['name']); ?></strong></td>
                                                <td><?php echo htmlspecialchars($row['mobile']); ?></td>
                                                <td class="text-end">₹<?php echo number_format($row['total_sales'], 2); ?></td>
                                                <td class="text-end text-success">
                                                    ₹<?php echo number_format($row['total_payments'], 2); ?></td>
                                                <td
                                                    class="text-end <?php echo $row['balance'] > 0 ? 'text-danger' : 'text-success'; ?>">
                                                    ₹<?php echo number_format($row['balance'], 2); ?>
                                                </td>
                                                <td
                                                    class="text-end <?php echo $row['current_balance'] > 0 ? 'text-danger' : 'text-success'; ?>">
                                                    <strong>₹<?php echo number_format($row['current_balance'], 2); ?></strong>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Sales Report -->
            <?php if ($report_type == 'sales'): ?>
                <div class="card report-card">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="bi bi-graph-up"></i> Sales Report</h5>
                        <div>
                            <span class="badge bg-light text-dark me-2">
                                Total: ₹<?php echo number_format($sales_total, 2); ?>
                            </span>
                            <a href="?type=sales&date_from=<?php echo $date_from; ?>&date_to=<?php echo $date_to; ?>&customer=<?php echo $customer_filter; ?>&export=csv"
                                class="btn btn-light btn-sm">
                                <i class="bi bi-download"></i> Export CSV
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if (empty($sales_data)): ?>
                            <div class="text-center py-5">
                                <i class="bi bi-inbox display-1 text-muted"></i>
                                <p class="text-muted mt-3">No sales data available for the selected period</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover report-table">
                                    <thead>
                                        <tr>
                                            <th>Bill No</th>
                                            <th>Date</th>
                                            <th>Customer</th>
                                            <th>Mobile</th>
                                            <th class="text-end">Amount</th>
                                            <th>Status</th>
                                            <th>Description</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($sales_data as $row): ?>
                                            <tr>
                                                <td><strong><?php echo htmlspecialchars($row['bill_no']); ?></strong></td>
                                                <td><?php echo date('d M Y', strtotime($row['transaction_date'])); ?></td>
                                                <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                                                <td><?php echo htmlspecialchars($row['mobile']); ?></td>
                                                <td class="text-end">
                                                    <strong>₹<?php echo number_format($row['amount'], 2); ?></strong></td>
                                                <td>
                                                    <?php
                                                    $badge_class = 'secondary';
                                                    if ($row['status'] == 'paid')
                                                        $badge_class = 'success';
                                                    elseif ($row['status'] == 'pending')
                                                        $badge_class = 'warning';
                                                    elseif ($row['status'] == 'partially_paid')
                                                        $badge_class = 'info';
                                                    ?>
                                                    <span class="badge bg-<?php echo $badge_class; ?>">
                                                        <?php echo ucfirst(str_replace('_', ' ', $row['status'])); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo htmlspecialchars($row['description']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot>
                                        <tr class="table-primary">
                                            <td colspan="4"><strong>Total</strong></td>
                                            <td class="text-end"><strong>₹<?php echo number_format($sales_total, 2); ?></strong>
                                            </td>
                                            <td colspan="2"></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Payment Report -->
            <?php if ($report_type == 'payment'): ?>
                <div class="card report-card">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="bi bi-cash-coin"></i> Payment Report</h5>
                        <div>
                            <span class="badge bg-light text-dark me-2">
                                Total: ₹<?php echo number_format($payment_total, 2); ?>
                            </span>
                            <a href="?type=payment&date_from=<?php echo $date_from; ?>&date_to=<?php echo $date_to; ?>&customer=<?php echo $customer_filter; ?>&export=csv"
                                class="btn btn-light btn-sm">
                                <i class="bi bi-download"></i> Export CSV
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if (empty($payment_data)): ?>
                            <div class="text-center py-5">
                                <i class="bi bi-inbox display-1 text-muted"></i>
                                <p class="text-muted mt-3">No payment data available for the selected period</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover report-table">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Customer</th>
                                            <th>Mobile</th>
                                            <th class="text-end">Amount</th>
                                            <th>Payment Mode</th>
                                            <th>Reference No</th>
                                            <th>Notes</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($payment_data as $row): ?>
                                            <tr>
                                                <td><?php echo date('d M Y', strtotime($row['payment_date'])); ?></td>
                                                <td><strong><?php echo htmlspecialchars($row['customer_name']); ?></strong></td>
                                                <td><?php echo htmlspecialchars($row['mobile']); ?></td>
                                                <td class="text-end text-success">
                                                    <strong>₹<?php echo number_format($row['amount'], 2); ?></strong></td>
                                                <td>
                                                    <span class="badge bg-info">
                                                        <?php echo ucfirst(str_replace('_', ' ', $row['payment_mode'])); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo htmlspecialchars($row['reference_no']); ?></td>
                                                <td><?php echo htmlspecialchars($row['notes']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot>
                                        <tr class="table-success">
                                            <td colspan="3"><strong>Total</strong></td>
                                            <td class="text-end">
                                                <strong>₹<?php echo number_format($payment_total, 2); ?></strong></td>
                                            <td colspan="3"></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Outstanding Report -->
            <?php if ($report_type == 'outstanding'): ?>
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="stat-box warning">
                            <h3>₹<?php echo number_format($total_outstanding, 2); ?></h3>
                            <p>Total Outstanding Amount</p>
                        </div>
                    </div>
                </div>

                <div class="card report-card">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="bi bi-exclamation-triangle"></i> Outstanding Balances</h5>
                        <a href="?type=outstanding&export=csv" class="btn btn-light btn-sm">
                            <i class="bi bi-download"></i> Export CSV
                        </a>
                    </div>
                    <div class="card-body">
                        <?php if (empty($outstanding_data)): ?>
                            <div class="text-center py-5">
                                <i class="bi bi-check-circle display-1 text-success"></i>
                                <p class="text-muted mt-3">No outstanding balances! All customers have cleared their dues.</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover report-table">
                                    <thead>
                                        <tr>
                                            <th>Customer</th>
                                            <th>Mobile</th>
                                            <th class="text-end">Balance</th>
                                            <th class="text-center">Total Bills</th>
                                            <th class="text-center">Total Payments</th>
                                            <th>Last Transaction</th>
                                            <th>Last Payment</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($outstanding_data as $row): ?>
                                            <tr>
                                                <td><strong><?php echo htmlspecialchars($row['name']); ?></strong></td>
                                                <td><?php echo htmlspecialchars($row['mobile']); ?></td>
                                                <td class="text-end text-danger">
                                                    <strong>₹<?php echo number_format($row['balance'], 2); ?></strong></td>
                                                <td class="text-center"><?php echo $row['total_bills']; ?></td>
                                                <td class="text-center"><?php echo $row['total_payments']; ?></td>
                                                <td>
                                                    <?php echo $row['last_transaction_date'] ? date('d M Y', strtotime($row['last_transaction_date'])) : '-'; ?>
                                                </td>
                                                <td>
                                                    <?php echo $row['last_payment_date'] ? date('d M Y', strtotime($row['last_payment_date'])) : '-'; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot>
                                        <tr class="table-warning">
                                            <td colspan="2"><strong>Total Outstanding</strong></td>
                                            <td class="text-end">
                                                <strong>₹<?php echo number_format($total_outstanding, 2); ?></strong></td>
                                            <td colspan="4"></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Trend Analysis -->
            <?php if ($report_type == 'trend'): ?>
                <div class="row">
                    <div class="col-md-12">
                        <div class="chart-container">
                            <h5 class="mb-4"><i class="bi bi-graph-up-arrow"></i> Sales & Payment Trend (Last 12 Months)
                            </h5>
                            <canvas id="trendChart" height="80"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Monthly Summary Table -->
                <div class="card report-card mt-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-calendar3"></i> Monthly Summary</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover report-table">
                                <thead>
                                    <tr>
                                        <th>Month</th>
                                        <th class="text-end">Total Sales</th>
                                        <th class="text-center">Transactions</th>
                                        <th class="text-end">Total Payments</th>
                                        <th class="text-center">Payments</th>
                                        <th class="text-end">Difference</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    // Merge sales and payment trends
                                    $months = [];
                                    foreach ($sales_trend as $st) {
                                        $months[$st['month']]['sales'] = $st['total_sales'];
                                        $months[$st['month']]['sales_count'] = $st['total_transactions'];
                                    }
                                    foreach ($payment_trend as $pt) {
                                        $months[$pt['month']]['payments'] = $pt['total_payments'];
                                        $months[$pt['month']]['payment_count'] = $pt['total_transactions'];
                                    }

                                    foreach ($months as $month => $data):
                                        $sales = $data['sales'] ?? 0;
                                        $payments = $data['payments'] ?? 0;
                                        $diff = $sales - $payments;
                                        ?>
                                        <tr>
                                            <td><strong><?php echo date('M Y', strtotime($month . '-01')); ?></strong></td>
                                            <td class="text-end">₹<?php echo number_format($sales, 2); ?></td>
                                            <td class="text-center"><?php echo $data['sales_count'] ?? 0; ?></td>
                                            <td class="text-end text-success">₹<?php echo number_format($payments, 2); ?></td>
                                            <td class="text-center"><?php echo $data['payment_count'] ?? 0; ?></td>
                                            <td class="text-end <?php echo $diff > 0 ? 'text-danger' : 'text-success'; ?>">
                                                ₹<?php echo number_format($diff, 2); ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

        </div>
    </div>

    <script>
        // Global variables for JavaScript
        window.reportType = '<?php echo $report_type; ?>';
        <?php if ($report_type == 'trend'): ?>
            window.trendLabels = <?php
            $all_months = array_keys($months);
            echo json_encode(array_map(function ($m) {
                return date('M Y', strtotime($m . '-01'));
            }, $all_months));
            ?>;
            window.salesData = <?php
            echo json_encode(array_map(function ($m) use ($months) {
                return $months[$m]['sales'] ?? 0;
            }, $all_months));
            ?>;
            window.paymentData = <?php
            echo json_encode(array_map(function ($m) use ($months) {
                return $months[$m]['payments'] ?? 0;
            }, $all_months));
            ?>;
        <?php endif; ?>
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <script src="assets/js/reports.js"></script>

</body>

</html>