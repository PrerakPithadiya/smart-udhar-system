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
    <link rel="stylesheet" href="assets/css/items.css">
</head>

<body>
    <?php include 'includes/header.php'; ?>


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
        <!-- Floating Toggle Button (visible when sidebar is closed) -->
        <button class="floating-toggle-btn" id="floatingToggle">
            <i class="bi bi-chevron-right"></i>
        </button>



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
                                        <div class="col-md-9">
                                            <div class="items-search-box">
                                                <i class="bi bi-search search-icon"></i>
                                                <input type="text" name="search" class="form-control"
                                                    placeholder="Search by item name, code or HSN..."
                                                    value="<?php echo htmlspecialchars($search); ?>" onchange="this.form.submit()">
                                            </div>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="assets/js/items.js"></script>
</body>

</html>