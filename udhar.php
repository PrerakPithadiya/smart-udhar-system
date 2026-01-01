<?php
// File: smart-udhar-system/udhar.php

require_once 'config/database.php';
requireLogin();

$conn = getDBConnection();

// Handle actions
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$udhar_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$customer_id = isset($_GET['customer_id']) ? intval($_GET['customer_id']) : 0;
$item_id = isset($_GET['item_id']) ? intval($_GET['item_id']) : 0;

// Generate bill number - FIXED VERSION
function generateBillNumber($conn, $user_id)
{
    $prefix = 'BILL';
    $year = date('Y');
    $month = date('m');

    // Get last bill number for this user
    $stmt = $conn->prepare("
        SELECT ut.bill_no 
        FROM udhar_transactions ut 
        JOIN customers c ON ut.customer_id = c.id 
        WHERE c.user_id = ? 
        AND ut.bill_no LIKE ? 
        ORDER BY ut.id DESC 
        LIMIT 1
    ");
    $like_pattern = $prefix . '-' . $year . $month . '-%';
    $stmt->bind_param("is", $user_id, $like_pattern);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $last_bill = $result->fetch_assoc()['bill_no'];
        $last_num = intval(substr($last_bill, -4));
        $new_num = str_pad($last_num + 1, 4, '0', STR_PAD_LEFT);
    } else {
        $new_num = '0001';
    }

    $stmt->close();
    return $prefix . '-' . $year . $month . '-' . $new_num;
}

