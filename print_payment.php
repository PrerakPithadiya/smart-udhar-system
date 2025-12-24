<?php
// File: smart-udhar-system/print_payment.php

require_once 'config/database.php';
requireLogin();

$conn = getDBConnection();

$payment_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($payment_id <= 0) {
    header('Location: payments.php');
    exit();
}

// Get payment details
$stmt = $conn->prepare("
    SELECT p.*, c.name as customer_name, c.mobile as customer_mobile, c.address as customer_address,
           u.shop_name, u.mobile as shop_mobile, u.address as shop_address, u.email as shop_email
    FROM payments p 
    JOIN customers c ON p.customer_id = c.id 
    JOIN users u ON c.user_id = u.id 
    WHERE p.id = ? AND c.user_id = ?
");
$stmt->bind_param("ii", $payment_id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$payment = $result->fetch_assoc();
$stmt->close();

if (!$payment) {
    setMessage("Payment receipt not found", "danger");
    header('Location: payments.php');
    exit();
}

// Get payment allocations
$stmt = $conn->prepare("
    SELECT pa.*, ut.bill_no, ut.description, ut.transaction_date as bill_date
    FROM payment_allocations pa 
    JOIN udhar_transactions ut ON pa.udhar_transaction_id = ut.id 
    WHERE pa.payment_id = ?
    ORDER BY pa.created_at ASC
");
$stmt->bind_param("i", $payment_id);
$stmt->execute();
$allocations = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Receipt - <?php echo htmlspecialchars($payment['customer_name']); ?></title>
    <style>
        @media print {
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
                width: 100%;
            }

            .no-print {
                display: none !important;
            }
        }

        body {
            font-family: 'Arial', sans-serif;
            background-color: #f8f9fa;
        }

        .receipt-container {
            max-width: 800px;
            margin: 20px auto;
            background: white;
            border: 1px solid #ddd;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .receipt-header {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .receipt-header h1 {
            margin: 0;
            font-size: 32px;
            font-weight: bold;
        }

        .receipt-header p {
            margin: 10px 0 0 0;
            opacity: 0.9;
        }

        .receipt-body {
            padding: 30px;
        }

        .receipt-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px dashed #3498db;
        }

        .info-left,
        .info-right {
            flex: 1;
        }

        .info-item {
            margin-bottom: 15px;
        }

        .info-label {
            font-weight: bold;
            color: #3498db;
            display: block;
            margin-bottom: 5px;
        }

        .info-value {
            font-size: 16px;
        }

        .amount-section {
            text-align: center;
            margin: 30px 0;
            padding: 20px;
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border-radius: 10px;
            border: 2px solid #3498db;
        }

        .amount-label {
            font-size: 18px;
            color: #666;
            margin-bottom: 10px;
        }

        .amount-value {
            font-size: 48px;
            font-weight: bold;
            color: #27ae60;
        }

        .amount-words {
            font-size: 18px;
            color: #666;
            margin-top: 10px;
            font-style: italic;
        }

        .payment-details {
            margin-top: 30px;
        }

        .payment-mode {
            display: inline-block;
            padding: 8px 20px;
            background: #3498db;
            color: white;
            border-radius: 20px;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .allocations-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .allocations-table th {
            background-color: #3498db;
            color: white;
            padding: 12px;
            text-align: left;
        }

        .allocations-table td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }

        .allocations-table tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .receipt-footer {
            padding: 20px 30px;
            background-color: #f8f9fa;
            border-top: 2px dashed #ddd;
            text-align: center;
        }

        .signature-area {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
        }

        .signature-box {
            text-align: center;
            width: 45%;
        }

        .signature-line {
            width: 100%;
            height: 1px;
            background: #000;
            margin: 40px 0 10px 0;
        }

        .stamp {
            position: absolute;
            right: 50px;
            bottom: 50px;
            width: 150px;
            opacity: 0.8;
        }

        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 120px;
            color: rgba(52, 152, 219, 0.1);
            z-index: -1;
            font-weight: bold;
        }

        .no-print {
            text-align: center;
            padding: 20px;
        }

        .print-btn {
            background: linear-gradient(135deg, #27ae60, #229954);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 25px;
            font-size: 18px;
            cursor: pointer;
            margin: 10px;
        }

        .print-btn:hover {
            background: linear-gradient(135deg, #229954, #1e8449);
        }

        .back-btn {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 25px;
            font-size: 18px;
            cursor: pointer;
            margin: 10px;
            text-decoration: none;
            display: inline-block;
        }

        .back-btn:hover {
            background: linear-gradient(135deg, #2980b9, #2471a3);
            color: white;
            text-decoration: none;
        }

        @media (max-width: 768px) {
            .receipt-info {
                flex-direction: column;
            }

            .info-left,
            .info-right {
                width: 100%;
            }

            .amount-value {
                font-size: 36px;
            }

            .signature-area {
                flex-direction: column;
            }

            .signature-box {
                width: 100%;
                margin-bottom: 20px;
            }
        }

        /* Number to words styling */
        .amount-in-words {
            background: white;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #27ae60;
            margin: 20px 0;
            font-size: 18px;
            line-height: 1.5;
        }
    </style>
    <script>
        // Function to convert number to words
        function numberToWords(num) {
            const ones = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten',
                'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen',
                'Eighteen', 'Nineteen'
            ];
            const tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];

            if (num === 0) return 'Zero';

            let words = '';

            // Handle lakhs
            if (num >= 100000) {
                words += numberToWords(Math.floor(num / 100000)) + ' Lakh ';
                num %= 100000;
            }

            // Handle thousands
            if (num >= 1000) {
                words += numberToWords(Math.floor(num / 1000)) + ' Thousand ';
                num %= 1000;
            }

            // Handle hundreds
            if (num >= 100) {
                words += numberToWords(Math.floor(num / 100)) + ' Hundred ';
                num %= 100;
            }

            // Handle tens and ones
            if (num > 0) {
                if (num < 20) {
                    words += ones[num];
                } else {
                    words += tens[Math.floor(num / 10)];
                    if (num % 10 > 0) {
                        words += ' ' + ones[num % 10];
                    }
                }
            }

            return words.trim();
        }

        // Function to format amount in words
        function formatAmountInWords(amount) {
            const rupees = Math.floor(amount);
            const paise = Math.round((amount - rupees) * 100);

            let words = numberToWords(rupees) + ' Rupees';

            if (paise > 0) {
                words += ' and ' + numberToWords(paise) + ' Paise';
            }

            words += ' Only';
            return words;
        }

        // Update amount in words on page load
        window.onload = function() {
            const amount = <?php echo $payment['amount']; ?>;
            document.getElementById('amountWords').textContent = formatAmountInWords(amount);
        };
    </script>
</head>

<body>
    <div class="no-print">
        <h2>Payment Receipt</h2>
        <p>This receipt is ready for printing. Click the button below to print.</p>
        <button class="print-btn" onclick="window.print()">
            <i class="bi bi-printer"></i> Print Receipt
        </button>
        <a href="payments.php?action=view&id=<?php echo $payment_id; ?>" class="back-btn">
            <i class="bi bi-arrow-left"></i> Back to Payment
        </a>
        <p class="text-muted mt-3">Press Ctrl+P or use the print button above.</p>
    </div>

    <!-- Watermark -->
    <div class="watermark">PAID</div>

    <!-- Receipt Content -->
    <div class="print-area">
        <div class="receipt-container">
            <!-- Header -->
            <div class="receipt-header">
                <h1>PAYMENT RECEIPT</h1>
                <p><?php echo htmlspecialchars($payment['shop_name']); ?></p>
                <p>
                    <?php if (!empty($payment['shop_address'])): ?>
                        <?php echo htmlspecialchars($payment['shop_address']); ?> |
                    <?php endif; ?>
                    <?php if (!empty($payment['shop_mobile'])): ?>
                        Tel: <?php echo htmlspecialchars($payment['shop_mobile']); ?>
                    <?php endif; ?>
                </p>
            </div>

            <!-- Body -->
            <div class="receipt-body">
                <!-- Receipt Info -->
                <div class="receipt-info">
                    <div class="info-left">
                        <div class="info-item">
                            <span class="info-label">Receipt No:</span>
                            <span class="info-value">#<?php echo str_pad($payment['id'], 6, '0', STR_PAD_LEFT); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Receipt Date:</span>
                            <span class="info-value"><?php echo date('d M Y', strtotime($payment['payment_date'])); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Payment Mode:</span>
                            <span class="payment-mode"><?php echo strtoupper(str_replace('_', ' ', $payment['payment_mode'])); ?></span>
                        </div>
                    </div>

                    <div class="info-right">
                        <div class="info-item">
                            <span class="info-label">Customer Name:</span>
                            <span class="info-value"><?php echo htmlspecialchars($payment['customer_name']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Mobile No:</span>
                            <span class="info-value"><?php echo htmlspecialchars($payment['customer_mobile']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Address:</span>
                            <span class="info-value"><?php echo htmlspecialchars($payment['customer_address']); ?></span>
                        </div>
                    </div>
                </div>

                <!-- Amount Section -->
                <div class="amount-section">
                    <div class="amount-label">Amount Received</div>
                    <div class="amount-value">₹<?php echo number_format($payment['amount'], 2); ?></div>
                    <div class="amount-words" id="amountWords"></div>
                </div>

                <!-- Reference Number -->
                <?php if (!empty($payment['reference_no'])): ?>
                    <div class="info-item text-center">
                        <span class="info-label">Reference Number:</span>
                        <span class="info-value" style="font-size: 20px; color: #3498db;">
                            <strong><?php echo htmlspecialchars($payment['reference_no']); ?></strong>
                        </span>
                    </div>
                <?php endif; ?>

                <!-- Allocations Table -->
                <?php if (!empty($allocations)): ?>
                    <div class="payment-details">
                        <h3 style="color: #3498db; border-bottom: 2px solid #3498db; padding-bottom: 10px;">
                            <i class="bi bi-list-check"></i> Payment Allocations
                        </h3>
                        <table class="allocations-table">
                            <thead>
                                <tr>
                                    <th>Bill No</th>
                                    <th>Description</th>
                                    <th>Bill Date</th>
                                    <th>Allocated Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($allocations as $alloc): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($alloc['bill_no']); ?></td>
                                        <td><?php echo htmlspecialchars($alloc['description']); ?></td>
                                        <td><?php echo date('d M Y', strtotime($alloc['bill_date'])); ?></td>
                                        <td style="color: #27ae60; font-weight: bold;">
                                            ₹<?php echo number_format($alloc['allocated_amount'], 2); ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <tr style="background-color: #e8f4fc;">
                                    <td colspan="3" style="text-align: right; font-weight: bold;">Total Allocated:</td>
                                    <td style="color: #3498db; font-weight: bold;">
                                        ₹<?php echo number_format($payment['allocated_amount'], 2); ?>
                                    </td>
                                </tr>
                                <?php if ($payment['remaining_amount'] > 0): ?>
                                    <tr style="background-color: #fff3cd;">
                                        <td colspan="3" style="text-align: right; font-weight: bold;">Remaining Balance:</td>
                                        <td style="color: #f39c12; font-weight: bold;">
                                            ₹<?php echo number_format($payment['remaining_amount'], 2); ?>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>

                <!-- Notes -->
                <?php if (!empty($payment['notes'])): ?>
                    <div class="info-item mt-4">
                        <span class="info-label">Notes:</span>
                        <span class="info-value" style="font-style: italic;">
                            <?php echo htmlspecialchars($payment['notes']); ?>
                        </span>
                    </div>
                <?php endif; ?>

                <!-- Signature Area -->
                <div class="signature-area">
                    <div class="signature-box">
                        <div class="signature-line"></div>
                        <p>Customer Signature</p>
                    </div>

                    <div class="signature-box">
                        <div class="signature-line"></div>
                        <p>Authorized Signature</p>
                        <p style="margin-top: 10px; font-weight: bold;">
                            <?php echo htmlspecialchars($payment['shop_name']); ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="receipt-footer">
                <p style="margin-bottom: 10px;">
                    <strong>Payment Status:</strong>
                    <?php if ($payment['is_allocated'] && $payment['remaining_amount'] == 0): ?>
                        <span style="color: #27ae60; font-weight: bold;">FULLY ALLOCATED</span>
                    <?php elseif ($payment['is_allocated'] && $payment['remaining_amount'] > 0): ?>
                        <span style="color: #f39c12; font-weight: bold;">PARTIALLY ALLOCATED</span>
                    <?php else: ?>
                        <span style="color: #e74c3c; font-weight: bold;">NOT ALLOCATED</span>
                    <?php endif; ?>
                </p>
                <p style="color: #666; font-size: 14px;">
                    This is a computer generated receipt. No signature required.<br>
                    Printed on: <?php echo date('d M Y, h:i A'); ?>
                </p>
                <p style="color: #999; font-size: 12px; margin-top: 20px;">
                    Thank you for your payment. Please keep this receipt for your records.
                </p>
            </div>
        </div>
    </div>

    // In print_payment.php, update the JavaScript function:

    <script>
        // Function to convert number to words - FIXED VERSION
        function numberToWords(num) {
            if (num === 0) return 'Zero';

            const ones = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten',
                'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen',
                'Eighteen', 'Nineteen'
            ];
            const tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];

            function convertLessThanThousand(n) {
                if (n === 0) return '';

                let words = '';

                // Hundreds
                if (n >= 100) {
                    words += ones[Math.floor(n / 100)] + ' Hundred ';
                    n %= 100;
                }

                // Tens and Ones
                if (n > 0) {
                    if (n < 20) {
                        words += ones[n];
                    } else {
                        words += tens[Math.floor(n / 10)];
                        if (n % 10 > 0) {
                            words += ' ' + ones[n % 10];
                        }
                    }
                }

                return words.trim();
            }

            if (num < 0) {
                return 'Minus ' + numberToWords(Math.abs(num));
            }

            let words = '';
            let crore = Math.floor(num / 10000000);
            num %= 10000000;

            let lakh = Math.floor(num / 100000);
            num %= 100000;

            let thousand = Math.floor(num / 1000);
            num %= 1000;

            let hundred = Math.floor(num / 100);
            let remainder = num % 100;

            if (crore > 0) {
                words += convertLessThanThousand(crore) + ' Crore ';
            }

            if (lakh > 0) {
                words += convertLessThanThousand(lakh) + ' Lakh ';
            }

            if (thousand > 0) {
                words += convertLessThanThousand(thousand) + ' Thousand ';
            }

            if (hundred > 0) {
                words += convertLessThanThousand(hundred) + ' Hundred ';
            }

            if (remainder > 0) {
                if (words !== '') words += 'and ';
                words += convertLessThanThousand(remainder);
            }

            return words.trim();
        }

        // Function to format amount in words
        function formatAmountInWords(amount) {
            const rupees = Math.floor(amount);
            const paise = Math.round((amount - rupees) * 100);

            let words = numberToWords(rupees) + ' Rupees';

            if (paise > 0) {
                words += ' and ' + numberToWords(paise) + ' Paise';
            }

            words += ' Only';
            return words;
        }

        // Update amount in words on page load
        window.onload = function() {
            const amount = <?php echo $payment['amount']; ?>;
            const amountWords = formatAmountInWords(amount);
            document.getElementById('amountWords').textContent = amountWords;

            // Also update the amount in words div
            const amountInWordsDiv = document.getElementById('amountInWords');
            if (amountInWordsDiv) {
                amountInWordsDiv.textContent = amountWords;
            }
        };
    </script>
</body>

</html>