<?php
// File: smart-udhar-system/edit_bill.php
// Direct bill editing without revision tracking

require_once 'config/database.php';
requireLogin();

$conn = getDBConnection();
$udhar_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($udhar_id <= 0) {
    setMessage("Invalid bill ID", "danger");
    header('Location: udhar.php');
    exit();
}

// Handle bill update
if (isset($_POST['update_bill'])) {
    // Validate
    $errors = [];

    if (!isset($_POST['items']) || count($_POST['items']) == 0) {
        $errors[] = "Please add at least one item";
    }

    if (empty($errors)) {
        $conn->begin_transaction();

        try {
            // Get bill data
            $transaction_date = sanitizeInput($_POST['transaction_date']);
            $due_date = sanitizeInput($_POST['due_date']);
            $description = sanitizeInput($_POST['description']);
            $notes = sanitizeInput($_POST['notes']);
            $status = sanitizeInput($_POST['status']);
            $discount = 0.0;
            $discount_type = 'fixed';
            $round_off = 0.0;
            $category = sanitizeInput($_POST['category'] ?? '');

            // Calculate totals from items
            $total_amount = 0;
            $cgst_amount = 0;
            $sgst_amount = 0;
            $igst_amount = 0;

            foreach ($_POST['items'] as $item) {
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

            // Update udhar transaction
            $stmt = $conn->prepare("
                UPDATE udhar_transactions SET
                    transaction_date = ?,
                    due_date = ?,
                    total_amount = ?,
                    cgst_amount = ?,
                    sgst_amount = ?,
                    igst_amount = ?,
                    discount = ?,
                    discount_type = ?,
                    round_off = ?,
                    description = ?,
                    notes = ?,
                    status = ?,
                    updated_at = NOW()
                WHERE id = ?
            ");

            $stmt->bind_param(
                "ssdddddsssssii",
                $transaction_date,
                $due_date,
                $total_amount,
                $cgst_amount,
                $sgst_amount,
                $igst_amount,
                $discount,
                $discount_type,
                $round_off,
                $description,
                $notes,
                $status,
                $udhar_id
            );

            if (!$stmt->execute()) {
                throw new Exception("Error updating bill: " . $stmt->error);
            }
            $stmt->close();

            // Delete existing items
            $stmt = $conn->prepare("DELETE FROM udhar_items WHERE udhar_id = ?");
            $stmt->bind_param("i", $udhar_id);
            $stmt->execute();
            $stmt->close();

            // Insert updated items
            foreach ($_POST['items'] as $item) {
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

                $stmt = $conn->prepare("
                    INSERT INTO udhar_items (
                        udhar_id, item_id, item_name, hsn_code, quantity, unit_price,
                        cgst_rate, sgst_rate, igst_rate, cgst_amount, sgst_amount,
                        igst_amount, total_amount
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");

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
                    throw new Exception("Error updating item: " . $stmt->error);
                }
                $stmt->close();
            }

            $conn->commit();

            setMessage("Bill updated successfully!", "success");

        } catch (Exception $e) {
            $conn->rollback();
            setMessage("Error: " . $e->getMessage(), "danger");
        }
    } else {
        setMessage(implode("<br>", $errors), "danger");
    }
}

// Get bill details
$stmt = $conn->prepare("
    SELECT ut.*, c.name as customer_name, c.mobile as customer_mobile
    FROM udhar_transactions ut
    JOIN customers c ON ut.customer_id = c.id
    WHERE ut.id = ? AND c.user_id = ?
");
$stmt->bind_param("ii", $udhar_id, $_SESSION['user_id']);
$stmt->execute();
$udhar = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$udhar) {
    setMessage("Bill not found or you don't have permission to edit it", "danger");
    header('Location: udhar.php');
    exit();
}

// Get bill items
$stmt = $conn->prepare("SELECT * FROM udhar_items WHERE udhar_id = ?");
$stmt->bind_param("i", $udhar_id);
$stmt->execute();
$udhar_items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get customers for dropdown
$stmt = $conn->prepare("SELECT id, name, mobile FROM customers WHERE user_id = ? AND status = 'active' ORDER BY name");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$customers = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get items for dropdown
$stmt = $conn->prepare("SELECT id, item_name, item_code, hsn_code, price, cgst_rate, sgst_rate, igst_rate, unit FROM items WHERE user_id = ? AND status = 'active' ORDER BY item_name");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$page_title = "Edit Bill - " . htmlspecialchars($udhar['bill_no']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.iconify.design/iconify-icon/1.0.7/iconify-icon.min.js"></script>
    <link
        href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=Outfit:wght@200;300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        :root {
            --bg-airy: #f8fafc;
            --accent-indigo: #6366f1;
            --glass-white: rgba(255, 255, 255, 0.85);
            --glass-border: rgba(255, 255, 255, 0.3);
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: #f1f5f9;
            /* Softer, calming background */
        }

        h1,
        h2,
        h3,
        h4,
        .font-space {
            font-family: 'Space Grotesk', sans-serif;
        }

        .glass-card {
            background: #ffffff;
            border: 1px solid rgba(226, 232, 240, 0.8);
            border-radius: 24px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
            transition: all 0.3s ease;
        }

        .section-header-bar {
            background: #f8fafc;
            border-bottom: 1px solid #f1f5f9;
            padding: 16px 24px;
            border-radius: 24px 24px 0 0;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .accent-card-indigo {
            border-left: 4px solid rgba(99, 102, 241, 1);
        }

        .accent-card-amber {
            border-left: 4px solid #f59e0b;
        }

        .accent-card-emerald {
            border-left: 4px solid #10b981;
        }

        .accent-card-violet {
            border-left: 4px solid #8b5cf6;
        }

        .accent-card-slate {
            border-left: 4px solid #64748b;
        }

        .inner-field-card {
            background: #f8fafc;
            border: 1px solid #f1f5f9;
            border-radius: 16px;
            padding: 16px;
        }

        .resizable-table {
            table-layout: fixed;
            width: 100%;
        }

        .resizable-table th {
            position: relative;
            user-select: none;
        }

        .resizable-table th .resizer {
            position: absolute;
            top: 0;
            right: 0;
            width: 4px;
            height: 100%;
            cursor: col-resize;
            z-index: 20;
            border-right: 2px solid transparent;
            transition: all 0.2s;
        }

        .resizable-table th .resizer:hover,
        .resizable-table th .resizer.resizing {
            border-right: 2px solid #6366f1;
            background: rgba(99, 102, 241, 0.05);
        }

        .resizable-table th .resizer:hover {
            background: rgba(99, 102, 241, 0.12);
        }

        /* Visible Column Dividers */
        #itemsTable thead th:not(:last-child) {
            border-right: 1px solid #e2e8f0;
        }

        #itemsTable tbody td:not(:last-child) {
            border-right: 1px solid #f1f5f9;
        }

        .row-colored td {
            background-color: var(--row-bg);
        }

        .table-input {
            background: rgba(248, 250, 252, 0.5);
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 10px 14px;
            width: 100%;
            font-size: 0.85rem;
            font-weight: 600;
            color: #334155;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .table-input:hover {
            border-color: #cbd5e1;
            background: rgba(255, 255, 255, 0.8);
        }

        .table-input:focus {
            outline: none;
            border-color: #6366f1;
            background: white;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
            color: #0f172a;
        }

        /* Fixed Read-only styling */
        .table-input:read-only {
            background: #f1f5f9;
            border-color: #e2e8f0;
            color: #64748b;
            cursor: default;
        }

        .table-select {
            appearance: none;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%2394a3b8' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 0.75rem center;
            background-size: 1.25em 1.25em;
            padding-right: 2.5rem;
        }

        .price-input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .price-input-wrapper .currency-symbol {
            position: absolute;
            left: 14px;
            font-size: 0.8rem;
            color: #6366f1;
            font-weight: 800;
            pointer-events: none;
        }

        .price-input-wrapper input {
            padding-left: 28px !important;
        }

        #itemsTable thead th {
            vertical-align: middle;
            background: #f8fafc;
            border-bottom: 2px solid #f1f5f9;
            padding: 16px 12px;
        }

        #itemsTable tbody td {
            vertical-align: middle;
            padding: 12px 8px;
        }

        .item-row {
            transition: background-color 0.2s;
        }

        .item-row:hover {
            background-color: #f8fafc;
        }

        .total-display {
            font-family: 'Space Grotesk', sans-serif;
            font-weight: 800;
            font-size: 0.95rem;
            color: #1e293b;
            letter-spacing: -0.02em;
        }
    </style>
</head>

<body>
    <?php include 'includes/sidebar.php'; ?>

    <!-- Main Content -->
    <div id="mainContent" class="main-content min-h-screen relative z-10 px-4 py-8 md:px-10">
        <!-- Floating Toggle Button (visible when sidebar is closed) -->
        <button class="floating-toggle-btn" id="floatingToggle">
            <i class="bi bi-chevron-right"></i>
        </button>

        <div class="container-fluid">
            <div class="container-fluid">
                <header class="mb-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                    <div class="flex items-center gap-4">
                        <div class="flex flex-col">
                            <nav
                                class="flex text-[10px] items-center gap-1.5 font-black uppercase tracking-widest text-slate-400 mb-2">
                                <iconify-icon icon="solar:home-2-bold" class="text-xs"></iconify-icon>
                                <span>Smart Udhar</span>
                                <iconify-icon icon="solar:alt-arrow-right-bold" class="text-[8px]"></iconify-icon>
                                <span class="text-indigo-500">Edit Bill</span>
                            </nav>
                            <h1 class="text-4xl font-black text-slate-800 tracking-tighter flex items-center gap-3">
                                <iconify-icon icon="solar:pen-new-square-bold-duotone"
                                    class="text-indigo-500"></iconify-icon>
                                Edit Bill
                            </h1>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <a href="udhar.php"
                            class="bg-white hover:bg-slate-50 text-slate-600 px-6 py-3 rounded-2xl font-bold border border-slate-200 flex items-center gap-2 transition-all">
                            <iconify-icon icon="solar:arrow-left-bold" class="text-xl"></iconify-icon>
                            Back
                        </a>
                    </div>
                </header>

                <?php displayMessage(); ?>

                <div class="glass-card overflow-hidden">
                    <div class="px-8 py-6 border-b border-slate-100 flex justify-between items-center">
                        <div class="flex items-center gap-3">
                            <iconify-icon icon="solar:bill-list-bold-duotone"
                                class="text-xl text-slate-400"></iconify-icon>
                            <h4 class="text-lg font-black text-slate-800 tracking-tight mb-0">Bill No:
                                <?php echo htmlspecialchars($udhar['bill_no']); ?></h4>
                        </div>
                    </div>

                    <div class="p-6 md:p-8">
                        <div class="glass-card p-6 border-2 border-amber-100 mb-6">
                            <div class="flex items-start gap-3">
                                <iconify-icon icon="solar:danger-triangle-bold-duotone"
                                    class="text-2xl text-amber-500"></iconify-icon>
                                <div>
                                    <p class="text-sm font-black text-slate-800 mb-1">Important</p>
                                    <p class="text-sm font-bold text-slate-600 mb-0">
                                        Editing this bill will create a revision for audit purposes. Please provide a
                                        clear reason for the changes.
                                    </p>
                                </div>
                            </div>

                            <form method="POST" action="" id="editBillForm" class="space-y-6">
                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                    <div class="glass-card accent-card-indigo p-6">
                                        <div class="flex items-center gap-3 mb-4">
                                            <iconify-icon icon="solar:user-bold-duotone"
                                                class="text-indigo-500 text-xl"></iconify-icon>
                                            <h6
                                                class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-0">
                                                Customer</h6>
                                        </div>
                                        <div class="inner-field-card">
                                            <span class="text-sm font-black text-slate-800">
                                                <?php echo htmlspecialchars($udhar['customer_name']); ?>
                                            </span>
                                        </div>
                                        <div class="mt-3">
                                            <a href="customers.php?action=view&id=<?php echo $udhar['customer_id']; ?>"
                                                class="text-[10px] font-black text-indigo-500 uppercase tracking-widest hover:underline flex items-center gap-1">
                                                View Customer Profile
                                                <iconify-icon icon="solar:arrow-right-up-bold"
                                                    class="text-xs"></iconify-icon>
                                            </a>
                                        </div>
                                    </div>

                                    <div class="glass-card accent-card-indigo p-6">
                                        <div class="flex items-center gap-3 mb-4">
                                            <iconify-icon icon="solar:hashtag-bold-duotone"
                                                class="text-indigo-500 text-xl"></iconify-icon>
                                            <h6
                                                class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-0">
                                                Bill Number</h6>
                                        </div>
                                        <div class="inner-field-card">
                                            <span class="text-sm font-black text-slate-800"><?php echo htmlspecialchars($udhar['bill_no']); ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="glass-card accent-card-indigo overflow-hidden mt-8">
                                                <div class="section-header-bar">
                                                    <iconify-icon icon="solar:calendar-bold-duotone"
                                                        class="text-indigo-500 text-xl"></iconify-icon>
                                                    <h5 class="text-sm font-black text-slate-800 tracking-tight mb-0">
                                                        Timeline & Status</h5>
                                                </div>
                                                <div class="p-6">
                                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                                        <div>
                                                            <label for="transaction_date"
                                                                class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Bill
                                                                Date</label>
                                                            <input type="date" id="transaction_date"
                                                                name="transaction_date"
                                                                value="<?php echo $udhar['transaction_date']; ?>"
                                                                class="w-full border border-slate-200 rounded-2xl px-4 py-3 text-sm font-bold text-slate-700 bg-slate-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-200 transition-all" />
                                                        </div>
                                                        <div>
                                                            <label for="due_date"
                                                                class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Due
                                                                Date</label>
                                                            <input type="date" id="due_date" name="due_date"
                                                                value="<?php echo $udhar['due_date']; ?>"
                                                                class="w-full border border-slate-200 rounded-2xl px-4 py-3 text-sm font-bold text-slate-700 bg-slate-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-200 transition-all" />
                                                        </div>
                                                        <div>
                                                            <label for="status"
                                                                class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Status</label>
                                                            <select id="status" name="status"
                                                                class="w-full border border-slate-200 rounded-2xl px-4 py-3 text-sm font-bold text-slate-700 bg-slate-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-200 transition-all">
                                                                <option value="pending" <?php echo $udhar['status'] == 'pending' ? 'selected' : ''; ?>>
                                                                    Pending</option>
                                                                <option value="partially_paid" <?php echo $udhar['status'] == 'partially_paid' ? 'selected' : ''; ?>>Partially Paid</option>
                                                                <option value="paid" <?php echo $udhar['status'] == 'paid' ? 'selected' : ''; ?>>Paid</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                        </div>

                                        <!-- Billing Details (Category & Description) -->
                                        <div class="glass-card accent-card-violet overflow-hidden mt-8">
                                            <div class="section-header-bar">
                                                <iconify-icon icon="solar:folder-with-files-bold-duotone"
                                                    class="text-violet-500 text-xl"></iconify-icon>
                                                <h5 class="text-sm font-black text-slate-800 tracking-tight mb-0">
                                                    Classification & Description</h5>
                                            </div>
                                            <div class="p-6">
                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                                    <div>
                                                        <label for="category"
                                                            class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Category</label>
                                                        <select id="category" name="category"
                                                            class="w-full border border-slate-200 rounded-2xl px-4 py-3 text-sm font-bold text-slate-700 bg-slate-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-violet-200 transition-all">
                                                            <option value="">Select Category</option>
                                                            <option value="Fertilizers" <?php echo ($udhar['category'] ?? '') == 'Fertilizers' ? 'selected' : ''; ?>>
                                                                Fertilizers</option>
                                                            <option value="Seeds" <?php echo ($udhar['category'] ?? '') == 'Seeds' ? 'selected' : ''; ?>>
                                                                Seeds
                                                            </option>
                                                            <option value="Insecticides" <?php echo ($udhar['category'] ?? '') == 'Insecticides' ? 'selected' : ''; ?>>Insecticides</option>
                                                            <option value="Others" <?php echo ($udhar['category'] ?? '') == 'Others' ? 'selected' : ''; ?>>
                                                                Others
                                                            </option>
                                                        </select>
                                                    </div>
                                                    <div>
                                                        <label for="description"
                                                            class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Description</label>
                                                        <input type="text" id="description" name="description"
                                                            placeholder="Main purpose of this bill..."
                                                            value="<?php echo htmlspecialchars($udhar['description']); ?>"
                                                            class="w-full border border-slate-200 rounded-2xl px-4 py-3 text-sm font-bold text-slate-700 bg-slate-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-violet-200 transition-all" />
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Items Section -->
                                        <div class="glass-card accent-card-emerald overflow-hidden mt-8">
                                            <div class="section-header-bar justify-between">
                                                <div class="flex items-center gap-3">
                                                    <iconify-icon icon="solar:box-bold-duotone"
                                                        class="text-emerald-500 text-xl"></iconify-icon>
                                                    <h4 class="text-lg font-black text-slate-800 tracking-tight mb-0">
                                                        Items
                                                    </h4>
                                                        Bill Items</h4>
                                                </div>
                                                <div class="flex items-center gap-4">
                                                    <button type="button" onclick="addItemRow()"
                                                        class="bg-emerald-600 hover:bg-emerald-500 text-white px-4 py-2 rounded-2xl font-bold flex items-center gap-2 shadow-lg shadow-emerald-100 transition-all">
                                                        <iconify-icon icon="solar:add-circle-bold"
                                                            class="text-xl"></iconify-icon>
                                                        Add Item
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="p-4 overflow-x-auto bg-slate-50/30">
                                                <table id="itemsTable" class="w-full text-left text-sm resizable-table">
                                                    <thead>
                                                        <tr
                                                            class="text-[11px] font-black text-slate-500 uppercase tracking-[0.1em]">
                                                            <th class="px-6 rounded-tl-2xl" style="width: 280px;">
                                                                <div class="flex items-center gap-2">
                                                                    <iconify-icon
                                                                        icon="solar:box-minimalistic-bold-duotone"
                                                                        class="text-indigo-500 text-lg"></iconify-icon>
                                                                    Item Name
                                                                </div>
                                                                <div class="resizer"></div>
                                                            </th>
                                                            <th class="px-6" style="width: 130px;">
                                                                <div class="flex items-center gap-2">
                                                                    <iconify-icon icon="solar:qr-code-bold-duotone"
                                                                        class="text-indigo-500 text-lg"></iconify-icon>
                                                                    HSN Code
                                                                </div>
                                                                <div class="resizer"></div>
                                                            </th>
                                                            <th class="px-6 text-center" style="width: 110px;">
                                                                <div class="flex items-center gap-2 justify-center">
                                                                    Quantity
                                                                </div>
                                                                <div class="resizer"></div>
                                                            </th>
                                                            <th class="px-6 text-center" style="width: 100px;">
                                                                <div class="flex items-center gap-2 justify-center">
                                                                    Unit
                                                                </div>
                                                                <div class="resizer"></div>
                                                            </th>
                                                            <th class="px-6" style="width: 160px;">
                                                                <div class="flex items-center gap-2">
                                                                    <iconify-icon icon="solar:wad-of-money-bold-duotone"
                                                                        class="text-indigo-500 text-lg"></iconify-icon>
                                                                    Unit Price
                                                                </div>
                                                                <div class="resizer"></div>
                                                            </th>
                                                            <th class="px-6 text-right" style="width: 150px;">
                                                                <div class="flex items-center gap-2 justify-end">
                                                                    <iconify-icon
                                                                        icon="solar:calculator-minimalistic-bold-duotone"
                                                                        class="text-indigo-500 text-lg"></iconify-icon>
                                                                    Subtotal
                                                                </div>
                                                                <div class="resizer"></div>
                                                            </th>
                                                            <th class="px-4 rounded-tr-2xl" style="width: 60px;"></th>
                                                        </tr>
                                                    </thead>

                                                    <tbody id="itemsBody" class="divide-y divide-slate-50">
                                                        <!-- Items will be loaded here -->
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                            <div class="glass-card accent-card-indigo p-6 flex flex-col justify-center">
                                                <div class="flex items-center gap-2 mb-2">
                                                    <iconify-icon icon="solar:wad-of-money-bold-duotone" class="text-indigo-500"></iconify-icon>
                                                    <p class="text-[10px] font-black text-indigo-400 uppercase tracking-widest mb-0">
                                                        Grand Total</p>
                                                </div>
                                                <h3 class="text-4xl font-black text-slate-800 tracking-tighter mb-0">₹<span
                                                        id="grandTotal">0.00</span></h3>
                                                <div class="mt-4 w-12 h-1 bg-indigo-500 rounded-full"></div>
                                            </div>
 
                                            <div class="glass-card accent-card-slate overflow-hidden md:col-span-2">
                                                <div class="section-header-bar">
                                                    <iconify-icon icon="solar:notes-bold-duotone"
                                                        class="text-slate-500 text-xl"></iconify-icon>
                                                    <h5 class="text-sm font-black text-slate-800 tracking-tight mb-0">
                                                        Internal Notes & Remarks</h5>
                                                </div>
                                                <div class="p-6 bg-slate-50/30">
                                                    <textarea id="notes" name="notes" rows="2"
                                                        placeholder="Add any specific details for future reference..."
                                                        class="w-full border border-slate-200 rounded-2xl px-4 py-3 text-sm font-bold text-slate-700 bg-white focus:outline-none focus:ring-2 focus:ring-slate-200 transition-all font-bold min-h-[100px]"><?php echo htmlspecialchars($udhar['notes']); ?></textarea>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Action Buttons -->
                                        <div class="flex flex-col md:flex-row justify-between gap-3 mt-8">
                                            <a href="udhar.php"
                                                class="bg-white hover:bg-slate-50 text-slate-600 px-6 py-3 rounded-2xl font-bold border border-slate-200 flex items-center justify-center gap-2 transition-all">
                                                <iconify-icon icon="solar:arrow-left-bold"
                                                    class="text-xl"></iconify-icon>
                                                Cancel
                                            </a>
                                            <button type="button" onclick="confirmUpdateBill()"
                                                class="bg-indigo-600 hover:bg-indigo-500 text-white px-6 py-3 rounded-2xl font-bold flex items-center justify-center gap-2 shadow-lg shadow-indigo-200 transition-all">
                                                <iconify-icon icon="solar:check-circle-bold"
                                                    class="text-xl"></iconify-icon>
                                                Update Bill
                                            </button>
                                        </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let itemCounter = 0;
        const items = <?php echo json_encode($items); ?>;
        const existingItems = <?php echo json_encode($udhar_items); ?>;
        const UDHAR_ID = <?php echo (int) $udhar_id; ?>;

        function storageKey(suffix) {
            return `edit_bill_${UDHAR_ID}_${suffix}`;
        }

        function randomPastel() {
            const hue = Math.floor(Math.random() * 360);
            return `hsla(${hue}, 85%, 95%, 1)`;
        }

        function getRowColorMap() {
            try {
                return JSON.parse(localStorage.getItem(storageKey('rowColors')) || '{}');
            } catch (e) {
                return {};
            }
        }

        function setRowColorMap(map) {
            localStorage.setItem(storageKey('rowColors'), JSON.stringify(map));
        }

        function isRowColorEnabled() {
            return localStorage.getItem(storageKey('colorRows')) === '1';
        }

        function applyRowColor(row, rowIndex) {
            const map = getRowColorMap();
            if (!map[rowIndex]) {
                map[rowIndex] = randomPastel();
                setRowColorMap(map);
            }
            row.style.setProperty('--row-bg', map[rowIndex]);
            if (isRowColorEnabled()) {
                row.classList.add('row-colored');
            } else {
                row.classList.remove('row-colored');
            }
        }

        function toggleRowColors(enabled) {
            localStorage.setItem(storageKey('colorRows'), enabled ? '1' : '0');
            document.querySelectorAll('#itemsBody tr').forEach((row) => {
                if (enabled) {
                    row.classList.add('row-colored');
                } else {
                    row.classList.remove('row-colored');
                }
            });
        }

        function applyColumnWidths() {
            const table = document.getElementById('itemsTable');
            if (!table) return;
            let widths = null;
            try {
                widths = JSON.parse(localStorage.getItem(storageKey('colWidths')) || 'null');
            } catch (e) {
                widths = null;
            }
            if (!Array.isArray(widths)) return;
            const ths = table.querySelectorAll('thead th');
            widths.forEach((w, i) => {
                if (ths[i] && typeof w === 'number' && w > 40) {
                    ths[i].style.width = w + 'px';
                }
            });
        }

        function saveColumnWidths() {
            const table = document.getElementById('itemsTable');
            if (!table) return;
            const ths = Array.from(table.querySelectorAll('thead th'));
            const widths = ths.map((th) => th.getBoundingClientRect().width);
            localStorage.setItem(storageKey('colWidths'), JSON.stringify(widths));
        }

        function initResizableColumns() {
            const table = document.getElementById('itemsTable');
            if (!table) return;
            applyColumnWidths();

            const ths = Array.from(table.querySelectorAll('thead th'));
            ths.forEach((th, index) => {
                const resizer = th.querySelector('.resizer');
                if (!resizer) return;

                let startX = 0;
                let startWidth = 0;
                const minWidth = 60;

                const onMouseMove = (e) => {
                    const dx = e.clientX - startX;
                    const newWidth = Math.max(minWidth, startWidth + dx);
                    th.style.width = newWidth + 'px';
                    resizer.classList.add('resizing');
                };

                const onMouseUp = () => {
                    document.removeEventListener('mousemove', onMouseMove);
                    document.removeEventListener('mouseup', onMouseUp);
                    resizer.classList.remove('resizing');
                    saveColumnWidths();
                };

                resizer.addEventListener('mousedown', (e) => {
                    e.preventDefault();
                    startX = e.clientX;
                    startWidth = th.getBoundingClientRect().width;
                    document.addEventListener('mousemove', onMouseMove);
                    document.addEventListener('mouseup', onMouseUp);
                });
            });
        }

        // Load existing items on page load
        $(document).ready(function () {
            const toggle = document.getElementById('toggleRowColors');
            if (toggle) {
                toggle.checked = isRowColorEnabled();
                toggle.addEventListener('change', function () {
                    toggleRowColors(this.checked);
                });
            }
            initResizableColumns();

            existingItems.forEach(item => {
                addItemRow(item);
            });
            calculateTotals();
        });

        function confirmUpdateBill() {
            // Create confirmation modal
            const modalHtml = `
                <div id="confirmUpdateModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center" style="display: none;">
                    <div class="bg-white rounded-2xl p-8 max-w-md mx-4 shadow-2xl transform transition-all">
                        <div class="text-center">
                            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100 mb-4">
                                <iconify-icon icon="solar:danger-triangle-bold-duotone" class="text-3xl text-red-600"></iconify-icon>
                            </div>
                            <h3 class="text-2xl font-black text-slate-800 mb-4">Confirm Bill Update</h3>
                            <div class="text-slate-600 mb-6 space-y-3">
                                <p class="font-semibold text-lg">⚠️ Important Warning</p>
                                <p>Once you update this bill, the original version will be permanently replaced and you will NOT be able to recover the previous bill details.</p>
                                <p class="text-sm text-slate-500">This action will create a revision for audit purposes, but the original bill data cannot be restored.</p>
                            </div>
                            <div class="flex gap-3 justify-center">
                                <button type="button" onclick="closeConfirmModal()" 
                                    class="px-6 py-3 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-2xl font-bold transition-all">
                                    Cancel
                                </button>
                                <button type="button" onclick="proceedWithUpdate()" 
                                    class="px-6 py-3 bg-red-600 hover:bg-red-700 text-white rounded-2xl font-bold transition-all">
                                    Yes, Update Bill
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            // Add modal to body
            document.body.insertAdjacentHTML('beforeend', modalHtml);
            
            // Show modal
            document.getElementById('confirmUpdateModal').style.display = 'flex';
        }

        function closeConfirmModal() {
            const modal = document.getElementById('confirmUpdateModal');
            if (modal) {
                modal.remove();
            }
        }

        function proceedWithUpdate() {
            // Client-side validation: Check if there are any items
            const itemRows = document.querySelectorAll('#itemsBody tr');
            if (itemRows.length === 0) {
                displayClientErrorMessage('Please add at least one item to the bill.');
                return;
            }
            
            // Clear any existing client-side error messages before proceeding
            const existingError = document.querySelector('.bg-red-100.border-red-red-400');
            if (existingError) {
                existingError.remove();
            }
            
            // Add hidden input to indicate confirmation
            const form = document.getElementById('editBillForm');
            const confirmedInput = document.createElement('input');
            confirmedInput.type = 'hidden';
            confirmedInput.name = 'update_bill';
            confirmedInput.value = 'confirmed';
            form.appendChild(confirmedInput);
            
            // Close modal and submit form
            closeConfirmModal();
            form.submit();
        }

        function addItemRow(itemData = null) {
            // Remove any existing client-side error message
            const existingError = document.querySelector('.bg-red-100.border-red-red-400');
            if (existingError) {
                existingError.remove();
            }
            
            const tbody = document.getElementById('itemsBody');
            const row = document.createElement('tr');
            row.id = 'itemRow_' + itemCounter;
            row.className = 'item-row';
            
            const defaultItem = itemData ? {
                item_id: itemData.item_id,
                item_name: itemData.item_name,
                hsn_code: itemData.hsn_code,
                unit_price: itemData.unit_price,
                quantity: itemData.quantity,
                cgst_rate: itemData.cgst_rate,
                sgst_rate: itemData.sgst_rate,
                igst_rate: itemData.igst_rate,
                unit: itemData.unit
            } : {
                item_id: '',
                item_name: '',
                hsn_code: '',
                unit_price: '0.00',
                quantity: '1.00',
                cgst_rate: '2.5',
                sgst_rate: '2.5',
                igst_rate: '0.00',
                unit: 'PCS'
            };
            
            row.innerHTML = `
                <td class="px-3">
                    <select class="table-input table-select item-select" name="items[${itemCounter}][item_id]" 
                            onchange="updateItemDetails(${itemCounter})">
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
                                    ${item.id == defaultItem.item_id ? 'selected' : ''}>
                                ${item.item_name}
                            </option>
                        `).join('')}
                    </select>
                    <input type="hidden" name="items[${itemCounter}][item_name]" value="${defaultItem.item_name}" class="item-name">
                </td>
                <td class="px-3">
                    <input type="text" class="table-input item-hsn font-mono text-xs" 
                           name="items[${itemCounter}][hsn_code]" value="${defaultItem.hsn_code}" readonly tabindex="-1">
                </td>
                <td class="px-3">
                    <input type="number" class="table-input quantity text-center" 
                           name="items[${itemCounter}][quantity]" value="${defaultItem.quantity}" 
                           step="0.01" min="0.01" onchange="calculateItemTotal(${itemCounter})" required>
                </td>
                <td class="px-3">
                    <input type="text" class="table-input text-center font-bold text-[10px] uppercase tracking-widest" 
                           name="items[${itemCounter}][unit]" value="${defaultItem.unit}" readonly tabindex="-1">
                </td>
                <td class="px-3">
                    <div class="price-input-wrapper">
                        <span class="currency-symbol">₹</span>
                        <input type="number" class="table-input price" 
                               name="items[${itemCounter}][price]" value="${defaultItem.unit_price}" 
                               step="0.01" min="0.01" onchange="calculateItemTotal(${itemCounter})">
                    </div>
                </td>
                <td class="hidden">
                    <input type="hidden" class="cgst-rate" name="items[${itemCounter}][cgst_rate]" value="${defaultItem.cgst_rate}" />
                    <input type="hidden" class="sgst-rate" name="items[${itemCounter}][sgst_rate]" value="${defaultItem.sgst_rate}" />
                    <input type="hidden" class="igst-rate" name="items[${itemCounter}][igst_rate]" value="${defaultItem.igst_rate}" />
                </td>
                <td class="px-6 text-right">
                    <span class="total-display">₹<span class="item-total">0.00</span></span>
                </td>
                <td class="px-4 text-center">
                    <button type="button" class="w-10 h-10 rounded-2xl flex items-center justify-center text-slate-300 hover:text-rose-500 hover:bg-rose-50 hover:shadow-sm transition-all" 
                            onclick="removeItemRow(${itemCounter})" title="Remove Item">
                        <iconify-icon icon="solar:trash-bin-trash-bold-duotone" class="text-2xl"></iconify-icon>
                    </button>
                </td>
            `;

            tbody.appendChild(row);
            applyRowColor(row, itemCounter);
            itemCounter++;
            calculateItemTotal(itemCounter - 1);
        }

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
            }

            calculateItemTotal(rowIndex);
        }

        function calculateItemTotal(rowIndex) {
            const row = document.getElementById('itemRow_' + rowIndex);
            if (!row) return;

            const qty = parseFloat(row.querySelector('.quantity').value) || 0;
            const price = parseFloat(row.querySelector('.price').value) || 0;
            const cgst = parseFloat(row.querySelector('.cgst-rate').value) || 0;
            const sgst = parseFloat(row.querySelector('.sgst-rate').value) || 0;
            const igst = parseFloat(row.querySelector('.igst-rate').value) || 0;

            const subtotal = qty * price;
            let tax = 0;

            if (igst > 0) {
                tax = (subtotal * igst) / 100;
            } else {
                tax = (subtotal * (cgst + sgst)) / 100;
            }

            const total = subtotal + tax;
            row.querySelector('.item-total').textContent = total.toFixed(2);

            calculateTotals();
        }

        function removeItemRow(rowIndex) {
            const row = document.getElementById('itemRow_' + rowIndex);
            if (row) {
                row.remove();
                calculateTotals();
            }
        }

        function calculateTotals() {
            let subtotal = 0;
            let totalTax = 0;

            document.querySelectorAll('#itemsBody tr').forEach(row => {
                const qty = parseFloat(row.querySelector('.quantity').value) || 0;
                const price = parseFloat(row.querySelector('.price').value) || 0;
                const cgst = parseFloat(row.querySelector('.cgst-rate').value) || 0;
                const sgst = parseFloat(row.querySelector('.sgst-rate').value) || 0;
                const igst = parseFloat(row.querySelector('.igst-rate').value) || 0;

                const itemSubtotal = qty * price;
                subtotal += itemSubtotal;

                if (igst > 0) {
                    totalTax += (itemSubtotal * igst) / 100;
                } else {
                    totalTax += (itemSubtotal * (cgst + sgst)) / 100;
                }
            });

            const grandTotal = subtotal + totalTax;
            document.getElementById('grandTotal').textContent = grandTotal.toFixed(2);
        }
    </script>
</body>

</html>