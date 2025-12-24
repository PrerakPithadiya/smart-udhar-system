<?php
// File: smart-udhar-system/items.php

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
                $stmt = $conn->prepare("INSERT INTO items (user_id, item_name, item_code, hsn_code, price, cgst_rate, sgst_rate, igst_rate, unit, description) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("isssddddss", $_SESSION['user_id'], $item_name, $item_code, $hsn_code, $price, $cgst_rate, $sgst_rate, $igst_rate, $unit, $description);
                
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
        $status = sanitizeInput($_POST['status']);
        
        $stmt = $conn->prepare("UPDATE items SET item_name = ?, item_code = ?, hsn_code = ?, price = ?, cgst_rate = ?, sgst_rate = ?, igst_rate = ?, unit = ?, description = ?, status = ?, updated_at = NOW() WHERE id = ? AND user_id = ?");
        $stmt->bind_param("sssddddsssii", $item_name, $item_code, $hsn_code, $price, $cgst_rate, $sgst_rate, $igst_rate, $unit, $description, $status, $id, $_SESSION['user_id']);
        
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
$status_filter = isset($_GET['status']) ? sanitizeInput($_GET['status']) : '';

$where_clause = "WHERE user_id = " . $_SESSION['user_id'];
$params = [];

if (!empty($search)) {
    $where_clause .= " AND (item_name LIKE ? OR item_code LIKE ? OR hsn_code LIKE ?)";
    $search_term = "%$search%";
    $params = array_fill(0, 3, $search_term);
}

if (!empty($status_filter) && in_array($status_filter, ['active', 'inactive'])) {
    $where_clause .= " AND status = ?";
    $params[] = $status_filter;
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

$page_title = "Items Management";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Items Management - Smart Udhar System</title>
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
        
        .navbar {
            background-color: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        
        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            color: var(--secondary-color);
            font-size: 1.5rem;
        }
        
        @media (max-width: 768px) {
            .mobile-menu-btn {
                display: block;
            }
        }





        /* Import common dashboard styles */
        
        
        /* Items specific styles */
        .items-container {
            padding: 20px;
        }

        .items-header {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            border-radius: 10px 10px 0 0;
            padding: 20px;
            margin-bottom: 0;
        }

        .items-card {
            border-radius: 0 0 10px 10px;
            border: none;
            box-shadow: 0 2px 20px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .items-table-container {
            overflow-x: auto;
            border-radius: 0 0 10px 10px;
        }

        .items-table {
            margin-bottom: 0;
        }

        .items-table thead th {
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
            color: #2c3e50;
            font-weight: 600;
            padding: 15px;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
        }

        .items-table tbody tr {
            transition: all 0.3s ease;
            border-bottom: 1px solid #f1f1f1;
        }

        .items-table tbody tr:hover {
            background-color: #f8f9fa;
            transform: translateX(5px);
        }

        .items-table tbody td {
            padding: 15px;
            vertical-align: middle;
            border-top: 1px solid #f1f1f1;
        }

        .item-avatar {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 16px;
            margin-right: 10px;
        }

        .item-row-actions {
            display: flex;
            gap: 5px;
        }

        .item-row-actions .btn {
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 0.85rem;
        }

        .item-status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .item-status-active {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .item-status-inactive {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .item-price-badge {
            background-color: #e3f2fd;
            color: #1565c0;
            border: 1px solid #bbdefb;
            padding: 5px 10px;
            border-radius: 5px;
            font-weight: 600;
        }

        .item-gst-badge {
            background-color: #fff3e0;
            color: #e65100;
            border: 1px solid #ffe0b2;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 0.75rem;
            margin: 2px;
        }

        .item-unit-badge {
            background-color: #f3e5f5;
            color: #7b1fa2;
            border: 1px solid #e1bee7;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 0.75rem;
        }

        .items-search-box {
            position: relative;
            margin-bottom: 20px;
        }

        .items-search-box .search-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
            z-index: 10;
        }

        .items-search-box input {
            padding-left: 45px;
            border-radius: 25px;
            border: 2px solid #e0e0e0;
            height: 45px;
        }

        .items-search-box input:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 0.25rem rgba(52, 152, 219, 0.25);
        }

        .items-filter-box {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .items-stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .items-stat-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
            border-left: 4px solid #3498db;
            position: relative;
        }

        .items-stat-card:hover {
            transform: translateY(-5px);
        }

        .items-stat-card.stat-success {
            border-left-color: #27ae60;
        }

        .items-stat-card.stat-warning {
            border-left-color: #f39c12;
        }

        .items-stat-card.stat-danger {
            border-left-color: #e74c3c;
        }

        .items-stat-card .stat-value {
            font-size: 2rem;
            font-weight: bold;
            color: #2c3e50;
            margin: 10px 0;
        }

        .items-stat-card .stat-label {
            color: #7f8c8d;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .items-stat-card .stat-icon {
            font-size: 2.5rem;
            opacity: 0.2;
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
        }

        /* Form Styles for Items */
        .item-form-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }

        .item-form-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #f1f1f1;
        }

        .item-form-header h3 {
            color: #2c3e50;
            font-weight: 600;
        }

        .item-form-group {
            margin-bottom: 25px;
        }

        .item-form-group label {
            display: block;
            margin-bottom: 8px;
            color: #34495e;
            font-weight: 600;
            font-size: 0.95rem;
        }

        .item-form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .item-form-control:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 0.25rem rgba(52, 152, 219, 0.25);
            outline: none;
        }

        .item-form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }

        .item-form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #f1f1f1;
        }

        /* GST Input Groups */
        .gst-input-group {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
        }

        .gst-input-item {
            position: relative;
        }

        .gst-input-item label {
            position: absolute;
            top: -10px;
            left: 10px;
            background: white;
            padding: 0 5px;
            font-size: 0.8rem;
            color: #7f8c8d;
        }

        /* Preview Card for Items */
        .item-preview-card {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
            border-left: 4px solid #27ae60;
        }

        .item-preview-card h5 {
            color: #2c3e50;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #dee2e6;
        }

        .item-preview-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 10px;
        }

        .item-preview-label {
            font-weight: 600;
            color: #7f8c8d;
        }

        .item-preview-value {
            color: #2c3e50;
            font-weight: 500;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .items-table-container {
                font-size: 0.9rem;
            }
            
            .items-table thead th,
            .items-table tbody td {
                padding: 10px 5px;
            }
            
            .item-row-actions {
                flex-direction: column;
                gap: 3px;
            }
            
            .item-form-row {
                grid-template-columns: 1fr;
            }
            
            .gst-input-group {
                grid-template-columns: 1fr;
            }
            
            .items-stats-cards {
                grid-template-columns: 1fr;
            }
            
            .item-form-actions {
                flex-direction: column;
            }
            
            .item-form-actions .btn {
                width: 100%;
            }
        }

        /* Animation for Item Operations */
        @keyframes itemFadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .item-card {
            animation: itemFadeIn 0.5s ease-out;
        }

        /* Empty State */
        .items-empty-state {
            text-align: center;
            padding: 50px 20px;
        }

        .items-empty-state .empty-icon {
            font-size: 4rem;
            color: #bdc3c7;
            margin-bottom: 20px;
        }

        .items-empty-state h4 {
            color: #7f8c8d;
            margin-bottom: 10px;
        }

        .items-empty-state p {
            color: #95a5a6;
            margin-bottom: 30px;
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
                <a class="nav-link " href="dashboard.php">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link " href="customers.php">
                    <i class="bi bi-people-fill"></i> Customers
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="items.php">
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


<div class="container-fluid items-container">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">
                        <i class="bi bi-box-seam"></i> Items Management
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <?php if ($action == 'list'): ?>
                            <a href="items.php?action=add" class="btn btn-primary">
                                <i class="bi bi-plus-circle"></i> Add New Item
                            </a>
                        <?php else: ?>
                            <a href="items.php" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left"></i> Back to List
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                
                <?php displayMessage(); ?>
                
                <?php if ($action == 'list'): ?>
                    <!-- Items List View -->
                    <div class="items-stat-card mb-4">
                        <div class="row align-items-center">
                            <div class="col-md-4">
                                <h5 class="mb-0">All Items (<?php echo $total_items; ?>)</h5>
                            </div>
                            <div class="col-md-8">
                                <form method="GET" class="row g-2">
                                    <input type="hidden" name="action" value="list">
                                    <div class="col-md-3">
                                        <select name="status" class="form-select" onchange="this.form.submit()">
                                            <option value="">All Status</option>
                                            <option value="active" <?php echo $status_filter == 'active' ? 'selected' : ''; ?>>Active</option>
                                            <option value="inactive" <?php echo $status_filter == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                        </select>
                                    </div>
                                    <div class="col-md-7">
                                        <div class="items-search-box">
                                            <i class="bi bi-search search-icon"></i>
                                            <input type="text" name="search" class="form-control" 
                                                   placeholder="Search by item name, code or HSN..." 
                                                   value="<?php echo htmlspecialchars($search); ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <button type="submit" class="btn btn-outline-primary w-100">
                                            <i class="bi bi-filter"></i> Filter
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Items Statistics -->
                    <div class="items-stats-cards">
                        <?php
                        // Get active items count
                        $active_stmt = $conn->prepare("SELECT COUNT(*) as count FROM items WHERE user_id = ? AND status = 'active'");
                        $active_stmt->bind_param("i", $_SESSION['user_id']);
                        $active_stmt->execute();
                        $active_count = $active_stmt->get_result()->fetch_assoc()['count'];
                        $active_stmt->close();
                        
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
                        ?>
                        
                        <div class="items-stat-card stat-success">
                            <div class="stat-value"><?php echo number_format($active_count); ?></div>
                            <div class="stat-label">Active Items</div>
                            <i class="bi bi-check-circle stat-icon"></i>
                        </div>
                        
                        <div class="items-stat-card">
                            <div class="stat-value">₹<?php echo number_format($avg_price ?? 0, 2); ?></div>
                            <div class="stat-label">Average Price</div>
                            <i class="bi bi-currency-rupee stat-icon"></i>
                        </div>
                        
                        <div class="items-stat-card stat-warning">
                            <div class="stat-value"><?php echo number_format($gst_count); ?></div>
                            <div class="stat-label">Items with GST</div>
                            <i class="bi bi-percent stat-icon"></i>
                        </div>
                        
                        <div class="items-stat-card stat-danger">
                            <div class="stat-value"><?php echo number_format($total_items - $active_count); ?></div>
                            <div class="stat-label">Inactive Items</div>
                            <i class="bi bi-x-circle stat-icon"></i>
                        </div>
                    </div>
                    
                    <div class="card items-card">
                        <div class="card-body">
                            <?php if (empty($items)): ?>
                                <div class="items-empty-state">
                                    <i class="bi bi-people display-1 empty-icon"></i>
                                    <h4 class="mt-3">No items found</h4>
                                    <p class="text-muted">Get started by adding your first item</p>
                                    <a href="items.php?action=add" class="btn btn-primary">
                                        <i class="bi bi-person-plus"></i> Add First Item
                                    </a>
                                </div>
                            <?php else: ?>
                                <div class="items-table-container">
                                    <table class="table items-table">
                                        <thead>
                                            <tr>
                                                <th>
                                                    <a href="?action=list&search=<?php echo urlencode($search); ?>&status=<?php echo $status_filter; ?>&order_by=item_name&order_dir=<?php echo $order_by == 'item_name' && $order_dir == 'ASC' ? 'DESC' : 'ASC'; ?>">
                                                        Item Name
                                                        <?php if ($order_by == 'item_name'): ?>
                                                            <i class="bi bi-chevron-<?php echo $order_dir == 'ASC' ? 'up' : 'down'; ?>"></i>
                                                        <?php endif; ?>
                                                    </a>
                                                </th>
                                                <th>Item Code</th>
                                                <th>HSN Code</th>
                                                <th>Price</th>
                                                <th>GST</th>
                                                <th>Unit</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($items as $itm): ?>
                                            <tr class="item-card">
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="item-avatar">
                                                            <?php echo strtoupper(substr($itm['item_name'], 0, 1)); ?>
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-0"><?php echo htmlspecialchars($itm['item_name']); ?></h6>
                                                            <?php if (!empty($itm['description'])): ?>
                                                                <small class="text-muted"><?php echo htmlspecialchars(substr($itm['description'], 0, 30)); ?>...</small>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td><?php echo htmlspecialchars($itm['item_code'] ?: 'N/A'); ?></td>
                                                <td><?php echo htmlspecialchars($itm['hsn_code'] ?: 'N/A'); ?></td>
                                                <td>
                                                    <span class="item-price-badge">
                                                        ₹<?php echo number_format($itm['price'], 2); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if ($itm['igst_rate'] > 0): ?>
                                                        <span class="item-gst-badge">IGST: <?php echo $itm['igst_rate']; ?>%</span>
                                                    <?php else: ?>
                                                        <span class="item-gst-badge">CGST: <?php echo $itm['cgst_rate']; ?>%</span>
                                                        <span class="item-gst-badge">SGST: <?php echo $itm['sgst_rate']; ?>%</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="item-unit-badge"><?php echo htmlspecialchars($itm['unit']); ?></span>
                                                </td>
                                                <td>
                                                    <span class="item-status-badge item-status-<?php echo $itm['status']; ?>">
                                                        <?php echo ucfirst($itm['status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="item-row-actions">
                                                        <a href="items.php?action=view&id=<?php echo $itm['id']; ?>" 
                                                           class="btn btn-sm btn-outline-info" title="View">
                                                            <i class="bi bi-eye"></i>
                                                        </a>
                                                        <a href="items.php?action=edit&id=<?php echo $itm['id']; ?>" 
                                                           class="btn btn-sm btn-outline-primary" title="Edit">
                                                            <i class="bi bi-pencil"></i>
                                                        </a>
                                                        <button type="button" class="btn btn-sm btn-outline-danger" 
                                                                onclick="confirmDelete(<?php echo $itm['id']; ?>, '<?php echo htmlspecialchars(addslashes($itm['item_name'])); ?>')"
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
                                
                                <!-- Pagination -->
                                <?php if ($total_pages > 1): ?>
                                <nav aria-label="Page navigation">
                                    <ul class="pagination justify-content-center">
                                        <li class="page-item <?php echo $page == 1 ? 'disabled' : ''; ?>">
                                            <a class="page-link" href="?action=list&search=<?php echo urlencode($search); ?>&status=<?php echo $status_filter; ?>&page=<?php echo $page - 1; ?>">
                                                Previous
                                            </a>
                                        </li>
                                        
                                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                            <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                                <a class="page-link" href="?action=list&search=<?php echo urlencode($search); ?>&status=<?php echo $status_filter; ?>&page=<?php echo $i; ?>">
                                                    <?php echo $i; ?>
                                                </a>
                                            </li>
                                        <?php endfor; ?>
                                        
                                        <li class="page-item <?php echo $page == $total_pages ? 'disabled' : ''; ?>">
                                            <a class="page-link" href="?action=list&search=<?php echo urlencode($search); ?>&status=<?php echo $status_filter; ?>&page=<?php echo $page + 1; ?>">
                                                Next
                                            </a>
                                        </li>
                                    </ul>
                                </nav>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                <?php elseif ($action == 'add' || $action == 'edit'): ?>
                    <!-- Add/Edit Item Form -->
                    <div class="item-form-container">
                        <div class="item-form-header">
                            <h3>
                                <i class="bi bi-<?php echo $action == 'add' ? 'plus-circle' : 'pencil'; ?>"></i>
                                <?php echo $action == 'add' ? 'Add New Item' : 'Edit Item'; ?>
                            </h3>
                        </div>
                        <form method="POST" action="" id="itemForm">
                            <div class="item-form-group">
                                <label for="item_name"><i class="bi bi-tag"></i> Item Name *</label>
                                <input type="text" class="item-form-control" id="item_name" name="item_name" 
                                       placeholder="Enter item name" required
                                       value="<?php echo $action == 'edit' && $item ? htmlspecialchars($item['item_name']) : ''; ?>">
                            </div>
                            
                            <div class="item-form-row">
                                <div class="item-form-group">
                                    <label for="item_code"><i class="bi bi-upc"></i> Item Code</label>
                                    <input type="text" class="item-form-control" id="item_code" name="item_code" 
                                           placeholder="Enter item code"
                                           value="<?php echo $action == 'edit' && $item ? htmlspecialchars($item['item_code']) : ''; ?>">
                                </div>
                                
                                <div class="item-form-group">
                                    <label for="hsn_code"><i class="bi bi-file-text"></i> HSN Code</label>
                                    <input type="text" class="item-form-control" id="hsn_code" name="hsn_code" 
                                           placeholder="Enter HSN code"
                                           value="<?php echo $action == 'edit' && $item ? htmlspecialchars($item['hsn_code']) : ''; ?>">
                                </div>
                            </div>
                            
                            <div class="item-form-row">
                                <div class="item-form-group">
                                    <label for="price"><i class="bi bi-currency-rupee"></i> Price *</label>
                                    <input type="number" class="item-form-control" id="price" name="price" 
                                           step="0.01" min="0.01" placeholder="0.00" required
                                           value="<?php echo $action == 'edit' && $item ? number_format($item['price'], 2, '.', '') : '0.00'; ?>">
                                </div>
                                
                                <div class="item-form-group">
                                    <label for="unit"><i class="bi bi-rulers"></i> Unit *</label>
                                    <select class="item-form-control" id="unit" name="unit" required>
                                        <option value="PCS" <?php echo ($action == 'edit' && $item && $item['unit'] == 'PCS') ? 'selected' : ''; ?>>Pieces (PCS)</option>
                                        <option value="KG" <?php echo ($action == 'edit' && $item && $item['unit'] == 'KG') ? 'selected' : ''; ?>>Kilogram (KG)</option>
                                        <option value="L" <?php echo ($action == 'edit' && $item && $item['unit'] == 'L') ? 'selected' : ''; ?>>Liter (L)</option>
                                        <option value="M" <?php echo ($action == 'edit' && $item && $item['unit'] == 'M') ? 'selected' : ''; ?>>Meter (M)</option>
                                        <option value="PACK" <?php echo ($action == 'edit' && $item && $item['unit'] == 'PACK') ? 'selected' : ''; ?>>Pack</option>
                                        <option value="BOTTLE" <?php echo ($action == 'edit' && $item && $item['unit'] == 'BOTTLE') ? 'selected' : ''; ?>>Bottle</option>
                                        <option value="BOX" <?php echo ($action == 'edit' && $item && $item['unit'] == 'BOX') ? 'selected' : ''; ?>>Box</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="item-form-group">
                                <label><i class="bi bi-percent"></i> GST Rates</label>
                                <div class="gst-input-group">
                                    <div class="gst-input-item">
                                        <label for="cgst_rate">CGST Rate (%)</label>
                                        <input type="number" class="item-form-control" id="cgst_rate" name="cgst_rate" 
                                               step="0.01" min="0" max="100" placeholder="2.5"
                                               value="<?php echo $action == 'edit' && $item ? number_format($item['cgst_rate'], 2, '.', '') : '2.5'; ?>">
                                    </div>
                                    
                                    <div class="gst-input-item">
                                        <label for="sgst_rate">SGST Rate (%)</label>
                                        <input type="number" class="item-form-control" id="sgst_rate" name="sgst_rate" 
                                               step="0.01" min="0" max="100" placeholder="2.5"
                                               value="<?php echo $action == 'edit' && $item ? number_format($item['sgst_rate'], 2, '.', '') : '2.5'; ?>">
                                    </div>
                                    
                                    <div class="gst-input-item">
                                        <label for="igst_rate">IGST Rate (%)</label>
                                        <input type="number" class="item-form-control" id="igst_rate" name="igst_rate" 
                                               step="0.01" min="0" max="100" placeholder="0.00"
                                               value="<?php echo $action == 'edit' && $item ? number_format($item['igst_rate'], 2, '.', '') : '0.00'; ?>">
                                        <small class="text-muted">Use for inter-state</small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="item-form-group">
                                <label for="description"><i class="bi bi-card-text"></i> Description</label>
                                <textarea class="item-form-control" id="description" name="description" 
                                          placeholder="Enter item description" rows="3"><?php echo $action == 'edit' && $item ? htmlspecialchars($item['description']) : ''; ?></textarea>
                            </div>
                            
                            <?php if ($action == 'edit' && $item): ?>
                            <div class="item-form-group">
                                <label for="status"><i class="bi bi-circle-fill"></i> Status</label>
                                <select class="item-form-control" id="status" name="status" required>
                                    <option value="active" <?php echo $item['status'] == 'active' ? 'selected' : ''; ?>>Active</option>
                                    <option value="inactive" <?php echo $item['status'] == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                </select>
                            </div>
                            
                            <!-- Item Preview -->
                            <div class="item-preview-card">
                                <h5>Item Preview</h5>
                                <div class="item-preview-row">
                                    <div class="item-preview-label">Current Price:</div>
                                    <div class="item-preview-value">₹<?php echo number_format($item['price'], 2); ?></div>
                                </div>
                                <div class="item-preview-row">
                                    <div class="item-preview-label">Tax Calculation:</div>
                                    <div class="item-preview-value">
                                        <?php
                                        $cgst_amount = ($item['price'] * $item['cgst_rate']) / 100;
                                        $sgst_amount = ($item['price'] * $item['sgst_rate']) / 100;
                                        $igst_amount = ($item['price'] * $item['igst_rate']) / 100;
                                        $total_with_tax = $item['price'] + $cgst_amount + $sgst_amount + $igst_amount;
                                        ?>
                                        ₹<?php echo number_format($total_with_tax, 2); ?> (incl. GST)
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <div class="item-form-actions">
                                <?php if ($action == 'edit' && $item): ?>
                                    <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                                    <button type="submit" name="update_item" class="btn btn-primary">
                                        <i class="bi bi-check-circle"></i> Update Item
                                    </button>
                                <?php else: ?>
                                    <button type="submit" name="add_item" class="btn btn-primary">
                                        <i class="bi bi-plus-circle"></i> Add Item
                                    </button>
                                <?php endif; ?>
                                <a href="items.php" class="btn btn-outline-secondary">
                                    <i class="bi bi-x-circle"></i> Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                    
                <?php elseif ($action == 'view' && $item): ?>
                    <!-- Item Detail View -->
                    <div class="item-form-container">
                        <div class="item-form-header">
                            <h3>
                                <i class="bi bi-box"></i> Item Details
                            </h3>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-8">
                                <h4><?php echo htmlspecialchars($item['item_name']); ?></h4>
                                <span class="item-status-badge item-status-<?php echo $item['status']; ?>">
                                    <?php echo ucfirst($item['status']); ?>
                                </span>
                                
                                <?php if (!empty($item['description'])): ?>
                                    <p class="mt-3"><?php echo htmlspecialchars($item['description']); ?></p>
                                <?php endif; ?>
                                
                                <div class="row mt-4">
                                    <div class="col-md-6">
                                        <table class="table table-sm">
                                            <tr>
                                                <th>Item Code:</th>
                                                <td><?php echo htmlspecialchars($item['item_code'] ?: 'N/A'); ?></td>
                                            </tr>
                                            <tr>
                                                <th>HSN Code:</th>
                                                <td><?php echo htmlspecialchars($item['hsn_code'] ?: 'N/A'); ?></td>
                                            </tr>
                                            <tr>
                                                <th>Unit:</th>
                                                <td>
                                                    <span class="item-unit-badge"><?php echo htmlspecialchars($item['unit']); ?></span>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                    <div class="col-md-6">
                                        <table class="table table-sm">
                                            <tr>
                                                <th>Price:</th>
                                                <td class="fw-bold">
                                                    <span class="item-price-badge">₹<?php echo number_format($item['price'], 2); ?></span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>CGST:</th>
                                                <td><?php echo number_format($item['cgst_rate'], 2); ?>%</td>
                                            </tr>
                                            <tr>
                                                <th>SGST:</th>
                                                <td><?php echo number_format($item['sgst_rate'], 2); ?>%</td>
                                            </tr>
                                            <?php if ($item['igst_rate'] > 0): ?>
                                            <tr>
                                                <th>IGST:</th>
                                                <td><?php echo number_format($item['igst_rate'], 2); ?>%</td>
                                            </tr>
                                            <?php endif; ?>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 text-center">
                                <div class="item-preview-card">
                                    <h6>Tax Calculation</h6>
                                    <?php
                                    $cgst_amount = ($item['price'] * $item['cgst_rate']) / 100;
                                    $sgst_amount = ($item['price'] * $item['sgst_rate']) / 100;
                                    $igst_amount = ($item['price'] * $item['igst_rate']) / 100;
                                    $total_with_tax = $item['price'] + $cgst_amount + $sgst_amount + $igst_amount;
                                    ?>
                                    <p class="mb-1">Base Price: ₹<?php echo number_format($item['price'], 2); ?></p>
                                    <?php if ($item['igst_rate'] > 0): ?>
                                        <p class="mb-1">IGST: ₹<?php echo number_format($igst_amount, 2); ?></p>
                                    <?php else: ?>
                                        <p class="mb-1">CGST: ₹<?php echo number_format($cgst_amount, 2); ?></p>
                                        <p class="mb-1">SGST: ₹<?php echo number_format($sgst_amount, 2); ?></p>
                                    <?php endif; ?>
                                    <hr>
                                    <h5 class="text-primary">Total: ₹<?php echo number_format($total_with_tax, 2); ?></h5>
                                </div>
                            </div>
                        </div>
                        
                        <div class="item-form-actions">
                            <a href="items.php?action=edit&id=<?php echo $item['id']; ?>" class="btn btn-primary">
                                <i class="bi bi-pencil"></i> Edit Item
                            </a>
                            <a href="udhar.php?action=add&item_id=<?php echo $item['id']; ?>" class="btn btn-success">
                                <i class="bi bi-cart-plus"></i> Use in Bill
                            </a>
                            <a href="items.php" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left"></i> Back to List
                            </a>
                        </div>
                        
                        <div class="mt-4 pt-3 border-top">
                            <small class="text-muted">
                                <i class="bi bi-clock"></i> Created: <?php echo date('d M Y, h:i A', strtotime($item['created_at'])); ?>
                                <?php if ($item['created_at'] != $item['updated_at']): ?>
                                    | Updated: <?php echo date('d M Y, h:i A', strtotime($item['updated_at'])); ?>
                                <?php endif; ?>
                            </small>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>   
    
    <script>
        // Sidebar toggle
        document.getElementById("sidebarToggle").addEventListener("click", function () {
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
        document.addEventListener("click", function (event) {
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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    function confirmDelete(itemId, itemName) {
        if (confirm('Are you sure you want to delete "' + itemName + '"? This action cannot be undone.')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '';
            
            const input1 = document.createElement('input');
            input1.type = 'hidden';
            input1.name = 'item_id';
            input1.value = itemId;
            
            const input2 = document.createElement('input');
            input2.type = 'hidden';
            input2.name = 'delete_item';
            input2.value = '1';
            
            form.appendChild(input1);
            form.appendChild(input2);
            document.body.appendChild(form);
            form.submit();
        }
    }

    // GST rate validation
    document.getElementById('igst_rate')?.addEventListener('input', function() {
        const igst = parseFloat(this.value) || 0;
        const cgst = document.getElementById('cgst_rate');
        const sgst = document.getElementById('sgst_rate');
        
        if (igst > 0) {
            cgst.value = 0;
            sgst.value = 0;
            cgst.disabled = true;
            sgst.disabled = true;
        } else {
            cgst.disabled = false;
            sgst.disabled = false;
        }
    });

    // Form validation
    document.getElementById('itemForm')?.addEventListener('submit', function(e) {
        const price = parseFloat(document.getElementById('price').value);
        if (isNaN(price) || price <= 0) {
            e.preventDefault();
            alert('Price must be greater than 0');
            document.getElementById('price').focus();
            return false;
        }
        
        const cgst = parseFloat(document.getElementById('cgst_rate').value) || 0;
        const sgst = parseFloat(document.getElementById('sgst_rate').value) || 0;
        const igst = parseFloat(document.getElementById('igst_rate').value) || 0;
        
        if (cgst < 0 || cgst > 100 || sgst < 0 || sgst > 100 || igst < 0 || igst > 100) {
            e.preventDefault();
            alert('GST rates must be between 0 and 100');
            return false;
        }
        
        if (igst > 0 && (cgst > 0 || sgst > 0)) {
            e.preventDefault();
            alert('Please use either IGST (for inter-state) OR CGST+SGST (for intra-state), not both');
            return false;
        }
        
        return true;
    });
    </script>
</body>
</html>