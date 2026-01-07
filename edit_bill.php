<?php
// File: smart-udhar-system/edit_bill.php
// Comprehensive bill editing with revision tracking

require_once 'config/database.php';
requireLogin();

$conn = getDBConnection();
$udhar_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($udhar_id <= 0) {
    setMessage("Invalid bill ID", "danger");
    header('Location: udhar.php');
    exit();
}

// Function to create revision before editing
function createBillRevision($conn, $udhar_id, $change_reason = '')
{
    // Get current bill data
    $stmt = $conn->prepare("
        SELECT ut.*, 
               (SELECT JSON_ARRAYAGG(
                   JSON_OBJECT(
                       'item_id', ui.item_id,
                       'item_name', ui.item_name,
                       'hsn_code', ui.hsn_code,
                       'quantity', ui.quantity,
                       'unit_price', ui.unit_price,
                       'cgst_rate', ui.cgst_rate,
                       'sgst_rate', ui.sgst_rate,
                       'igst_rate', ui.igst_rate,
                       'cgst_amount', ui.cgst_amount,
                       'sgst_amount', ui.sgst_amount,
                       'igst_amount', ui.igst_amount,
                       'total_amount', ui.total_amount
                   )
               ) FROM udhar_items ui WHERE ui.udhar_id = ut.id) as items_json
        FROM udhar_transactions ut
        WHERE ut.id = ?
    ");
    $stmt->bind_param("i", $udhar_id);
    $stmt->execute();
    $bill = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$bill)
        return false;

    // Get next revision number
    $revision_num = ($bill['revision_number'] ?? 0) + 1;

    // Insert revision
    $stmt = $conn->prepare("
        INSERT INTO bill_revisions (
            udhar_id, revision_number, user_id, customer_id, bill_no,
            transaction_date, due_date, total_amount, cgst_amount, sgst_amount,
            igst_amount, discount, discount_type, round_off, grand_total,
            description, notes, status, category, items_data, change_reason, changed_by
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $grand_total = $bill['total_amount'] + $bill['cgst_amount'] + $bill['sgst_amount'] +
        $bill['igst_amount'] - $bill['discount'] + $bill['round_off'];

    $stmt->bind_param(
        "iiissssdddddssdssssssi",
        $udhar_id,
        $revision_num,
        $bill['user_id'],
        $bill['customer_id'],
        $bill['bill_no'],
        $bill['transaction_date'],
        $bill['due_date'],
        $bill['total_amount'],
        $bill['cgst_amount'],
        $bill['sgst_amount'],
        $bill['igst_amount'],
        $bill['discount'],
        $bill['discount_type'],
        $bill['round_off'],
        $grand_total,
        $bill['description'],
        $bill['notes'],
        $bill['status'],
        $bill['category'],
        $bill['items_json'],
        $change_reason,
        $_SESSION['user_id']
    );

    return $stmt->execute();
}

// Handle bill update
if (isset($_POST['update_bill'])) {
    $change_reason = sanitizeInput($_POST['change_reason']);

    // Validate
    $errors = [];

    if (empty($change_reason)) {
        $errors[] = "Please provide a reason for editing this bill";
    }

    if (!isset($_POST['items']) || count($_POST['items']) == 0) {
        $errors[] = "Please add at least one item";
    }

    if (empty($errors)) {
        $conn->begin_transaction();

        try {
            // Create revision before updating
            if (!createBillRevision($conn, $udhar_id, $change_reason)) {
                throw new Exception("Failed to create bill revision");
            }

            // Get bill data
            $transaction_date = sanitizeInput($_POST['transaction_date']);
            $due_date = sanitizeInput($_POST['due_date']);
            $description = sanitizeInput($_POST['description']);
            $notes = sanitizeInput($_POST['notes']);
            $status = sanitizeInput($_POST['status']);
            $discount = floatval($_POST['discount'] ?? 0);
            $discount_type = sanitizeInput($_POST['discount_type'] ?? 'fixed');
            $round_off = floatval($_POST['round_off'] ?? 0);
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
                    category = ?,
                    revision_number = revision_number + 1,
                    last_edited_by = ?,
                    last_edited_at = NOW(),
                    updated_at = NOW()
                WHERE id = ?
            ");

            $stmt->bind_param(
                "ssdddddssssssii",
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
                $category,
                $_SESSION['user_id'],
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

            setMessage("Bill updated successfully! Revision created for audit trail.", "success");
            header("Location: udhar.php?action=view&id=$udhar_id");
            exit();

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

// Get revision history
$stmt = $conn->prepare("
    SELECT br.*, u.username as changed_by_name
    FROM bill_revisions br
    LEFT JOIN users u ON br.changed_by = u.id
    WHERE br.udhar_id = ?
    ORDER BY br.revision_number DESC
");
$stmt->bind_param("i", $udhar_id);
$stmt->execute();
$revisions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$page_title = "Edit Bill - " . htmlspecialchars($udhar['bill_no']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2c3e50;
            --success-color: #27ae60;
            --warning-color: #f39c12;
            --danger-color: #e74c3c;
        }

        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .edit-container {
            max-width: 1400px;
            margin: 20px auto;
            padding: 20px;
        }

        .bill-header {
            background: linear-gradient(135deg, var(--warning-color), #e67e22);
            color: white;
            padding: 20px;
            border-radius: 10px 10px 0 0;
            margin-bottom: 0;
        }

        .bill-card {
            background: white;
            border-radius: 0 0 10px 10px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            padding: 30px;
        }

        .alert-warning {
            border-left: 4px solid var(--warning-color);
        }

        .items-table {
            margin-top: 20px;
        }

        .items-table th {
            background-color: #f8f9fa;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .revision-badge {
            background: #3498db;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.85rem;
            display: inline-block;
        }

        .revision-history {
            margin-top: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
        }

        .revision-item {
            padding: 15px;
            background: white;
            margin-bottom: 10px;
            border-radius: 8px;
            border-left: 4px solid #3498db;
        }

        .btn-add-item {
            background: linear-gradient(135deg, var(--success-color), #219653);
            color: white;
            border: none;
        }

        .btn-add-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(39, 174, 96, 0.3);
        }
    </style>
</head>

<body>
    <?php include 'includes/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Floating Toggle Button (visible when sidebar is closed) -->
        <button class="floating-toggle-btn" id="floatingToggle">
            <i class="bi bi-chevron-right"></i>
        </button>

        <div class="edit-container">
            <div class="bill-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3><i class="bi bi-pencil-square"></i> Edit Bill</h3>
                        <p class="mb-0">Bill No: <?php echo htmlspecialchars($udhar['bill_no']); ?></p>
                        <?php if (isset($udhar['revision_number']) && $udhar['revision_number'] > 1): ?>
                            <span class="revision-badge">Revision #<?php echo $udhar['revision_number']; ?></span>
                        <?php endif; ?>
                    </div>
                    <a href="udhar.php" class="btn btn-light">
                        <i class="bi bi-x-circle"></i> Cancel
                    </a>
                </div>
            </div>

            <div class="bill-card">
                <?php displayMessage(); ?>

                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle"></i>
                    <strong>Important:</strong> Editing this bill will create a revision for audit purposes.
                    The original bill data will be preserved in the revision history. Please provide a clear reason for
                    the
                    changes.
                </div>

                <form method="POST" action="" id="editBillForm">
                    <!-- Change Reason -->
                    <div class="mb-4">
                        <label for="change_reason" class="form-label">
                            <i class="bi bi-chat-left-text"></i> Reason for Editing *
                        </label>
                        <textarea class="form-control" id="change_reason" name="change_reason" rows="2"
                            placeholder="E.g., Correcting item quantity, Updating price, Customer requested changes..."
                            required></textarea>
                        <small class="text-muted">This will be logged in the revision history</small>
                    </div>

                    <div class="row">
                        <!-- Customer Info (Read-only) -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><i class="bi bi-person"></i> Customer</label>
                            <input type="text" class="form-control"
                                value="<?php echo htmlspecialchars($udhar['customer_name']); ?>" readonly>
                            <small class="text-muted">Customer cannot be changed after bill creation</small>
                            <div class="mt-2">
                                <a href="customers.php?action=view&id=<?php echo $udhar['customer_id']; ?>"
                                    class="btn btn-outline-info btn-sm">
                                    <i class="bi bi-person-circle"></i> View Customer Profile
                                </a>
                            </div>
                        </div>

                        <!-- Bill Number (Read-only) -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><i class="bi bi-receipt"></i> Bill Number</label>
                            <input type="text" class="form-control"
                                value="<?php echo htmlspecialchars($udhar['bill_no']); ?>" readonly>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Transaction Date -->
                        <div class="col-md-4 mb-3">
                            <label for="transaction_date" class="form-label"><i class="bi bi-calendar"></i> Bill Date
                                *</label>
                            <input type="date" class="form-control" id="transaction_date" name="transaction_date"
                                value="<?php echo $udhar['transaction_date']; ?>" required>
                        </div>

                        <!-- Due Date -->
                        <div class="col-md-4 mb-3">
                            <label for="due_date" class="form-label"><i class="bi bi-calendar-check"></i> Due
                                Date</label>
                            <input type="date" class="form-control" id="due_date" name="due_date"
                                value="<?php echo $udhar['due_date']; ?>">
                        </div>

                        <!-- Status -->
                        <div class="col-md-4 mb-3">
                            <label for="status" class="form-label"><i class="bi bi-flag"></i> Status *</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="pending" <?php echo $udhar['status'] == 'pending' ? 'selected' : ''; ?>>
                                    Pending
                                </option>
                                <option value="partially_paid" <?php echo $udhar['status'] == 'partially_paid' ? 'selected' : ''; ?>>Partially Paid</option>
                                <option value="paid" <?php echo $udhar['status'] == 'paid' ? 'selected' : ''; ?>>Paid
                                </option>
                            </select>
                        </div>
                    </div>

                    <!-- Category -->
                    <div class="mb-3">
                        <label for="category" class="form-label"><i class="bi bi-tags"></i> Category</label>
                        <select class="form-select" id="category" name="category">
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

                    <!-- Description -->
                    <div class="mb-3">
                        <label for="description" class="form-label"><i class="bi bi-file-text"></i> Description</label>
                        <input type="text" class="form-control" id="description" name="description"
                            value="<?php echo htmlspecialchars($udhar['description']); ?>">
                    </div>

                    <!-- Items Section -->
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5><i class="bi bi-box-seam"></i> Bill Items</h5>
                            <button type="button" class="btn btn-add-item" onclick="addItemRow()">
                                <i class="bi bi-plus-circle"></i> Add Item
                            </button>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered items-table">
                                <thead>
                                    <tr>
                                        <th width="25%">Item</th>
                                        <th width="12%">HSN</th>
                                        <th width="10%">Qty</th>
                                        <th width="8%">Unit</th>
                                        <th width="12%">Price</th>
                                        <th width="18%">GST Rates</th>
                                        <th width="12%">Total</th>
                                        <th width="3%"></th>
                                    </tr>
                                </thead>
                                <tbody id="itemsBody">
                                    <!-- Items will be loaded here -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Discount and Round Off -->
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="discount" class="form-label"><i class="bi bi-percent"></i> Discount</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="discount" name="discount"
                                    value="<?php echo $udhar['discount']; ?>" step="0.01" min="0"
                                    onchange="calculateTotals()">
                                <select class="form-select" name="discount_type" style="max-width: 120px;"
                                    onchange="calculateTotals()">
                                    <option value="fixed" <?php echo $udhar['discount_type'] == 'fixed' ? 'selected' : ''; ?>>
                                        Fixed (₹)</option>
                                    <option value="percentage" <?php echo $udhar['discount_type'] == 'percentage' ? 'selected' : ''; ?>>Percentage (%)</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="round_off" class="form-label"><i class="bi bi-calculator"></i> Round Off</label>
                            <input type="number" class="form-control" id="round_off" name="round_off"
                                value="<?php echo $udhar['round_off']; ?>" step="0.01" onchange="calculateTotals()">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label"><i class="bi bi-cash-stack"></i> Grand Total</label>
                            <div class="form-control bg-light" style="font-size: 1.2rem; font-weight: bold;">
                                ₹<span id="grandTotal">0.00</span>
                            </div>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="mb-3">
                        <label for="notes" class="form-label"><i class="bi bi-sticky"></i> Notes</label>
                        <textarea class="form-control" id="notes" name="notes"
                            rows="2"><?php echo htmlspecialchars($udhar['notes']); ?></textarea>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="d-flex justify-content-between mt-4">
                        <a href="udhar.php" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle"></i> Cancel
                        </a>
                        <button type="submit" name="update_bill" class="btn btn-warning btn-lg">
                            <i class="bi bi-check-circle"></i> Update Bill & Create Revision
                        </button>
                    </div>
                </form>

                <!-- Revision History -->
                <?php if (!empty($revisions)): ?>
                    <div class="revision-history">
                        <h5><i class="bi bi-clock-history"></i> Revision History</h5>
                        <p class="text-muted">This bill has been edited <?php echo count($revisions); ?> time(s)</p>

                        <?php foreach ($revisions as $rev): ?>
                            <div class="revision-item">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <strong>Revision #<?php echo $rev['revision_number']; ?></strong>
                                        <span class="text-muted"> -
                                            <?php echo date('d M Y, h:i A', strtotime($rev['changed_at'])); ?></span>
                                    </div>
                                    <span class="badge bg-info">₹<?php echo number_format($rev['grand_total'], 2); ?></span>
                                </div>
                                <div class="mt-2">
                                    <small class="text-muted">
                                        <i class="bi bi-person"></i> By:
                                        <?php echo htmlspecialchars($rev['changed_by_name'] ?? 'Unknown'); ?>
                                    </small>
                                </div>
                                <?php if (!empty($rev['change_reason'])): ?>
                                    <div class="mt-2">
                                        <small><strong>Reason:</strong>
                                            <?php echo htmlspecialchars($rev['change_reason']); ?></small>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let itemCounter = 0;
        const items = <?php echo json_encode($items); ?>;
        const existingItems = <?php echo json_encode($udhar_items); ?>;

        // Load existing items on page load
        $(document).ready(function () {
            existingItems.forEach(item => {
                addItemRow(item);
            });
            calculateTotals();
        });

        function addItemRow(itemData = null) {
            const tbody = document.getElementById('itemsBody');
            const row = document.createElement('tr');
            row.id = 'itemRow_' + itemCounter;

            const defaultItem = itemData || {
                id: '',
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
                <td>
                    <select class="form-select form-select-sm item-select" name="items[${itemCounter}][item_id]" 
                            onchange="updateItemDetails(${itemCounter})" required>
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
                <td>
                    <input type="text" class="form-control form-control-sm item-hsn" 
                           name="items[${itemCounter}][hsn_code]" value="${defaultItem.hsn_code}" readonly>
                </td>
                <td>
                    <input type="number" class="form-control form-control-sm quantity" 
                           name="items[${itemCounter}][quantity]" value="${defaultItem.quantity}" 
                           step="0.01" min="0.01" onchange="calculateItemTotal(${itemCounter})" required>
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm text-center" 
                           name="items[${itemCounter}][unit]" value="${defaultItem.unit}" readonly>
                </td>
                <td>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text">₹</span>
                        <input type="number" class="form-control price" 
                               name="items[${itemCounter}][price]" value="${defaultItem.unit_price}" 
                               step="0.01" min="0.01" onchange="calculateItemTotal(${itemCounter})" required>
                    </div>
                </td>
                <td>
                    <div class="row g-1">
                        <div class="col-4">
                            <input type="number" class="form-control form-control-sm cgst-rate" 
                                   name="items[${itemCounter}][cgst_rate]" value="${defaultItem.cgst_rate}" 
                                   step="0.01" min="0" max="100" placeholder="C" 
                                   onchange="calculateItemTotal(${itemCounter})">
                        </div>
                        <div class="col-4">
                            <input type="number" class="form-control form-control-sm sgst-rate" 
                                   name="items[${itemCounter}][sgst_rate]" value="${defaultItem.sgst_rate}" 
                                   step="0.01" min="0" max="100" placeholder="S" 
                                   onchange="calculateItemTotal(${itemCounter})">
                        </div>
                        <div class="col-4">
                            <input type="number" class="form-control form-control-sm igst-rate" 
                                   name="items[${itemCounter}][igst_rate]" value="${defaultItem.igst_rate}" 
                                   step="0.01" min="0" max="100" placeholder="I" 
                                   onchange="calculateItemTotal(${itemCounter})">
                        </div>
                    </div>
                </td>
                <td class="text-end fw-bold">₹<span class="item-total">0.00</span></td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-outline-danger border-0" 
                            onclick="removeItemRow(${itemCounter})">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            `;

            tbody.appendChild(row);
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

            const discount = parseFloat(document.getElementById('discount').value) || 0;
            const discountType = document.querySelector('select[name="discount_type"]').value;
            const roundOff = parseFloat(document.getElementById('round_off').value) || 0;

            let discountAmount = 0;
            if (discountType === 'percentage') {
                discountAmount = (subtotal * discount) / 100;
            } else {
                discountAmount = discount;
            }

            const grandTotal = subtotal + totalTax - discountAmount + roundOff;
            document.getElementById('grandTotal').textContent = grandTotal.toFixed(2);
        }
    </script>
</body>

</html>