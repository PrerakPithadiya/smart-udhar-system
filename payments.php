<?php
// File: smart-udhar-system/payments.php

require_once 'config/database.php';
requireLogin();

$conn = getDBConnection();

// Handle actions
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$payment_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$customer_id = isset($_GET['customer_id']) ? intval($_GET['customer_id']) : 0;

// Process form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_payment'])) {
        // Add new payment
        $customer_id = intval($_POST['customer_id']);
        $payment_date = sanitizeInput($_POST['payment_date']);
        $amount = floatval($_POST['amount']);
        $payment_mode = sanitizeInput($_POST['payment_mode']);
        $reference_no = sanitizeInput($_POST['reference_no']);
        $notes = sanitizeInput($_POST['notes']);

        // Validation
        $errors = [];

        if ($customer_id <= 0) {
            $errors[] = "Please select a customer";
        }

        if (empty($payment_date)) {
            $errors[] = "Payment date is required";
        }

        if ($amount <= 0) {
            $errors[] = "Amount must be greater than 0";
        }

        if (empty($errors)) {
            $conn->begin_transaction();

            try {
                // Get customer name
                $stmt = $conn->prepare("SELECT name FROM customers WHERE id = ? AND user_id = ?");
                $stmt->bind_param("ii", $customer_id, $_SESSION['user_id']);
                $stmt->execute();
                $result = $stmt->get_result();
                $customer = $result->fetch_assoc();
                $stmt->close();

                if (!$customer) {
                    throw new Exception("Customer not found");
                }

                // Insert payment
                $stmt = $conn->prepare("INSERT INTO payments (customer_id, customer_name, payment_date, amount, payment_mode, reference_no, notes, remaining_amount) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $remaining_amount = $amount; // Initially remaining amount equals payment amount
                $stmt->bind_param("isssdssd", $customer_id, $customer['name'], $payment_date, $amount, $payment_mode, $reference_no, $notes, $remaining_amount);

                if (!$stmt->execute()) {
                    throw new Exception("Error adding payment: " . $stmt->error);
                }

                $payment_id = $stmt->insert_id;
                $stmt->close();

                // If auto-allocate is requested
                if (isset($_POST['auto_allocate']) && $_POST['auto_allocate'] == '1') {
                    allocatePaymentToUdhar($conn, $payment_id, $customer_id, $amount);
                }

                $conn->commit();

                setMessage("Payment added successfully!", "success");
                header("Location: payments.php?action=view&id=$payment_id");
                exit();
            } catch (Exception $e) {
                $conn->rollback();
                setMessage("Error: " . $e->getMessage(), "danger");
            }
        } else {
            setMessage(implode("<br>", $errors), "danger");
        }
    }

    if (isset($_POST['allocate_payment'])) {
        // Allocate payment to udhar entries
        $payment_id = intval($_POST['payment_id']);
        $allocations = $_POST['allocations'] ?? [];

        if (empty($allocations)) {
            setMessage("Please select at least one udhar entry to allocate", "warning");
        } else {
            $conn->begin_transaction();

            try {
                $total_allocated = 0;

                foreach ($allocations as $udhar_id => $alloc_amount) {
                    $alloc_amount = floatval($alloc_amount);
                    if ($alloc_amount > 0) {
                        // Check if udhar entry exists and belongs to same customer as payment
                        $check_stmt = $conn->prepare("
                            SELECT ut.id, ut.remaining_amount, ut.customer_id 
                            FROM udhar_transactions ut 
                            JOIN payments p ON p.id = ? 
                            WHERE ut.id = ? AND ut.customer_id = p.customer_id
                        ");
                        $check_stmt->bind_param("ii", $payment_id, $udhar_id);
                        $check_stmt->execute();
                        $result = $check_stmt->get_result();
                        $udhar = $result->fetch_assoc();
                        $check_stmt->close();

                        if (!$udhar) {
                            throw new Exception("Invalid udhar entry selected");
                        }

                        if ($alloc_amount > $udhar['remaining_amount']) {
                            throw new Exception("Allocation amount cannot exceed remaining amount");
                        }

                        // Insert allocation
                        $alloc_stmt = $conn->prepare("INSERT INTO payment_allocations (payment_id, udhar_transaction_id, allocated_amount) VALUES (?, ?, ?)");
                        $alloc_stmt->bind_param("iid", $payment_id, $udhar_id, $alloc_amount);

                        if (!$alloc_stmt->execute()) {
                            throw new Exception("Error creating allocation: " . $alloc_stmt->error);
                        }
                        $alloc_stmt->close();

                        // Update udhar transaction remaining amount
                        $update_stmt = $conn->prepare("UPDATE udhar_transactions SET remaining_amount = remaining_amount - ?, status = CASE WHEN (remaining_amount - ?) <= 0 THEN 'paid' ELSE 'partially_paid' END WHERE id = ?");
                        $update_stmt->bind_param("ddi", $alloc_amount, $alloc_amount, $udhar_id);

                        if (!$update_stmt->execute()) {
                            throw new Exception("Error updating udhar entry: " . $update_stmt->error);
                        }
                        $update_stmt->close();

                        $total_allocated += $alloc_amount;
                    }
                }

                // Update payment allocation status
                $payment_stmt = $conn->prepare("UPDATE payments SET allocated_amount = allocated_amount + ?, remaining_amount = remaining_amount - ?, is_allocated = 1, allocation_date = CURDATE() WHERE id = ?");
                $payment_stmt->bind_param("ddi", $total_allocated, $total_allocated, $payment_id);

                if (!$payment_stmt->execute()) {
                    throw new Exception("Error updating payment: " . $payment_stmt->error);
                }
                $payment_stmt->close();

                $conn->commit();

                setMessage("Payment allocated successfully! Total allocated: ₹" . number_format($total_allocated, 2), "success");
                header("Location: payments.php?action=view&id=$payment_id");
                exit();
            } catch (Exception $e) {
                $conn->rollback();
                setMessage("Error: " . $e->getMessage(), "danger");
            }
        }
    }

    if (isset($_POST['update_payment'])) {
        // Update payment
        $id = intval($_POST['payment_id']);
        $payment_date = sanitizeInput($_POST['payment_date']);
        $amount = floatval($_POST['amount']);
        $payment_mode = sanitizeInput($_POST['payment_mode']);
        $reference_no = sanitizeInput($_POST['reference_no']);
        $notes = sanitizeInput($_POST['notes']);

        $stmt = $conn->prepare("UPDATE payments SET payment_date = ?, amount = ?, payment_mode = ?, reference_no = ?, notes = ? WHERE id = ? AND customer_id IN (SELECT id FROM customers WHERE user_id = ?)");
        $stmt->bind_param("sdsssii", $payment_date, $amount, $payment_mode, $reference_no, $notes, $id, $_SESSION['user_id']);

        if ($stmt->execute()) {
            setMessage("Payment updated successfully!", "success");
            header("Location: payments.php?action=view&id=$id");
            exit();
        } else {
            setMessage("Error updating payment: " . $stmt->error, "danger");
        }
        $stmt->close();
    }

    if (isset($_POST['delete_payment'])) {
        // Delete payment
        $id = intval($_POST['payment_id']);

        $conn->begin_transaction();

        try {
            // Check if payment has allocations
            $check_stmt = $conn->prepare("SELECT COUNT(*) as count FROM payment_allocations WHERE payment_id = ?");
            $check_stmt->bind_param("i", $id);
            $check_stmt->execute();
            $result = $check_stmt->get_result();
            $row = $result->fetch_assoc();
            $check_stmt->close();

            if ($row['count'] > 0) {
                // Remove allocations first
                $alloc_stmt = $conn->prepare("DELETE FROM payment_allocations WHERE payment_id = ?");
                $alloc_stmt->bind_param("i", $id);

                if (!$alloc_stmt->execute()) {
                    throw new Exception("Error removing allocations: " . $alloc_stmt->error);
                }
                $alloc_stmt->close();
            }

            // Delete payment
            $stmt = $conn->prepare("DELETE FROM payments WHERE id = ? AND customer_id IN (SELECT id FROM customers WHERE user_id = ?)");
            $stmt->bind_param("ii", $id, $_SESSION['user_id']);

            if ($stmt->execute()) {
                $conn->commit();
                setMessage("Payment deleted successfully!", "success");
            } else {
                throw new Exception("Error deleting payment: " . $stmt->error);
            }
            $stmt->close();
        } catch (Exception $e) {
            $conn->rollback();
            setMessage("Error: " . $e->getMessage(), "danger");
        }

        header("Location: payments.php");
        exit();
    }
}

