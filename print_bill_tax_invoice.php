<?php
// File: smart-udhar-system-2/print_bill_tax_invoice.php
// Tax Invoice format matching the exact layout from the provided bill image

require_once 'config/database.php';
requireLogin();

$conn = getDBConnection();

$udhar_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($udhar_id <= 0) {
    header('Location: udhar.php');
    exit();
}

// Get udhar entry details with shop information
$stmt = $conn->prepare("
    SELECT ut.*, c.name as customer_name, c.mobile as customer_mobile, c.address as customer_address,
           u.shop_name, u.mobile as shop_mobile, u.address as shop_address, u.email as shop_email,
           u.license_no, u.license_date, u.gst_no, u.registration_no, u.registration_date
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

// Get udhar items with unit information
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

$udhar_items = array_values(array_filter($udhar_items, function ($item) {
    $qty = isset($item['quantity']) ? (float) $item['quantity'] : 0;
    $unitPrice = isset($item['unit_price']) ? (float) $item['unit_price'] : 0;
    $total = isset($item['total_amount']) ? (float) $item['total_amount'] : 0;

    return $qty > 0 && $unitPrice >= 0 && $total > 0;
}));

$calculated_total_amount = 0.0;
foreach ($udhar_items as $item) {
    $calculated_total_amount += (float) ($item['total_amount'] ?? 0);
}

$discount_amount = (float) ($udhar['discount'] ?? 0);
$transportation_charge = (float) ($udhar['transportation_charge'] ?? 0);
$round_off_amount = (float) ($udhar['round_off'] ?? 0);

$calculated_grand_total = $calculated_total_amount - $discount_amount + $transportation_charge + $round_off_amount;
$calculated_grand_total = round($calculated_grand_total, 2);

// Update print count
$stmt = $conn->prepare("UPDATE udhar_transactions SET print_count = print_count + 1 WHERE id = ?");
$stmt->bind_param("i", $udhar_id);
$stmt->execute();
$stmt->close();

// Function to convert number to words (Indian format)
function numberToWords($number)
{
    $number = (int) $number;

    $words = array(
        0 => '',
        1 => 'One',
        2 => 'Two',
        3 => 'Three',
        4 => 'Four',
        5 => 'Five',
        6 => 'Six',
        7 => 'Seven',
        8 => 'Eight',
        9 => 'Nine',
        10 => 'Ten',
        11 => 'Eleven',
        12 => 'Twelve',
        13 => 'Thirteen',
        14 => 'Fourteen',
        15 => 'Fifteen',
        16 => 'Sixteen',
        17 => 'Seventeen',
        18 => 'Eighteen',
        19 => 'Nineteen',
        20 => 'Twenty',
        30 => 'Thirty',
        40 => 'Forty',
        50 => 'Fifty',
        60 => 'Sixty',
        70 => 'Seventy',
        80 => 'Eighty',
        90 => 'Ninety'
    );

    if ($number == 0) {
        return 'Zero';
    }

    $crore = (int) ($number / 10000000);
    $number -= $crore * 10000000;
    $lakh = (int) ($number / 100000);
    $number -= $lakh * 100000;
    $thousand = (int) ($number / 1000);
    $number -= $thousand * 1000;
    $hundred = (int) ($number / 100);
    $number -= $hundred * 100;
    $ten = (int) ($number / 10);
    $unit = $number % 10;

    $result = '';

    if ($crore) {
        $result .= numberToWords($crore) . ' Crore ';
    }
    if ($lakh) {
        $result .= numberToWords($lakh) . ' Lakh ';
    }
    if ($thousand) {
        $result .= numberToWords($thousand) . ' Thousand ';
    }
    if ($hundred) {
        $result .= $words[$hundred] . ' Hundred ';
    }
    if ($ten >= 2) {
        $result .= $words[$ten * 10] . ' ';
        if ($unit) {
            $result .= $words[$unit] . ' ';
        }
    } elseif ($ten == 1) {
        $result .= $words[10 + $unit] . ' ';
    } elseif ($unit) {
        $result .= $words[$unit] . ' ';
    }

    return trim($result);
}

function amountToWords($amount)
{
    $formatted = number_format((float) $amount, 2, '.', '');
    [$rupeesStr, $paiseStr] = array_pad(explode('.', $formatted, 2), 2, '00');
    $rupees = (int) $rupeesStr;
    $paise = (int) $paiseStr;

    $words = numberToWords($rupees) . ' Rupees';
    if ($paise > 0) {
        $words .= ' and ' . numberToWords($paise) . ' Paise';
    }

    return $words . ' Only.';
}

$amount_in_words = amountToWords($calculated_grand_total);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tax Invoice - <?php echo htmlspecialchars($udhar['bill_no']); ?></title>
    <style>
        /* Page setup for A4 size */
        @page {
            size: A4;
            margin: 10mm;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 11pt;
            line-height: 1.3;
            color: #000;
        }

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

        /* Bill container */
        .bill-container {
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            padding: 5mm;
            background: white;
            border: 1px solid #000;
        }

        /* Header section */
        .bill-header {
            display: table;
            width: 100%;
            margin-bottom: 3px;
            font-size: 10pt;
        }

        .header-left,
        .header-center,
        .header-right {
            display: table-cell;
            vertical-align: top;
        }

        .header-left {
            width: 35%;
            text-align: left;
        }

        .header-center {
            width: 30%;
            text-align: center;
        }

        .header-right {
            width: 35%;
            text-align: right;
        }

        .tax-invoice-title {
            font-size: 16pt;
            font-weight: bold;
            text-align: center;
            margin: 5px 0;
        }

        .company-name {
            font-size: 14pt;
            font-weight: bold;
            text-align: center;
            margin: 3px 0;
        }

        .company-address {
            font-size: 9pt;
            text-align: center;
            margin-bottom: 5px;
        }

        /* Bill info section */
        .bill-info-section {
            border: 1px solid #000;
            border-bottom: none;
            padding: 3px 5px;
            display: table;
            width: 100%;
        }

        .bill-info-left,
        .bill-info-center,
        .bill-info-right {
            display: table-cell;
            vertical-align: top;
            font-size: 10pt;
        }

        .bill-info-left {
            width: 20%;
        }

        .bill-info-center {
            width: 60%;
            text-align: center;
            font-weight: bold;
            font-size: 12pt;
        }

        .bill-info-right {
            width: 20%;
            text-align: right;
        }

        /* Customer info */
        .customer-info {
            border: 1px solid #000;
            border-bottom: none;
            padding: 3px 5px;
            font-size: 10pt;
        }

        /* Items table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8mm;
            font-size: 10pt;
        }

        .items-summary-spacer {
            height: 6mm;
            border-top: 2px solid #000;
        }

        .summary-section.first {
            border-top: 2px solid #000;
        }

        .items-table th,
        .items-table td {
            border: 1px solid #000;
            padding: 4px 6px;
            text-align: center;
        }

        .items-table th {
            font-weight: bold;
            background-color: #fff;
        }

        .items-table .text-left {
            text-align: left;
        }

        .items-table .text-right {
            text-align: right;
        }

        /* Column widths */
        .col-srno {
            width: 6%;
        }

        .col-item-name {
            width: 28%;
        }

        .col-hsn {
            width: 10%;
        }

        .col-gst {
            width: 8%;
        }

        .col-unit-sold {
            width: 10%;
        }

        .col-qty {
            width: 10%;
        }

        .col-rate {
            width: 10%;
        }

        .col-amount {
            width: 18%;
        }

        /* Phone section */
        .phone-section {
            border: 1px solid #000;
            border-top: none;
            padding: 3px 5px;
            font-size: 10pt;
            min-height: 80px;
        }

        /* Summary section */
        .summary-section {
            border: 1px solid #000;
            border-top: none;
            display: table;
            width: 100%;
        }

        .summary-left {
            display: table-cell;
            width: 50%;
            padding: 5px;
            vertical-align: top;
            border-right: 1px solid #000;
        }

        .summary-right {
            display: table-cell;
            width: 50%;
            padding: 0;
            vertical-align: top;
        }

        .summary-table {
            width: 100%;
            font-size: 10pt;
        }

        .summary-table td {
            padding: 3px 8px;
            border-bottom: 1px solid #000;
        }

        .summary-table td:first-child {
            text-align: right;
            font-weight: normal;
        }

        .summary-table td:last-child {
            text-align: right;
            font-weight: bold;
            width: 30%;
        }

        .summary-table tr:last-child td {
            border-bottom: none;
            font-weight: bold;
            font-size: 11pt;
        }

        /* Amount in words */
        .amount-words {
            border: 1px solid #000;
            border-top: none;
            padding: 5px;
            font-size: 10pt;
        }

        /* Signature section */
        .signature-section {
            border: 1px solid #000;
            border-top: none;
            display: table;
            width: 100%;
            min-height: 80px;
        }

        .signature-left,
        .signature-right {
            display: table-cell;
            width: 50%;
            padding: 10px;
            vertical-align: bottom;
        }

        .signature-left {
            text-align: left;
            border-right: 1px solid #000;
        }

        .signature-right {
            text-align: right;
        }

        /* Screen view only */
        @media screen {
            body {
                background-color: #f5f5f5;
                padding: 20px;
            }

            .bill-container {
                box-shadow: 0 0 20px rgba(0, 0, 0, 0.2);
            }
        }

        /* Print button */
        .print-button-container {
            text-align: center;
            margin: 20px 0;
        }

        .print-btn {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 12px 30px;
            font-size: 14pt;
            cursor: pointer;
            border-radius: 5px;
            margin: 0 10px;
        }

        .print-btn:hover {
            background-color: #0056b3;
        }

        .back-btn {
            background-color: #6c757d;
        }

        .back-btn:hover {
            background-color: #545b62;
        }
    </style>
</head>

<body>
    <!-- Print buttons -->
    <div class="print-button-container no-print">
        <button class="print-btn" onclick="window.print()"> Print Bill</button>
        <button class="print-btn back-btn" onclick="window.location.href='udhar.php'"> Back to List</button>
    </div>

    <!-- Bill Content -->
    <div class="print-area">
        <div class="bill-container">
            <!-- Header with License, Title, GST -->
            <div class="bill-header">
                <div class="header-left">
                    <?php if (!empty($udhar['license_no'])): ?>
                        Lic.No. : <?php echo htmlspecialchars($udhar['license_no']); ?>
                        <?php if (!empty($udhar['license_date'])): ?>
                            Dt. <?php echo date('d/m/Y', strtotime($udhar['license_date'])); ?>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
                <div class="header-center">
                    <strong>TAX INVOICE</strong>
                </div>
                <div class="header-right">
                    <?php if (!empty($udhar['gst_no'])): ?>
                        GST No. : <?php echo htmlspecialchars($udhar['gst_no']); ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Company Name -->
            <div class="company-name">
                <?php echo htmlspecialchars($udhar['shop_name']); ?>
            </div>

            <?php if (!empty($udhar['registration_no'])): ?>
                <div class="company-address">
                    [R.No. <?php echo htmlspecialchars($udhar['registration_no']); ?>
                    <?php if (!empty($udhar['registration_date'])): ?>
                        Dt.<?php echo date('d/m/Y', strtotime($udhar['registration_date'])); ?>
                    <?php endif; ?>]
                </div>
            <?php endif; ?>

            <!-- Bill Info Section -->
            <div class="bill-info-section">
                <div class="bill-info-left">
                    Bill No. : <?php echo htmlspecialchars($udhar['bill_no']); ?>
                </div>
                <div class="bill-info-center">
                    CREDIT MEMO
                </div>
                <div class="bill-info-right">
                    Date : <?php echo date('d/m/Y', strtotime($udhar['transaction_date'])); ?>
                </div>
            </div>

            <!-- Customer Info -->
            <div class="customer-info">
                Buyer's Name : <?php echo htmlspecialchars($udhar['customer_name']); ?>
                <span style="float: right;">
                    Address : <?php echo htmlspecialchars(substr($udhar['customer_address'], 0, 50)); ?>
                </span>
            </div>

            <!-- Items Table -->
            <table class="items-table">
                <thead>
                    <tr>
                        <th class="col-srno">Sr No.</th>
                        <th class="col-item-name">Item Name</th>
                        <th class="col-hsn">HSN Code</th>
                        <th class="col-gst">GST%</th>
                        <th class="col-unit-sold">Unit Sold</th>
                        <th class="col-qty">Qty Sold</th>
                        <th class="col-rate">Rate Rate</th>
                        <th class="col-amount">Total Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $total_gst_percent = 0;
                    foreach ($udhar_items as $index => $item):
                        $gst_percent = $item['cgst_rate'] + $item['sgst_rate'] + $item['igst_rate'];
                        $unit = !empty($item['item_unit']) ? $item['item_unit'] : (!empty($item['unit']) ? $item['unit'] : 'PCS');
                        ?>
                        <tr>
                            <td><?php echo $index + 1; ?></td>
                            <td class="text-left"><?php echo htmlspecialchars($item['item_name']); ?></td>
                            <td><?php echo htmlspecialchars($item['hsn_code']); ?></td>
                            <td><?php echo number_format($gst_percent, 2); ?></td>
                            <td><?php echo htmlspecialchars($unit); ?></td>
                            <td><?php echo number_format($item['quantity'], 2); ?></td>
                            <td><?php echo number_format($item['unit_price'], 2); ?></td>
                            <td class="text-right"><?php echo number_format($item['total_amount'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="items-summary-spacer"></div>

            <!-- Phone and Summary Section -->
            <div class="summary-section first">
                <div class="summary-left">
                    <div class="phone-section" style="border: none; min-height: auto;">
                        Phone :
                        <?php if (!empty($udhar['customer_mobile'])): ?>
                            <?php echo htmlspecialchars($udhar['customer_mobile']); ?>
                        <?php elseif (!empty($udhar['shop_mobile'])): ?>
                            <?php echo htmlspecialchars($udhar['shop_mobile']); ?>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="summary-right">
                    <table class="summary-table">
                        <tr>
                            <td>Total :</td>
                            <td><?php echo number_format($calculated_total_amount, 2); ?></td>
                        </tr>
                        <tr>
                            <td>Discount</td>
                            <td><?php echo number_format($discount_amount, 2); ?></td>
                        </tr>
                        <tr>
                            <td>Transportation Charges</td>
                            <td><?php echo number_format($transportation_charge, 2); ?></td>
                        </tr>
                        <tr>
                            <td>Round Off</td>
                            <td><?php echo number_format($round_off_amount, 2); ?></td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Amount in Words and Total -->
            <div class="summary-section" style="border-top: 1px solid #000;">
                <div class="summary-left">
                    <div style="padding: 5px;">
                        In words : <?php echo $amount_in_words; ?>
                    </div>
                </div>
                <div class="summary-right">
                    <table class="summary-table">
                        <tr>
                            <td>Final Total :</td>
                            <td><?php echo number_format($calculated_grand_total, 2); ?></td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Signature Section -->
            <div class="signature-section">
                <div class="signature-left">
                    Buyer's Signature
                </div>
                <div class="signature-right">
                    For<br>
                    <strong><?php echo htmlspecialchars($udhar['shop_name']); ?></strong>
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