<?php
// File: smart-udhar-system/udhar_view.php
// This file contains the HTML template for the Udhar page
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Udhar - Smart Udhar System</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.iconify.design/iconify-icon/1.0.7/iconify-icon.min.js"></script>
    <link
        href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=Outfit:wght@200;300;400;500;600;700;800&display=swap"
        rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="assets/css/udhar.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/udhar_custom.css">

    <?php
    if (!function_exists('getUdharListUrl')) {
        function getUdharListUrl($page)
        {
            $params = $_GET;
            $params['action'] = 'list';
            $params['page'] = max(1, (int) $page);
            return 'udhar.php?' . http_build_query($params);
        }
    }
    ?>

    <style>
        :root {
            --bg-airy: #f8fafc;
            --glass-white: rgba(255, 255, 255, 0.9);
            --glass-border: rgba(255, 255, 255, 0.2);
            --accent-indigo: #6366f1;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--bg-airy);
            color: #1e293b;
            overflow-x: hidden;

            background-image:
                radial-gradient(at 0% 0%, rgba(99, 102, 241, 0.05) 0px, transparent 50%),
                radial-gradient(at 100% 0%, rgba(168, 85, 247, 0.05) 0px, transparent 50%);
        }

        .udhar-container .udhar-stat-card,
        .udhar-container .card.udhar-card,
        .udhar-container .bill-form-container,
        .udhar-container .bill-view-card,
        .udhar-container .bill-summary-card,
        .udhar-container .modal-content {
            background: var(--glass-white) !important;
            backdrop-filter: blur(12px);
            border: 1px solid var(--glass-border) !important;
            border-radius: 24px !important;
            box-shadow: 0 10px 40px -10px rgba(0, 0, 0, 0.04) !important;
        }

        .udhar-container .bill-form-header,
        .udhar-container .bill-view-header {
            background: transparent !important;
            border-bottom: 1px solid rgba(0, 0, 0, 0.06) !important;
            border-top-left-radius: 24px !important;
            border-top-right-radius: 24px !important;
        }

        .udhar-container .btn,
        .udhar-container .btn-primary,
        .udhar-container .btn-outline-secondary {
            border-radius: 16px !important;
            font-weight: 800 !important;
        }

        .udhar-container .btn-primary {
            background: var(--accent-indigo) !important;
            border-color: var(--accent-indigo) !important;
            box-shadow: 0 10px 20px -5px rgba(99, 102, 241, 0.3) !important;
        }

        .udhar-container .btn-primary:hover {
            background: #4f46e5 !important;
            border-color: #4f46e5 !important;
        }

        .udhar-container .btn-outline-secondary {
            background: #fff !important;
            border-color: #e2e8f0 !important;
            color: #475569 !important;
        }

        .udhar-container .btn-outline-secondary:hover {
            background: #f8fafc !important;
            border-color: #cbd5e1 !important;
            color: #0f172a !important;
        }

        .udhar-container .form-control,
        .udhar-container .form-select,
        .udhar-container .bill-form-control {
            border-radius: 16px !important;
            border-color: #e2e8f0 !important;
        }

        .udhar-container .table {
            margin-bottom: 0;
        }

        .udhar-container .udhar-table-container {
            border-radius: 24px;
            overflow: hidden;
        }

        .udhar-container .udhar-table thead {
            background: rgba(248, 250, 252, 0.6) !important;
        }

        /* Ensure containers allow search suggestions overflow */
        .udhar-container .bill-form-container,
        .udhar-container .bill-form-body {
            overflow: visible !important;
        }

        /* Prevent transform on containers with search */
        .udhar-container .bill-form-container:has(#customer_search) {
            transform: none !important;
        }

        /* Ensure customer search wrapper has proper stacking */
        .customer-search-wrapper {
            position: relative;
            z-index: 1000;
        }
    </style>
</head>