// Helper function to auto-allocate payment
function allocatePaymentToUdhar($conn, $payment_id, $customer_id, $amount)
{
    // Get oldest pending udhar entries for this customer
    $stmt = $conn->prepare("
        SELECT id, remaining_amount 
        FROM udhar_transactions 
        WHERE customer_id = ? AND status IN ('pending', 'partially_paid') 
        ORDER BY transaction_date ASC, id ASC
    ");
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $udhar_entries = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    $remaining_payment = $amount;

    foreach ($udhar_entries as $entry) {
        if ($remaining_payment <= 0) break;

        $alloc_amount = min($entry['remaining_amount'], $remaining_payment);

        if ($alloc_amount > 0) {
            // Insert allocation
            $alloc_stmt = $conn->prepare("INSERT INTO payment_allocations (payment_id, udhar_transaction_id, allocated_amount) VALUES (?, ?, ?)");
            $alloc_stmt->bind_param("iid", $payment_id, $entry['id'], $alloc_amount);
            $alloc_stmt->execute();
            $alloc_stmt->close();

            // Update udhar transaction
            $update_stmt = $conn->prepare("UPDATE udhar_transactions SET remaining_amount = remaining_amount - ?, status = CASE WHEN (remaining_amount - ?) <= 0 THEN 'paid' ELSE 'partially_paid' END WHERE id = ?");
            $update_stmt->bind_param("ddi", $alloc_amount, $alloc_amount, $entry['id']);
            $update_stmt->execute();
            $update_stmt->close();

            $remaining_payment -= $alloc_amount;
        }
    }

    // Update payment allocation status
    $allocated_amount = $amount - $remaining_payment;
    if ($allocated_amount > 0) {
        $payment_stmt = $conn->prepare("UPDATE payments SET allocated_amount = ?, remaining_amount = ?, is_allocated = 1, allocation_date = CURDATE() WHERE id = ?");
        $payment_stmt->bind_param("ddi", $allocated_amount, $remaining_payment, $payment_id);
        $payment_stmt->execute();
        $payment_stmt->close();
    }
}

// Get payment for edit/view
$payment = null;
$allocations = [];
if ($payment_id > 0 && ($action == 'edit' || $action == 'view' || $action == 'allocate')) {
    $stmt = $conn->prepare("SELECT p.*, c.name as customer_name, c.mobile as customer_mobile FROM payments p JOIN customers c ON p.customer_id = c.id WHERE p.id = ? AND c.user_id = ?");
    $stmt->bind_param("ii", $payment_id, $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $payment = $result->fetch_assoc();
    $stmt->close();

    if ($payment) {
        // Get payment allocations
        $stmt = $conn->prepare("SELECT pa.*, ut.bill_no, ut.description FROM payment_allocations pa JOIN udhar_transactions ut ON pa.udhar_transaction_id = ut.id WHERE pa.payment_id = ?");
        $stmt->bind_param("i", $payment_id);
        $stmt->execute();
        $allocations = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    }
}

// Get customers for dropdown
$customers = [];
$stmt = $conn->prepare("SELECT id, name, mobile, balance FROM customers WHERE user_id = ? AND status = 'active' ORDER BY name");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$customers = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get udhar entries for allocation
$udhar_entries = [];
if (isset($payment) && $payment) {
    $stmt = $conn->prepare("
        SELECT id, bill_no, description, transaction_date, amount, remaining_amount, status 
        FROM udhar_transactions 
        WHERE customer_id = ? AND status IN ('pending', 'partially_paid') AND remaining_amount > 0
        ORDER BY transaction_date ASC
    ");
    $stmt->bind_param("i", $payment['customer_id']);
    $stmt->execute();
    $udhar_entries = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

// Get all payments for listing
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';
$payment_mode_filter = isset($_GET['payment_mode']) ? sanitizeInput($_GET['payment_mode']) : '';
$customer_filter = isset($_GET['customer']) ? intval($_GET['customer']) : 0;
$date_from = isset($_GET['date_from']) ? sanitizeInput($_GET['date_from']) : '';
$date_to = isset($_GET['date_to']) ? sanitizeInput($_GET['date_to']) : '';

$where_clause = "WHERE c.user_id = " . $_SESSION['user_id'];
$params = [];

if (!empty($search)) {
    $where_clause .= " AND (p.customer_name LIKE ? OR p.reference_no LIKE ? OR p.notes LIKE ?)";
    $search_term = "%$search%";
    $params = array_fill(0, 3, $search_term);
}

if (!empty($payment_mode_filter)) {
    $where_clause .= " AND p.payment_mode = ?";
    $params[] = $payment_mode_filter;
}

if ($customer_filter > 0) {
    $where_clause .= " AND p.customer_id = ?";
    $params[] = $customer_filter;
}

if (!empty($date_from)) {
    $where_clause .= " AND p.payment_date >= ?";
    $params[] = $date_from;
}

if (!empty($date_to)) {
    $where_clause .= " AND p.payment_date <= ?";
    $params[] = $date_to;
}

// Get total payments count
$count_query = "SELECT COUNT(*) as total FROM payments p JOIN customers c ON p.customer_id = c.id $where_clause";
$count_stmt = $conn->prepare($count_query);
if (!empty($params)) {
    $types = str_repeat('s', count($params));
    $count_stmt->bind_param($types, ...$params);
}
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$total_payments = $count_result->fetch_assoc()['total'];
$count_stmt->close();

// Pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;
$total_pages = ceil($total_payments / $limit);

// Get payments with pagination
$order_by = isset($_GET['order_by']) ? sanitizeInput($_GET['order_by']) : 'p.payment_date';
$order_dir = isset($_GET['order_dir']) ? sanitizeInput($_GET['order_dir']) : 'DESC';

// Only allow columns from payments table or specific customer columns that exist
$allowed_columns = ['p.payment_date', 'p.customer_name', 'p.amount', 'p.payment_mode', 'p.is_allocated', 'p.created_at'];
$order_by = in_array($order_by, $allowed_columns) ? $order_by : 'p.payment_date';
$order_dir = in_array(strtoupper($order_dir), ['ASC', 'DESC']) ? strtoupper($order_dir) : 'DESC';

// Ensure we're not selecting non-existent columns
$query = "SELECT p.* FROM payments p JOIN customers c ON p.customer_id = c.id $where_clause ORDER BY $order_by $order_dir LIMIT ? OFFSET ?";
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
$payments_list = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$page_title = "Payment Management";
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Management - Smart Udhar System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
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
            background: linear-gradient(180deg, var(--secondary-color) 0%, #1a252f 100%);
            color: #e5e7eb;
            min-height: 100vh;
            position: fixed;
            width: 260px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 1000;
            left: 0;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
            border-right: 1px solid rgba(255, 255, 255, 0.05);
        }

        .sidebar.closed {
            margin-left: -260px;
        }

        .sidebar-header {
            padding: 24px 20px;
            background: linear-gradient(135deg, var(--primary-color) 0%, #2980b9 100%);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .sidebar-header-content {
            flex: 1;
        }

        .sidebar-toggle-btn {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: white;
            width: 36px;
            height: 36px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            font-size: 18px;
        }

        .sidebar-toggle-btn:hover {
            background: rgba(255, 255, 255, 0.2);
            border-color: rgba(255, 255, 255, 0.3);
            transform: scale(1.05);
        }

        .sidebar-toggle-btn:active {
            transform: scale(0.95);
        }

        .sidebar-toggle-btn i {
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .sidebar.closed .sidebar-toggle-btn i {
            transform: rotate(180deg);
        }

        /* Arrow tab toggle - visible when sidebar is closed */
        .floating-toggle-btn {
            position: fixed;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            background: linear-gradient(135deg, var(--primary-color) 0%, #2980b9 100%);
            border: none;
            border-top-right-radius: 8px;
            border-bottom-right-radius: 8px;
            color: white;
            width: 28px;
            height: 70px;
            display: none;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 2px 0 8px rgba(0, 0, 0, 0.15);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            font-size: 16px;
            z-index: 1001;
            padding-left: 4px;
        }

        .floating-toggle-btn:hover {
            width: 32px;
            box-shadow: 3px 0 12px rgba(0, 0, 0, 0.25);
            background: linear-gradient(135deg, #2980b9 0%, var(--primary-color) 100%);
        }

        .floating-toggle-btn:active {
            transform: translateY(-50%) scale(0.95);
        }

        .sidebar.closed + .main-content .floating-toggle-btn {
            display: flex;
        }

        .sidebar-header h4 {
            margin: 0;
            color: white;
            font-weight: 700;
            font-size: 20px;
            letter-spacing: -0.5px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .sidebar-header .shop-name {
            font-size: 0.85rem;
            opacity: 0.95;
            margin-top: 5px;
            font-weight: 400;
        }

        .sidebar .nav-link {
            color: #9ca3af;
            padding: 14px 20px;
            border-left: 3px solid transparent;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 500;
            font-size: 14px;
        }

        .sidebar .nav-link:hover {
            color: white;
            background-color: rgba(255, 255, 255, 0.08);
            border-left-color: var(--primary-color);
            transform: translateX(2px);
        }

        .sidebar .nav-link.active {
            color: white;
            background: linear-gradient(90deg, rgba(52, 152, 219, 0.15) 0%, transparent 100%);
            border-left-color: var(--primary-color);
            font-weight: 600;
        }

        .sidebar .nav-link i {
            width: 20px;
            font-size: 16px;
            text-align: center;
        }

        .sidebar-footer {
            padding: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
        }

        .main-content {
            margin-left: 260px;
            padding: 20px;
            transition: all 0.3s;
            min-height: 100vh;
        }

        .main-content.expanded {
            margin-left: 0;
        }

        @media (max-width: 768px) {
            .sidebar {
                margin-left: -260px;
            }

            .sidebar.active {
                margin-left: 0;
            }

            .main-content {
                margin-left: 0 !important;
            }

            .main-content.active {
                margin-left: 260px;
            }
        }

        .navbar {
            background-color: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        .mobile-menu-btn {
            display: block;
            background: none;
            border: none;
            color: var(--secondary-color);
            font-size: 1.5rem;
            cursor: pointer;
        }

        .mobile-menu-btn:hover {
            color: var(--primary-color);
        }

        /* Internal CSS for Payment Management */
        :root {
            --primary-color: #3498db;
            --secondary-color: #2c3e50;
            --success-color: #27ae60;
            --warning-color: #f39c12;
            --danger-color: #e74c3c;
            --info-color: #17a2b8;
            --light-bg: #f8f9fa;
        }

        .payment-card {
            border-radius: 10px;
            transition: all 0.3s;
            border: none;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .payment-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
        }

        .payment-header {
            background: linear-gradient(135deg, var(--primary-color), #2980b9);
            color: white;
            border-radius: 10px 10px 0 0;
            padding: 15px 20px;
        }

        .payment-amount {
            font-size: 1.8rem;
            font-weight: bold;
            color: var(--success-color);
        }

        .payment-mode-badge {
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .mode-cash {
            background-color: #d4edda;
            color: #155724;
        }

        .mode-bank {
            background-color: #cce5ff;
            color: #004085;
        }

        .mode-upi {
            background-color: #d1ecf1;
            color: #0c5460;
        }

        .mode-cheque {
            background-color: #fff3cd;
            color: #856404;
        }

        .mode-other {
            background-color: #f8d7da;
            color: #721c24;
        }

        .allocation-status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .allocated {
            background-color: #d4edda;
            color: #155724;
        }

        .partial {
            background-color: #fff3cd;
            color: #856404;
        }

        .unallocated {
            background-color: #f8d7da;
            color: #721c24;
        }

        .search-filter-box {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .stats-card {
            border-radius: 10px;
            color: white;
            padding: 15px;
            text-align: center;
            margin-bottom: 15px;
        }

        .stats-card .stat-value {
            font-size: 1.8rem;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .stats-card .stat-label {
            font-size: 0.9rem;
            opacity: 0.9;
        }

        .payment-table th {
            background-color: var(--secondary-color);
            color: white;
            border: none;
        }

        .payment-table td {
            vertical-align: middle;
        }

        .payment-table tr:hover {
            background-color: rgba(52, 152, 219, 0.1);
        }

        .action-buttons .btn {
            padding: 5px 10px;
            font-size: 0.8rem;
            margin-right: 5px;
        }

        .allocation-table {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .allocation-table th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: var(--secondary-color);
        }

        .amount-input {
            max-width: 150px;
        }

        .payment-summary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .payment-summary h4 {
            margin-bottom: 20px;
            color: white;
        }

        .summary-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }

        .summary-item:last-child {
            border-bottom: none;
            font-weight: bold;
            font-size: 1.1rem;
        }

        .date-range-picker {
            background: white;
            border-radius: 10px;
            padding: 15px;
            border: 1px solid #dee2e6;
        }

        .print-btn {
            background: linear-gradient(135deg, var(--success-color), #229954);
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 20px;
            font-weight: 600;
        }

        .print-btn:hover {
            background: linear-gradient(135deg, #229954, #1e8449);
            color: white;
        }

        .mobile-view {
            display: none;
        }

        @media (max-width: 768px) {
            .desktop-view {
                display: none;
            }

            .mobile-view {
                display: block;
            }

            .payment-card {
                margin-bottom: 15px;
            }

            .stats-card {
                margin-bottom: 10px;
            }
        }

        /* Custom form styles */
        .form-floating>label {
            padding-left: 1.5rem;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(52, 152, 219, 0.25);
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-primary:hover {
            background-color: #2980b9;
            border-color: #2980b9;
        }

        .btn-success {
            background-color: var(--success-color);
            border-color: var(--success-color);
        }

        .btn-warning {
            background-color: var(--warning-color);
            border-color: var(--warning-color);
        }

        .btn-danger {
            background-color: var(--danger-color);
            border-color: var(--danger-color);
        }

        .badge-success {
            background-color: var(--success-color);
        }

        .badge-warning {
            background-color: var(--warning-color);
        }

        .badge-danger {
            background-color: var(--danger-color);
        }

        .badge-info {
            background-color: var(--info-color);
        }

        /* Pagination styles */
        .pagination .page-link {
            color: var(--primary-color);
        }

        .pagination .page-item.active .page-link {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        /* Modal styles */
        .modal-header {
            background-color: var(--primary-color);
            color: white;
        }

        /* Alert styles */
        .alert {
            border-radius: 10px;
            border: none;
        }

        /* Card header styles */
        .card-header {
            background-color: var(--light-bg);
            border-bottom: 2px solid var(--primary-color);
            font-weight: 600;
        }
    </style>
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
                <a class="nav-link " href="dashboard.php">
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
                <a class="nav-link " href="udhar.php">
                    <i class="bi bi-credit-card"></i> Udhar Entry
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="payments.php">
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



        <div class="container-fluid">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                        <h1 class="h2">
                            <i class="bi bi-cash-stack"></i> Payment Management
                        </h1>
                        <div class="btn-toolbar mb-2 mb-md-0">
                            <?php if ($action == 'list'): ?>
                                <a href="payments.php?action=add" class="btn btn-primary">
                                    <i class="bi bi-cash-coin"></i> Receive Payment
                                </a>
                            <?php else: ?>
                                <a href="payments.php" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-left"></i> Back to List
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Display Messages -->
                    <?php displayMessage(); ?>

                    <?php if ($action == 'list'): ?>
                        <!-- Payment List View -->

                        <!-- Statistics Cards -->
                        <div class="row mb-4">
                            <div class="col-md-3 col-sm-6">
                                <div class="stats-card" style="background: linear-gradient(135deg, var(--primary-color), #2980b9);">
                                    <div class="stat-value">₹<?php
                                                                $total_stmt = $conn->prepare("SELECT SUM(amount) as total FROM payments p JOIN customers c ON p.customer_id = c.id WHERE c.user_id = ?");
                                                                $total_stmt->bind_param("i", $_SESSION['user_id']);
                                                                $total_stmt->execute();
                                                                $total_result = $total_stmt->get_result()->fetch_assoc();
                                                                $total_stmt->close();
                                                                echo number_format($total_result['total'] ?? 0, 2);
                                                                ?></div>
                                    <div class="stat-label">Total Payments</div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <div class="stats-card" style="background: linear-gradient(135deg, var(--success-color), #229954);">
                                    <div class="stat-value">₹<?php
                                                                $allocated_stmt = $conn->prepare("SELECT SUM(allocated_amount) as total FROM payments p JOIN customers c ON p.customer_id = c.id WHERE c.user_id = ? AND p.is_allocated = 1");
                                                                $allocated_stmt->bind_param("i", $_SESSION['user_id']);
                                                                $allocated_stmt->execute();
                                                                $allocated_result = $allocated_stmt->get_result()->fetch_assoc();
                                                                $allocated_stmt->close();
                                                                echo number_format($allocated_result['total'] ?? 0, 2);
                                                                ?></div>
                                    <div class="stat-label">Allocated</div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <div class="stats-card" style="background: linear-gradient(135deg, var(--warning-color), #e67e22);">
                                    <div class="stat-value">₹<?php
                                                                $unallocated_stmt = $conn->prepare("SELECT SUM(remaining_amount) as total FROM payments p JOIN customers c ON p.customer_id = c.id WHERE c.user_id = ? AND p.remaining_amount > 0");
                                                                $unallocated_stmt->bind_param("i", $_SESSION['user_id']);
                                                                $unallocated_stmt->execute();
                                                                $unallocated_result = $unallocated_stmt->get_result()->fetch_assoc();
                                                                $unallocated_stmt->close();
                                                                echo number_format($unallocated_result['total'] ?? 0, 2);
                                                                ?></div>
                                    <div class="stat-label">Unallocated</div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <div class="stats-card" style="background: linear-gradient(135deg, var(--danger-color), #c0392b);">
                                    <div class="stat-value"><?php
                                                            $today_stmt = $conn->prepare("SELECT COUNT(*) as count FROM payments p JOIN customers c ON p.customer_id = c.id WHERE c.user_id = ? AND p.payment_date = CURDATE()");
                                                            $today_stmt->bind_param("i", $_SESSION['user_id']);
                                                            $today_stmt->execute();
                                                            $today_result = $today_stmt->get_result()->fetch_assoc();
                                                            $today_stmt->close();
                                                            echo number_format($today_result['count'] ?? 0);
                                                            ?></div>
                                    <div class="stat-label">Today's Payments</div>
                                </div>
                            </div>
                        </div>

                        <!-- Search and Filter Box -->
                        <div class="search-filter-box mb-4">
                            <form method="GET" class="row g-3">
                                <input type="hidden" name="action" value="list">

                                <div class="col-md-3">
                                    <label class="form-label">Customer</label>
                                    <select name="customer" class="form-select" onchange="this.form.submit()">
                                        <option value="">All Customers</option>
                                        <?php foreach ($customers as $cust): ?>
                                            <option value="<?php echo $cust['id']; ?>" <?php echo $customer_filter == $cust['id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($cust['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label">Payment Mode</label>
                                    <select name="payment_mode" class="form-select" onchange="this.form.submit()">
                                        <option value="">All Modes</option>
                                        <option value="cash" <?php echo $payment_mode_filter == 'cash' ? 'selected' : ''; ?>>Cash</option>
                                        <option value="bank_transfer" <?php echo $payment_mode_filter == 'bank_transfer' ? 'selected' : ''; ?>>Bank Transfer</option>
                                        <option value="upi" <?php echo $payment_mode_filter == 'upi' ? 'selected' : ''; ?>>UPI</option>
                                        <option value="cheque" <?php echo $payment_mode_filter == 'cheque' ? 'selected' : ''; ?>>Cheque</option>
                                        <option value="other" <?php echo $payment_mode_filter == 'other' ? 'selected' : ''; ?>>Other</option>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Date Range</label>
                                    <div class="input-group">
                                        <input type="date" class="form-control" name="date_from"
                                            value="<?php echo htmlspecialchars($date_from); ?>">
                                        <span class="input-group-text">to</span>
                                        <input type="date" class="form-control" name="date_to"
                                            value="<?php echo htmlspecialchars($date_to); ?>">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-filter"></i> Filter
                                        </button>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="input-group">
                                        <input type="text" name="search" class="form-control"
                                            placeholder="Search by customer name, reference or notes..."
                                            value="<?php echo htmlspecialchars($search); ?>">
                                        <button type="submit" class="btn btn-outline-primary">
                                            <i class="bi bi-search"></i> Search
                                        </button>
                                        <a href="payments.php" class="btn btn-outline-secondary">
                                            <i class="bi bi-x-circle"></i> Clear
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!-- Payments Table -->
                        <div class="card payment-card">
                            <div class="payment-header">
                                <h5 class="mb-0">All Payments (<?php echo $total_payments; ?>)</h5>
                            </div>
                            <div class="card-body">
                                <?php if (empty($payments_list)): ?>
                                    <div class="text-center py-5">
                                        <i class="bi bi-cash display-1 text-muted"></i>
                                        <h4 class="mt-3">No payments found</h4>
                                        <p class="text-muted">Receive your first payment</p>
                                        <a href="payments.php?action=add" class="btn btn-primary">
                                            <i class="bi bi-cash-coin"></i> Receive First Payment
                                        </a>
                                    </div>
                                <?php else: ?>
                                    <!-- Desktop View -->
                                    <div class="table-responsive desktop-view">
                                        <table class="table table-hover payment-table">
                                            <thead>
                                                <tr>
                                                    <th>Date</th>
                                                    <th>Customer</th>
                                                    <th>Amount</th>
                                                    <th>Mode</th>
                                                    <th>Reference</th>
                                                    <th>Allocation</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($payments_list as $payment_item): ?>
                                                    <tr>
                                                        <td>
                                                            <?php echo date('d M Y', strtotime($payment_item['payment_date'])); ?>
                                                            <?php if ($payment_item['payment_date'] == date('Y-m-d')): ?>
                                                                <span class="badge bg-success">Today</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <strong><?php echo htmlspecialchars($payment_item['customer_name']); ?></strong>
                                                        </td>
                                                        <td>
                                                            <span class="payment-amount">
                                                                ₹<?php echo number_format($payment_item['amount'], 2); ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <?php
                                                            $mode_class = 'mode-' . $payment_item['payment_mode'];
                                                            $mode_text = ucfirst(str_replace('_', ' ', $payment_item['payment_mode']));
                                                            ?>
                                                            <span class="payment-mode-badge <?php echo $mode_class; ?>">
                                                                <?php echo $mode_text; ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <?php if (!empty($payment_item['reference_no'])): ?>
                                                                <code><?php echo htmlspecialchars($payment_item['reference_no']); ?></code>
                                                            <?php else: ?>
                                                                <span class="text-muted">N/A</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <?php
                                                            $alloc_class = '';
                                                            if ($payment_item['is_allocated'] && $payment_item['remaining_amount'] == 0) {
                                                                $alloc_class = 'allocated';
                                                                $alloc_text = 'Allocated';
                                                            } elseif ($payment_item['is_allocated'] && $payment_item['remaining_amount'] > 0) {
                                                                $alloc_class = 'partial';
                                                                $alloc_text = 'Partial';
                                                            } else {
                                                                $alloc_class = 'unallocated';
                                                                $alloc_text = 'Unallocated';
                                                            }
                                                            ?>
                                                            <span class="allocation-status <?php echo $alloc_class; ?>">
                                                                <?php echo $alloc_text; ?>
                                                            </span>
                                                            <?php if ($payment_item['is_allocated']): ?>
                                                                <br>
                                                                <small class="text-muted">
                                                                    Allocated: ₹<?php echo number_format($payment_item['allocated_amount'], 2); ?>
                                                                    <?php if ($payment_item['remaining_amount'] > 0): ?>
                                                                        | Remaining: ₹<?php echo number_format($payment_item['remaining_amount'], 2); ?>
                                                                    <?php endif; ?>
                                                                </small>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <div class="action-buttons">
                                                                <a href="payments.php?action=view&id=<?php echo $payment_item['id']; ?>"
                                                                    class="btn btn-sm btn-outline-info" title="View">
                                                                    <i class="bi bi-eye"></i>
                                                                </a>
                                                                <?php if ($payment_item['is_allocated'] == 0 || $payment_item['remaining_amount'] > 0): ?>
                                                                    <a href="payments.php?action=allocate&id=<?php echo $payment_item['id']; ?>"
                                                                        class="btn btn-sm btn-outline-warning" title="Allocate">
                                                                        <i class="bi bi-cash-coin"></i>
                                                                    </a>
                                                                <?php endif; ?>
                                                                <a href="payments.php?action=edit&id=<?php echo $payment_item['id']; ?>"
                                                                    class="btn btn-sm btn-outline-primary" title="Edit">
                                                                    <i class="bi bi-pencil"></i>
                                                                </a>
                                                                <button type="button" class="btn btn-sm btn-outline-danger"
                                                                    onclick="confirmDelete(<?php echo $payment_item['id']; ?>, '<?php echo htmlspecialchars(addslashes($payment_item['customer_name'])); ?>')"
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

                                    <!-- Mobile View -->
                                    <div class="mobile-view">
                                        <?php foreach ($payments_list as $payment_item): ?>
                                            <div class="card payment-card mb-3">
                                                <div class="card-body">
                                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                                        <div>
                                                            <h6 class="mb-1"><?php echo htmlspecialchars($payment_item['customer_name']); ?></h6>
                                                            <small class="text-muted">
                                                                <?php echo date('d M Y', strtotime($payment_item['payment_date'])); ?>
                                                                <?php if ($payment_item['payment_date'] == date('Y-m-d')): ?>
                                                                    <span class="badge bg-success ms-1">Today</span>
                                                                <?php endif; ?>
                                                            </small>
                                                        </div>
                                                        <div class="text-end">
                                                            <h5 class="payment-amount mb-1">₹<?php echo number_format($payment_item['amount'], 2); ?></h5>
                                                            <?php
                                                            $mode_class = 'mode-' . $payment_item['payment_mode'];
                                                            $mode_text = ucfirst(str_replace('_', ' ', $payment_item['payment_mode']));
                                                            ?>
                                                            <span class="payment-mode-badge <?php echo $mode_class; ?>">
                                                                <?php echo $mode_text; ?>
                                                            </span>
                                                        </div>
                                                    </div>

                                                    <?php if (!empty($payment_item['reference_no'])): ?>
                                                        <div class="mb-2">
                                                            <small class="text-muted">Reference:</small>
                                                            <code><?php echo htmlspecialchars($payment_item['reference_no']); ?></code>
                                                        </div>
                                                    <?php endif; ?>

                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <?php
                                                            $alloc_class = '';
                                                            if ($payment_item['is_allocated'] && $payment_item['remaining_amount'] == 0) {
                                                                $alloc_class = 'allocated';
                                                                $alloc_text = 'Allocated';
                                                            } elseif ($payment_item['is_allocated'] && $payment_item['remaining_amount'] > 0) {
                                                                $alloc_class = 'partial';
                                                                $alloc_text = 'Partial';
                                                            } else {
                                                                $alloc_class = 'unallocated';
                                                                $alloc_text = 'Unallocated';
                                                            }
                                                            ?>
                                                            <span class="allocation-status <?php echo $alloc_class; ?>">
                                                                <?php echo $alloc_text; ?>
                                                            </span>
                                                        </div>
                                                        <div class="action-buttons">
                                                            <a href="payments.php?action=view&id=<?php echo $payment_item['id']; ?>"
                                                                class="btn btn-sm btn-outline-info" title="View">
                                                                <i class="bi bi-eye"></i>
                                                            </a>
                                                            <?php if ($payment_item['is_allocated'] == 0 || $payment_item['remaining_amount'] > 0): ?>
                                                                <a href="payments.php?action=allocate&id=<?php echo $payment_item['id']; ?>"
                                                                    class="btn btn-sm btn-outline-warning" title="Allocate">
                                                                    <i class="bi bi-cash-coin"></i>
                                                                </a>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>

                                    <?php if ($total_pages > 1): ?>
                                        <nav aria-label="Page navigation">
                                            <ul class="pagination justify-content-center">
                                                <li class="page-item <?php echo $page == 1 ? 'disabled' : ''; ?>">
                                                    <a class="page-link" href="?action=list&search=<?php echo urlencode($search); ?>&payment_mode=<?php echo $payment_mode_filter; ?>&customer=<?php echo $customer_filter; ?>&date_from=<?php echo $date_from; ?>&date_to=<?php echo $date_to; ?>&page=<?php echo $page - 1; ?>">
                                                        Previous
                                                    </a>
                                                </li>

                                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                                        <a class="page-link" href="?action=list&search=<?php echo urlencode($search); ?>&payment_mode=<?php echo $payment_mode_filter; ?>&customer=<?php echo $customer_filter; ?>&date_from=<?php echo $date_from; ?>&date_to=<?php echo $date_to; ?>&page=<?php echo $i; ?>">
                                                            <?php echo $i; ?>
                                                        </a>
                                                    </li>
                                                <?php endfor; ?>

                                                <li class="page-item <?php echo $page == $total_pages ? 'disabled' : ''; ?>">
                                                    <a class="page-link" href="?action=list&search=<?php echo urlencode($search); ?>&payment_mode=<?php echo $payment_mode_filter; ?>&customer=<?php echo $customer_filter; ?>&date_from=<?php echo $date_from; ?>&date_to=<?php echo $date_to; ?>&page=<?php echo $page + 1; ?>">
                                                        Next
                                                    </a>
                                                </li>
                                            </ul>
                                        </nav>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>

                    <?php elseif ($action == 'add'): ?>
                        <!-- Add New Payment Form -->
                        <div class="row">
                            <div class="col-lg-8 mx-auto">
                                <div class="card payment-card">
                                    <div class="payment-header">
                                        <h5 class="mb-0">
                                            <i class="bi bi-cash-coin"></i> Receive Payment
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <form method="POST" action="" id="paymentForm">
                                            <div class="row g-3">
                                                <div class="col-md-12">
                                                    <div class="form-floating">
                                                        <select class="form-select" id="customer_id" name="customer_id" required onchange="updateCustomerBalance(this.value)">
                                                            <option value="">Select Customer</option>
                                                            <?php foreach ($customers as $cust): ?>
                                                                <option value="<?php echo $cust['id']; ?>" <?php echo $customer_id == $cust['id'] ? 'selected' : ''; ?> data-balance="<?php echo $cust['balance']; ?>">
                                                                    <?php echo htmlspecialchars($cust['name']); ?>
                                                                    (₹<?php echo number_format($cust['balance'], 2); ?>)
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                        <label for="customer_id"><i class="bi bi-person"></i> Customer *</label>
                                                    </div>
                                                    <div class="mt-2" id="customerBalanceInfo" style="display: none;">
                                                        <span class="badge bg-info">Customer Balance: <span id="customerBalance">0.00</span></span>
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-floating">
                                                        <input type="date" class="form-control" id="payment_date" name="payment_date"
                                                            value="<?php echo date('Y-m-d'); ?>" required>
                                                        <label for="payment_date"><i class="bi bi-calendar"></i> Payment Date *</label>
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-floating">
                                                        <input type="number" class="form-control" id="amount" name="amount"
                                                            step="0.01" min="0.01" placeholder="Amount" required>
                                                        <label for="amount"><i class="bi bi-currency-rupee"></i> Amount *</label>
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-floating">
                                                        <select class="form-select" id="payment_mode" name="payment_mode" required>
                                                            <option value="cash">Cash</option>
                                                            <option value="bank_transfer">Bank Transfer</option>
                                                            <option value="upi">UPI</option>
                                                            <option value="cheque">Cheque</option>
                                                            <option value="other">Other</option>
                                                        </select>
                                                        <label for="payment_mode"><i class="bi bi-credit-card"></i> Payment Mode *</label>
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-floating">
                                                        <input type="text" class="form-control" id="reference_no" name="reference_no"
                                                            placeholder="Reference No">
                                                        <label for="reference_no"><i class="bi bi-receipt"></i> Reference No (Optional)</label>
                                                    </div>
                                                </div>

                                                <div class="col-12">
                                                    <div class="form-floating">
                                                        <textarea class="form-control" id="notes" name="notes"
                                                            placeholder="Notes" style="height: 100px"></textarea>
                                                        <label for="notes"><i class="bi bi-sticky"></i> Notes</label>
                                                    </div>
                                                </div>

                                                <div class="col-12">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="auto_allocate" name="auto_allocate" value="1" checked>
                                                        <label class="form-check-label" for="auto_allocate">
                                                            <i class="bi bi-lightning-charge"></i> Auto-allocate payment to pending udhar entries (oldest first)
                                                        </label>
                                                        <small class="text-muted d-block">
                                                            If checked, the system will automatically allocate this payment to the customer's pending udhar entries starting from the oldest.
                                                        </small>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mt-4">
                                                <button type="submit" name="add_payment" class="btn btn-primary btn-lg">
                                                    <i class="bi bi-check-circle"></i> Receive Payment
                                                </button>
                                                <a href="payments.php" class="btn btn-outline-secondary">
                                                    <i class="bi bi-x-circle"></i> Cancel
                                                </a>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                    <?php elseif ($action == 'view' && $payment): ?>
                        <!-- View Payment Details -->
                        <div class="row">
                            <div class="col-lg-8 mx-auto">
                                <div class="card payment-card">
                                    <div class="payment-header">
                                        <h5 class="mb-0">Payment Details</h5>
                                        <div class="mt-2">
                                            <?php
                                            $mode_class = 'mode-' . $payment['payment_mode'];
                                            $mode_text = ucfirst(str_replace('_', ' ', $payment['payment_mode']));
                                            ?>
                                            <span class="payment-mode-badge <?php echo $mode_class; ?>">
                                                <?php echo $mode_text; ?>
                                            </span>
                                            <?php
                                            $alloc_class = '';
                                            if ($payment['is_allocated'] && $payment['remaining_amount'] == 0) {
                                                $alloc_class = 'allocated';
                                                $alloc_text = 'Fully Allocated';
                                            } elseif ($payment['is_allocated'] && $payment['remaining_amount'] > 0) {
                                                $alloc_class = 'partial';
                                                $alloc_text = 'Partially Allocated';
                                            } else {
                                                $alloc_class = 'unallocated';
                                                $alloc_text = 'Not Allocated';
                                            }
                                            ?>
                                            <span class="allocation-status <?php echo $alloc_class; ?>">
                                                <?php echo $alloc_text; ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <table class="table table-sm">
                                                    <tr>
                                                        <th width="40%">Payment ID:</th>
                                                        <td>#<?php echo str_pad($payment['id'], 6, '0', STR_PAD_LEFT); ?></td>
                                                    </tr>
                                                    <tr>
                                                        <th>Customer:</th>
                                                        <td><?php echo htmlspecialchars($payment['customer_name']); ?></td>
                                                    </tr>
                                                    <tr>
                                                        <th>Mobile:</th>
                                                        <td><?php echo htmlspecialchars($payment['customer_mobile']); ?></td>
                                                    </tr>
                                                    <tr>
                                                        <th>Payment Date:</th>
                                                        <td><?php echo date('d M Y', strtotime($payment['payment_date'])); ?></td>
                                                    </tr>
                                                    <tr>
                                                        <th>Payment Mode:</th>
                                                        <td><?php echo ucfirst(str_replace('_', ' ', $payment['payment_mode'])); ?></td>
                                                    </tr>
                                                </table>
                                            </div>
                                            <div class="col-md-6">
                                                <table class="table table-sm">
                                                    <tr>
                                                        <th width="40%">Amount:</th>
                                                        <td class="payment-amount">₹<?php echo number_format($payment['amount'], 2); ?></td>
                                                    </tr>
                                                    <tr>
                                                        <th>Allocated:</th>
                                                        <td>₹<?php echo number_format($payment['allocated_amount'], 2); ?></td>
                                                    </tr>
                                                    <tr>
                                                        <th>Remaining:</th>
                                                        <td>₹<?php echo number_format($payment['remaining_amount'], 2); ?></td>
                                                    </tr>
                                                    <tr>
                                                        <th>Reference No:</th>
                                                        <td>
                                                            <?php if (!empty($payment['reference_no'])): ?>
                                                                <code><?php echo htmlspecialchars($payment['reference_no']); ?></code>
                                                            <?php else: ?>
                                                                <span class="text-muted">N/A</span>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <th>Allocation Date:</th>
                                                        <td>
                                                            <?php if (!empty($payment['allocation_date'])): ?>
                                                                <?php echo date('d M Y', strtotime($payment['allocation_date'])); ?>
                                                            <?php else: ?>
                                                                <span class="text-muted">Not allocated</span>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </div>

                                        <?php if (!empty($payment['notes'])): ?>
                                            <div class="alert alert-light mt-3">
                                                <strong>Notes:</strong><br>
                                                <?php echo htmlspecialchars($payment['notes']); ?>
                                            </div>
                                        <?php endif; ?>

                                        <!-- Allocations Table -->
                                        <?php if (!empty($allocations)): ?>
                                            <div class="mt-4">
                                                <h6><i class="bi bi-list-check"></i> Payment Allocations</h6>
                                                <div class="table-responsive">
                                                    <table class="table table-sm allocation-table">
                                                        <thead>
                                                            <tr>
                                                                <th>Bill No</th>
                                                                <th>Description</th>
                                                                <th>Allocated Amount</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php foreach ($allocations as $alloc): ?>
                                                                <tr>
                                                                    <td>
                                                                        <a href="udhar.php?action=view&id=<?php echo $alloc['udhar_transaction_id']; ?>"
                                                                            class="text-decoration-none">
                                                                            <?php echo htmlspecialchars($alloc['bill_no']); ?>
                                                                        </a>
                                                                    </td>
                                                                    <td><?php echo htmlspecialchars($alloc['description']); ?></td>
                                                                    <td class="text-success fw-bold">₹<?php echo number_format($alloc['allocated_amount'], 2); ?></td>
                                                                </tr>
                                                            <?php endforeach; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        <?php endif; ?>

                                        <div class="mt-4">
                                            <?php if ($payment['remaining_amount'] > 0): ?>
                                                <a href="payments.php?action=allocate&id=<?php echo $payment['id']; ?>" class="btn btn-warning">
                                                    <i class="bi bi-cash-coin"></i> Allocate Payment
                                                </a>
                                            <?php endif; ?>
                                            <a href="payments.php?action=edit&id=<?php echo $payment['id']; ?>" class="btn btn-primary">
                                                <i class="bi bi-pencil"></i> Edit Payment
                                            </a>
                                            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                                                <i class="bi bi-trash"></i> Delete Payment
                                            </button>
                                            <a href="payments.php" class="btn btn-outline-secondary">
                                                <i class="bi bi-arrow-left"></i> Back to List
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Delete Confirmation Modal -->
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
                                            <strong>Warning:</strong> Are you sure you want to delete this payment?
                                        </div>
                                        <p class="mb-0">
                                            Customer: <strong><?php echo htmlspecialchars($payment['customer_name']); ?></strong><br>
                                            Amount: <strong>₹<?php echo number_format($payment['amount'], 2); ?></strong><br>
                                            Date: <strong><?php echo date('d M Y', strtotime($payment['payment_date'])); ?></strong>
                                        </p>
                                        <p class="mt-2 text-danger">
                                            <?php if (!empty($allocations)): ?>
                                                <strong>Note:</strong> This payment has <?php echo count($allocations); ?> allocation(s). Deleting it will also remove these allocations.
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                    <div class="modal-footer">
                                        <form method="POST" action="">
                                            <input type="hidden" name="payment_id" value="<?php echo $payment['id']; ?>">
                                            <button type="submit" name="delete_payment" class="btn btn-danger">
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

                    <?php elseif ($action == 'edit' && $payment): ?>
                        <!-- Edit Payment Form -->
                        <div class="row">
                            <div class="col-lg-8 mx-auto">
                                <div class="card payment-card">
                                    <div class="payment-header">
                                        <h5 class="mb-0">
                                            <i class="bi bi-pencil"></i> Edit Payment
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <form method="POST" action="">
                                            <input type="hidden" name="payment_id" value="<?php echo $payment['id']; ?>">

                                            <div class="row g-3">
                                                <div class="col-md-12">
                                                    <div class="form-floating">
                                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($payment['customer_name']); ?>" disabled>
                                                        <label><i class="bi bi-person"></i> Customer</label>
                                                        <small class="text-muted">Customer cannot be changed for existing payments</small>
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-floating">
                                                        <input type="date" class="form-control" id="payment_date" name="payment_date"
                                                            value="<?php echo $payment['payment_date']; ?>" required>
                                                        <label for="payment_date"><i class="bi bi-calendar"></i> Payment Date *</label>
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-floating">
                                                        <input type="number" class="form-control" id="amount" name="amount"
                                                            step="0.01" min="0.01" placeholder="Amount" required
                                                            value="<?php echo number_format($payment['amount'], 2, '.', ''); ?>">
                                                        <label for="amount"><i class="bi bi-currency-rupee"></i> Amount *</label>
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-floating">
                                                        <select class="form-select" id="payment_mode" name="payment_mode" required>
                                                            <option value="cash" <?php echo $payment['payment_mode'] == 'cash' ? 'selected' : ''; ?>>Cash</option>
                                                            <option value="bank_transfer" <?php echo $payment['payment_mode'] == 'bank_transfer' ? 'selected' : ''; ?>>Bank Transfer</option>
                                                            <option value="upi" <?php echo $payment['payment_mode'] == 'upi' ? 'selected' : ''; ?>>UPI</option>
                                                            <option value="cheque" <?php echo $payment['payment_mode'] == 'cheque' ? 'selected' : ''; ?>>Cheque</option>
                                                            <option value="other" <?php echo $payment['payment_mode'] == 'other' ? 'selected' : ''; ?>>Other</option>
                                                        </select>
                                                        <label for="payment_mode"><i class="bi bi-credit-card"></i> Payment Mode *</label>
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-floating">
                                                        <input type="text" class="form-control" id="reference_no" name="reference_no"
                                                            placeholder="Reference No"
                                                            value="<?php echo htmlspecialchars($payment['reference_no']); ?>">
                                                        <label for="reference_no"><i class="bi bi-receipt"></i> Reference No (Optional)</label>
                                                    </div>
                                                </div>

                                                <div class="col-12">
                                                    <div class="form-floating">
                                                        <textarea class="form-control" id="notes" name="notes"
                                                            placeholder="Notes" style="height: 100px"><?php echo htmlspecialchars($payment['notes']); ?></textarea>
                                                        <label for="notes"><i class="bi bi-sticky"></i> Notes</label>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mt-4">
                                                <button type="submit" name="update_payment" class="btn btn-primary">
                                                    <i class="bi bi-check-circle"></i> Update Payment
                                                </button>
                                                <a href="payments.php?action=view&id=<?php echo $payment['id']; ?>" class="btn btn-outline-secondary">
                                                    <i class="bi bi-x-circle"></i> Cancel
                                                </a>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                    <?php elseif ($action == 'allocate' && $payment): ?>
                        <!-- Allocate Payment to Udhar Entries -->
                        <div class="row">
                            <div class="col-lg-10 mx-auto">
                                <div class="card payment-card">
                                    <div class="payment-header">
                                        <h5 class="mb-0">
                                            <i class="bi bi-cash-coin"></i> Allocate Payment
                                        </h5>
                                        <div class="mt-2">
                                            <span class="text-white">Customer: <?php echo htmlspecialchars($payment['customer_name']); ?></span>
                                            <span class="badge bg-light text-dark ms-2">Payment: ₹<?php echo number_format($payment['amount'], 2); ?></span>
                                            <span class="badge bg-warning ms-2">Remaining: ₹<?php echo number_format($payment['remaining_amount'], 2); ?></span>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <?php if (empty($udhar_entries)): ?>
                                            <div class="text-center py-5">
                                                <i class="bi bi-check-circle display-1 text-success"></i>
                                                <h4 class="mt-3">No Pending Udhar Entries</h4>
                                                <p class="text-muted">This customer has no pending udhar entries to allocate.</p>
                                                <a href="payments.php?action=view&id=<?php echo $payment['id']; ?>" class="btn btn-primary">
                                                    <i class="bi bi-arrow-left"></i> Back to Payment
                                                </a>
                                            </div>
                                        <?php else: ?>
                                            <form method="POST" action="" id="allocateForm">
                                                <input type="hidden" name="payment_id" value="<?php echo $payment['id']; ?>">

                                                <div class="alert alert-info">
                                                    <i class="bi bi-info-circle"></i>
                                                    <strong>Instructions:</strong> Enter allocation amounts for each pending udhar entry.
                                                    The total allocation cannot exceed the remaining payment amount (₹<?php echo number_format($payment['remaining_amount'], 2); ?>).
                                                </div>

                                                <div class="table-responsive">
                                                    <table class="table allocation-table">
                                                        <thead>
                                                            <tr>
                                                                <th width="5%">#</th>
                                                                <th width="15%">Bill No</th>
                                                                <th width="25%">Description</th>
                                                                <th width="15%">Date</th>
                                                                <th width="15%">Total Amount</th>
                                                                <th width="15%">Remaining</th>
                                                                <th width="10%">Allocate</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php foreach ($udhar_entries as $index => $entry): ?>
                                                                <tr>
                                                                    <td><?php echo $index + 1; ?></td>
                                                                    <td>
                                                                        <a href="udhar.php?action=view&id=<?php echo $entry['id']; ?>"
                                                                            class="text-decoration-none" target="_blank">
                                                                            <?php echo htmlspecialchars($entry['bill_no']); ?>
                                                                        </a>
                                                                    </td>
                                                                    <td><?php echo htmlspecialchars($entry['description']); ?></td>
                                                                    <td><?php echo date('d M Y', strtotime($entry['transaction_date'])); ?></td>
                                                                    <td>₹<?php echo number_format($entry['amount'], 2); ?></td>
                                                                    <td>
                                                                        <span class="badge <?php echo $entry['remaining_amount'] == $entry['amount'] ? 'bg-danger' : 'bg-warning'; ?>">
                                                                            ₹<?php echo number_format($entry['remaining_amount'], 2); ?>
                                                                        </span>
                                                                    </td>
                                                                    <td>
                                                                        <input type="number" class="form-control amount-input allocate-amount"
                                                                            name="allocations[<?php echo $entry['id']; ?>]"
                                                                            step="0.01" min="0" max="<?php echo $entry['remaining_amount']; ?>"
                                                                            placeholder="0.00" onchange="updateTotalAllocation()">
                                                                        <small class="text-muted">Max: ₹<?php echo number_format($entry['remaining_amount'], 2); ?></small>
                                                                    </td>
                                                                </tr>
                                                            <?php endforeach; ?>
                                                        </tbody>
                                                        <tfoot>
                                                            <tr class="table-active">
                                                                <td colspan="5" class="text-end"><strong>Total Allocation:</strong></td>
                                                                <td><strong id="totalAllocated">0.00</strong></td>
                                                                <td>
                                                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="autoAllocate()">
                                                                        <i class="bi bi-lightning-charge"></i> Auto Allocate
                                                                    </button>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td colspan="7" class="text-center">
                                                                    <div class="alert alert-warning" id="allocationWarning" style="display: none;">
                                                                        <i class="bi bi-exclamation-triangle"></i>
                                                                        <span id="warningMessage"></span>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        </tfoot>
                                                    </table>
                                                </div>

                                                <div class="mt-4">
                                                    <button type="submit" name="allocate_payment" class="btn btn-primary btn-lg">
                                                        <i class="bi bi-check-circle"></i> Save Allocations
                                                    </button>
                                                    <a href="payments.php?action=view&id=<?php echo $payment['id']; ?>" class="btn btn-outline-secondary">
                                                        <i class="bi bi-x-circle"></i> Cancel
                                                    </a>
                                                </div>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

    <script>
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

    <script>
        // Update customer balance when customer is selected
        function updateCustomerBalance(customerId) {
            const customerSelect = document.getElementById('customer_id');
            const selectedOption = customerSelect.options[customerSelect.selectedIndex];
            const balance = selectedOption.dataset.balance || 0;

            if (customerId) {
                document.getElementById('customerBalanceInfo').style.display = 'block';
                document.getElementById('customerBalance').textContent = parseFloat(balance).toFixed(2);
            } else {
                document.getElementById('customerBalanceInfo').style.display = 'none';
            }
        }

        // Form validation for add payment
        document.getElementById('paymentForm')?.addEventListener('submit', function(e) {
            const customerId = document.getElementById('customer_id').value;
            const amount = parseFloat(document.getElementById('amount').value);

            if (!customerId) {
                e.preventDefault();
                alert('Please select a customer');
                document.getElementById('customer_id').focus();
                return false;
            }

            if (isNaN(amount) || amount <= 0) {
                e.preventDefault();
                alert('Please enter a valid amount greater than 0');
                document.getElementById('amount').focus();
                return false;
            }

            return true;
        });

        // Delete confirmation
        function confirmDelete(id, customerName) {
            if (confirm('Are you sure you want to delete payment for "' + customerName + '"? This action cannot be undone.')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '';

                const input1 = document.createElement('input');
                input1.type = 'hidden';
                input1.name = 'payment_id';
                input1.value = id;

                const input2 = document.createElement('input');
                input2.type = 'hidden';
                input2.name = 'delete_payment';
                input2.value = '1';

                form.appendChild(input1);
                form.appendChild(input2);
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Update total allocation amount
        function updateTotalAllocation() {
            let total = 0;
            const remainingAmount = <?php echo $payment['remaining_amount'] ?? 0; ?>;

            document.querySelectorAll('.allocate-amount').forEach(input => {
                const value = parseFloat(input.value) || 0;
                const max = parseFloat(input.max) || 0;

                if (value > max) {
                    input.value = max.toFixed(2);
                    total += max;
                } else {
                    total += value;
                }
            });

            document.getElementById('totalAllocated').textContent = total.toFixed(2);

            const warningDiv = document.getElementById('allocationWarning');
            const warningMsg = document.getElementById('warningMessage');

            if (total > remainingAmount) {
                warningDiv.style.display = 'block';
                warningMsg.textContent = `Total allocation (₹${total.toFixed(2)}) exceeds remaining payment amount (₹${remainingAmount.toFixed(2)})`;
                warningDiv.className = 'alert alert-danger';
            } else if (total > 0) {
                warningDiv.style.display = 'block';
                warningMsg.textContent = `Total allocation: ₹${total.toFixed(2)} | Remaining: ₹${(remainingAmount - total).toFixed(2)}`;
                warningDiv.className = 'alert alert-success';
            } else {
                warningDiv.style.display = 'none';
            }
        }

        // Auto allocate payment to pending udhar entries
        function autoAllocate() {
            const remainingAmount = <?php echo $payment['remaining_amount'] ?? 0; ?>;
            let amountToAllocate = remainingAmount;

            document.querySelectorAll('.allocate-amount').forEach(input => {
                const max = parseFloat(input.max) || 0;
                const allocate = Math.min(max, amountToAllocate);

                input.value = allocate.toFixed(2);
                amountToAllocate -= allocate;

                if (amountToAllocate <= 0) {
                    amountToAllocate = 0;
                }
            });

            updateTotalAllocation();
        }

        // Validate allocation form
        document.getElementById('allocateForm')?.addEventListener('submit', function(e) {
            let total = 0;
            document.querySelectorAll('.allocate-amount').forEach(input => {
                total += parseFloat(input.value) || 0;
            });

            const remainingAmount = <?php echo $payment['remaining_amount'] ?? 0; ?>;

            if (total <= 0) {
                e.preventDefault();
                alert('Please allocate at least some amount');
                return false;
            }

            if (total > remainingAmount) {
                e.preventDefault();
                alert(`Total allocation (₹${total.toFixed(2)}) cannot exceed remaining payment amount (₹${remainingAmount.toFixed(2)})`);
                return false;
            }

            return true;
        });

        // Initialize customer balance if customer is pre-selected
        <?php if ($action == 'add' && $customer_id > 0): ?>
            document.addEventListener('DOMContentLoaded', function() {
                updateCustomerBalance(<?php echo $customer_id; ?>);
            });
        <?php endif; ?>

        // Initialize allocation update
        <?php if ($action == 'allocate'): ?>
            document.addEventListener('DOMContentLoaded', function() {
                updateTotalAllocation();
            });
        <?php endif; ?>

        // Quick amount entry for payment form
        document.getElementById('amount')?.addEventListener('focus', function() {
            const customerSelect = document.getElementById('customer_id');
            if (customerSelect.value) {
                const selectedOption = customerSelect.options[customerSelect.selectedIndex];
                const balance = parseFloat(selectedOption.dataset.balance) || 0;
                if (balance > 0) {
                    this.value = Math.min(balance, 1000000).toFixed(2);
                }
            }
        });
    </script>

</body>

</html>