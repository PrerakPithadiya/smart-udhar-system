<?php
// File: smart-udhar-system/items_control.php

require_once 'config/database.php';
requireLogin();

$conn = getDBConnection();

// Handle actions
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$item_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Process form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_item'])) {
        // Add new item
        $item_name = sanitizeInput($_POST['item_name']);
        $item_code = sanitizeInput($_POST['item_code']);
        $hsn_code = sanitizeInput($_POST['hsn_code']);
        $price = floatval($_POST['price']);
        $cgst_rate = floatval($_POST['cgst_rate']);
        $sgst_rate = floatval($_POST['sgst_rate']);
        $igst_rate = floatval($_POST['igst_rate']);
        $unit = sanitizeInput($_POST['unit']);
        $description = sanitizeInput($_POST['description']);
        $category = sanitizeInput($_POST['category']);

        // Validation
        $errors = [];

        if (empty($item_name)) {
            $errors[] = "Item name is required";
        }

        if ($price <= 0) {
            $errors[] = "Price must be greater than 0";
        }

        if (empty($errors)) {
            // Check if item already exists for this user
            $check_stmt = $conn->prepare("SELECT id FROM items WHERE user_id = ? AND item_name = ?");
            $check_stmt->bind_param("is", $_SESSION['user_id'], $item_name);
            $check_stmt->execute();

            if ($check_stmt->get_result()->num_rows > 0) {
                setMessage("Item with this name already exists!", "warning");
            } else {
                $stmt = $conn->prepare("INSERT INTO items (user_id, item_name, item_code, hsn_code, price, cgst_rate, sgst_rate, igst_rate, unit, description, category) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("isssddddsss", $_SESSION['user_id'], $item_name, $item_code, $hsn_code, $price, $cgst_rate, $sgst_rate, $igst_rate, $unit, $description, $category);

                if ($stmt->execute()) {
                    setMessage("Item added successfully!", "success");
                    header("Location: items.php");
                    exit();
                } else {
                    setMessage("Error adding item: " . $stmt->error, "danger");
                }
                $stmt->close();
            }
            $check_stmt->close();
        } else {
            setMessage(implode("<br>", $errors), "danger");
        }
    }

    if (isset($_POST['update_item'])) {
        // Update item
        $id = intval($_POST['item_id']);
        $item_name = sanitizeInput($_POST['item_name']);
        $item_code = sanitizeInput($_POST['item_code']);
        $hsn_code = sanitizeInput($_POST['hsn_code']);
        $price = floatval($_POST['price']);
        $cgst_rate = floatval($_POST['cgst_rate']);
        $sgst_rate = floatval($_POST['sgst_rate']);
        $igst_rate = floatval($_POST['igst_rate']);
        $unit = sanitizeInput($_POST['unit']);
        $description = sanitizeInput($_POST['description']);
        $category = sanitizeInput($_POST['category']);

        $stmt = $conn->prepare("UPDATE items SET item_name = ?, item_code = ?, hsn_code = ?, price = ?, cgst_rate = ?, sgst_rate = ?, igst_rate = ?, unit = ?, description = ?, category = ?, updated_at = NOW() WHERE id = ? AND user_id = ?");
        $stmt->bind_param("isssdddddssii", $item_name, $item_code, $hsn_code, $price, $cgst_rate, $sgst_rate, $igst_rate, $unit, $description, $category, $id, $_SESSION['user_id']);

        if ($stmt->execute()) {
            setMessage("Item updated successfully!", "success");
            header("Location: items.php");
            exit();
        } else {
            setMessage("Error updating item: " . $stmt->error, "danger");
        }
        $stmt->close();
    }

    if (isset($_POST['delete_item'])) {
        // Delete item
        $id = intval($_POST['item_id']);

        // Check if item is used in any transactions
        $check_stmt = $conn->prepare("SELECT COUNT(*) as count FROM udhar_items WHERE item_id = ?");
        $check_stmt->bind_param("i", $id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        $row = $result->fetch_assoc();
        $check_stmt->close();

        if ($row['count'] > 0) {
            setMessage("Cannot delete item that is used in transactions. Mark as inactive instead.", "warning");
        } else {
            $stmt = $conn->prepare("DELETE FROM items WHERE id = ? AND user_id = ?");
            $stmt->bind_param("ii", $id, $_SESSION['user_id']);

            if ($stmt->execute()) {
                setMessage("Item deleted successfully!", "success");
            } else {
                setMessage("Error deleting item: " . $stmt->error, "danger");
            }
            $stmt->close();
        }
        header("Location: items.php");
        exit();
    }
}

// Get item for edit
$item = null;
if ($item_id > 0 && ($action == 'edit' || $action == 'view')) {
    $stmt = $conn->prepare("SELECT * FROM items WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $item_id, $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $item = $result->fetch_assoc();
    $stmt->close();
}

// Get all items for listing
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';
$category_filter = isset($_GET['category']) ? sanitizeInput($_GET['category']) : '';

$where_clause = "WHERE user_id = " . $_SESSION['user_id'];
$params = [];

if (!empty($search)) {
    $where_clause .= " AND (item_name LIKE ? OR item_code LIKE ? OR hsn_code LIKE ?)";
    $search_term = "%$search%";
    $params = array_fill(0, 3, $search_term);
}

if (!empty($category_filter)) {
    $where_clause .= " AND category = ?";
    $params[] = $category_filter;
}

// Get total items count
$count_query = "SELECT COUNT(*) as total FROM items $where_clause";
$count_stmt = $conn->prepare($count_query);
if (!empty($params)) {
    $types = str_repeat('s', count($params));
    $count_stmt->bind_param($types, ...$params);
}
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$total_items = $count_result->fetch_assoc()['total'];
$count_stmt->close();

// Pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 15;
$offset = ($page - 1) * $limit;
$total_pages = ceil($total_items / $limit);

// Get items with pagination
$order_by = isset($_GET['order_by']) ? sanitizeInput($_GET['order_by']) : 'item_name';
$order_dir = isset($_GET['order_dir']) ? sanitizeInput($_GET['order_dir']) : 'ASC';

// Validate order parameters
$allowed_columns = ['item_name', 'item_code', 'hsn_code', 'price', 'created_at'];
$order_by = in_array($order_by, $allowed_columns) ? $order_by : 'item_name';
$order_dir = in_array(strtoupper($order_dir), ['ASC', 'DESC']) ? strtoupper($order_dir) : 'ASC';

$query = "SELECT * FROM items $where_clause ORDER BY $order_by $order_dir LIMIT ? OFFSET ?";
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
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get average price
$avg_stmt = $conn->prepare("SELECT AVG(price) as avg_price FROM items WHERE user_id = ?");
$avg_stmt->bind_param("i", $_SESSION['user_id']);
$avg_stmt->execute();
$avg_price = $avg_stmt->get_result()->fetch_assoc()['avg_price'];
$avg_stmt->close();

// Get items with GST
$gst_stmt = $conn->prepare("SELECT COUNT(*) as count FROM items WHERE user_id = ? AND (cgst_rate > 0 OR sgst_rate > 0 OR igst_rate > 0)");
$gst_stmt->bind_param("i", $_SESSION['user_id']);
$gst_stmt->execute();
$gst_count = $gst_stmt->get_result()->fetch_assoc()['count'];
$gst_stmt->close();

$page_title = "Items Management";
