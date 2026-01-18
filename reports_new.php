<?php
// File: smart-udhar-system/reports.php
 
require_once 'config/database.php';
requireLogin();
 
$user = getCurrentUser();
$conn = getDBConnection();
 
$bill = null;
$error = '';
 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bill_no'])) {
    $bill_no = sanitizeInput($_POST['bill_no']);
    if (!empty($bill_no)) {
        $stmt = $conn->prepare("
            SELECT u.*, c.name as customer_name, c.mobile as customer_mobile, c.address as customer_address
            FROM udhar u
            JOIN customers c ON u.customer_id = c.id
            WHERE u.bill_no = ? AND u.user_id = ?
        ");
        $stmt->bind_param("si", $bill_no, $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $bill = $result->fetch_assoc();
        $stmt->close();
 
        if (!$bill) {
            $error = 'Bill not found.';
        } else {
            // Fetch items for this bill
            $stmt = $conn->prepare("
                SELECT ui.*, i.item_name, i.hsn_code, i.unit
                FROM udhar_items ui
                JOIN items i ON ui.item_id = i.id
                WHERE ui.udhar_id = ?
                ORDER BY ui.id
            ");
            $stmt->bind_param("i", $bill['id']);
            $stmt->execute();
            $bill_items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            $bill['items'] = $bill_items;
        }
    } else {
        $error = 'Please enter a bill number.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
 
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bill Lookup - Smart Udhar System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
 
<body>
    <?php include 'includes/sidebar.php'; ?>
 
    <!-- Main Content -->
    <div class="main-content">
        <!-- Floating Toggle Button (visible when sidebar is closed) -->
        <button class="floating-toggle-btn" id="floatingToggle">
            <i class="bi bi-chevron-right"></i>
        </button>
 
        <!-- Page Content -->
        <div class="container-fluid p-4">
            <!-- Page Header -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <h2><i class="bi bi-receipt"></i> Bill Lookup</h2>
                    <p class="text-muted">Find and print any bill by its number</p>
                </div>
            </div>
 
            <!-- Bill Lookup Form -->
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <form method="POST" action="">
                                <div class="mb-3">
                                    <label for="bill_no" class="form-label">Enter Bill Number</label>
                                    <input type="text" class="form-control form-control-lg" id="bill_no" name="bill_no" 
                                           placeholder="e.g., BILL-001" value="<?php echo isset($_POST['bill_no']) ? htmlspecialchars($_POST['bill_no']) : ''; ?>" required>
                                </div>
                                <button type="submit" class="btn btn-primary btn-lg w-100">
                                    <i class="bi bi-search"></i> Find Bill
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
 
            <?php if ($error): ?>
                <div class="row justify-content-center mt-4">
                    <div class="col-md-6">
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle"></i> <?php echo $error; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
 
            <?php if ($bill): ?>
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><i class="bi bi-receipt"></i> Bill Details</h5>
                                <div>
                                    <a href="print_bill_tax_invoice.php?id=<?php echo $bill['id']; ?>" target="_blank" class="btn btn-light btn-sm">
                                        <i class="bi bi-printer"></i> Print Tax Invoice
                                    </a>
                                </div>
                            </div>
                            <div class="card-body">
                                <!-- Bill Header -->
                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <h6>Bill Information</h6>
                                        <p><strong>Bill No:</strong> <?php echo htmlspecialchars($bill['bill_no']); ?></p>
                                        <p><strong>Date:</strong> <?php echo date('d M Y', strtotime($bill['transaction_date'])); ?></p>
                                        <p><strong>Due Date:</strong> <?php echo $bill['due_date'] ? date('d M Y', strtotime($bill['due_date'])) : 'Not specified'; ?></p>
                                        <p><strong>Category:</strong> <?php echo htmlspecialchars($bill['category']); ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <h6>Customer Information</h6>
                                        <p><strong>Name:</strong> <?php echo htmlspecialchars($bill['customer_name']); ?></p>
                                        <p><strong>Mobile:</strong> <?php echo htmlspecialchars($bill['customer_mobile']); ?></p>
                                        <p><strong>Address:</strong> <?php echo htmlspecialchars($bill['customer_address'] ?: 'Not added'); ?></p>
                                    </div>
                                </div>
 
                                <!-- Items Table -->
                                <h6>Items</h6>
                                <div class="table-responsive mb-4">
                                    <table class="table table-bordered">
                                        <thead class="table-light">
                                            <tr>
                                                <th>#</th>
                                                <th>Item Name</th>
                                                <th>HSN</th>
                                                <th class="text-end">Qty</th>
                                                <th class="text-end">Price</th>
                                                <th class="text-end">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($bill['items'] as $index => $item): ?>
                                                <tr>
                                                    <td><?php echo $index + 1; ?></td>
                                                    <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                                                    <td><?php echo htmlspecialchars($item['hsn_code']); ?></td>
                                                    <td class="text-end"><?php echo number_format($item['quantity'], 2); ?></td>
                                                    <td class="text-end">₹<?php echo number_format($item['unit_price'], 2); ?></td>
                                                    <td class="text-end">₹<?php echo number_format($item['total_amount'], 2); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
 
                                <!-- Totals -->
                                <div class="row">
                                    <div class="col-md-6">
                                        <?php if (!empty($bill['notes'])): ?>
                                            <h6>Notes</h6>
                                            <p><?php echo htmlspecialchars($bill['notes']); ?></p>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="table-responsive">
                                            <table class="table table-sm">
                                                <tr>
                                                    <td><strong>Subtotal:</strong></td>
                                                    <td class="text-end">₹<?php echo number_format($bill['amount'], 2); ?></td>
                                                </tr>
                                                <?php if ($bill['discount'] > 0): ?>
                                                    <tr>
                                                        <td><strong>Discount:</strong></td>
                                                        <td class="text-end">-₹<?php echo number_format($bill['discount'], 2); ?></td>
                                                    </tr>
                                                <?php endif; ?>
                                                <?php if ($bill['transportation_charge'] > 0): ?>
                                                    <tr>
                                                        <td><strong>Transportation:</strong></td>
                                                        <td class="text-end">+₹<?php echo number_format($bill['transportation_charge'], 2); ?></td>
                                                    </tr>
                                                <?php endif; ?>
                                                <?php if ($bill['round_off'] != 0): ?>
                                                    <tr>
                                                        <td><strong>Round Off:</strong></td>
                                                        <td class="text-end"><?php echo $bill['round_off'] > 0 ? '+' : ''; ?>₹<?php echo number_format($bill['round_off'], 2); ?></td>
                                                    </tr>
                                                <?php endif; ?>
                                                <tr class="table-primary">
                                                    <td><strong>Grand Total:</strong></td>
                                                    <td class="text-end"><strong>₹<?php echo number_format($bill['grand_total'], 2); ?></strong></td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
 
                                <!-- Action Buttons -->
                                <div class="text-center mt-4">
                                    <a href="print_bill_tax_invoice.php?id=<?php echo $bill['id']; ?>" target="_blank" class="btn btn-primary btn-lg">
                                        <i class="bi bi-printer"></i> Print Tax Invoice
                                    </a>
                                    <a href="reports.php" class="btn btn-secondary btn-lg ms-2">
                                        <i class="bi bi-arrow-left"></i> New Search
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
 
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="assets/js/sidebar.js"></script>
    <script>
        // Auto-focus bill number input on page load
        document.addEventListener('DOMContentLoaded', function() {
            const billNoInput = document.getElementById('bill_no');
            if (billNoInput) {
                billNoInput.focus();
                billNoInput.select();
            }
        });
    </script>
</body>
 
</html>