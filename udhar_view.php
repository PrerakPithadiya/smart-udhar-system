<?php
// File: smart-udhar-system/udhar_view.php
// This file contains the HTML template for the Udhar page
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
    <link rel="stylesheet" href="assets/css/udhar_custom.css">
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
                                                    <div class="position-relative customer-search-wrapper">
                                                        <input type="text" class="bill-form-control shadow-sm"
                                                            id="customer_search" name="customer_search"
                                                            placeholder="Type customer name or mobile number..."
                                                            style="height: 55px; font-size: 1.1rem;" required>

                                                        <div class="mt-2 d-flex align-items-center justify-content-between">
                                                            <button type="button" class="btn-search-dynamic"
                                                                id="customer_search_btn" title="Search Customer" disabled>
                                                                <i class="bi bi-search"></i> Search
                                                            </button>
                                                            <div class="form-text mt-0">
                                                                <i class="bi bi-info-circle-fill me-1"></i>
                                                                Start typing to search existing customers
                                                            </div>
                                                        </div>
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
                                                <div class="bill-form-group" style="grid-column: span 3;">
                                                    <label for="notes"><i class="bi bi-sticky"></i> Notes</label>
                                                    <input type="text" class="bill-form-control" id="notes" name="notes"
                                                        placeholder="Additional notes or description">
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Items Section -->
                                        <div class="bill-form-section">
                                            <div class="d-flex align-items-center justify-content-between mb-3">
                                                <h5 class="mb-0"><i class="bi bi-cart-plus"></i> Bill Items</h5>
                                                <div class="form-check form-switch premium-switch">
                                                    <input class="form-check-input" type="checkbox" id="toggleRowColors">
                                                    <label class="form-check-label fw-500" for="toggleRowColors"
                                                        style="font-size: 0.9rem; color: #666;">
                                                        <i class="bi bi-palette2 me-1"></i> Multi-color Rows
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="bill-items-container table-responsive">
                                                <table
                                                    class="table table-bordered table-hover align-middle mb-0 resizable-table"
                                                    id="billTable">
                                                    <thead class="table-light">
                                                        <tr class="resizable-header">
                                                            <th style="width: 300px;">Item Name<div class="resizer"></div>
                                                            </th>
                                                            <th style="width: 120px;">HSN<div class="resizer"></div>
                                                            </th>
                                                            <th style="width: 100px;">Qty<div class="resizer"></div>
                                                            </th>
                                                            <th style="width: 110px;">Unit<div class="resizer"></div>
                                                            </th>
                                                            <th style="width: 150px;">Price<div class="resizer"></div>
                                                            </th>
                                                            <th style="width: 190px;">GST (%)<div class="resizer"></div>
                                                            </th>
                                                            <th style="width: 140px;">Total<div class="resizer"></div>
                                                            </th>
                                                            <th style="width: 60px;"></th>
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
                                                <div class="col-lg-12">
                                                    <div
                                                        class="bill-summary-card shadow-sm h-100 d-flex flex-column justify-content-between">
                                                        <table class="bill-summary-table mb-0">
                                                            <tr>
                                                                <td class="summary-label">Sub Total:</td>
                                                                <td class="summary-value">₹<span id="subTotal">0.00</span>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td class="summary-label">CGST (+):</td>
                                                                <td class="summary-value">
                                                                    <input type="number" step="0.01" class="summary-input"
                                                                        id="cgst_amount" name="cgst_amount" value="0.00"
                                                                        oninput="calculateGrandTotal()">
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td class="summary-label">SGST (+):</td>
                                                                <td class="summary-value">
                                                                    <input type="number" step="0.01" class="summary-input"
                                                                        id="sgst_amount" name="sgst_amount" value="0.00"
                                                                        oninput="calculateGrandTotal()">
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td class="summary-label">IGST (+):</td>
                                                                <td class="summary-value">
                                                                    <input type="number" step="0.01" class="summary-input"
                                                                        id="igst_amount" name="igst_amount" value="0.00"
                                                                        oninput="calculateGrandTotal()">
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td class="summary-label">Discount (-):</td>
                                                                <td class="summary-value">
                                                                    <input type="number" step="0.01" class="summary-input"
                                                                        id="discount" name="discount" value="0.00"
                                                                        oninput="calculateGrandTotal()">
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td class="summary-label">Transportation (+):</td>
                                                                <td class="summary-value">
                                                                    <input type="number" step="0.01" class="summary-input"
                                                                        id="transportation_charge"
                                                                        name="transportation_charge" value="0.00"
                                                                        oninput="calculateGrandTotal()">
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td class="summary-label">Round Off (+/-):</td>
                                                                <td class="summary-value">
                                                                    <input type="number" step="0.01" class="summary-input"
                                                                        id="round_off" name="round_off" value="0.00"
                                                                        oninput="calculateGrandTotal()">
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td class="summary-label grand-total">Grand Total:</td>
                                                                <td class="summary-value grand-total">₹<span
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
                                                        <?php if ($udhar['transportation_charge'] > 0): ?>
                                                            <tr>
                                                                <td class="bill-info-label">Transportation:</td>
                                                                <td class="bill-info-value">
                                                                    +₹<?php echo number_format($udhar['transportation_charge'], 2); ?>
                                                                </td>
                                                            </tr>
                                                        <?php endif; ?>
                                                        <?php if ($udhar['round_off'] != 0): ?>
                                                            <tr>
                                                                <td class="bill-info-label">Round Off:</td>
                                                                <td class="bill-info-value">
                                                                    <?php echo $udhar['round_off'] > 0 ? '+' : ''; ?>₹<?php echo number_format($udhar['round_off'], 2); ?>
                                                                </td>
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

    <!-- Initialization and Data passing to External Script -->
    <script>
        // Global data from PHP
        const ITEMS_LIST = <?php echo json_encode($items); ?>;
        const PRE_SELECTED_ITEM_ID = <?php echo $item_id; ?>;
        const CURRENT_ACTION = '<?php echo $action; ?>';
        const CUSTOMER_ID = <?php echo $customer_id; ?>;
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="assets/js/search_suggestions.js"></script>
    <script src="assets/js/udhar_custom.js"></script>

    <script>
        // Inline script for PHP-dependent initialization
        <?php if ($action == 'add'): ?>
            window.addEventListener('DOMContentLoaded', function () {
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
    </script>
</body>

</html>