<body class="bg-[var(--bg-airy)]">
    <!-- Sidebar Toggle Commander (Visible when closed) -->
    <button id="sidebarOpenBtn"
        class="fixed left-0 top-1/2 -translate-y-1/2 w-12 h-16 bg-white border border-slate-200 text-indigo-600 rounded-r-2xl flex items-center justify-center shadow-xl shadow-indigo-100/50 hover:w-14 active:scale-95 transition-all z-[100] hidden">
        <iconify-icon icon="solar:sidebar-minimalistic-bold-duotone" width="24"></iconify-icon>
    </button>

    <?php include 'includes/sidebar.php'; ?>

    <!-- Main Content -->
    <div id="mainContent" class="main-content min-h-screen relative z-10 px-4 py-8 md:px-10">

        <div class="container-fluid">
            <div class="container-fluid udhar-container">
                <div class="row">
                    <div class="col-12">
                        <header
                            class="mb-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                            <div class="flex items-center gap-4">
                                <div class="flex flex-col">
                                    <nav
                                        class="flex text-[10px] items-center gap-1.5 font-bold uppercase tracking-widest text-slate-400 mb-2">
                                        <iconify-icon icon="solar:home-2-bold" class="text-xs"></iconify-icon>
                                        <span>Smart Udhar</span>
                                        <iconify-icon icon="solar:alt-arrow-right-bold"
                                            class="text-[8px]"></iconify-icon>
                                        <span class="text-indigo-500">Udhar</span>
                                    </nav>
                                    <h1
                                        class="text-4xl font-black text-slate-800 tracking-tighter flex items-center gap-3">
                                        <iconify-icon icon="solar:card-transfer-bold-duotone"
                                            class="text-indigo-500"></iconify-icon>
                                        Udhar Book
                                    </h1>
                                </div>
                            </div>

                            <div class="flex items-center gap-3">
                                <?php if ($action == 'list'): ?>
                                    <a href="udhar.php?action=add"
                                        class="bg-indigo-600 hover:bg-indigo-500 text-white px-6 py-3 rounded-2xl font-bold flex items-center gap-2 shadow-lg shadow-indigo-200 transition-all hover:-translate-y-1">
                                        <iconify-icon icon="solar:add-circle-bold" class="text-xl"></iconify-icon>
                                        Add New Udhar Bill
                                    </a>
                                <?php else: ?>
                                    <a href="udhar.php"
                                        class="bg-white hover:bg-slate-50 text-slate-600 px-6 py-3 rounded-2xl font-bold border border-slate-200 flex items-center gap-2 transition-all">
                                        <iconify-icon icon="solar:arrow-left-bold" class="text-xl"></iconify-icon>
                                        Back
                                    </a>
                                <?php endif; ?>
                            </div>
                        </header>

                        <?php displayMessage(); ?>

                        <?php if ($action == 'list'): ?>
                            <!-- Udhar List View -->
                            <div class="udhar-stat-card mb-4">
                                <div class="row align-items-center">
                                    <div class="col-md-4">
                                        <h5 class="mb-0">All Udhar Bills (<?php echo $total_udhar; ?>)</h5>
                                    </div>
                                    <div class="col-md-8">
                                        <form method="GET" class="row g-2">
                                            <input type="hidden" name="action" value="list">
                                            <div class="col-md-6">
                                                <select name="status" class="form-select" onchange="this.form.submit()">
                                                    <option value="">All</option>
                                                    <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Not paid</option>
                                                    <option value="partially_paid" <?php echo $status_filter == 'partially_paid' ? 'selected' : ''; ?>>Paid a little
                                                    </option>
                                                    <option value="paid" <?php echo $status_filter == 'paid' ? 'selected' : ''; ?>>Paid</option>
                                                    <option value="overdue" <?php echo $status_filter == 'overdue' ? 'selected' : ''; ?>>Past due date</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <select name="category" class="form-select" onchange="this.form.submit()">
                                                    <option value="">All Categories</option>
                                                    <option value="Fertilizers" <?php echo $category_filter == 'Fertilizers' ? 'selected' : ''; ?>>Fertilizers</option>
                                                    <option value="Seeds" <?php echo $category_filter == 'Seeds' ? 'selected' : ''; ?>>Seeds</option>
                                                    <option value="Insecticides" <?php echo $category_filter == 'Insecticides' ? 'selected' : ''; ?>>Insecticides</option>
                                                    <option value="Others" <?php echo $category_filter == 'Others' ? 'selected' : ''; ?>>Others</option>
                                                </select>
                                            </div>
                                            <div class="col-12">
                                                <div class="udhar-search-box">
                                                    <i class="bi bi-search search-icon"></i>
                                                    <input type="text" id="udhar-search" name="search" class="form-control"
                                                        autocomplete="off" placeholder="Search bill or customer..."
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
                                    <div class="stat-label">Total amount</div>
                                    <i class="bi bi-cash-coin stat-icon"></i>
                                </div>

                                <div class="udhar-stat-card stat-danger">
                                    <div class="stat-value">
                                        ₹<?php echo number_format($pending_result['total'] ?? 0, 2); ?></div>
                                    <div class="stat-label">Not paid amount</div>
                                    <i class="bi bi-clock-history stat-icon"></i>
                                </div>

                                <div class="udhar-stat-card stat-warning">
                                    <div class="stat-value"><?php echo number_format($overdue_result['count'] ?? 0); ?>
                                    </div>
                                    <div class="stat-label">Past due date</div>
                                    <i class="bi bi-exclamation-triangle stat-icon"></i>
                                </div>

                                <div class="udhar-stat-card stat-success">
                                    <div class="stat-value"><?php echo number_format($paid_result['count'] ?? 0); ?>
                                    </div>
                                    <div class="stat-label">Paid bills</div>
                                    <i class="bi bi-check-circle stat-icon"></i>
                                </div>
                            </div>

                            <div class="card udhar-card">
                                <div class="card-body">
                                    <?php if (empty($udhar_list)): ?>
                                        <div class="udhar-empty-state">
                                            <i class="bi bi-receipt display-1 empty-icon"></i>
                                            <h4 class="mt-3">No udhar bills found</h4>
                                            <p class="text-muted">Add your first udhar bill</p>
                                            <a href="udhar.php?action=add" class="btn btn-primary">
                                                <i class="bi bi-plus-circle"></i> Add First Udhar Bill
                                            </a>
                                        </div>
                                    <?php else: ?>
                                        <div class="udhar-table-container">
                                            <table class="table udhar-table resizable-table" id="udharTable">
                                                <thead>
                                                    <tr>
                                                        <th class="col-bill-no">Bill No. <div class="resizer"></div>
                                                        </th>
                                                        <th class="col-customer">Customer <div class="resizer"></div>
                                                        </th>
                                                        <th class="col-date">Date <div class="resizer"></div>
                                                        </th>
                                                        <th class="col-amount">Amount <div class="resizer"></div>
                                                        </th>
                                                        <th class="col-due-date">Due Date <div class="resizer"></div>
                                                        </th>
                                                        <th>Status</th>
                                                        <th>Options</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($udhar_list as $entry): ?>
                                                        <tr class="bill-card">
                                                            <td class="col-bill-no">
                                                                <strong
                                                                    class="udhar-bill-number"><?php echo htmlspecialchars($entry['bill_no']); ?></strong>
                                                            </td>
                                                            <td class="col-customer">
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
                                                            <td class="col-date">
                                                                <span
                                                                    class="udhar-date"><?php echo date('d M Y', strtotime($entry['transaction_date'])); ?></span>
                                                            </td>
                                                            <td class="col-amount">
                                                                <span
                                                                    class="udhar-amount">₹<?php echo number_format($entry['amount'], 2); ?></span>
                                                            </td>
                                                            <td class="col-due-date">
                                                                <?php if (!empty($entry['due_date'])): ?>
                                                                    <span
                                                                        class="udhar-date"><?php echo date('d M Y', strtotime($entry['due_date'])); ?></span>
                                                                    <?php if (strtotime($entry['due_date']) < time() && $entry['status'] != 'paid'): ?>
                                                                        <br><span class="udhar-overdue-badge">Late</span>
                                                                    <?php endif; ?>
                                                                <?php else: ?>
                                                                    <span class="text-muted">No due date</span>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td>
                                                                <?php
                                                                $status_class = 'udhar-status-paid';
                                                                if ($entry['status'] == 'pending') {
                                                                    $status_class = 'udhar-status-pending';
                                                                } elseif ($entry['status'] == 'partially_paid') {
                                                                    $status_class = 'udhar-status-partially_paid';
                                                                }
                                                                ?>
                                                                <span class="udhar-status-badge <?php echo $status_class; ?>">
                                                                    <?php
                                                                    $status_label = $entry['status'];
                                                                    if ($status_label === 'pending') {
                                                                        $status_label = 'Not paid';
                                                                    } elseif ($status_label === 'partially_paid') {
                                                                        $status_label = 'Paid a little';
                                                                    } elseif ($status_label === 'paid') {
                                                                        $status_label = 'Paid';
                                                                    }
                                                                    echo $status_label;
                                                                    ?>
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <div class="udhar-row-actions">
                                                                    <!-- Print Tax Invoice (New) -->
                                                                    <a href="print_bill_tax_invoice.php?id=<?php echo $entry['id']; ?>"
                                                                        class="btn btn-sm btn-outline-primary"
                                                                        title="Print Tax Invoice">
                                                                        <i class="bi bi-file-earmark-text"></i>
                                                                    </a>

                                                                    <!-- Print Standard Bill (Old) -->
                                                                    <a href="print_bill.php?id=<?php echo $entry['id']; ?>"
                                                                        class="btn btn-sm btn-outline-secondary"
                                                                        title="Print Standard Bill">
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
                                                            href="<?php echo getUdharListUrl($page - 1); ?>">
                                                            <i class="bi bi-chevron-left"></i>
                                                        </a>
                                                    </li>

                                                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                                            <a class="page-link"
                                                                href="<?php echo getUdharListUrl($i); ?>">
                                                                <?php echo $i; ?>
                                                            </a>
                                                        </li>
                                                    <?php endfor; ?>

                                                    <li class="page-item <?php echo $page == $total_pages ? 'disabled' : ''; ?>">
                                                        <a class="page-link"
                                                            href="<?php echo getUdharListUrl($page + 1); ?>">
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
                            <div class="bill-form-container" style="position: relative; z-index: 100;">
                                <div class="bill-form-header">
                                    <h3><i class="bi bi-plus-circle"></i> Add New Udhar Bill</h3>
                                </div>
                                <div class="bill-form-body">
                                    <form method="POST" action="" id="udharForm">
                                        <div class="bill-form-section">
                                            <h5><i class="bi bi-info-circle"></i> Basic Details</h5>
                                            <div class="bill-form-row">
                                                <div class="bill-form-group" style="grid-column: span 2;">
                                                    <label for="customer_search"><i class="bi bi-person"></i> Customer
                                                        *</label>
                                                    <div class="position-relative customer-search-wrapper"
                                                        style="z-index: 1000;">
                                                        <input type="text" class="bill-form-control shadow-sm"
                                                            id="customer_search" name="customer_search"
                                                            placeholder="Type customer name or mobile..."
                                                            style="height: 55px; font-size: 1.1rem;" required>

                                                        <div class="mt-2 d-flex align-items-center justify-content-between">
                                                            <button type="button" class="btn-search-dynamic"
                                                                id="customer_search_btn" title="Find Customer" disabled>
                                                                <i class="bi bi-search"></i> Find
                                                            </button>
                                                            <div class="form-text mt-0">
                                                                <i class="bi bi-info-circle-fill me-1"></i>
                                                                Type to find an existing customer
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
                                                        <option value="">Choose Category</option>
                                                        <option value="Fertilizers">Fertilizers</option>
                                                        <option value="Seeds">Seeds</option>
                                                        <option value="Insecticides">Insecticides</option>
                                                        <option value="Others">Others</option>
                                                    </select>
                                                </div>

                                                <div class="bill-form-group">
                                                    <label for="transaction_date"><i class="bi bi-calendar"></i> Bill Date
                                                        *</label>
                                                    <input type="date" class="bill-form-control" id="transaction_date"
                                                        name="transaction_date" value="<?php echo date('Y-m-d'); ?>"
                                                        required>
                                                </div>

                                                <div class="bill-form-group">
                                                    <label for="due_date"><i class="bi bi-calendar-check"></i> Due date
                                                        (if any)</label>
                                                    <input type="date" class="bill-form-control" id="due_date"
                                                        name="due_date">
                                                </div>
                                            </div>

                                            <div class="bill-form-row">
                                                <div class="bill-form-group" style="grid-column: span 3;">
                                                    <label for="notes"><i class="bi bi-sticky"></i> Notes</label>
                                                    <input type="text" class="bill-form-control" id="notes" name="notes"
                                                        placeholder="Any notes (optional)">
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Items Section -->
                                        <div class="bill-form-section">
                                            <div class="d-flex align-items-center justify-content-between mb-3">
                                                <h5 class="mb-0"><i class="bi bi-cart-plus"></i> Items in this bill</h5>
                                                <div class="form-check form-switch premium-switch">
                                                    <input class="form-check-input" type="checkbox" id="toggleRowColors">
                                                    <label class="form-check-label fw-500" for="toggleRowColors"
                                                        style="font-size: 0.9rem; color: #666;">
                                                        <i class="bi bi-palette2 me-1"></i> Color rows
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="bill-items-container table-responsive">
                                                <table
                                                    class="table table-bordered table-hover align-middle mb-0 resizable-table"
                                                    id="billTable">
                                                    <thead class="table-light">
                                                        <tr class="resizable-header">
                                                            <th style="width: 300px;">Item<div class="resizer"></div>
                                                            </th>
                                                            <th style="width: 120px;">HSN<div class="resizer"></div>
                                                            </th>
                                                            <th style="width: 100px;">Qty<div class="resizer"></div>
                                                            </th>
                                                            <th style="width: 110px;">Unit<div class="resizer"></div>
                                                            </th>
                                                            <th style="width: 150px;">Rate<div class="resizer"></div>
                                                            </th>
                                                            <th style="width: 140px;">Amount<div class="resizer"></div>
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
                                                        <i class="bi bi-plus-circle"></i> Add Item Row
                                                    </button>
                                                    <button type="button" class="btn btn-outline-info"
                                                        onclick="addItemFromList()">
                                                        <i class="bi bi-list-check"></i> Pick from list
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
                                                                <td class="summary-label">Items total:</td>
                                                                <td class="summary-value">₹<span id="subTotal">0.00</span>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td class="summary-label">Discount (minus):</td>
                                                                <td class="summary-value">
                                                                    <input type="number" step="0.01" class="summary-input"
                                                                        id="discount" name="discount" value="0.00"
                                                                        oninput="calculateGrandTotal()">
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td class="summary-label">Transport charge (plus):</td>
                                                                <td class="summary-value">
                                                                    <input type="number" step="0.01" class="summary-input"
                                                                        id="transportation_charge"
                                                                        name="transportation_charge" value="0.00"
                                                                        oninput="calculateGrandTotal()">
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td class="summary-label">Round off (+/-):</td>
                                                                <td class="summary-value">
                                                                    <input type="number" step="0.01" class="summary-input"
                                                                        id="round_off" name="round_off" value="0.00"
                                                                        oninput="calculateGrandTotal()">
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td class="summary-label grand-total">Final total:</td>
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
                                                <i class="bi bi-check-circle"></i> Save & Print Bill
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
                                            <?php echo $action == 'edit' ? 'Edit Bill' : 'Bill Details'; ?>
                                            <span
                                                class="bill-number-badge"><?php echo htmlspecialchars($udhar['bill_no']); ?></span>
                                        </h3>
                                    </div>
                                    <div class="bill-view-body">
                                        <?php if ($action == 'edit'): ?>
                                            <div class="glass-card overflow-hidden">
                                                <div
                                                    class="px-8 py-6 border-b border-slate-100 flex items-center justify-between">
                                                    <div class="flex items-center gap-3">
                                                        <div
                                                            class="w-10 h-10 bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center text-xl">
                                                            <i class="bi bi-pencil"></i>
                                                        </div>
                                                        <div>
                                                            <h4 class="text-lg font-black text-slate-800 tracking-tight mb-0">
                                                                Edit Bill</h4>
                                                            <p
                                                                class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-0">
                                                                <?php echo htmlspecialchars($udhar['bill_no']); ?>
                                                            </p>
                                                        </div>
                                                    </div>
                                                    <a href="udhar.php?action=view&id=<?php echo $udhar['id']; ?>"
                                                        class="text-[10px] font-black text-indigo-500 uppercase tracking-widest hover:underline">Back</a>
                                                </div>

                                                <form method="POST" action="" class="p-6 md:p-8">
                                                    <input type="hidden" name="udhar_id" value="<?php echo $udhar['id']; ?>">

                                                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                                        <div class="glass-card p-6">
                                                            <div class="flex items-center gap-3 mb-4">
                                                                <iconify-icon icon="solar:user-id-bold-duotone"
                                                                    class="text-xl text-slate-400"></iconify-icon>
                                                                <h5
                                                                    class="text-sm font-black text-slate-800 tracking-tight mb-0">
                                                                    Bill Info</h5>
                                                            </div>

                                                            <div class="space-y-3 text-sm">
                                                                <div class="flex justify-between items-center">
                                                                    <span
                                                                        class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Customer</span>
                                                                    <span
                                                                        class="font-black text-slate-800 tracking-tight"><?php echo htmlspecialchars($udhar['customer_name']); ?></span>
                                                                </div>
                                                                <div class="flex justify-between items-center">
                                                                    <span
                                                                        class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Bill
                                                                        Date</span>
                                                                    <span
                                                                        class="font-black text-slate-800 tracking-tight"><?php echo date('d M Y', strtotime($udhar['transaction_date'])); ?></span>
                                                                </div>
                                                                <div class="flex justify-between items-center">
                                                                    <span
                                                                        class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Bill
                                                                        No</span>
                                                                    <span
                                                                        class="font-black text-slate-800 tracking-tight"><?php echo htmlspecialchars($udhar['bill_no']); ?></span>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="glass-card p-6">
                                                            <div class="flex items-center gap-3 mb-4">
                                                                <iconify-icon icon="solar:settings-bold-duotone"
                                                                    class="text-xl text-slate-400"></iconify-icon>
                                                                <h5
                                                                    class="text-sm font-black text-slate-800 tracking-tight mb-0">
                                                                    Update Details</h5>
                                                            </div>

                                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                                <div>
                                                                    <label for="due_date"
                                                                        class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Due
                                                                        Date</label>
                                                                    <input type="date" id="due_date" name="due_date"
                                                                        value="<?php echo !empty($udhar['due_date']) ? $udhar['due_date'] : ''; ?>"
                                                                        class="form-control"
                                                                        style="height: 48px; border-radius: 16px;" />
                                                                </div>
                                                                <div>
                                                                    <label for="status"
                                                                        class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Status</label>
                                                                    <select id="status" name="status" required
                                                                        class="form-select"
                                                                        style="height: 48px; border-radius: 16px;">
                                                                        <option value="pending" <?php echo $udhar['status'] == 'pending' ? 'selected' : ''; ?>>
                                                                            Pending</option>
                                                                        <option value="partially_paid" <?php echo $udhar['status'] == 'partially_paid' ? 'selected' : ''; ?>>Partially Paid</option>
                                                                        <option value="paid" <?php echo $udhar['status'] == 'paid' ? 'selected' : ''; ?>>Paid</option>
                                                                    </select>
                                                                </div>
                                                            </div>

                                                            <div class="mt-4">
                                                                <label for="notes"
                                                                    class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Notes</label>
                                                                <textarea id="notes" name="notes" rows="3" class="form-control"
                                                                    style="border-radius: 16px;"><?php echo htmlspecialchars($udhar['notes']); ?></textarea>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="flex flex-col md:flex-row justify-between gap-3 mt-6">
                                                        <div class="flex flex-col sm:flex-row gap-3">
                                                            <button type="submit" name="update_udhar" class="btn btn-primary"
                                                                style="border-radius: 16px; font-weight: 800;">
                                                                <i class="bi bi-check-circle"></i> Save Changes
                                                            </button>
                                                            <a href="udhar.php?action=view&id=<?php echo $udhar['id']; ?>"
                                                                class="btn btn-outline-secondary"
                                                                style="border-radius: 16px; font-weight: 800;">
                                                                <i class="bi bi-x-circle"></i> Cancel
                                                            </a>
                                                        </div>
                                                        <button type="button" class="btn btn-danger" data-bs-toggle="modal"
                                                            data-bs-target="#deleteModal"
                                                            style="border-radius: 16px; font-weight: 800;">
                                                            <i class="bi bi-trash"></i> Delete Bill
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        <?php else: ?>
                                            <!-- View Mode -->
                                            <div class="bill-info-section">
                                                <div class="bill-info-card">
                                                    <h5>Customer info</h5>
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
                                                                    <span class="text-muted">Not added</span>
                                                                <?php endif; ?>
                                                            </td>
                                                        </tr>
                                                    </table>

                                                    <!-- View Customer Profile Button -->
                                                    <div class="mt-3">
                                                        <a href="customers.php?action=view&id=<?php echo $udhar['customer_id']; ?>"
                                                            class="btn btn-outline-primary btn-sm w-100">
                                                            <i class="bi bi-person-circle"></i> View Customer Profile
                                                        </a>
                                                    </div>
                                                </div>

                                                <div class="bill-info-card">
                                                    <h5>Totals</h5>
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
                                                            <td class="bill-info-label">Final total:</td>
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
                                                <h5>Items in this bill</h5>
                                                <div class="bill-items-list">
                                                    <div class="bill-item-row header">
                                                        <div class="bill-item-sno">#</div>
                                                        <div class="bill-item-name">Item Name</div>
                                                        <div class="bill-item-qty">Qty</div>
                                                        <div class="bill-item-price">Price</div>
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
                                                                    ₹<?php echo number_format($item['unit_price'], 2); ?>
                                                                </div>
                                                                <div class="bill-item-total-view">
                                                                    ₹<?php echo number_format($item['total_amount'], 2); ?>
                                                                </div>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    <?php endif; ?>
                                                </div>
                                            </div>

                                            <div class="d-flex justify-content-between mt-4">
                                                <div class="d-flex gap-2">
                                                    <a href="print_bill_tax_invoice.php?id=<?php echo $udhar['id']; ?>"
                                                        class="btn btn-primary">
                                                        <i class="bi bi-file-earmark-text"></i> Print Tax Invoice
                                                    </a>
                                                    <a href="print_bill.php?id=<?php echo $udhar['id']; ?>"
                                                        class="btn btn-outline-primary">
                                                        <i class="bi bi-printer"></i> Print Standard Bill
                                                    </a>
                                                </div>
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

                                <!-- Delete Confirmation Modal -->
                                <?php if ($action == 'edit'): ?>
                                    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel"
                                        aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="deleteModalLabel">Delete bill?</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                        aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="alert alert-warning">
                                                        <i class="bi bi-exclamation-triangle"></i>
                                                        <strong>Warning:</strong> Are you sure you want to delete this bill?
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
                                                        This cannot be undone. Items and payments linked to this bill will also
                                                        be deleted.
                                                    </p>
                                                </div>
                                                <div class="modal-footer">
                                                    <form method="POST" action="">
                                                        <input type="hidden" name="udhar_id"
                                                            value="<?php echo $udhar['id']; ?>">
                                                        <button type="submit" name="delete_udhar" class="btn btn-danger">
                                                            <i class="bi bi-trash"></i> Delete Forever
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
                    <div class="modal-dialog modal-lg modal-dialog-scrollable">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="table-responsive" style="max-height: 70vh; overflow: auto;">
                                    <table class="table table-hover" id="itemsSelectTable">
                                        <thead>
                                            <tr>
                                                <th width="5%">Pick</th>
                                                <th width="30%">Item</th>
                                                <th width="15%">HSN</th>
                                                <th width="15%">Price</th>
                                                <th width="15%">Unit</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($items as $itm): ?>
                                                <tr>
                                                    <td>
                                                        <input type="checkbox" class="form-check-input item-checkbox"
                                                            value="<?php echo (int) $itm['id']; ?>">
                                                    </td>
                                                    <td><?php echo htmlspecialchars($itm['item_name']); ?></td>
                                                    <td><?php echo htmlspecialchars($itm['hsn_code']); ?></td>
                                                    <td>₹<?php echo number_format($itm['price'], 2); ?></td>
                                                    <td><?php echo htmlspecialchars($itm['unit']); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="button" class="btn btn-primary" onclick="addSelectedItems()">Add selected
                                    items</button>
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
    <script src="assets/js/common.js"></script>
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