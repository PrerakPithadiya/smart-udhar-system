<?php
// File: smart-udhar-system/import_items.php

require_once 'config/database.php';
requireLogin();

$page_title = "Import Items";

$conn = getDBConnection();
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['import_items'])) {
    if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] == 0) {
        $file = $_FILES['csv_file']['tmp_name'];
        $filename = $_FILES['csv_file']['name'];
        $ext = pathinfo($filename, PATHINFO_EXTENSION);

        if (strtolower($ext) !== 'csv') {
            setMessage("Please upload a valid CSV file.", "danger");
        } else {
            $handle = fopen($file, "r");
            if ($handle !== FALSE) {
                // Skip the header row
                fgetcsv($handle);

                $success_count = 0;
                $error_count = 0;
                $row_num = 1;

                $stmt = $conn->prepare("INSERT INTO items (user_id, item_name, item_code, hsn_code, price, cgst_rate, sgst_rate, igst_rate, unit, description, category) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE item_code = VALUES(item_code), hsn_code = VALUES(hsn_code), price = VALUES(price), cgst_rate = VALUES(cgst_rate), sgst_rate = VALUES(sgst_rate), igst_rate = VALUES(igst_rate), unit = VALUES(unit), description = VALUES(description), category = VALUES(category), updated_at = NOW()");

                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    $row_num++;

                    // Map CSV columns to variables (Assumes specific column order in sample CSV)
                    // Order: Item Name, Item Code, HSN Code, Price, CGST, SGST, IGST, Unit, Description, Category

                    $item_name = isset($data[0]) ? sanitizeInput($data[0]) : '';
                    $item_code = isset($data[1]) ? sanitizeInput($data[1]) : '';
                    $hsn_code = isset($data[2]) ? sanitizeInput($data[2]) : '';
                    $price = isset($data[3]) ? floatval($data[3]) : 0.00;
                    $cgst = isset($data[4]) ? floatval($data[4]) : 0.00;
                    $sgst = isset($data[5]) ? floatval($data[5]) : 0.00;
                    $igst = isset($data[6]) ? floatval($data[6]) : 0.00;
                    $unit = isset($data[7]) ? sanitizeInput($data[7]) : 'PCS';
                    $desc = isset($data[8]) ? sanitizeInput($data[8]) : '';
                    $cat = isset($data[9]) ? sanitizeInput($data[9]) : 'Others';

                    if (empty($item_name) || $price <= 0) {
                        $error_count++;
                        continue; // Skip invalid rows
                    }

                    $stmt->bind_param("isssddddsss", $_SESSION['user_id'], $item_name, $item_code, $hsn_code, $price, $cgst, $sgst, $igst, $unit, $desc, $cat);

                    if ($stmt->execute()) {
                        $success_count++;
                    } else {
                        $error_count++;
                    }
                }
                fclose($handle);
                $stmt->close();

                if ($success_count > 0) {
                    setMessage("Successfully imported/updated $success_count items. Errors: $error_count", "success");
                } else {
                    setMessage("Import failed. No items were added. Errors: $error_count", "warning");
                }
            } else {
                setMessage("Could not open file.", "danger");
            }
        }
    } else {
        setMessage("Please select a file to upload.", "danger");
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Smart Udhar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/items.css">
    <style>
        .import-card {
            max-width: 600px;
            margin: 50px auto;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            background: #fff;
        }

        .step-num {
            display: inline-block;
            width: 30px;
            height: 30px;
            background: #e9ecef;
            color: #495057;
            border-radius: 50%;
            text-align: center;
            line-height: 30px;
            margin-right: 10px;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <?php include 'includes/header.php'; ?>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar (Hidden on mobile) -->
            <div class="col-md-3 col-lg-2 d-md-block sidebar collapse">
                <!-- Reuse sidebar from items.php or include a sidebar component if available -->

            </div>

            <div class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="pt-3">
                    <a href="items.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Back to
                        Items</a>
                </div>
                <div class="import-card">
                    <div class="text-center mb-4">
                        <i class="bi bi-cloud-upload display-4 text-primary"></i>
                        <h2 class="mt-3">Import Items</h2>
                        <p class="text-muted">Bulk upload items from a CSV file</p>
                    </div>

                    <?php displayMessage(); ?>

                    <div class="mb-4">
                        <h5><span class="step-num">1</span> Download Sample File</h5>
                        <p class="text-muted small">Download existing format to check required columns.</p>
                        <a href="assets/sample_items.csv" class="btn btn-outline-primary btn-sm" download>
                            <i class="bi bi-download"></i> Download sample_items.csv
                        </a>
                    </div>

                    <div class="mb-4">
                        <h5><span class="step-num">2</span> Prepare Your Data</h5>
                        <p class="text-muted small">
                            - <strong>Item Name</strong> and <strong>Price</strong> are required.<br>
                            - Duplicate item names will be <strong>Updated</strong>.<br>
                            - Ensure tax rates are numbers (e.g., 5.00).
                        </p>
                    </div>

                    <form method="POST" enctype="multipart/form-data">
                        <h5><span class="step-num">3</span> Upload File</h5>
                        <div class="mb-3">
                            <input class="form-control" type="file" name="csv_file" accept=".csv" required>
                        </div>
                        <div class="d-grid">
                            <button type="submit" name="import_items" class="btn btn-primary">
                                <i class="bi bi-cloud-upload"></i> Upload and Import
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/common.js"></script>
</body>

</html>