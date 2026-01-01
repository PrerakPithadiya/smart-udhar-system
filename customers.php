<?php
// File: smart-udhar-system/customers.php

require_once 'config/database.php';

requireLogin();

$conn = getDBConnection();

// Handle actions
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$customer_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Process form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_customer'])) {
        // Add new customer
        $name = sanitizeInput($_POST['name']);
        $mobile = sanitizeInput($_POST['mobile']);
        $email = sanitizeInput($_POST['email']);
        $address = sanitizeInput($_POST['address']);

        // Validation
        $errors = [];

        if (empty($name)) {
            $errors[] = "Customer name is required";
        }

        if (!empty($mobile) && !preg_match('/^[0-9]{10}$/', $mobile)) {
            $errors[] = "Mobile number must be 10 digits";
        }

        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email address";
        }

        if (empty($errors)) {
            $stmt = $conn->prepare("INSERT INTO customers (user_id, name, mobile, email, address) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("issss", $_SESSION['user_id'], $name, $mobile, $email, $address);

            if ($stmt->execute()) {
                setMessage("Customer added successfully!", "success");
                header("Location: customers.php");
                exit();
            } else {
                setMessage("Error adding customer: " . $stmt->error, "danger");
            }
            $stmt->close();
        } else {
            setMessage(implode("<br>", $errors), "danger");
        }
    }

    if (isset($_POST['update_customer'])) {
        // Update customer
        $id = intval($_POST['customer_id']);
        $name = sanitizeInput($_POST['name']);
        $mobile = sanitizeInput($_POST['mobile']);
        $email = sanitizeInput($_POST['email']);
        $address = sanitizeInput($_POST['address']);
        $status = sanitizeInput($_POST['status']);

        $stmt = $conn->prepare("UPDATE customers SET name = ?, mobile = ?, email = ?, address = ?, status = ?, updated_at = NOW() WHERE id = ? AND user_id = ?");
        $stmt->bind_param("sssssii", $name, $mobile, $email, $address, $status, $id, $_SESSION['user_id']);

        if ($stmt->execute()) {
            setMessage("Customer updated successfully!", "success");
            header("Location: customers.php");
            exit();
        } else {
            setMessage("Error updating customer: " . $stmt->error, "danger");
        }
        $stmt->close();
    }

    if (isset($_POST['delete_customer'])) {
        // Delete customer
        $id = intval($_POST['customer_id']);

        // Check if customer has any transactions
        $check_stmt = $conn->prepare("SELECT COUNT(*) as count FROM udhar_transactions WHERE customer_id = ?");
        $check_stmt->bind_param("i", $id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        $row = $result->fetch_assoc();
        $check_stmt->close();

        if ($row['count'] > 0) {
            setMessage("Cannot delete customer with existing transactions. Please delete transactions first or mark as inactive.", "warning");
        } else {
            $stmt = $conn->prepare("DELETE FROM customers WHERE id = ? AND user_id = ?");
            $stmt->bind_param("ii", $id, $_SESSION['user_id']);

            if ($stmt->execute()) {
                setMessage("Customer deleted successfully!", "success");
            } else {
                setMessage("Error deleting customer: " . $stmt->error, "danger");
            }
            $stmt->close();
        }
        header("Location: customers.php");
        exit();
    }
}

