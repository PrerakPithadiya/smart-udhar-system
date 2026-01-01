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
    <link rel="stylesheet" href="assets/css/reminders.css">
    <link rel="stylesheet" href="assets/css/style.css">

</head>

<body>
    <?php include 'includes/header.php'; ?>

    <?php include 'includes/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Floating Toggle Button (visible when sidebar is closed) -->
        <button class="floating-toggle-btn" id="floatingToggle">
            <i class="bi bi-chevron-right"></i>
        </button>



        <div class="container-fluid">
            <div class="row">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-12">
                            <div
                                class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                                <h1 class="h2">
                                    <i class="bi bi-bell"></i> Reminders
                                </h1>
                                <div class="btn-toolbar mb-2 mb-md-0">
                                    <form method="GET" class="d-inline">
                                        <select name="days" class="form-select" onchange="this.form.submit()">
                                            <option value="1" <?php echo $reminder_days == 1 ? 'selected' : ''; ?>>Due
                                                Today</option>
                                            <option value="3" <?php echo $reminder_days == 3 ? 'selected' : ''; ?>>Due in
                                                3 days</option>
                                            <option value="7" <?php echo $reminder_days == 7 ? 'selected' : ''; ?>>Due in
                                                7 days</option>
                                            <option value="14" <?php echo $reminder_days == 14 ? 'selected' : ''; ?>>Due
                                                in 14 days</option>
                                            <option value="30" <?php echo $reminder_days == 30 ? 'selected' : ''; ?>>Due
                                                in 30 days</option>
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
                                    <div
                                        class="reminder-card <?php echo $reminder['days_remaining'] < 0 ? 'reminder-overdue' : 'reminder-upcoming'; ?>">
                                        <div class="reminder-header">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <h5 class="mb-0">
                                                    <strong><?php echo htmlspecialchars($reminder['bill_no']); ?></strong>
                                                    - <?php echo htmlspecialchars($reminder['customer_name']); ?>
                                                </h5>
                                                <span
                                                    class="reminder-status <?php echo $reminder['days_remaining'] < 0 ? 'status-overdue' : 'status-upcoming'; ?>">
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
                                                        <strong>Amount:</strong>
                                                        ₹<?php echo number_format($reminder['amount'], 2); ?>
                                                    </p>
                                                    <p class="mb-2">
                                                        <strong>Due Date:</strong>
                                                        <?php echo date('d M Y', strtotime($reminder['due_date'])); ?>
                                                    </p>
                                                    <p class="mb-0">
                                                        <strong>Bill Date:</strong>
                                                        <?php echo date('d M Y', strtotime($reminder['transaction_date'])); ?>
                                                    </p>
                                                </div>
                                                <div class="col-md-6">
                                                    <p class="mb-2">
                                                        <strong>Customer:</strong>
                                                        <?php echo htmlspecialchars($reminder['customer_name']); ?>
                                                    </p>
                                                    <?php if (!empty($reminder['customer_mobile'])): ?>
                                                        <p class="mb-2">
                                                            <strong>Mobile:</strong>
                                                            <?php echo htmlspecialchars($reminder['customer_mobile']); ?>
                                                        </p>
                                                    <?php endif; ?>
                                                    <p class="mb-0">
                                                        <strong>Status:</strong>
                                                        <span
                                                            class="badge bg-<?php echo $reminder['status'] == 'partially_paid' ? 'warning' : 'danger'; ?>">
                                                            <?php echo ucfirst(str_replace('_', ' ', $reminder['status'])); ?>
                                                        </span>
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="mt-3">
                                                <a href="udhar.php?action=view&id=<?php echo $reminder['id']; ?>"
                                                    class="btn btn-outline-primary btn-sm">
                                                    <i class="bi bi-eye"></i> View Details
                                                </a>
                                                <a href="payments.php?customer_id=<?php echo $reminder['customer_id']; ?>"
                                                    class="btn btn-outline-success btn-sm">
                                                    <i class="bi bi-cash"></i> Record Payment
                                                </a>
                                                <?php if (!empty($reminder['customer_mobile'])): ?>
                                                    <a href="tel:<?php echo htmlspecialchars($reminder['customer_mobile']); ?>"
                                                        class="btn btn-outline-info btn-sm">
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


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/reminders.js"></script>
</body>

</html>