// Process form submissions
if (isset($_POST['add_udhar'])) {
    $category = isset($_POST['category']) ? sanitizeInput($_POST['category']) : '';

    // Add new udhar entry with items
    $customer_id = intval($_POST['customer_id']);
    $transaction_date = sanitizeInput($_POST['transaction_date']);
    $due_date = sanitizeInput($_POST['due_date']);
    $description = sanitizeInput($_POST['description']);
    $notes = sanitizeInput($_POST['notes']);
    $discount = floatval($_POST['discount'] ?? 0);
    $discount_type = sanitizeInput($_POST['discount_type'] ?? 'fixed');
    $round_off = floatval($_POST['round_off'] ?? 0);

    // Validate
    $errors = [];

    if ($customer_id <= 0) {
        $errors[] = "Please select a customer";
    }

    if (empty($transaction_date)) {
        $errors[] = "Transaction date is required";
    }

    // Check if items are added
    if (!isset($_POST['items']) || count($_POST['items']) == 0) {
        $errors[] = "Please add at least one item";
    }

    if (empty($errors)) {
        $conn->begin_transaction();

        try {
            // Generate bill number
            $bill_no = generateBillNumber($conn, $_SESSION['user_id']);

            // Calculate totals from items
            $total_amount = 0;
            $cgst_amount = 0;
            $sgst_amount = 0;
            $igst_amount = 0;

            if (isset($_POST['items'])) {
                foreach ($_POST['items'] as $index => $item) {
                    $qty = floatval($item['quantity']);
                    $price = floatval($item['price']);
                    $cgst = floatval($item['cgst_rate']);
                    $sgst = floatval($item['sgst_rate']);
                    $igst = floatval($item['igst_rate']);

                    $item_total = $qty * $price;
                    $total_amount += $item_total;

                    if ($igst > 0) {
                        $igst_amount += ($item_total * $igst) / 100;
                    } else {
                        $cgst_amount += ($item_total * $cgst) / 100;
                        $sgst_amount += ($item_total * $sgst) / 100;
                    }
                }
            }

            // Apply discount
            $discount_amount = 0;
            if ($discount > 0) {
                if ($discount_type == 'percentage') {
                    $discount_amount = ($total_amount * $discount) / 100;
                } else {
                    $discount_amount = $discount;
                }
            }

            $grand_total = $total_amount + $cgst_amount + $sgst_amount + $igst_amount - $discount_amount + $round_off;

            // Insert udhar transaction - FIXED INSERT STATEMENT
            // Insert udhar transaction - ADD STATUS COLUMN
            $stmt = $conn->prepare("INSERT INTO udhar_transactions (customer_id, bill_no, transaction_date, due_date, amount, cgst_amount, sgst_amount, igst_amount, discount, discount_type, round_off, description, notes, status, user_id, category) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?, ?)");
            $stmt->bind_param("issssddddsssssis", $customer_id, $bill_no, $transaction_date, $due_date, $total_amount, $cgst_amount, $sgst_amount, $igst_amount, $discount, $discount_type, $round_off, $description, $notes, $_SESSION['user_id'], $category);

            if (!$stmt->execute()) {
                throw new Exception("Error creating udhar transaction: " . $stmt->error);
            }

            $udhar_id = $stmt->insert_id;
            $stmt->close();

            // Insert udhar items
            if (isset($_POST['items'])) {
                foreach ($_POST['items'] as $index => $item) {
                    $item_id = intval($item['item_id']);
                    $item_name = sanitizeInput($item['item_name']);
                    $hsn_code = sanitizeInput($item['hsn_code']);
                    $quantity = floatval($item['quantity']);
                    $unit_price = floatval($item['price']);
                    $cgst_rate = floatval($item['cgst_rate']);
                    $sgst_rate = floatval($item['sgst_rate']);
                    $igst_rate = floatval($item['igst_rate']);

                    $item_total = $quantity * $unit_price;
                    $item_cgst = ($item_total * $cgst_rate) / 100;
                    $item_sgst = ($item_total * $sgst_rate) / 100;
                    $item_igst = ($item_total * $igst_rate) / 100;

                    $stmt = $conn->prepare("INSERT INTO udhar_items 
                        (udhar_id, item_id, item_name, hsn_code, quantity, unit_price, 
                         cgst_rate, sgst_rate, igst_rate, cgst_amount, sgst_amount, 
                         igst_amount, total_amount) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param(
                        "iissddddddddd",
                        $udhar_id,
                        $item_id,
                        $item_name,
                        $hsn_code,
                        $quantity,
                        $unit_price,
                        $cgst_rate,
                        $sgst_rate,
                        $igst_rate,
                        $item_cgst,
                        $item_sgst,
                        $item_igst,
                        $item_total
                    );

                    if (!$stmt->execute()) {
                        throw new Exception("Error adding item: " . $stmt->error);
                    }
                    $stmt->close();
                }
            }

            $conn->commit();

            // Redirect to bill print page
            setMessage("Udhar entry created successfully! Bill No: $bill_no", "success");
            header("Location: print_bill.php?id=$udhar_id");
            exit();
        } catch (Exception $e) {
            $conn->rollback();
            setMessage("Error: " . $e->getMessage(), "danger");
        }
    } else {
        setMessage(implode("<br>", $errors), "danger");
    }
}

if (isset($_POST['update_udhar'])) {
    // Update udhar entry
    $id = intval($_POST['udhar_id']);
    $description = sanitizeInput($_POST['description']);
    $due_date = sanitizeInput($_POST['due_date']);
    $notes = sanitizeInput($_POST['notes']);
    $status = sanitizeInput($_POST['status']);

    $stmt = $conn->prepare("UPDATE udhar_transactions SET description = ?, due_date = ?, notes = ?, status = ?, updated_at = NOW() WHERE id = ?");
    $stmt->bind_param("ssssi", $description, $due_date, $notes, $status, $id);

    if ($stmt->execute()) {
        setMessage("Udhar entry updated successfully!", "success");
        header("Location: udhar.php?action=view&id=$id");
        exit();
    } else {
        setMessage("Error updating udhar entry: " . $stmt->error, "danger");
    }
    $stmt->close();
}

if (isset($_POST['delete_udhar'])) {
    // Delete udhar entry
    $id = intval($_POST['udhar_id']);

    $conn->begin_transaction();

    try {
        // First, delete all udhar items
        $stmt = $conn->prepare("DELETE FROM udhar_items WHERE udhar_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();

        // Then delete the udhar transaction
        $stmt = $conn->prepare("
                DELETE FROM udhar_transactions 
                WHERE id = ? 
                AND customer_id IN (
                    SELECT id FROM customers WHERE user_id = ?
                )
            ");
        $stmt->bind_param("ii", $id, $_SESSION['user_id']);

        if ($stmt->execute()) {
            $conn->commit();
            setMessage("Udhar entry deleted successfully!", "success");
        } else {
            throw new Exception("Error deleting udhar entry: " . $stmt->error);
        }
        $stmt->close();
    } catch (Exception $e) {
        $conn->rollback();
        setMessage("Error: " . $e->getMessage(), "danger");
    }

    header("Location: udhar.php");
    exit();
}



// Get udhar entry for edit/view
$udhar = null;
$udhar_items = [];
if ($udhar_id > 0 && ($action == 'edit' || $action == 'view' || $action == 'print')) {
    $stmt = $conn->prepare("
        SELECT ut.*, c.name as customer_name, c.mobile as customer_mobile 
        FROM udhar_transactions ut 
        JOIN customers c ON ut.customer_id = c.id 
        WHERE ut.id = ? AND c.user_id = ?
    ");
    $stmt->bind_param("ii", $udhar_id, $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $udhar = $result->fetch_assoc();
    $stmt->close();

    if ($udhar) {
        // Get udhar items
        $stmt = $conn->prepare("SELECT * FROM udhar_items WHERE udhar_id = ?");
        $stmt->bind_param("i", $udhar_id);
        $stmt->execute();
        $udhar_items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    }
}

// Get customers for dropdown
$customers = [];
$stmt = $conn->prepare("SELECT id, name, mobile FROM customers WHERE user_id = ? AND status = 'active' ORDER BY name");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$customers = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get items for dropdown
$items = [];
$stmt = $conn->prepare("SELECT id, item_name, item_code, hsn_code, price, cgst_rate, sgst_rate, igst_rate, unit FROM items WHERE user_id = ? AND status = 'active' ORDER BY item_name");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get all udhar entries for listing
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? sanitizeInput($_GET['status']) : '';
$category_filter = isset($_GET['category']) ? sanitizeInput($_GET['category']) : '';

$where_clause = "WHERE c.user_id = " . $_SESSION['user_id'];
$params = [];

if (!empty($search)) {
    $where_clause .= " AND (ut.description LIKE ? OR ut.bill_no LIKE ? OR c.name LIKE ?)";
    $search_term = "%$search%";
    $params = array_fill(0, 3, $search_term);
}

if (!empty($status_filter)) {
    if ($status_filter == 'overdue') {
        // Filter for overdue bills: due_date is in the past and status is not 'paid'
        $where_clause .= " AND ut.due_date < CURDATE() AND ut.status IN ('pending', 'partially_paid')";
    } elseif (in_array($status_filter, ['pending', 'partially_paid', 'paid'])) {
        $where_clause .= " AND ut.status = ?";
        $params[] = $status_filter;
    }
}

if (!empty($category_filter)) {
    $where_clause .= " AND ut.category = ?";
    $params[] = $category_filter;
}

// Get total udhar count
$count_query = "SELECT COUNT(*) as total FROM udhar_transactions ut JOIN customers c ON ut.customer_id = c.id $where_clause";
$count_stmt = $conn->prepare($count_query);
if (!empty($params)) {
    $types = str_repeat('s', count($params));
    $count_stmt->bind_param($types, ...$params);
}
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$total_udhar = $count_result->fetch_assoc()['total'];
$count_stmt->close();

// Pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;
$total_pages = ceil($total_udhar / $limit);

// Get udhar entries with pagination
$order_by = isset($_GET['order_by']) ? sanitizeInput($_GET['order_by']) : 'ut.transaction_date';
$order_dir = isset($_GET['order_dir']) ? sanitizeInput($_GET['order_dir']) : 'DESC';

$allowed_columns = ['ut.transaction_date', 'c.name', 'ut.amount', 'ut.status', 'ut.bill_no'];
$order_by = in_array($order_by, $allowed_columns) ? $order_by : 'ut.transaction_date';
$order_dir = in_array(strtoupper($order_dir), ['ASC', 'DESC']) ? strtoupper($order_dir) : 'DESC';

$query = "SELECT ut.*, c.name as customer_name, c.mobile as customer_mobile FROM udhar_transactions ut JOIN customers c ON ut.customer_id = c.id $where_clause ORDER BY $order_by $order_dir LIMIT ? OFFSET ?";
$stmt = $conn->prepare($query);

if (!empty($params)) {
    $params[] = $limit;
    $params[] = $offset;
    $types = str_repeat('s', count($params) - 2) . 'ii';
    $stmt->bind_param($types, ...$params);
} else {
    $stmt->bind_param("ii", $limit, $offset);
}

$stmt->execute();
$udhar_list = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$page_title = "Udhar Entry Management";

// ... [rest of the HTML/CSS/JavaScript code remains the same] ...
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Udhar Entry - Smart Udhar System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="assets/css/udhar.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .search-suggestions-container {
            position: absolute;
            border: 1px solid #ddd;
            border-top: none;
            z-index: 99;
            top: 100%;
            left: 0;
            right: 0;
            max-height: 200px;
            overflow-y: auto;
            background: white;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            display: none;
        }

        .search-suggestion-item {
            padding: 10px;
            cursor: pointer;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .search-suggestion-item:hover,
        .search-suggestion-item.active {
            background-color: #e9ecef;
        }
    </style>
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
            <div class="container-fluid udhar-container">
                <div class="row">
                    <div class="col-12">
                        <div
                            class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                            <h1 class="h2">
                                <i class="bi bi-credit-card"></i> Udhar Entry
                            </h1>
                            <div class="btn-toolbar mb-2 mb-md-0">
                                <?php if ($action == 'list'): ?>
                                    <a href="udhar.php?action=add" class="btn btn-primary">
                                        <i class="bi bi-plus-circle"></i> New Udhar Entry
                                    </a>
                                <?php else: ?>
                                    <a href="udhar.php" class="btn btn-outline-secondary">
                                        <i class="bi bi-arrow-left"></i> Back to List
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php displayMessage(); ?>

                        <?php if ($action == 'list'): ?>
                            <!-- Udhar List View -->
                            <div class="udhar-stat-card mb-4">
                                <div class="row align-items-center">
                                    <div class="col-md-4">
                                        <h5 class="mb-0">All Udhar Entries (<?php echo $total_udhar; ?>)</h5>
                                    </div>
                                    <div class="col-md-8">
                                        <form method="GET" class="row g-2">
                                            <input type="hidden" name="action" value="list">
                                            <div class="col-md-4">
                                                <select name="status" class="form-select" onchange="this.form.submit()">
                                                    <option value="">All Status</option>
                                                    <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                    <option value="partially_paid" <?php echo $status_filter == 'partially_paid' ? 'selected' : ''; ?>>Partially
                                                        Paid</option>
                                                    <option value="paid" <?php echo $status_filter == 'paid' ? 'selected' : ''; ?>>Paid</option>
                                                    <option value="overdue" <?php echo $status_filter == 'overdue' ? 'selected' : ''; ?>>Overdue Bills</option>
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <select name="category" class="form-select" onchange="this.form.submit()">
                                                    <option value="">All Categories</option>
                                                    <option value="Fertilizers" <?php echo $category_filter == 'Fertilizers' ? 'selected' : ''; ?>>Fertilizers</option>
                                                    <option value="Seeds" <?php echo $category_filter == 'Seeds' ? 'selected' : ''; ?>>Seeds</option>
                                                    <option value="Insecticides" <?php echo $category_filter == 'Insecticides' ? 'selected' : ''; ?>>Insecticides</option>
                                                    <option value="Others" <?php echo $category_filter == 'Others' ? 'selected' : ''; ?>>Others</option>
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="udhar-search-box">
                                                    <i class="bi bi-search search-icon"></i>
                                                    <input type="text" name="search" class="form-control"
                                                        placeholder="Search..."
                                                        value="<?php echo htmlspecialchars($search); ?>">
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Udhar Statistics -->
                            <div class="udhar-stats-cards">
                                <?php
                                // Total udhar amount
                                $total_stmt = $conn->prepare("SELECT SUM(ut.amount) as total FROM udhar_transactions ut JOIN customers c ON ut.customer_id = c.id WHERE c.user_id = ?");
                                $total_stmt->bind_param("i", $_SESSION['user_id']);
                                $total_stmt->execute();
                                $total_result = $total_stmt->get_result()->fetch_assoc();
                                $total_stmt->close();

                                // Pending udhar amount
                                $pending_stmt = $conn->prepare("SELECT SUM(ut.amount) as total FROM udhar_transactions ut JOIN customers c ON ut.customer_id = c.id WHERE c.user_id = ? AND ut.status IN ('pending', 'partially_paid')");
                                $pending_stmt->bind_param("i", $_SESSION['user_id']);
                                $pending_stmt->execute();
                                $pending_result = $pending_stmt->get_result()->fetch_assoc();
                                $pending_stmt->close();

                                // Overdue bills count
                                $overdue_stmt = $conn->prepare("SELECT COUNT(*) as count FROM udhar_transactions ut JOIN customers c ON ut.customer_id = c.id WHERE c.user_id = ? AND ut.due_date < CURDATE() AND ut.status IN ('pending', 'partially_paid')");
                                $overdue_stmt->bind_param("i", $_SESSION['user_id']);
                                $overdue_stmt->execute();
                                $overdue_result = $overdue_stmt->get_result()->fetch_assoc();
                                $overdue_stmt->close();

                                // Paid bills count
                                $paid_stmt = $conn->prepare("SELECT COUNT(*) as count FROM udhar_transactions ut JOIN customers c ON ut.customer_id = c.id WHERE c.user_id = ? AND ut.status = 'paid'");
                                $paid_stmt->bind_param("i", $_SESSION['user_id']);
                                $paid_stmt->execute();
                                $paid_result = $paid_stmt->get_result()->fetch_assoc();
                                $paid_stmt->close();
                                ?>

                                <div class="udhar-stat-card">
                                    <div class="stat-value">
                                        ₹<?php echo number_format($total_result['total'] ?? 0, 2); ?></div>
                                    <div class="stat-label">Total Udhar</div>
                                    <i class="bi bi-cash-coin stat-icon"></i>
                                </div>

                                <div class="udhar-stat-card stat-danger">
                                    <div class="stat-value">
                                        ₹<?php echo number_format($pending_result['total'] ?? 0, 2); ?></div>
                                    <div class="stat-label">Pending Udhar</div>
                                    <i class="bi bi-clock-history stat-icon"></i>
                                </div>

                                <div class="udhar-stat-card stat-warning">
                                    <div class="stat-value"><?php echo number_format($overdue_result['count'] ?? 0); ?>
                                    </div>
                                    <div class="stat-label">Overdue Bills</div>
                                    <i class="bi bi-exclamation-triangle stat-icon"></i>
                                </div>

                                <div class="udhar-stat-card stat-success">
                                    <div class="stat-value"><?php echo number_format($paid_result['count'] ?? 0); ?>
                                    </div>
                                    <div class="stat-label">Paid Bills</div>
                                    <i class="bi bi-check-circle stat-icon"></i>
                                </div>
                            </div>

                            <div class="card udhar-card">
                                <div class="card-body">
                                    <?php if (empty($udhar_list)): ?>
                                        <div class="udhar-empty-state">
                                            <i class="bi bi-receipt display-1 empty-icon"></i>
                                            <h4 class="mt-3">No udhar entries found</h4>
                                            <p class="text-muted">Create your first udhar entry</p>
                                            <a href="udhar.php?action=add" class="btn btn-primary">
                                                <i class="bi bi-plus-circle"></i> Create First Udhar Entry
                                            </a>
                                        </div>
                                    <?php else: ?>
                                        <div class="udhar-table-container table-responsive">
                                            <table class="table udhar-table">
                                                <thead>
                                                    <tr>
                                                        <th>Bill No</th>
                                                        <th>Customer</th>
                                                        <th>Date</th>
                                                        <th>Amount</th>
                                                        <th>Due Date</th>
                                                        <th>Status</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($udhar_list as $entry): ?>
                                                        <tr class="bill-card">
                                                            <td>
                                                                <strong
                                                                    class="udhar-bill-number"><?php echo htmlspecialchars($entry['bill_no']); ?></strong>
                                                            </td>
                                                            <td>
                                                                <div class="udhar-customer-info">
                                                                    <div class="udhar-customer-avatar">
                                                                        <?php echo strtoupper(substr($entry['customer_name'], 0, 1)); ?>
                                                                    </div>
                                                                    <div>
                                                                        <div class="udhar-customer-name">
                                                                            <?php echo htmlspecialchars($entry['customer_name']); ?>
                                                                        </div>
                                                                        <?php if (!empty($entry['customer_mobile'])): ?>
                                                                            <div class="udhar-customer-mobile">
                                                                                <?php echo htmlspecialchars($entry['customer_mobile']); ?>
                                                                            </div>
                                                                        <?php endif; ?>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <span
                                                                    class="udhar-date"><?php echo date('d M Y', strtotime($entry['transaction_date'])); ?></span>
                                                            </td>
                                                            <td>
                                                                <span
                                                                    class="udhar-amount">₹<?php echo number_format($entry['amount'], 2); ?></span>
                                                            </td>
                                                            <td>
                                                                <?php if (!empty($entry['due_date'])): ?>
                                                                    <span
                                                                        class="udhar-date"><?php echo date('d M Y', strtotime($entry['due_date'])); ?></span>
                                                                    <?php if (strtotime($entry['due_date']) < time() && $entry['status'] != 'paid'): ?>
                                                                        <br><span class="udhar-overdue-badge">Overdue</span>
                                                                    <?php endif; ?>
                                                                <?php else: ?>
                                                                    <span class="text-muted">No due date</span>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td>
                                                                <?php
                                                                $status_class = 'udhar-status-pending';
                                                                if ($entry['status'] == 'paid') {
                                                                    $status_class = 'udhar-status-paid';
                                                                } elseif ($entry['status'] == 'partially_paid') {
                                                                    $status_class = 'udhar-status-partially_paid';
                                                                }
                                                                ?>
                                                                <span class="udhar-status-badge <?php echo $status_class; ?>">
                                                                    <?php echo ucfirst(str_replace('_', ' ', $entry['status'])); ?>
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <div class="udhar-row-actions">
                                                                    <a href="print_bill.php?id=<?php echo $entry['id']; ?>"
                                                                        class="btn btn-sm btn-outline-primary" title="Print Bill"
                                                                        target="_blank">
                                                                        <i class="bi bi-printer"></i>
                                                                    </a>
                                                                    <a href="udhar.php?action=view&id=<?php echo $entry['id']; ?>"
                                                                        class="btn btn-sm btn-outline-info" title="View">
                                                                        <i class="bi bi-eye"></i>
                                                                    </a>
                                                                    <a href="edit_bill.php?id=<?php echo $entry['id']; ?>"
                                                                        class="btn btn-sm btn-outline-warning" title="Edit Bill">
                                                                        <i class="bi bi-pencil"></i>
                                                                    </a>
                                                                    <button type="button" class="btn btn-sm btn-outline-danger"
                                                                        onclick="confirmDelete(<?php echo $entry['id']; ?>, '<?php echo htmlspecialchars(addslashes($entry['bill_no'])); ?>')"
                                                                        title="Delete">
                                                                        <i class="bi bi-trash"></i>
                                                                    </button>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>

                                        <?php if ($total_pages > 1): ?>
                                            <nav aria-label="Page navigation">
                                                <ul class="pagination justify-content-center">
                                                    <li class="page-item <?php echo $page == 1 ? 'disabled' : ''; ?>">
                                                        <a class="page-link"
                                                            href="?action=list&search=<?php echo urlencode($search); ?>&status=<?php echo $status_filter; ?>&customer=<?php echo $customer_filter; ?>&page=<?php echo $page - 1; ?>">
                                                            <i class="bi bi-chevron-left"></i>
                                                        </a>
                                                    </li>

                                                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                                            <a class="page-link"
                                                                href="?action=list&search=<?php echo urlencode($search); ?>&status=<?php echo $status_filter; ?>&customer=<?php echo $customer_filter; ?>&page=<?php echo $i; ?>">
                                                                <?php echo $i; ?>
                                                            </a>
                                                        </li>
                                                    <?php endfor; ?>

                                                    <li class="page-item <?php echo $page == $total_pages ? 'disabled' : ''; ?>">
                                                        <a class="page-link"
                                                            href="?action=list&search=<?php echo urlencode($search); ?>&status=<?php echo $status_filter; ?>&customer=<?php echo $customer_filter; ?>&page=<?php echo $page + 1; ?>">
                                                            <i class="bi bi-chevron-right"></i>
                                                        </a>
                                                    </li>
                                                </ul>
                                            </nav>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </div>

                        <?php elseif ($action == 'add'): ?>
                            <!-- Add New Udhar Entry Form -->
                            <div class="bill-form-container">
                                <div class="bill-form-header">
                                    <h3><i class="bi bi-plus-circle"></i> New Udhar Entry (Bill)</h3>
                                </div>
                                <div class="bill-form-body">
                                    <form method="POST" action="" id="udharForm">
                                        <div class="bill-form-section">
                                            <h5><i class="bi bi-info-circle"></i> Basic Information</h5>
                                            <div class="bill-form-row">
                                                <div class="bill-form-group" style="grid-column: span 2;">
                                                    <label for="customer_search"><i class="bi bi-person"></i> Customer
                                                        *</label>
                                                    <div class="position-relative">
                                                        <div class="input-group">
                                                            <input type="text" class="bill-form-control"
                                                                id="customer_search" name="customer_search"
                                                                placeholder="Type customer name or mobile number..."
                                                                required>
                                                            <button type="button" class="btn btn-outline-secondary"
                                                                id="customer_search_btn" title="Search Customer">
                                                                <i class="bi bi-search"></i>
                                                            </button>
                                                        </div>
                                                        <div class="form-text">Start typing to search existing customers or
                                                            click search button</div>
                                                    </div>

                                                    <!-- Hidden field to store selected customer ID -->
                                                    <input type="hidden" id="customer_id" name="customer_id" value="">

                                                    <!-- Customer info display (optional) -->
                                                    <div id="customer_info" class="mt-2" style="display: none;">
                                                        <div class="card">
                                                            <div class="card-body py-2">
                                                                <div
                                                                    class="d-flex justify-content-between align-items-center">
                                                                    <div>
                                                                        <strong id="selected_customer_name"></strong>
                                                                        <span id="selected_customer_mobile"
                                                                            class="text-muted ms-2"></span>
                                                                    </div>
                                                                    <div>
                                                                        Balance: <span id="selected_customer_balance"
                                                                            class="badge bg-warning"></span>
                                                                    </div>
                                                                </div>
                                                                <small id="selected_customer_address"
                                                                    class="text-muted"></small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="bill-form-row">
                                                <div class="bill-form-group">
                                                    <label for="category"><i class="bi bi-tags"></i> Category *</label>
                                                    <select class="bill-form-control" id="category" name="category"
                                                        required>
                                                        <option value="">Select Category</option>
                                                        <option value="Fertilizers">Fertilizers</option>
                                                        <option value="Seeds">Seeds</option>
                                                        <option value="Insecticides">Insecticides</option>
                                                        <option value="Others">Others</option>
                                                    </select>
                                                </div>

                                                <div class="bill-form-group">
                                                    <label for="transaction_date"><i class="bi bi-calendar"></i> Bill
                                                        Date *</label>
                                                    <input type="date" class="bill-form-control" id="transaction_date"
                                                        name="transaction_date" value="<?php echo date('Y-m-d'); ?>"
                                                        required>
                                                </div>

                                                <div class="bill-form-group">
                                                    <label for="due_date"><i class="bi bi-calendar-check"></i> Due Date
                                                        (Optional)</label>
                                                    <input type="date" class="bill-form-control" id="due_date"
                                                        name="due_date">
                                                </div>
                                            </div>

                                            <div class="bill-form-row">
                                                <div class="bill-form-group" style="grid-column: span 2;">
                                                    <label for="description"><i class="bi bi-card-text"></i>
                                                        Description</label>
                                                    <input type="text" class="bill-form-control" id="description"
                                                        name="description" placeholder="Enter bill description">
                                                </div>

                                                <div class="bill-form-group">
                                                    <label for="notes"><i class="bi bi-sticky"></i> Notes</label>
                                                    <input type="text" class="bill-form-control" id="notes" name="notes"
                                                        placeholder="Additional notes">
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Items Section -->
                                        <div class="bill-form-section">
                                            <h5><i class="bi bi-cart-plus"></i> Bill Items</h5>
                                            <div class="bill-items-container table-responsive">
                                                <table class="table table-bordered table-hover align-middle mb-0"
                                                    id="billTable">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th style="min-width: 200px;">Item</th>
                                                            <th style="width: 100px;">HSN</th>
                                                            <th style="width: 100px;">Qty</th>
                                                            <th style="width: 80px;">Unit</th>
                                                            <th style="width: 130px;">Price</th>
                                                            <th style="width: 180px;">GST (%)</th>
                                                            <th style="width: 120px;">Total</th>
                                                            <th style="width: 50px;"></th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="itemsBody">
                                                        <!-- Items will be added here dynamically -->
                                                    </tbody>
                                                </table>

                                                <div class="d-flex gap-2">
                                                    <button type="button" class="btn-add-item" onclick="addItemRow()">
                                                        <i class="bi bi-plus-circle"></i> Add Item
                                                    </button>
                                                    <button type="button" class="btn btn-outline-info"
                                                        onclick="addItemFromList()">
                                                        <i class="bi bi-list-check"></i> Add from Items List
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Totals Section -->
                                        <div class="bill-form-section bill-summary-enhanced">
                                            <div class="row g-4 align-items-stretch">
                                                <div class="col-lg-8 mb-3 mb-lg-0">
                                                    <div class="card h-100 shadow-sm p-4">
                                                        <h5 class="mb-4"><i class="bi bi-calculator"></i> Bill Summary</h5>
                                                        <div class="row g-3">
                                                            <div class="col-md-6">
                                                                <div class="bill-form-group">
                                                                    <label for="discount"
                                                                        class="form-label">Discount</label>
                                                                    <div class="input-group">
                                                                        <input type="number" class="bill-form-control"
                                                                            id="discount" name="discount" step="0.01"
                                                                            min="0" placeholder="0.00" value="0">
                                                                        <select class="bill-form-control" id="discount_type"
                                                                            name="discount_type" style="max-width: 120px;">
                                                                            <option value="fixed">₹ Fixed</option>
                                                                            <option value="percentage">% Percentage</option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="bill-form-group">
                                                                    <label for="round_off" class="form-label">Round
                                                                        Off</label>
                                                                    <div class="input-group">
                                                                        <input type="number" class="bill-form-control"
                                                                            id="round_off" name="round_off" step="0.01"
                                                                            placeholder="0.00" value="0">
                                                                        <span class="input-group-text">₹</span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-lg-4">
                                                    <div
                                                        class="bill-summary-card shadow-sm h-100 d-flex flex-column justify-content-between">
                                                        <table class="bill-summary-table mb-0">
                                                            <tr>
                                                                <td class="summary-label">Sub Total:</td>
                                                                <td class="summary-value"><span id="subTotal">0.00</span>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td class="summary-label">CGST:</td>
                                                                <td class="summary-value"><span id="cgstTotal">0.00</span>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td class="summary-label">SGST:</td>
                                                                <td class="summary-value"><span id="sgstTotal">0.00</span>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td class="summary-label">IGST:</td>
                                                                <td class="summary-value"><span id="igstTotal">0.00</span>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td class="summary-label">Discount:</td>
                                                                <td class="summary-value"><span
                                                                        id="discountTotal">0.00</span></td>
                                                            </tr>
                                                            <tr>
                                                                <td class="summary-label">Round Off:</td>
                                                                <td class="summary-value"><span
                                                                        id="roundOffTotal">0.00</span></td>
                                                            </tr>
                                                            <tr>
                                                                <td class="summary-label grand-total">Grand Total:</td>
                                                                <td class="summary-value grand-total"><span
                                                                        id="grandTotal">0.00</span></td>
                                                            </tr>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="d-flex justify-content-between mt-4">
                                            <a href="udhar.php" class="btn btn-outline-secondary">
                                                <i class="bi bi-x-circle"></i> Cancel
                                            </a>
                                            <button type="submit" name="add_udhar" class="btn btn-primary btn-lg">
                                                <i class="bi bi-check-circle"></i> Create Bill & Print
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>

                        <?php elseif (($action == 'edit' || $action == 'view') && $udhar): ?>
                            <!-- Edit/View Udhar Entry -->
                            <div class="bill-view-container">
                                <div class="bill-view-card">
                                    <div class="bill-view-header">
                                        <h3>
                                            <i class="bi bi-<?php echo $action == 'edit' ? 'pencil' : 'eye'; ?>"></i>
                                            <?php echo $action == 'edit' ? 'Edit Udhar Entry' : 'Udhar Entry Details'; ?>
                                            <span
                                                class="bill-number-badge"><?php echo htmlspecialchars($udhar['bill_no']); ?></span>
                                        </h3>
                                    </div>
                                    <div class="bill-view-body">
                                        <?php if ($action == 'edit'): ?>
                                            <form method="POST" action="">
                                                <input type="hidden" name="udhar_id" value="<?php echo $udhar['id']; ?>">

                                                <div class="bill-info-section">
                                                    <div class="bill-info-card">
                                                        <h5>Bill Information</h5>
                                                        <table class="bill-info-table">
                                                            <tr>
                                                                <td class="bill-info-label">Bill Number:</td>
                                                                <td class="bill-info-value">
                                                                    <?php echo htmlspecialchars($udhar['bill_no']); ?>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td class="bill-info-label">Customer:</td>
                                                                <td class="bill-info-value">
                                                                    <?php echo htmlspecialchars($udhar['customer_name']); ?>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td class="bill-info-label">Bill Date:</td>
                                                                <td class="bill-info-value">
                                                                    <?php echo date('d M Y', strtotime($udhar['transaction_date'])); ?>
                                                                </td>
                                                            </tr>
                                                        </table>
                                                    </div>

                                                    <div class="bill-info-card">
                                                        <h5>Update Details</h5>
                                                        <div class="bill-form-group">
                                                            <label for="description">Description</label>
                                                            <input type="text" class="bill-form-control" id="description"
                                                                name="description"
                                                                value="<?php echo htmlspecialchars($udhar['description']); ?>"
                                                                required>
                                                        </div>

                                                        <div class="bill-form-group">
                                                            <label for="due_date">Due Date</label>
                                                            <input type="date" class="bill-form-control" id="due_date"
                                                                name="due_date"
                                                                value="<?php echo !empty($udhar['due_date']) ? $udhar['due_date'] : ''; ?>">
                                                        </div>

                                                        <div class="bill-form-group">
                                                            <label for="status">Status</label>
                                                            <select class="bill-form-control" id="status" name="status"
                                                                required>
                                                                <option value="pending" <?php echo $udhar['status'] == 'pending' ? 'selected' : ''; ?>>
                                                                    Pending</option>
                                                                <option value="partially_paid" <?php echo $udhar['status'] == 'partially_paid' ? 'selected' : ''; ?>>
                                                                    Partially Paid</option>
                                                                <option value="paid" <?php echo $udhar['status'] == 'paid' ? 'selected' : ''; ?>>Paid</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="bill-form-group">
                                                    <label for="notes">Notes</label>
                                                    <textarea class="bill-form-control" id="notes" name="notes"
                                                        rows="3"><?php echo htmlspecialchars($udhar['notes']); ?></textarea>
                                                </div>

                                                <div class="d-flex justify-content-between mt-4">
                                                    <div>
                                                        <button type="submit" name="update_udhar" class="btn btn-primary">
                                                            <i class="bi bi-check-circle"></i> Update Udhar Entry
                                                        </button>
                                                        <a href="udhar.php?action=view&id=<?php echo $udhar['id']; ?>"
                                                            class="btn btn-outline-secondary">
                                                            <i class="bi bi-x-circle"></i> Cancel
                                                        </a>
                                                    </div>
                                                    <button type="button" class="btn btn-danger" data-bs-toggle="modal"
                                                        data-bs-target="#deleteModal">
                                                        <i class="bi bi-trash"></i> Delete
                                                    </button>
                                                </div>
                                            </form>
                                        <?php else: ?>
                                            <!-- View Mode -->
                                            <div class="bill-info-section">
                                                <div class="bill-info-card">
                                                    <h5>Customer Information</h5>
                                                    <table class="bill-info-table">
                                                        <tr>
                                                            <td class="bill-info-label">Customer:</td>
                                                            <td class="bill-info-value">
                                                                <?php echo htmlspecialchars($udhar['customer_name']); ?>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="bill-info-label">Mobile:</td>
                                                            <td class="bill-info-value">
                                                                <?php echo htmlspecialchars($udhar['customer_mobile']); ?>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="bill-info-label">Bill Date:</td>
                                                            <td class="bill-info-value">
                                                                <?php echo date('d M Y', strtotime($udhar['transaction_date'])); ?>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="bill-info-label">Due Date:</td>
                                                            <td class="bill-info-value">
                                                                <?php if (!empty($udhar['due_date'])): ?>
                                                                    <?php echo date('d M Y', strtotime($udhar['due_date'])); ?>
                                                                    <?php if (strtotime($udhar['due_date']) < time() && $udhar['status'] != 'paid'): ?>
                                                                        <span class="udhar-overdue-badge">Overdue</span>
                                                                    <?php endif; ?>
                                                                <?php else: ?>
                                                                    <span class="text-muted">Not set</span>
                                                                <?php endif; ?>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </div>

                                                <div class="bill-info-card">
                                                    <h5>Bill Summary</h5>
                                                    <table class="bill-info-table">
                                                        <tr>
                                                            <td class="bill-info-label">Status:</td>
                                                            <td class="bill-info-value">
                                                                <?php
                                                                $status_class = 'udhar-status-pending';
                                                                if ($udhar['status'] == 'paid') {
                                                                    $status_class = 'udhar-status-paid';
                                                                } elseif ($udhar['status'] == 'partially_paid') {
                                                                    $status_class = 'udhar-status-partially_paid';
                                                                }
                                                                ?>
                                                                <span class="udhar-status-badge <?php echo $status_class; ?>">
                                                                    <?php echo ucfirst(str_replace('_', ' ', $udhar['status'])); ?>
                                                                </span>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="bill-info-label">Sub Total:</td>
                                                            <td class="bill-info-value">
                                                                ₹<?php echo number_format($udhar['total_amount'], 2); ?>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="bill-info-label">CGST:</td>
                                                            <td class="bill-info-value">
                                                                ₹<?php echo number_format($udhar['cgst_amount'], 2); ?></td>
                                                        </tr>
                                                        <tr>
                                                            <td class="bill-info-label">SGST:</td>
                                                            <td class="bill-info-value">
                                                                ₹<?php echo number_format($udhar['sgst_amount'], 2); ?></td>
                                                        </tr>
                                                        <?php if ($udhar['igst_amount'] > 0): ?>
                                                            <tr>
                                                                <td class="bill-info-label">IGST:</td>
                                                                <td class="bill-info-value">
                                                                    ₹<?php echo number_format($udhar['igst_amount'], 2); ?></td>
                                                            </tr>
                                                        <?php endif; ?>
                                                        <?php if ($udhar['discount'] > 0): ?>
                                                            <tr>
                                                                <td class="bill-info-label">Discount:</td>
                                                                <td class="bill-info-value">
                                                                    -₹<?php echo number_format($udhar['discount'], 2); ?></td>
                                                            </tr>
                                                        <?php endif; ?>
                                                        <tr>
                                                            <td class="bill-info-label">Grand Total:</td>
                                                            <td class="bill-info-value fw-bold">
                                                                ₹<?php echo number_format($udhar['grand_total'], 2); ?></td>
                                                        </tr>
                                                    </table>
                                                </div>
                                            </div>

                                            <?php if (!empty($udhar['description'])): ?>
                                                <div class="alert alert-info mt-3">
                                                    <strong>Description:</strong><br>
                                                    <?php echo htmlspecialchars($udhar['description']); ?>
                                                </div>
                                            <?php endif; ?>

                                            <?php if (!empty($udhar['notes'])): ?>
                                                <div class="alert alert-light mt-3">
                                                    <strong>Notes:</strong><br>
                                                    <?php echo htmlspecialchars($udhar['notes']); ?>
                                                </div>
                                            <?php endif; ?>

                                            <!-- Items List -->
                                            <div class="bill-items-section">
                                                <h5>Bill Items</h5>
                                                <div class="bill-items-list">
                                                    <div class="bill-item-row header">
                                                        <div class="bill-item-sno">#</div>
                                                        <div class="bill-item-name">Item Name</div>
                                                        <div class="bill-item-qty">Qty</div>
                                                        <div class="bill-item-price">Price</div>
                                                        <div class="bill-item-gst-view">GST</div>
                                                        <div class="bill-item-total-view">Total</div>
                                                    </div>

                                                    <?php if (empty($udhar_items)): ?>
                                                        <div class="text-center py-4">
                                                            <p class="text-muted">No items in this bill</p>
                                                        </div>
                                                    <?php else: ?>
                                                        <?php foreach ($udhar_items as $index => $item): ?>
                                                            <div class="bill-item-row">
                                                                <div class="bill-item-sno"><?php echo $index + 1; ?></div>
                                                                <div class="bill-item-name">
                                                                    <?php echo htmlspecialchars($item['item_name']); ?>
                                                                </div>
                                                                <div class="bill-item-qty">
                                                                    <?php echo number_format($item['quantity'], 2); ?>
                                                                </div>
                                                                <div class="bill-item-price">
                                                                    ₹<?php echo number_format($item['unit_price'], 2); ?></div>
                                                                <div class="bill-item-gst-view">
                                                                    <?php if ($item['igst_rate'] > 0): ?>
                                                                        IGST: <?php echo $item['igst_rate']; ?>%
                                                                    <?php else: ?>
                                                                        CGST: <?php echo $item['cgst_rate']; ?>%<br>
                                                                        SGST: <?php echo $item['sgst_rate']; ?>%
                                                                    <?php endif; ?>
                                                                </div>
                                                                <div class="bill-item-total-view">
                                                                    ₹<?php echo number_format($item['total_amount'], 2); ?></td>
                                                                </div>
                                                            <?php endforeach; ?>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>

                                                <div class="d-flex justify-content-between mt-4">
                                                    <a href="print_bill.php?id=<?php echo $udhar['id']; ?>"
                                                        class="btn btn-primary" target="_blank">
                                                        <i class="bi bi-printer"></i> Print Bill
                                                    </a>
                                                    <div>
                                                        <a href="udhar.php?action=edit&id=<?php echo $udhar['id']; ?>"
                                                            class="btn btn-warning">
                                                            <i class="bi bi-pencil"></i> Edit
                                                        </a>
                                                        <a href="udhar.php" class="btn btn-outline-secondary">
                                                            <i class="bi bi-arrow-left"></i> Back to List
                                                        </a>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- Delete Confirmation Modal -->
                                <?php if ($action == 'edit'): ?>
                                    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel"
                                        aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                        aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="alert alert-warning">
                                                        <i class="bi bi-exclamation-triangle"></i>
                                                        <strong>Warning:</strong> Are you sure you want to delete this udhar
                                                        entry?
                                                    </div>
                                                    <p class="mb-0">
                                                        Bill No:
                                                        <strong><?php echo htmlspecialchars($udhar['bill_no']); ?></strong><br>
                                                        Customer:
                                                        <strong><?php echo htmlspecialchars($udhar['customer_name']); ?></strong><br>
                                                        Amount:
                                                        <strong>₹<?php echo number_format($udhar['amount'], 2); ?></strong>
                                                    </p>
                                                    <p class="mt-2 text-danger">
                                                        This action cannot be undone. All items and payments related to this
                                                        entry will also be deleted.
                                                    </p>
                                                </div>
                                                <div class="modal-footer">
                                                    <form method="POST" action="">
                                                        <input type="hidden" name="udhar_id"
                                                            value="<?php echo $udhar['id']; ?>">
                                                        <button type="submit" name="delete_udhar" class="btn btn-danger">
                                                            <i class="bi bi-trash"></i> Delete Permanently
                                                        </button>
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                            <i class="bi bi-x-circle"></i> Cancel
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Items Modal for Selection -->
                <div class="modal fade" id="itemsModal" tabindex="-1" aria-labelledby="itemsModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="itemsModalLabel">Select Items</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="table-responsive">
                                    <table class="table table-hover" id="itemsSelectTable">
                                        <thead>
                                            <tr>
                                                <th width="5%">Select</th>
                                                <th width="30%">Item Name</th>
                                                <th width="15%">HSN Code</th>
                                                <th width="15%">Price</th>
                                                <th width="20%">GST</th>
                                                <th width="15%">Unit</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($items as $itm): ?>
                                                <tr>
                                                    <td>
                                                        <input type="checkbox" class="form-check-input item-checkbox"
                                                            value='<?php echo json_encode($itm); ?>'>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($itm['item_name']); ?></td>
                                                    <td><?php echo htmlspecialchars($itm['hsn_code']); ?></td>
                                                    <td>₹<?php echo number_format($itm['price'], 2); ?></td>
                                                    <td>
                                                        <?php if ($itm['igst_rate'] > 0): ?>
                                                            IGST: <?php echo $itm['igst_rate']; ?>%
                                                        <?php else: ?>
                                                            CGST: <?php echo $itm['cgst_rate']; ?>%<br>
                                                            SGST: <?php echo $itm['sgst_rate']; ?>%
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($itm['unit']); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="button" class="btn btn-primary" onclick="addSelectedItems()">Add
                                    Selected Items</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
    </div>

    <script>
        // Sidebar toggle for both mobile and desktop
        // Sidebar toggle function
        function toggleSidebar() {
            const sidebar = document.querySelector(".sidebar");
            const mainContent = document.querySelector(".main-content");

            sidebar.classList.toggle("closed");
            mainContent.classList.toggle("expanded");
        }

        // Sidebar toggle button inside sidebar
        const sidebarToggleBtn = document.getElementById("sidebarToggle");
        if (sidebarToggleBtn) {
            sidebarToggleBtn.addEventListener("click", toggleSidebar);
        }

        // Floating toggle button (visible when sidebar is closed)
        const floatingToggleBtn = document.getElementById("floatingToggle");
        if (floatingToggleBtn) {
            floatingToggleBtn.addEventListener("click", toggleSidebar);
        }

        // Auto-hide sidebar on mobile when clicking outside
        document.addEventListener("click", function(event) {
            const sidebar = document.querySelector(".sidebar");
            const toggleBtn = document.getElementById("sidebarToggle");
            const floatingBtn = document.getElementById("floatingToggle");
            const mainContent = document.querySelector(".main-content");

            if (
                window.innerWidth <= 768 &&
                !sidebar.contains(event.target) &&
                !toggleBtn.contains(event.target) &&
                !floatingBtn.contains(event.target) &&
                !sidebar.classList.contains("closed")
            ) {
                sidebar.classList.add("closed");
      mainContent.classList.add("expanded");
            }
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="assets/js/search_suggestions.js"></script>
    <script>
        // Global variables
        let itemCounter = 0;
        const items = <?php echo json_encode($items); ?>;
        const preSelectedItemId = <?php echo $item_id; ?>;

        // Initialize customer search suggestions
        document.addEventListener('DOMContentLoaded', function() {
            if (document.getElementById('customer_search')) {
                const customerSearch = new SearchSuggestions('#customer_search', {
                    apiUrl: 'api/search_customers.php',
                    minChars: 2,
                    delay: 300,
                    maxSuggestions: 10,
                    onSelect: function(customer) {
                        const infoDiv = document.getElementById('customer_info');
                        document.getElementById('customer_id').value = customer.id;
                        document.getElementById('selected_customer_name').textContent = customer.name;
                        document.getElementById('selected_customer_mobile').textContent = customer.mobile || '';
                        document.getElementById('selected_customer_address').textContent = customer.address || 'No address provided';

                        // Display balance
                        const balance = parseFloat(customer.balance) || 0;
                        const balanceBadge = document.getElementById('selected_customer_balance');
                        balanceBadge.textContent = '₹' + Math.abs(balance).toFixed(2);

                        if (balance > 0) {
                            balanceBadge.className = 'badge bg-danger';
                            balanceBadge.textContent += ' (Due)';
                        } else if (balance < 0) {
                            balanceBadge.className = 'badge bg-success';
                            balanceBadge.textContent += ' (Advance)';
                        } else {
                            balanceBadge.className = 'badge bg-secondary';
                            balanceBadge.textContent += ' (Clear)';
                        }

                        infoDiv.style.display = 'block';
                    }
                });

            // Optional: Add "Add New Customer" button functionality
            const customerSearchInput = document.getElementById('customer_search');
            customerSearchInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' && customerSearchInput.value.trim() !== '' && !document.getElementById('customer_id').value) {
                    e.preventDefault();
                    if (confirm(`Customer "${customerSearchInput.value}" not found. Would you like to add as a new customer?`)) {
                        // You can redirect to customer add page or show a modal
                        window.location.href = `customers.php?action=add&name=${encodeURIComponent(customerSearchInput.value)}`;
                    }
                }
            });
        }
        });

        // Add item row dynamically
        function addItemRow(itemData = null) {
            const tbody = document.getElementById('itemsBody');
            const row = document.createElement('tr');
            row.id = 'itemRow_' + itemCounter;

            // Default values
            const defaultItem = itemData || {
                id: '',
                item_name: '',
                hsn_code: '',
                price: '0.00',
                cgst_rate: '2.5',
                sgst_rate: '2.5',
                igst_rate: '0.00',
                unit: 'PCS'
            };

            row.innerHTML = `
        <td>
            <select class="form-select form-select-sm item-select" name="items[${itemCounter}][item_id]" 
                    onchange="updateItemDetails(${itemCounter})" required>
                <option value="">Select Item</option>
                ${items.map(item => `
                    <option value="${item.id}" 
                            data-name="${item.item_name}"
                            data-hsn="${item.hsn_code}"
                            data-price="${item.price}"
                            data-cgst="${item.cgst_rate}"
                            data-sgst="${item.sgst_rate}"
                            data-igst="${item.igst_rate}"
                            data-unit="${item.unit}"
                            ${item.id == defaultItem.id ? 'selected' : ''}>
                        ${item.item_name}
                    </option>
                `).join('')}
            </select>
            <input type="hidden" name="items[${itemCounter}][item_name]" 
                   value="${defaultItem.item_name}" class="item-name">
        </td>
        <td>
            <input type="text" class="form-control form-control-sm item-hsn" 
                   name="items[${itemCounter}][hsn_code]" 
                   value="${defaultItem.hsn_code}" 
                   placeholder="HSN" readonly>
        </td>
        <td>
            <input type="number" class="form-control form-control-sm quantity" 
                   name="items[${itemCounter}][quantity]" 
                   value="1" step="0.01" min="0.01" 
                   onchange="calculateItemTotal(${itemCounter})" required>
        </td>
        <td>
            <input type="text" class="form-control form-control-sm-plaintext item-unit text-center" 
                   name="items[${itemCounter}][unit]" 
                   value="${defaultItem.unit}" 
                   readonly>
        </td>
        <td>
            <div class="input-group input-group-sm">
                <span class="input-group-text">₹</span>
                <input type="number" class="form-control price" 
                       name="items[${itemCounter}][price]" 
                       value="${defaultItem.price}" step="0.01" min="0.01" 
                       onchange="calculateItemTotal(${itemCounter})" required>
            </div>
        </td>
        <td>
            <div class="row g-1">
                <div class="col-6">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text px-1" title="CGST">C</span>
                        <input type="number" class="form-control px-1 cgst-rate" 
                               name="items[${itemCounter}][cgst_rate]" 
                               value="${defaultItem.cgst_rate}" step="0.01" min="0" max="100"
                               onchange="calculateItemTotal(${itemCounter})">
                    </div>
                </div>
                <div class="col-6">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text px-1" title="SGST">S</span>
                        <input type="number" class="form-control px-1 sgst-rate" 
                               name="items[${itemCounter}][sgst_rate]" 
                               value="${defaultItem.sgst_rate}" step="0.01" min="0" max="100"
                               onchange="calculateItemTotal(${itemCounter})">
                    </div>
                </div>
                <div class="col-12">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text px-1" title="IGST">I</span>
                        <input type="number" class="form-control px-1 igst-rate" 
                               name="items[${itemCounter}][igst_rate]" 
                               value="${defaultItem.igst_rate}" step="0.01" min="0" max="100"
                               onchange="calculateItemTotal(${itemCounter})">
                    </div>
                </div>
            </div>
        </td>
        <td class="text-end fw-bold">
            ₹<span class="item-total">0.00</span>
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-outline-danger border-0" 
                    onclick="removeItemRow(${itemCounter})">
                <i class="bi bi-trash"></i>
            </button>
        </td>
                        `;

            tbody.appendChild(row);
            itemCounter++;
            calculateItemTotal(itemCounter - 1);
            calculateTotals();
        }

        // Update item details when selection changes
        function updateItemDetails(rowIndex) {
            const row = document.getElementById('itemRow_' + rowIndex);
            const select = row.querySelector('.item-select');
            const selectedOption = select.options[select.selectedIndex];

            if (selectedOption.value) {
                row.querySelector('.item-name').value = selectedOption.dataset.name;
                row.querySelector('.item-hsn').value = selectedOption.dataset.hsn;
                row.querySelector('input[name="items[' + rowIndex + '][hsn_code]"]').value = selectedOption.dataset.hsn;
                row.querySelector('input[name="items[' + rowIndex + '][unit]"]').value = selectedOption.dataset.unit;
                row.querySelector('.price').value = selectedOption.dataset.price;
                row.querySelector('.cgst-rate').value = selectedOption.dataset.cgst;
                row.querySelector('.sgst-rate').value = selectedOption.dataset.sgst;
                row.querySelector('.igst-rate').value = selectedOption.dataset.igst;
            } else {
                row.querySelector('.item-name').value = '';
                row.querySelector('.item-hsn').value = '';
                row.querySelector('input[name="items[' + rowIndex + '][hsn_code]"]').value = '';
                row.querySelector('input[name="items[' + rowIndex + '][unit]"]').value = 'PCS';
                row.querySelector('.price').value = '0.00';
                row.querySelector('.cgst-rate').value = '2.5';
                row.querySelector('.sgst-rate').value = '2.5';
                row.querySelector('.igst-rate').value = '0.00';
            }

            calculateItemTotal(rowIndex);
        }

        // Calculate item total
        function calculateItemTotal(rowIndex) {
            const row = document.getElementById('itemRow_' + rowIndex);
            const quantity = parseFloat(row.querySelector('.quantity').value) || 0;
            const price = parseFloat(row.querySelector('.price').value) || 0;
            const cgstRate = parseFloat(row.querySelector('.cgst-rate').value) || 0;
            const sgstRate = parseFloat(row.querySelector('.sgst-rate').value) || 0;
            const igstRate = parseFloat(row.querySelector('.igst-rate').value) || 0;

            const itemTotal = quantity * price;
            row.querySelector('.item-total').textContent = itemTotal.toFixed(2);

            calculateTotals();
        }

        // Remove item row
        function removeItemRow(rowIndex) {
            const row = document.getElementById('itemRow_' + rowIndex);
            row.remove();
            calculateTotals();
        }

        // Calculate all totals
        function calculateTotals() {
            let subTotal = 0;
            let cgstTotal = 0;
            let sgstTotal = 0;
            let igstTotal = 0;

            // Calculate from all item rows
            for (let i = 0; i < itemCounter; i++) {
                const row = document.getElementById('itemRow_' + i);
                if (row) {
                    const quantity = parseFloat(row.querySelector('.quantity').value) || 0;
                    const price = parseFloat(row.querySelector('.price').value) || 0;
                    const cgstRate = parseFloat(row.querySelector('.cgst-rate').value) || 0;
                    const sgstRate = parseFloat(row.querySelector('.sgst-rate').value) || 0;
                    const igstRate = parseFloat(row.querySelector('.igst-rate').value) || 0;

                    const itemTotal = quantity * price;
                    subTotal += itemTotal;

                    if (igstRate > 0) {
                        igstTotal += (itemTotal * igstRate) / 100;
                    } else {
                        cgstTotal += (itemTotal * cgstRate) / 100;
                        sgstTotal += (itemTotal * sgstRate) / 100;
                    }
                }
            }

            // Calculate discount
            const discount = parseFloat(document.getElementById('discount').value) || 0;
            const discountType = document.getElementById('discount_type').value;
            let discountAmount = 0;

            if (discountType === 'percentage') {
                discountAmount = (subTotal * discount) / 100;
            } else {
                discountAmount = discount;
            }

            // Round off
            const roundOff = parseFloat(document.getElementById('round_off').value) || 0;

            // Calculate grand total
            const grandTotal = subTotal + cgstTotal + sgstTotal + igstTotal - discountAmount + roundOff;

            // Update display
            document.getElementById('subTotal').textContent = subTotal.toFixed(2);
            document.getElementById('cgstTotal').textContent = cgstTotal.toFixed(2);
            document.getElementById('sgstTotal').textContent = sgstTotal.toFixed(2);
            document.getElementById('igstTotal').textContent = igstTotal.toFixed(2);
            document.getElementById('discountTotal').textContent = discountAmount.toFixed(2);
            document.getElementById('roundOffTotal').textContent = roundOff.toFixed(2);
            document.getElementById('grandTotal').textContent = grandTotal.toFixed(2);
        }

        // Show items modal for selection
        function addItemFromList() {
            const modal = new bootstrap.Modal(document.getElementById('itemsModal'));
            modal.show();
        }

        // Add selected items from modal
        function addSelectedItems() {
            const checkboxes = document.querySelectorAll('.item-checkbox:checked');
            checkboxes.forEach(checkbox => {
                const itemData = JSON.parse(checkbox.value);
                addItemRow(itemData);
            });

            // Close modal
            bootstrap.Modal.getInstance(document.getElementById('itemsModal')).hide();

            // Uncheck all checkboxes
            document.querySelectorAll('.item-checkbox').forEach(cb => cb.checked = false);
        }

        // Form validation
        document.getElementById('udharForm')?.addEventListener('submit', function(e) {
            // Check if customer is selected
            const customerId = document.getElementById('customer_id').value;
            if (!customerId) {
                e.preventDefault();
                alert('Please select a customer');
                document.getElementById('customer_search').focus();
                return false;
            }

            // Check if at least one item is added
            if (itemCounter === 0) {
                e.preventDefault();
                alert('Please add at least one item to the bill');
                return false;
            }

            // Check all items have valid data
            let hasErrors = false;
            for (let i = 0; i < itemCounter; i++) {
                const row = document.getElementById('itemRow_' + i);
                if (row) {
                    const itemId = row.querySelector('.item-select').value;
                    const quantity = row.querySelector('.quantity').value;
                    const price = row.querySelector('.price').value;

                    if (!itemId || parseFloat(quantity) <= 0 || parseFloat(price) <= 0) {
                        hasErrors = true;
                        break;
                    }
                }
            }

            if (hasErrors) {
                e.preventDefault();
                alert('Please fill all item details correctly');
                return false;
            }

            // Calculate totals one more time before submit
            calculateTotals();

            return true;
        });

        // Initialize with one empty row if adding new udhar
        <?php if ($action == 'add'): ?>
                    window.addEventListener('DOMContentLoaded', function() {
                        <?php if ($item_id > 0): ?>
                                    // Add pre-selected item
                                    <?php
                                    $pre_selected_item = null;
                                    foreach ($items as $itm) {
                                        if ($itm['id'] == $item_id) {
                                            $pre_selected_item = $itm;
                                            break;
                                        }
                                    }
                                    if ($pre_selected_item): ?>
                                                addItemRow(<?php echo json_encode($pre_selected_item); ?>);
                                    <?php else: ?>
                                                addItemRow();
                                    <?php endif; ?>
                        <?php else: ?>
                                    addItemRow();
                        <?php endif; ?>

                        // Auto-focus first customer field if coming from customer page
                        <?php if ($customer_id > 0): ?>
                                    document.getElementById('customer_id').value = <?php echo $customer_id; ?>;
                                    // Get customer name to populate the search field
                                    <?php
                                    $stmt = $conn->prepare("SELECT name FROM customers WHERE id = ? AND user_id = ?");
                                    $stmt->bind_param("ii", $customer_id, $_SESSION['user_id']);
                                    $stmt->execute();
                                    $result = $stmt->get_result();
                                    $customer = $result->fetch_assoc();
                                    $stmt->close();
                                    if ($customer): ?>
                                                document.getElementById('customer_search').value = "<?php echo addslashes(htmlspecialchars($customer['name'])); ?>";
                                    <?php endif; ?>
                        <?php endif; ?>
                    });
        <?php endif; ?>

        // Delete confirmation
        function confirmDelete(id, billNo) {
            if (confirm('Are you sure you want to delete bill "' + billNo + '"? This action cannot be undone.')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '';

                const input1 = document.createElement('input');
                input1.type = 'hidden';
                input1.name = 'udhar_id';
                input1.value = id;

                const input2 = document.createElement('input');
                input2.type = 'hidden';
                input2.name = 'delete_udhar';
                input2.value = '1';

                form.appendChild(input1);
                form.appendChild(input2);
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Update totals on discount change
        document.getElementById('discount')?.addEventListener('input', calculateTotals);
        document.getElementById('discount_type')?.addEventListener('change', calculateTotals);
        document.getElementById('round_off')?.addEventListener('input', calculateTotals);
    </script>
</body>

</html>