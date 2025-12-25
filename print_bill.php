<?php
// File: smart-udhar-system/print_bill.php

require_once 'config/database.php';
requireLogin();

$conn = getDBConnection();

$udhar_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($udhar_id <= 0) {
    header('Location: udhar.php');
    exit();
}

// Get udhar entry details
$stmt = $conn->prepare("
    SELECT ut.*, c.name as customer_name, c.mobile as customer_mobile, c.address as customer_address,
           u.shop_name, u.mobile as shop_mobile, u.address as shop_address, u.email as shop_email
    FROM udhar_transactions ut 
    JOIN customers c ON ut.customer_id = c.id 
    JOIN users u ON c.user_id = u.id 
    WHERE ut.id = ? AND c.user_id = ?
");
$stmt->bind_param("ii", $udhar_id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$udhar = $result->fetch_assoc();
$stmt->close();

if (!$udhar) {
    setMessage("Bill not found or you don't have permission to view it", "danger");
    header('Location: udhar.php');
    exit();
}

// Get udhar items - UPDATED to include unit
$stmt = $conn->prepare("
    SELECT ui.*, i.unit as item_unit 
    FROM udhar_items ui 
    LEFT JOIN items i ON ui.item_id = i.id 
    WHERE ui.udhar_id = ?
");
$stmt->bind_param("i", $udhar_id);
$stmt->execute();
$udhar_items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Update print count
$stmt = $conn->prepare("UPDATE udhar_transactions SET print_count = print_count + 1 WHERE id = ?");
$stmt->bind_param("i", $udhar_id);
$stmt->execute();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Bill - <?php echo htmlspecialchars($udhar['bill_no']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Page setup for A5 size */
        @page {
            size: A5;
            margin: 10mm;
        }

        @media print {
            body {
                margin: 0;
                padding: 0;
            }

            body * {
                visibility: hidden;
            }

            .print-area,
            .print-area * {
                visibility: visible;
            }

            .print-area {
                position: absolute;
                left: 0;
                top: 0;
                width: 148mm;
                min-height: 210mm;
                margin: 0;
                padding: 0;
            }

            .no-print {
                display: none !important;
            }

            .page-break {
                page-break-after: always;
            }

            .avoid-break {
                page-break-inside: avoid;
            }
        }

        /* A5 container styling */
        .bill-container {
            width: 148mm;
            min-height: 210mm;
            padding: 5mm 7mm;
            font-family: 'Arial', sans-serif;
            font-size: 9pt;
            margin: 0 auto;
            background: white;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
        }

        /* Compact header */
        .bill-header {
            text-align: center;
            padding-bottom: 3mm;
            margin-bottom: 4mm;
            border-bottom: 1px solid #000;
        }

        .shop-title {
            font-size: 14pt;
            font-weight: bold;
            margin-bottom: 2px;
            text-transform: uppercase;
        }

        .shop-details {
            font-size: 8pt;
            color: #555;
            line-height: 1.2;
        }

        /* Compact info tables */
        .info-table {
            font-size: 8pt;
            margin-bottom: 4mm;
        }

        .info-table th {
            width: 25mm;
            padding: 2px 5px;
            font-weight: bold;
            white-space: nowrap;
        }

        .info-table td {
            padding: 2px 5px;
        }

        /* Compact items table */
        .items-table {
            width: 100%;
            font-size: 8pt;
            border-collapse: collapse;
            margin-bottom: 4mm;
        }

        .items-table th,
        .items-table td {
            border: 1px solid #000;
            padding: 3px 4px;
            text-align: center;
            line-height: 1.2;
        }

        .items-table th {
            background-color: #f0f0f0;
            font-weight: bold;
            padding: 4px;
        }

        /* Column widths for compact layout */
        .col-sno {
            width: 8mm;
        }

        .col-desc {
            width: 45mm;
            text-align: left !important;
        }

        .col-hsn {
            width: 15mm;
        }

        .col-qty {
            width: 12mm;
        }

        .col-unit {
            width: 12mm;
        }

        .col-rate {
            width: 18mm;
        }

        .col-amount {
            width: 20mm;
        }

        /* Totals section */
        .totals-table {
            width: 60mm;
            float: right;
            font-size: 8pt;
            margin-bottom: 4mm;
        }

        .totals-table th,
        .totals-table td {
            padding: 3px 5px;
            text-align: right;
        }

        .totals-table th {
            width: 35mm;
        }

        .totals-table td {
            width: 25mm;
        }

        .grand-total {
            font-weight: bold;
            font-size: 9pt;
            border-top: 2px solid #000;
            border-bottom: 2px solid #000;
            padding: 4px 5px !important;
        }

        /* Footer */
        .bill-footer {
            clear: both;
            margin-top: 8mm;
            padding-top: 3mm;
            border-top: 1px dashed #000;
            font-size: 7pt;
            color: #555;
        }

        .signature-box {
            margin-top: 15mm;
            text-align: center;
        }

        .signature-line {
            width: 50mm;
            border-top: 1px solid #000;
            margin: 5px auto;
            padding-top: 2px;
        }

        /* Watermark */
        .watermark {
            position: fixed;
            top: 40%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 80px;
            color: rgba(0, 0, 0, 0.05);
            z-index: -1;
            font-weight: bold;
        }

        /* Status badges */
        .status-badge {
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 7pt;
            font-weight: bold;
        }

        .status-paid {
            background-color: #d4edda;
            color: #155724;
        }

        .status-partial {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-pending {
            background-color: #f8d7da;
            color: #721c24;
        }

        /* Description and notes */
        .description-box {
            font-size: 8pt;
            margin: 3mm 0;
            padding: 2mm;
            background-color: #f8f9fa;
            border-left: 3px solid #007bff;
        }

        /* Print message */
        .print-message {
            font-size: 7pt;
            text-align: center;
            color: #666;
            margin-top: 2mm;
        }

        /* Responsive for screen view */
        @media screen {
            body {
                background-color: #f5f5f5;
                padding: 20px;
            }

            .bill-container {
                box-shadow: 0 0 20px rgba(0, 0, 0, 0.2);
                margin: 20px auto;
            }
        }
    </style>
</head>

<body>
    <div class="container-fluid mt-3 no-print">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4>
                        <i class="bi bi-printer"></i> Print Bill (A5 Format)
                    </h4>
                    <div>
                        <button class="btn btn-primary" onclick="window.print()">
                            <i class="bi bi-printer"></i> Print Bill
                        </button>
                        <a href="udhar.php" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Back to List
                        </a>
                    </div>
                </div>
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> This bill is optimized for A5 paper size (148mm x 210mm). Press
                    Ctrl+P to print.
                </div>
            </div>
        </div>
    </div>

    <!-- Bill Content -->
    <div class="print-area">
        <div class="bill-container avoid-break">
            <!-- Watermark -->
            <div class="watermark">UDHAR</div>

            <!-- Bill Header -->
            <div class="bill-header">
                <div class="shop-title"><?php echo htmlspecialchars($udhar['shop_name']); ?></div>
                <div class="shop-details">
                    <?php if (!empty($udhar['shop_address'])): ?>
                        <?php echo htmlspecialchars($udhar['shop_address']); ?>
                    <?php endif; ?>
                    <?php if (!empty($udhar['shop_mobile'])): ?>
                        | Mob: <?php echo htmlspecialchars($udhar['shop_mobile']); ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Bill Information -->
            <div class="row">
                <div class="col-6">
                    <table class="info-table">
                        <tr>
                            <th>Bill No:</th>
                            <td><?php echo htmlspecialchars($udhar['bill_no']); ?></td>
                        </tr>
                        <tr>
                            <th>Date:</th>
                            <td><?php echo date('d/m/Y', strtotime($udhar['transaction_date'])); ?></td>
                        </tr>
                        <tr>
                            <th>Due Date:</th>
                            <td>
                                <?php if (!empty($udhar['due_date'])): ?>
                                    <?php echo date('d/m/Y', strtotime($udhar['due_date'])); ?>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="col-6">
                    <table class="info-table">
                        <tr>
                            <th>Customer:</th>
                            <td><?php echo htmlspecialchars($udhar['customer_name']); ?></td>
                        </tr>
                        <tr>
                            <th>Mobile:</th>
                            <td><?php echo htmlspecialchars($udhar['customer_mobile']); ?></td>
                        </tr>
                        <tr>
                            <th>Address:</th>
                            <td><?php echo htmlspecialchars(substr($udhar['customer_address'], 0, 30));
                            if (strlen($udhar['customer_address']) > 30)
                                echo '...'; ?></td>
                        </tr>
                    </table>
                </div>
            </div>

            <?php if (!empty($udhar['description'])): ?>
                <div class="description-box">
                    <strong>Description:</strong> <?php echo htmlspecialchars($udhar['description']); ?>
                </div>
            <?php endif; ?>

            <!-- Bill Items -->
            <table class="items-table">
                <thead>
                    <tr>
                        <th class="col-sno">#</th>
                        <th class="col-desc">Item Description</th>
                        <th class="col-hsn">HSN</th>
                        <th class="col-qty">Qty</th>
                        <th class="col-unit">Unit</th>
                        <th class="col-rate">Rate</th>
                        <th class="col-amount">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($udhar_items as $index => $item): ?>
                        <tr>
                            <td><?php echo $index + 1; ?></td>
                            <td class="col-desc"><?php echo htmlspecialchars($item['item_name']); ?></td>
                            <td><?php echo htmlspecialchars($item['hsn_code']); ?></td>
                            <td><?php echo number_format($item['quantity'], 2); ?></td>
                            <td>
                                <?php
                                $unit = !empty($item['item_unit']) ? $item['item_unit'] :
                                    (!empty($item['unit']) ? $item['unit'] : 'PCS');
                                echo htmlspecialchars($unit);
                                ?>
                            </td>
                            <td>₹<?php echo number_format($item['unit_price'], 2); ?></td>
                            <td>₹<?php echo number_format($item['total_amount'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3"
                            style="text-align: right; font-weight: bold; border: 1px solid #000; padding: 3px 4px;">
                            Total Items:</td>
                        <td colspan="4"
                            style="text-align: left; font-weight: bold; border: 1px solid #000; padding: 3px 4px;">
                            <?php echo count($udhar_items); ?>
                        </td>
                    </tr>
                </tfoot>
            </table>

            <!-- Bill Totals -->
            <table class="totals-table">
                <tr>
                    <th>Sub Total:</th>
                    <td>₹<?php echo number_format($udhar['total_amount'], 2); ?></td>
                </tr>
                <?php
                // Calculate total GST/Tax
                $total_tax = $udhar['cgst_amount'] + $udhar['sgst_amount'] + $udhar['igst_amount'];

                // Calculate what's being added to subtotal
                $charges_added = $udhar['grand_total'] - $udhar['total_amount'] + $udhar['discount'] - $udhar['round_off'];

                // If there's a difference but no tax breakdown, show it as total charges
                if ($charges_added > 0.01):
                    ?>
                    <tr style="border-top: 1px solid #ddd;">
                        <th colspan="2"
                            style="text-align: left; font-size: 7.5pt; padding: 2px 5px; background-color: #f8f9fa;">
                            Additional Charges:</th>
                    </tr>
                    <?php if ($udhar['cgst_amount'] > 0): ?>
                        <tr>
                            <th style="padding-left: 15px;">CGST:</th>
                            <td>₹<?php echo number_format($udhar['cgst_amount'], 2); ?></td>
                        </tr>
                    <?php endif; ?>
                    <?php if ($udhar['sgst_amount'] > 0): ?>
                        <tr>
                            <th style="padding-left: 15px;">SGST:</th>
                            <td>₹<?php echo number_format($udhar['sgst_amount'], 2); ?></td>
                        </tr>
                    <?php endif; ?>
                    <?php if ($udhar['igst_amount'] > 0): ?>
                        <tr>
                            <th style="padding-left: 15px;">IGST:</th>
                            <td>₹<?php echo number_format($udhar['igst_amount'], 2); ?></td>
                        </tr>
                    <?php endif; ?>
                    <?php if ($total_tax > 0): ?>
                        <tr style="background-color: #f0f0f0;">
                            <th>Total Tax (GST):</th>
                            <td>₹<?php echo number_format($total_tax, 2); ?></td>
                        </tr>
                    <?php elseif ($charges_added > 0.01): ?>
                        <tr style="background-color: #f0f0f0;">
                            <th>Tax/Charges:</th>
                            <td>₹<?php echo number_format($charges_added, 2); ?></td>
                        </tr>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if ($udhar['discount'] > 0): ?>
                    <tr style="border-top: 1px solid #ddd;">
                        <th colspan="2"
                            style="text-align: left; font-size: 7.5pt; padding: 2px 5px; background-color: #f8f9fa;">
                            Deductions:</th>
                    </tr>
                    <tr>
                        <th style="padding-left: 15px;">Discount:</th>
                        <td>-₹<?php echo number_format($udhar['discount'], 2); ?></td>
                    </tr>
                <?php endif; ?>

                <?php if ($udhar['round_off'] != 0): ?>
                    <tr>
                        <th>Round Off:</th>
                        <td><?php echo ($udhar['round_off'] >= 0 ? '+' : '') . '₹' . number_format($udhar['round_off'], 2); ?>
                        </td>
                    </tr>
                <?php endif; ?>
                <tr class="grand-total">
                    <th>Grand Total:</th>
                    <td>₹<?php echo number_format($udhar['grand_total'], 2); ?></td>
                </tr>
                <tr>
                    <th>Status:</th>
                    <td>
                        <?php if ($udhar['status'] == 'paid'): ?>
                            <span class="status-badge status-paid">PAID</span>
                        <?php elseif ($udhar['status'] == 'partially_paid'): ?>
                            <span class="status-badge status-partial">PARTIALLY PAID</span>
                        <?php else: ?>
                            <span class="status-badge status-pending">PENDING</span>
                        <?php endif; ?>
                    </td>
                </tr>
            </table>

            <?php if (!empty($udhar['notes'])): ?>
                <div style="clear: both; margin-top: 3mm; font-size: 8pt;">
                    <strong>Notes:</strong> <?php echo htmlspecialchars($udhar['notes']); ?>
                </div>
            <?php endif; ?>

            <!-- Footer -->
            <div class="bill-footer">
                <div class="row">
                    <div class="col-6">
                        <div class="signature-box">
                            <div class="signature-line"></div>
                            Customer Signature
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="signature-box">
                            <div class="signature-line"></div>
                            Authorized Signature
                        </div>
                    </div>
                </div>

                <div class="print-message">
                    <div>Thank you for your business!</div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Auto print after page load (optional)
        window.addEventListener('load', function () {
            // Uncomment to auto-print
            // setTimeout(function() { window.print(); }, 500);
        });
    </script>
</body>

</html>