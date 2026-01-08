<?php
// File: smart-udhar-system/customers_control.php

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
            $stmt = $conn->prepare("INSERT INTO customers (user_id, name, mobile, email, address, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, 'active', NOW(), NOW())");
            $status = 'active';
            $stmt->bind_param("issss", $_SESSION['user_id'], $name, $mobile, $email, $address);

            if ($stmt->execute()) {
                $new_id = $stmt->insert_id;
                error_log("Customer added successfully. ID: " . $new_id . " Name: " . $name);
                setMessage("Entity '" . $name . "' has been successfully initialized in the data core.", "success");
                header("Location: customers.php?id=" . $new_id . "&action=view");
                exit();
            } else {
                error_log("Error adding customer: " . $stmt->error);
                setMessage("Database rejection: " . $stmt->error, "danger");
            }
            $stmt->close();
        } else {
            setMessage("Protocol violation: " . implode(" | ", $errors), "danger");
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

// Quick Stats for list view
if ($action == 'list') {
    $active_stmt = $conn->prepare("SELECT COUNT(*) as count FROM customers WHERE user_id = ? AND status = 'active'");
    $active_stmt->bind_param("i", $_SESSION['user_id']);
    $active_stmt->execute();
    $active_count = $active_stmt->get_result()->fetch_assoc()['count'];
    $active_stmt->close();

    $due_stmt = $conn->prepare("SELECT SUM(balance) as total FROM customers WHERE user_id = ? AND balance > 0");
    $due_stmt->bind_param("i", $_SESSION['user_id']);
    $due_stmt->execute();
    $due_total = $due_stmt->get_result()->fetch_assoc()['total'] ?? 0;
    $due_stmt->close();

    $adv_stmt = $conn->prepare("SELECT SUM(balance) as total FROM customers WHERE user_id = ? AND balance < 0");
    $adv_stmt->bind_param("i", $_SESSION['user_id']);
    $adv_stmt->execute();
    $adv_total = $adv_stmt->get_result()->fetch_assoc()['total'] ?? 0;
    $adv_stmt->close();
}

// Transaction and Payment history for detail view
if ($action == 'view' && $customer) {
    // Get recent transactions
    $trans_stmt = $conn->prepare("
        SELECT 
            ut.*,
            COALESCE(SUM(CASE WHEN ui.quantity > 0 AND ui.total_amount > 0 THEN ui.total_amount ELSE 0 END), 0) AS items_total,
            COALESCE(SUM(CASE WHEN ui.quantity > 0 AND ui.total_amount > 0 THEN 1 ELSE 0 END), 0) AS items_count
        FROM udhar_transactions ut
        LEFT JOIN udhar_items ui ON ui.udhar_id = ut.id
        WHERE ut.customer_id = ?
        GROUP BY ut.id
        ORDER BY ut.transaction_date DESC 
        LIMIT 5
    ");
    $trans_stmt->bind_param("i", $customer['id']);
    $trans_stmt->execute();
    $transactions = $trans_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $trans_stmt->close();

    foreach ($transactions as &$trans) {
        $items_count = (int) ($trans['items_count'] ?? 0);
        if ($items_count <= 0) {
            $trans['amount'] = 0.00;
        } else {
            $items_total = (float) ($trans['items_total'] ?? 0);
            $discount_amount = (float) ($trans['discount'] ?? 0);
            $transportation_charge = (float) ($trans['transportation_charge'] ?? 0);
            $round_off = (float) ($trans['round_off'] ?? 0);
            $trans['amount'] = round($items_total - $discount_amount + $transportation_charge + $round_off, 2);
        }
    }
    unset($trans);

    $transactions = array_values(array_filter($transactions, function ($trans) {
        return (float) ($trans['amount'] ?? 0) > 0;
    }));

    // Get recent payments
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
}

$page_title = "Customer Management";