// Get customer for edit
$customer = null;
if ($customer_id > 0 && ($action == 'edit' || $action == 'view')) {
    $stmt = $conn->prepare("SELECT * FROM customers WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $customer_id, $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $customer = $result->fetch_assoc();
    $stmt->close();
}

// Get all customers for listing
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? sanitizeInput($_GET['status']) : '';

$where_clause = "WHERE user_id = " . $_SESSION['user_id'];
$params = [];

if (!empty($search)) {
    $where_clause .= " AND (name LIKE ? OR mobile LIKE ? OR email LIKE ?)";
    $search_term = "%$search%";
    $params = array_fill(0, 3, $search_term);
}

if (!empty($status_filter) && in_array($status_filter, ['active', 'inactive'])) {
    $where_clause .= " AND status = ?";
    $params[] = $status_filter;
}

// Get total customers count
$count_stmt = $conn->prepare("SELECT COUNT(*) as total FROM customers $where_clause");
if (!empty($params)) {
    $types = str_repeat('s', count($params));
    $count_stmt->bind_param($types, ...$params);
}
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$total_customers = $count_result->fetch_assoc()['total'];
$count_stmt->close();

// Pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;
$total_pages = ceil($total_customers / $limit);

// Get customers with pagination
$order_by = isset($_GET['order_by']) ? sanitizeInput($_GET['order_by']) : 'created_at';
$order_dir = isset($_GET['order_dir']) ? sanitizeInput($_GET['order_dir']) : 'DESC';

// Validate order parameters
$allowed_columns = ['name', 'mobile', 'balance', 'created_at'];
$order_by = in_array($order_by, $allowed_columns) ? $order_by : 'created_at';
$order_dir = in_array(strtoupper($order_dir), ['ASC', 'DESC']) ? strtoupper($order_dir) : 'DESC';

$query = "SELECT * FROM customers $where_clause ORDER BY $order_by $order_dir LIMIT ? OFFSET ?";
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
$customers = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Management - Smart Udhar System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">

    <!-- Link to external CSS file -->
    <link rel="stylesheet" href="assets/css/customers.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

    <?php include 'includes/sidebar.php'; ?>


    <div class="main-content">
        <!-- Floating Toggle Button (visible when sidebar is closed) -->
        <button class="floating-toggle-btn" id="floatingToggle">
            <i class="bi bi-chevron-right"></i>
        </button>

        <div
            class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
            <div class="d-flex align-items-center">
                <h1 class="h2 mb-0">
                    <i class="bi bi-people-fill"></i> Customer Management
                </h1>
            </div>
            <div class="btn-toolbar">
                <?php if ($action == 'list'): ?>
                    <a href="customers.php?action=add" class="btn btn-primary">
                        <i class="bi bi-person-plus"></i> Add New Customer
                    </a>
                <?php else: ?>
                    <a href="customers.php" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Back to List
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Display Messages -->
        <?php displayMessage(); ?>

        <?php if ($action == 'list'): ?>
            <!-- Filter and Search Section -->
            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-body">
                    <form method="GET" class="row g-3 align-items-end">
                        <input type="hidden" name="action" value="list">

                        <div class="col-md-3">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-funnel"></i> Filter by Status
                            </label>
                            <select name="status" class="form-select" onchange="this.form.submit()">
                                <option value="">All Status</option>
                                <option value="active" <?php echo $status_filter == 'active' ? 'selected' : ''; ?>>Active Only
                                </option>
                                <option value="inactive" <?php echo $status_filter == 'inactive' ? 'selected' : ''; ?>>
                                    Inactive Only</option>
                            </select>
                        </div>

                        <div class="col-md-7">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-search"></i> Search Customers
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-white">
                                    <i class="bi bi-search text-muted"></i>
                                </span>
                                <input type="text" name="search" id="customer-search" class="form-control"
                                    placeholder="Search by name, mobile or email..."
                                    value="<?php echo htmlspecialchars($search); ?>"
                                    data-api-url="api/search_customers.php">
                                <?php if (!empty($search)): ?>
                                    <a href="customers.php?action=list" class="btn btn-outline-secondary">
                                        <i class="bi bi-x-lg"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-search"></i> Search
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Customer List View -->
            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-header bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-people"></i> All Customers
                            <span class="badge bg-primary rounded-pill"><?php echo $total_customers; ?></span>
                        </h5>
                        <div class="text-muted small">
                            Showing <?php echo count($customers); ?> of <?php echo $total_customers; ?> customers
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (empty($customers)): ?>
                        <div class="text-center py-5">
                            <i class="bi bi-people display-1 text-muted"></i>
                            <h4 class="mt-3">No customers found</h4>
                            <p class="text-muted">Get started by adding your first customer</p>
                            <a href="customers.php?action=add" class="btn btn-primary">
                                <i class="bi bi-person-plus"></i> Add First Customer
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>
                                            <a
                                                href="?action=list&search=<?php echo urlencode($search); ?>&status=<?php echo $status_filter; ?>&order_by=name&order_dir=<?php echo $order_by == 'name' && $order_dir == 'ASC' ? 'DESC' : 'ASC'; ?>">
                                                Customer
                                                <?php if ($order_by == 'name'): ?>
                                                    <i class="bi bi-chevron-<?php echo $order_dir == 'ASC' ? 'up' : 'down'; ?>"></i>
                                                <?php endif; ?>
                                            </a>
                                        </th>
                                        <th>Mobile</th>
                                        <th>
                                            <a
                                                href="?action=list&search=<?php echo urlencode($search); ?>&status=<?php echo $status_filter; ?>&order_by=balance&order_dir=<?php echo $order_by == 'balance' && $order_dir == 'ASC' ? 'DESC' : 'ASC'; ?>">
                                                Balance
                                                <?php if ($order_by == 'balance'): ?>
                                                    <i class="bi bi-chevron-<?php echo $order_dir == 'ASC' ? 'up' : 'down'; ?>"></i>
                                                <?php endif; ?>
                                            </a>
                                        </th>
                                        <th>Status</th>
                                        <th>Last Transaction</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($customers as $customer): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="customer-avatar me-3">
                                                        <?php echo strtoupper(substr($customer['name'], 0, 1)); ?>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0"><?php echo htmlspecialchars($customer['name']); ?></h6>
                                                        <small
                                                            class="text-muted"><?php echo htmlspecialchars($customer['email']); ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <?php if (!empty($customer['mobile'])): ?>
                                                    <a href="tel:<?php echo htmlspecialchars($customer['mobile']); ?>"
                                                        class="text-decoration-none">
                                                        <i class="bi bi-telephone"></i>
                                                        <?php echo htmlspecialchars($customer['mobile']); ?>
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted">N/A</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php
                                                $balance_class = 'balance-zero';
                                                if ($customer['balance'] > 0) {
                                                    $balance_class = 'balance-negative';
                                                } elseif ($customer['balance'] < 0) {
                                                    $balance_class = 'balance-positive';
                                                }
                                                ?>
                                                <span class="balance-badge <?php echo $balance_class; ?>">
                                                    ₹<?php echo number_format(abs($customer['balance']), 2); ?>
                                                    <?php if ($customer['balance'] > 0): ?>
                                                        <small class="ms-1">(Due)</small>
                                                    <?php elseif ($customer['balance'] < 0): ?>
                                                        <small class="ms-1">(Advance)</small>
                                                    <?php endif; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="status-badge status-<?php echo $customer['status']; ?>">
                                                    <?php echo ucfirst($customer['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if (!empty($customer['last_transaction_date'])): ?>
                                                    <?php echo date('d M Y', strtotime($customer['last_transaction_date'])); ?>
                                                <?php else: ?>
                                                    <span class="text-muted">No transactions</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="customers.php?action=view&id=<?php echo $customer['id']; ?>"
                                                        class="btn btn-sm btn-outline-info" title="View Details">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    <a href="customers.php?action=edit&id=<?php echo $customer['id']; ?>"
                                                        class="btn btn-sm btn-outline-primary" title="Edit Customer">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    <div class="btn-group" role="group">
                                                        <button type="button"
                                                            class="btn btn-sm btn-outline-secondary dropdown-toggle"
                                                            data-bs-toggle="dropdown" aria-expanded="false" title="More Actions">
                                                            <i class="bi bi-three-dots-vertical"></i>
                                                        </button>
                                                        <ul class="dropdown-menu dropdown-menu-end">
                                                            <li>
                                                                <a class="dropdown-item"
                                                                    href="udhar.php?action=add&customer_id=<?php echo $customer['id']; ?>">
                                                                    <i class="bi bi-plus-circle text-success"></i> Add Udhar Entry
                                                                </a>
                                                            </li>
                                                            <li>
                                                                <a class="dropdown-item"
                                                                    href="payments.php?action=add&customer_id=<?php echo $customer['id']; ?>">
                                                                    <i class="bi bi-cash text-warning"></i> Receive Payment
                                                                </a>
                                                            </li>
                                                            <li>
                                                                <hr class="dropdown-divider">
                                                            </li>
                                                            <li>
                                                                <a class="dropdown-item text-danger" href="#"
                                                                    onclick="if(confirm('Are you sure you want to delete this customer?')) { document.getElementById('deleteForm<?php echo $customer['id']; ?>').submit(); } return false;">
                                                                    <i class="bi bi-trash"></i> Delete Customer
                                                                </a>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                                <form id="deleteForm<?php echo $customer['id']; ?>" method="POST"
                                                    style="display:none;">
                                                    <input type="hidden" name="customer_id" value="<?php echo $customer['id']; ?>">
                                                    <input type="hidden" name="delete_customer" value="1">
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                            <nav aria-label="Page navigation">
                                <ul class="pagination justify-content-center">
                                    <li class="page-item <?php echo $page == 1 ? 'disabled' : ''; ?>">
                                        <a class="page-link"
                                            href="?action=list&search=<?php echo urlencode($search); ?>&status=<?php echo $status_filter; ?>&page=<?php echo $page - 1; ?>">
                                            <i class="bi bi-chevron-left"></i>
                                        </a>
                                    </li>

                                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                            <a class="page-link"
                                                href="?action=list&search=<?php echo urlencode($search); ?>&status=<?php echo $status_filter; ?>&page=<?php echo $i; ?>">
                                                <?php echo $i; ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>

                                    <li class="page-item <?php echo $page == $total_pages ? 'disabled' : ''; ?>">
                                        <a class="page-link"
                                            href="?action=list&search=<?php echo urlencode($search); ?>&status=<?php echo $status_filter; ?>&page=<?php echo $page + 1; ?>">
                                            <i class="bi bi-chevron-right"></i>
                                        </a>
                                    </li>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="card-title">Total Customers</h6>
                                    <h3><?php echo number_format($total_customers); ?></h3>
                                </div>
                                <div>
                                    <i class="bi bi-people display-6 opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="card-title">Active Customers</h6>
                                    <?php
                                    $active_stmt = $conn->prepare("SELECT COUNT(*) as count FROM customers WHERE user_id = ? AND status = 'active'");
                                    $active_stmt->bind_param("i", $_SESSION['user_id']);
                                    $active_stmt->execute();
                                    $active_count = $active_stmt->get_result()->fetch_assoc()['count'];
                                    $active_stmt->close();
                                    ?>
                                    <h3><?php echo number_format($active_count); ?></h3>
                                </div>
                                <div>
                                    <i class="bi bi-check-circle display-6 opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="card-title">Total Due Amount</h6>
                                    <?php
                                    $due_stmt = $conn->prepare("SELECT SUM(balance) as total FROM customers WHERE user_id = ? AND balance > 0");
                                    $due_stmt->bind_param("i", $_SESSION['user_id']);
                                    $due_stmt->execute();
                                    $due_total = $due_stmt->get_result()->fetch_assoc()['total'] ?? 0;
                                    $due_stmt->close();
                                    ?>
                                    <h3>₹<?php echo number_format($due_total, 2); ?></h3>
                                </div>
                                <div>
                                    <i class="bi bi-cash-coin display-6 opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="card-title">Total Advance</h6>
                                    <?php
                                    $adv_stmt = $conn->prepare("SELECT SUM(balance) as total FROM customers WHERE user_id = ? AND balance < 0");
                                    $adv_stmt->bind_param("i", $_SESSION['user_id']);
                                    $adv_stmt->execute();
                                    $adv_total = $adv_stmt->get_result()->fetch_assoc()['total'] ?? 0;
                                    $adv_stmt->close();
                                    ?>
                                    <h3>₹<?php echo number_format(abs($adv_total), 2); ?></h3>
                                </div>
                                <div>
                                    <i class="bi bi-wallet2 display-6 opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        <?php elseif ($action == 'add' || $action == 'edit'): ?>
            <!-- Add/Edit Customer Form -->
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="bi bi-person-<?php echo $action == 'add' ? 'plus' : 'check'; ?>"></i>
                                <?php echo $action == 'add' ? 'Add New Customer' : 'Edit Customer'; ?>
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="">
                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <div class="form-floating">
                                            <input type="text" class="form-control" id="name" name="name"
                                                placeholder="Customer Name" required
                                                value="<?php echo $action == 'edit' && $customer ? htmlspecialchars($customer['name']) : ''; ?>">
                                            <label for="name"><i class="bi bi-person"></i> Customer Name *</label>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <input type="tel" class="form-control" id="mobile" name="mobile"
                                                placeholder="Mobile Number" pattern="[0-9]{10}"
                                                value="<?php echo $action == 'edit' && $customer ? htmlspecialchars($customer['mobile']) : ''; ?>">
                                            <label for="mobile"><i class="bi bi-phone"></i> Mobile Number</label>
                                            <small class="text-muted">10 digits without spaces</small>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <input type="email" class="form-control" id="email" name="email"
                                                placeholder="Email Address"
                                                value="<?php echo $action == 'edit' && $customer ? htmlspecialchars($customer['email']) : ''; ?>">
                                            <label for="email"><i class="bi bi-envelope"></i> Email Address</label>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <div class="form-floating">
                                            <textarea class="form-control" id="address" name="address" placeholder="Address"
                                                style="height: 100px"><?php echo $action == 'edit' && $customer ? htmlspecialchars($customer['address']) : ''; ?></textarea>
                                            <label for="address"><i class="bi bi-house-door"></i> Address</label>
                                        </div>
                                    </div>

                                    <?php if ($action == 'edit' && $customer): ?>
                                        <div class="col-md-6">
                                            <div class="form-floating">
                                                <select class="form-select" id="status" name="status" required>
                                                    <option value="active" <?php echo $customer['status'] == 'active' ? 'selected' : ''; ?>>Active</option>
                                                    <option value="inactive" <?php echo $customer['status'] == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                                </select>
                                                <label for="status"><i class="bi bi-circle-fill"></i> Status</label>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="card bg-light">
                                                <div class="card-body">
                                                    <h6 class="card-title">Current Balance</h6>
                                                    <h4
                                                        class="<?php echo $customer['balance'] > 0 ? 'text-danger' : ($customer['balance'] < 0 ? 'text-success' : 'text-muted'); ?>">
                                                        ₹<?php echo number_format($customer['balance'], 2); ?>
                                                        <?php if ($customer['balance'] > 0): ?>
                                                            <small class="text-muted">(Due)</small>
                                                        <?php elseif ($customer['balance'] < 0): ?>
                                                            <small class="text-muted">(Advance)</small>
                                                        <?php endif; ?>
                                                    </h4>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="mt-4">
                                    <?php if ($action == 'edit' && $customer): ?>
                                        <input type="hidden" name="customer_id" value="<?php echo $customer['id']; ?>">
                                        <button type="submit" name="update_customer" class="btn btn-primary">
                                            <i class="bi bi-check-circle"></i> Update Customer
                                        </button>
                                        <button type="button" class="btn btn-danger" data-bs-toggle="modal"
                                            data-bs-target="#deleteModal">
                                            <i class="bi bi-trash"></i> Delete Customer
                                        </button>
                                    <?php else: ?>
                                        <button type="submit" name="add_customer" class="btn btn-primary">
                                            <i class="bi bi-person-plus"></i> Add Customer
                                        </button>
                                    <?php endif; ?>
                                    <a href="customers.php" class="btn btn-outline-secondary">
                                        <i class="bi bi-x-circle"></i> Cancel
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Delete Confirmation Modal -->
            <?php if ($action == 'edit' && $customer): ?>
                <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="alert alert-warning">
                                    <i class="bi bi-exclamation-triangle"></i>
                                    <strong>Warning:</strong> Are you sure you want to delete customer
                                    <strong><?php echo htmlspecialchars($customer['name']); ?></strong>?
                                </div>
                                <p class="mb-0">This action cannot be undone. All customer data including udhar history will be
                                    permanently deleted.</p>
                            </div>
                            <div class="modal-footer">
                                <form method="POST" action="">
                                    <input type="hidden" name="customer_id" value="<?php echo $customer['id']; ?>">
                                    <button type="submit" name="delete_customer" class="btn btn-danger">
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

        <?php elseif ($action == 'view' && $customer): ?>
            <!-- Customer Detail View -->
            <div class="row">
                <div class="col-lg-4">
                    <div class="card mb-4">
                        <div class="card-body text-center">
                            <div class="customer-avatar mx-auto mb-3" style="width: 100px; height: 100px; font-size: 40px;">
                                <?php echo strtoupper(substr($customer['name'], 0, 1)); ?>
                            </div>
                            <h4><?php echo htmlspecialchars($customer['name']); ?></h4>
                            <span class="status-badge status-<?php echo $customer['status']; ?>">
                                <?php echo ucfirst($customer['status']); ?>
                            </span>

                            <div class="mt-4">
                                <?php if (!empty($customer['mobile'])): ?>
                                    <p>
                                        <i class="bi bi-telephone text-primary"></i>
                                        <a href="tel:<?php echo htmlspecialchars($customer['mobile']); ?>"
                                            class="text-decoration-none">
                                            <?php echo htmlspecialchars($customer['mobile']); ?>
                                        </a>
                                    </p>
                                <?php endif; ?>

                                <?php if (!empty($customer['email'])): ?>
                                    <p>
                                        <i class="bi bi-envelope text-primary"></i>
                                        <a href="mailto:<?php echo htmlspecialchars($customer['email']); ?>"
                                            class="text-decoration-none">
                                            <?php echo htmlspecialchars($customer['email']); ?>
                                        </a>
                                    </p>
                                <?php endif; ?>
                            </div>

                            <div class="mt-4">
                                <a href="customers.php?action=edit&id=<?php echo $customer['id']; ?>"
                                    class="btn btn-primary btn-sm">
                                    <i class="bi bi-pencil"></i> Edit Profile
                                </a>
                                <a href="udhar.php?action=add&customer_id=<?php echo $customer['id']; ?>"
                                    class="btn btn-success btn-sm">
                                    <i class="bi bi-plus-circle"></i> Add Udhar
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="bi bi-info-circle"></i> Customer Information</h6>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm">
                                <tr>
                                    <th>Customer ID</th>
                                    <td>#<?php echo str_pad($customer['id'], 5, '0', STR_PAD_LEFT); ?></td>
                                </tr>
                                <tr>
                                    <th>Member Since</th>
                                    <td><?php echo date('d M Y', strtotime($customer['created_at'])); ?></td>
                                </tr>
                                <tr>
                                    <th>Last Updated</th>
                                    <td><?php echo date('d M Y', strtotime($customer['updated_at'])); ?></td>
                                </tr>
                                <tr>
                                    <th>Last Transaction</th>
                                    <td>
                                        <?php if (!empty($customer['last_transaction_date'])): ?>
                                            <?php echo date('d M Y', strtotime($customer['last_transaction_date'])); ?>
                                        <?php else: ?>
                                            <span class="text-muted">No transactions</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-lg-8">
                    <!-- Balance Summary -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card bg-primary text-white">
                                <div class="card-body text-center">
                                    <h6 class="card-title">Total Udhar Given</h6>
                                    <h3>₹<?php echo number_format($customer['total_udhar'], 2); ?></h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center">
                                    <h6 class="card-title">Total Paid</h6>
                                    <h3>₹<?php echo number_format($customer['total_paid'], 2); ?></h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div
                                class="card <?php echo $customer['balance'] > 0 ? 'bg-danger' : ($customer['balance'] < 0 ? 'bg-warning' : 'bg-secondary'); ?> text-white">
                                <div class="card-body text-center">
                                    <h6 class="card-title">Current Balance</h6>
                                    <h3>₹<?php echo number_format(abs($customer['balance']), 2); ?></h3>
                                    <?php if ($customer['balance'] > 0): ?>
                                        <small>(Due)</small>
                                    <?php elseif ($customer['balance'] < 0): ?>
                                        <small>(Advance)</small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Transactions -->
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0"><i class="bi bi-clock-history"></i> Recent Transactions</h6>
                            <a href="reports.php?customer_id=<?php echo $customer['id']; ?>"
                                class="btn btn-sm btn-outline-primary">
                                View All
                            </a>
                        </div>
                        <div class="card-body">
                            <?php
                            // Get recent transactions for this customer
                            $trans_stmt = $conn->prepare("
                                        SELECT * FROM udhar_transactions 
                                        WHERE customer_id = ? 
                                        ORDER BY transaction_date DESC 
                                        LIMIT 5
                                    ");
                            $trans_stmt->bind_param("i", $customer['id']);
                            $trans_stmt->execute();
                            $transactions = $trans_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                            $trans_stmt->close();
                            ?>

                            <?php if (empty($transactions)): ?>
                                <div class="text-center py-4">
                                    <i class="bi bi-receipt display-1 text-muted"></i>
                                    <p class="mt-3">No transactions yet</p>
                                    <a href="udhar.php?action=add&customer_id=<?php echo $customer['id']; ?>"
                                        class="btn btn-primary">
                                        <i class="bi bi-plus-circle"></i> Add First Transaction
                                    </a>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Description</th>
                                                <th>Amount</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($transactions as $trans): ?>
                                                <tr>
                                                    <td><?php echo date('d M Y', strtotime($trans['transaction_date'])); ?></td>
                                                    <td><?php echo htmlspecialchars($trans['description']); ?></td>
                                                    <td class="fw-bold">₹<?php echo number_format($trans['amount'], 2); ?></td>
                                                    <td>
                                                        <?php
                                                        $status_class = '';
                                                        switch ($trans['status']) {
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
                                                            <?php echo ucfirst(str_replace('_', ' ', $trans['status'])); ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Recent Payments -->
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0"><i class="bi bi-cash-coin"></i> Recent Payments</h6>
                            <a href="payments.php?customer_id=<?php echo $customer['id']; ?>"
                                class="btn btn-sm btn-outline-primary">
                                View All
                            </a>
                        </div>
                        <div class="card-body">
                            <?php
                            // Get recent payments for this customer
                            $pay_stmt = $conn->prepare("
                                        SELECT * FROM payments 
                                        WHERE customer_id = ? 
                                        ORDER BY payment_date DESC 
                                        LIMIT 5
                                    ");
                            $pay_stmt->bind_param("i", $customer['id']);
                            $pay_stmt->execute();
                            $payments = $pay_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                            $pay_stmt->close();
                            ?>

                            <?php if (empty($payments)): ?>
                                <div class="text-center py-4">
                                    <i class="bi bi-cash display-1 text-muted"></i>
                                    <p class="mt-3">No payments yet</p>
                                    <a href="payments.php?action=add&customer_id=<?php echo $customer['id']; ?>"
                                        class="btn btn-success">
                                        <i class="bi bi-cash-coin"></i> Receive Payment
                                    </a>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Amount</th>
                                                <th>Payment Mode</th>
                                                <th>Reference</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($payments as $payment): ?>
                                                <tr>
                                                    <td><?php echo date('d M Y', strtotime($payment['payment_date'])); ?></td>
                                                    <td class="fw-bold text-success">
                                                        ₹<?php echo number_format($payment['amount'], 2); ?></td>
                                                    <td>
                                                        <span class="badge bg-light text-dark">
                                                            <?php echo ucfirst($payment['payment_mode']); ?>
                                                        </span>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($payment['reference_no'] ?? 'N/A'); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Link to external JavaScript file -->
    <script src="assets/js/common.js"></script>
    <script src="assets/js/customers.js"></script>
    <!-- Search Suggestions Feature -->
    <script src="assets/js/search_suggestions.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Initialize search suggestions for customer search
            const customerSearch = new SearchSuggestions('#customer-search', {
                apiUrl: 'api/search_customers.php',
                minChars: 1,
                delay: 300,
                onSelect: function (suggestion) {
                    // When a suggestion is selected, redirect to the customer details page
                    window.location.href = `customers.php?action=view&id=${suggestion.id}`;
                }
            });
        });
    </script>
</body>

</html>