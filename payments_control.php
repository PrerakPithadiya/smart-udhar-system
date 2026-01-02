<?php
// File: smart-udhar-system/payments_control.php

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
        if ($remaining_payment <= 0)
            break;

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

// If filtering by customer ID, get the customer name to show in search box if search is empty
if ($customer_filter > 0 && empty($search)) {
    $c_stmt = $conn->prepare("SELECT name FROM customers WHERE id = ? AND user_id = ?");
    $c_stmt->bind_param("ii", $customer_filter, $_SESSION['user_id']);
    $c_stmt->execute();
    $c_res = $c_stmt->get_result();
    if ($c_row = $c_res->fetch_assoc()) {
        $search = $c_row['name'];
    }
    $c_stmt->close();
}

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
$allowed_columns = ['p.payment_date', 'c.name', 'p.amount', 'p.payment_mode', 'p.is_allocated', 'p.created_at'];
$order_by = in_array($order_by, $allowed_columns) ? $order_by : 'p.payment_date';
$order_dir = in_array(strtoupper($order_dir), ['ASC', 'DESC']) ? strtoupper($order_dir) : 'DESC';

// Ensure we're not selecting non-existent columns
// Ensure we're selecting name from customers table as customer_name
$query = "SELECT p.*, c.name as customer_name FROM payments p JOIN customers c ON p.customer_id = c.id $where_clause ORDER BY $order_by $order_dir LIMIT ? OFFSET ?";
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
