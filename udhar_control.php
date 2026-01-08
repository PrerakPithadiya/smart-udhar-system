<?php
// File: smart-udhar-system/udhar_control.php

require_once 'config/database.php';
requireLogin();

$conn = getDBConnection();

function tableColumnExists($conn, $table, $column)
{
    static $cache = [];
    $key = $table . ':' . $column;
    if (array_key_exists($key, $cache)) {
        return $cache[$key];
    }

    $stmt = $conn->prepare("SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ? LIMIT 1");
    if (!$stmt) {
        $cache[$key] = false;
        return $cache[$key];
    }
    $stmt->bind_param("ss", $table, $column);
    $stmt->execute();
    $result = $stmt->get_result();
    $cache[$key] = ($result && $result->num_rows > 0);
    $stmt->close();

    return $cache[$key];
}

function tableColumnType($conn, $table, $column)
{
    static $cache = [];
    $key = $table . ':' . $column . ':type';
    if (array_key_exists($key, $cache)) {
        return $cache[$key];
    }

    $stmt = $conn->prepare("SELECT DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ? LIMIT 1");
    if (!$stmt) {
        $cache[$key] = null;
        return $cache[$key];
    }
    $stmt->bind_param("ss", $table, $column);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result ? $result->fetch_assoc() : null;
    $cache[$key] = $row['DATA_TYPE'] ?? null;
    $stmt->close();

    return $cache[$key];
}

function mysqliBindParamsDynamic($stmt, $types, $params)
{
    $bind_names = [];
    $bind_names[] = $types;
    foreach ($params as $k => $value) {
        $bind_names[] = &$params[$k];
    }
    return call_user_func_array([$stmt, 'bind_param'], $bind_names);
}

// Handle actions
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$udhar_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$customer_id = isset($_GET['customer_id']) ? intval($_GET['customer_id']) : 0;
$item_id = isset($_GET['item_id']) ? intval($_GET['item_id']) : 0;

// Generate bill number - FIXED VERSION
function generateBillNumber($conn, $user_id)
{
    $prefix = 'BILL';
    $year = date('Y');
    $month = date('m');

    // Get last bill number for this user
    $stmt = $conn->prepare("
        SELECT ut.bill_no 
        FROM udhar_transactions ut 
        JOIN customers c ON ut.customer_id = c.id 
        WHERE c.user_id = ? 
        AND ut.bill_no LIKE ? 
        ORDER BY ut.id DESC 
        LIMIT 1
    ");
    $like_pattern = $prefix . '-' . $year . $month . '-%';
    $stmt->bind_param("is", $user_id, $like_pattern);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $last_bill = $result->fetch_assoc()['bill_no'];
        $last_num = intval(substr($last_bill, -4));
        $new_num = str_pad($last_num + 1, 4, '0', STR_PAD_LEFT);
    } else {
        $new_num = '0001';
    }

    $stmt->close();
    return $prefix . '-' . $year . $month . '-' . $new_num;
}

function generateBillNumberFlexible($conn, $user_id)
{
    $type = tableColumnType($conn, 'udhar_transactions', 'bill_no');

    // Older schemas store bill_no as int; newer schemas use string like BILL-YYYYMM-XXXX.
    if ($type && in_array(strtolower($type), ['int', 'bigint', 'mediumint', 'smallint', 'tinyint', 'decimal', 'float', 'double'], true)) {
        $stmt = $conn->prepare("
            SELECT COALESCE(MAX(ut.bill_no), 0) AS max_bill
            FROM udhar_transactions ut
            JOIN customers c ON ut.customer_id = c.id
            WHERE c.user_id = ?
        ");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result ? $result->fetch_assoc() : null;
        $stmt->close();
        return (int)($row['max_bill'] ?? 0) + 1;
    }

    return generateBillNumber($conn, $user_id);
}

// Process form submissions
if (isset($_POST['add_udhar'])) {
    $category = isset($_POST['category']) ? sanitizeInput($_POST['category']) : '';

    // Add new udhar entry with items
    $customer_id = intval($_POST['customer_id']);
    $transaction_date = sanitizeInput($_POST['transaction_date']);
    $due_date = sanitizeInput($_POST['due_date']);
    $description = ''; // Description removed from UI
    $notes = sanitizeInput($_POST['notes']);
    $discount = floatval($_POST['discount'] ?? 0);
    $transportation_charge = floatval($_POST['transportation_charge'] ?? 0);
    $round_off = floatval($_POST['round_off'] ?? 0);
    $discount_type = 'fixed';

    // Validate
    $errors = [];

    if ($customer_id <= 0) {
        $errors[] = "Please choose a customer";
    }

    if (empty($transaction_date)) {
        $errors[] = "Bill date is required";
    }

    // Check if items are added
    if (!isset($_POST['items']) || count($_POST['items']) == 0) {
        $errors[] = "Please add at least one item";
    }

    if (empty($errors)) {
        $conn->begin_transaction();

        try {
            // Generate bill number
            $bill_no = generateBillNumberFlexible($conn, $_SESSION['user_id']);

            // Calculate totals from items
            $total_amount = 0;
            $cgst_calc = 0;
            $sgst_calc = 0;
            $igst_calc = 0;

            if (isset($_POST['items'])) {
                foreach ($_POST['items'] as $index => $item) {
                    $qty = floatval($item['quantity']);
                    $price = floatval($item['price']);
                    $cgst = floatval($item['cgst_rate'] ?? 0);
                    $sgst = floatval($item['sgst_rate'] ?? 0);
                    $igst = floatval($item['igst_rate'] ?? 0);

                    $item_total = $qty * $price;
                    $total_amount += $item_total;

                    if ($igst > 0) {
                        $igst_calc += ($item_total * $igst) / 100;
                    } else {
                        $cgst_calc += ($item_total * $cgst) / 100;
                        $sgst_calc += ($item_total * $sgst) / 100;
                    }
                }
            }

            // Take manually edited values from POST, or use calculated ones as fallback
            $cgst_amount = floatval($_POST['cgst_amount'] ?? 0);
            $sgst_amount = floatval($_POST['sgst_amount'] ?? 0);
            $igst_amount = floatval($_POST['igst_amount'] ?? 0);
            $discount_amount = floatval($_POST['discount'] ?? 0);
            $transportation_charge = floatval($_POST['transportation_charge'] ?? 0);
            $round_off = floatval($_POST['round_off'] ?? 0);

            $grand_total = $total_amount - $discount_amount + $transportation_charge + $round_off;

            // Insert udhar transaction (schema-aware: some DBs don't have user_id/category columns)
            $columns = [
                'customer_id',
                'bill_no',
                'transaction_date',
                'due_date',
                'amount',
                'cgst_amount',
                'sgst_amount',
                'igst_amount',
                'discount',
                'discount_type',
                'round_off',
                'transportation_charge',
                'description',
                'notes',
                'status'
            ];

            $placeholders = array_fill(0, count($columns), '?');
            $types = "i"; // customer_id
            $params = [$customer_id];

            // bill_no can be string (BILL-...) or int depending on schema
            if (is_int($bill_no)) {
                $types .= "i";
            } else {
                $types .= "s";
            }
            $params[] = $bill_no;

            // transaction_date, due_date
            $types .= "ss";
            $params[] = $transaction_date;
            $params[] = $due_date;

            // amount, cgst_amount, sgst_amount, igst_amount, discount, round_off, transportation_charge
            $types .= "ddddddd";
            $params[] = $total_amount;
            $params[] = $cgst_amount;
            $params[] = $sgst_amount;
            $params[] = $igst_amount;
            $params[] = $discount_amount;
            $params[] = $round_off;
            $params[] = $transportation_charge;

            // discount_type, description, notes, status
            $types .= "ssss";
            $params[] = $discount_type;
            $params[] = $description;
            $params[] = $notes;
            $params[] = 'pending';

            if (tableColumnExists($conn, 'udhar_transactions', 'user_id')) {
                $columns[] = 'user_id';
                $placeholders[] = '?';
                $types .= 'i';
                $params[] = (int)$_SESSION['user_id'];
            }

            if (tableColumnExists($conn, 'udhar_transactions', 'category')) {
                $columns[] = 'category';
                $placeholders[] = '?';
                $types .= 's';
                $params[] = $category;
            }

            $sql = "INSERT INTO udhar_transactions (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $placeholders) . ")";
            $stmt = $conn->prepare($sql);
            mysqliBindParamsDynamic($stmt, $types, $params);

            if (!$stmt->execute()) {
                throw new Exception("Error creating udhar transaction: " . $stmt->error);
            }

            $udhar_id = $stmt->insert_id;
            $stmt->close();

            // Insert udhar items
            if (isset($_POST['items'])) {
                foreach ($_POST['items'] as $index => $item) {
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

                    $stmt = $conn->prepare("INSERT INTO udhar_items 
                        (udhar_id, item_id, item_name, hsn_code, quantity, unit_price, 
                         cgst_rate, sgst_rate, igst_rate, cgst_amount, sgst_amount, 
                         igst_amount, total_amount) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
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
                        throw new Exception("Error adding item: " . $stmt->error);
                    }
                    $stmt->close();
                }
            }

            $conn->commit();

            // Redirect to bill print page
            setMessage("Bill saved! Bill No: $bill_no", "success");
            header("Location: print_bill.php?id=$udhar_id");
            exit();
        } catch (Exception $e) {
            $conn->rollback();
            setMessage("Something went wrong: " . $e->getMessage(), "danger");
        }
    } else {
        setMessage(implode("<br>", $errors), "danger");
    }
}

if (isset($_POST['update_udhar'])) {
    // Update udhar entry
    $id = intval($_POST['udhar_id']);
    $due_date = sanitizeInput($_POST['due_date']);
    $notes = sanitizeInput($_POST['notes']);
    $status = sanitizeInput($_POST['status']);

    $stmt = $conn->prepare("UPDATE udhar_transactions SET due_date = ?, notes = ?, status = ?, updated_at = NOW() WHERE id = ?");
    $stmt->bind_param("sssi", $due_date, $notes, $status, $id);

    if ($stmt->execute()) {
        setMessage("Udhar entry updated successfully!", "success");
        header("Location: udhar.php?action=view&id=$id");
        exit();
    } else {
        setMessage("Error updating udhar entry: " . $stmt->error, "danger");
    }
    $stmt->close();
}

if (isset($_POST['delete_udhar'])) {
    // Delete udhar entry
    $id = intval($_POST['udhar_id']);

    $conn->begin_transaction();

    try {
        // First, delete all udhar items
        $stmt = $conn->prepare("DELETE FROM udhar_items WHERE udhar_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();

        // Then delete the udhar transaction
        $stmt = $conn->prepare("
                DELETE FROM udhar_transactions 
                WHERE id = ? 
                AND customer_id IN (
                    SELECT id FROM customers WHERE user_id = ?
                )
            ");
        $stmt->bind_param("ii", $id, $_SESSION['user_id']);

        if ($stmt->execute()) {
            $conn->commit();
            setMessage("Udhar entry deleted successfully!", "success");
        } else {
            throw new Exception("Error deleting udhar entry: " . $stmt->error);
        }
        $stmt->close();
    } catch (Exception $e) {
        $conn->rollback();
        setMessage("Error: " . $e->getMessage(), "danger");
    }

    header("Location: udhar.php");
    exit();
}



// Get udhar entry for edit/view
$udhar = null;
$udhar_items = [];
if ($udhar_id > 0 && ($action == 'edit' || $action == 'view' || $action == 'print')) {
    $stmt = $conn->prepare("
        SELECT ut.*, c.name as customer_name, c.mobile as customer_mobile 
        FROM udhar_transactions ut 
        JOIN customers c ON ut.customer_id = c.id 
        WHERE ut.id = ? AND c.user_id = ?
    ");
    $stmt->bind_param("ii", $udhar_id, $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $udhar = $result->fetch_assoc();
    $stmt->close();

    if ($udhar) {
        // Get udhar items
        $stmt = $conn->prepare("SELECT * FROM udhar_items WHERE udhar_id = ?");
        $stmt->bind_param("i", $udhar_id);
        $stmt->execute();
        $udhar_items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        $udhar_items = array_values(array_filter($udhar_items, function ($item) {
            $qty = isset($item['quantity']) ? (float) $item['quantity'] : 0;
            $total = isset($item['total_amount']) ? (float) $item['total_amount'] : 0;
            return $qty > 0 && $total > 0;
        }));

        $calculated_total_amount = 0.0;
        foreach ($udhar_items as $item) {
            $calculated_total_amount += (float) ($item['total_amount'] ?? 0);
        }

        if (count($udhar_items) === 0) {
            $udhar['total_amount'] = 0.00;
            $udhar['grand_total'] = 0.00;
            $udhar['amount'] = 0.00;
            $udhar['discount'] = 0.00;
            $udhar['transportation_charge'] = 0.00;
            $udhar['round_off'] = 0.00;

            $stmt = $conn->prepare("UPDATE udhar_transactions SET total_amount = 0.00, grand_total = 0.00, amount = 0.00, cgst_amount = 0.00, sgst_amount = 0.00, igst_amount = 0.00, discount = 0.00, transportation_charge = 0.00, round_off = 0.00 WHERE id = ?");
            $stmt->bind_param("i", $udhar_id);
            $stmt->execute();
            $stmt->close();
        } else {
            $discount_amount = (float) ($udhar['discount'] ?? 0);
            $transportation_charge = (float) ($udhar['transportation_charge'] ?? 0);
            $round_off = (float) ($udhar['round_off'] ?? 0);

            $udhar['total_amount'] = round($calculated_total_amount, 2);
            $udhar['grand_total'] = round($calculated_total_amount - $discount_amount + $transportation_charge + $round_off, 2);
            $udhar['amount'] = $udhar['grand_total'];

            $stmt = $conn->prepare("UPDATE udhar_transactions SET total_amount = ?, grand_total = ?, amount = ? WHERE id = ?");
            $stmt->bind_param("dddi", $udhar['total_amount'], $udhar['grand_total'], $udhar['amount'], $udhar_id);
            $stmt->execute();
            $stmt->close();
        }
    }
}

// Get customers for dropdown
$customers = [];
$stmt = $conn->prepare("SELECT id, name, mobile FROM customers WHERE user_id = ? AND status = 'active' ORDER BY name");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$customers = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get items for dropdown
$items = [];
$stmt = $conn->prepare("SELECT id, item_name, item_code, hsn_code, price, cgst_rate, sgst_rate, igst_rate, unit FROM items WHERE user_id = ? AND status = 'active' ORDER BY item_name");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get all udhar entries for listing
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? sanitizeInput($_GET['status']) : '';
$category_filter = isset($_GET['category']) ? sanitizeInput($_GET['category']) : '';

$where_clause = "WHERE c.user_id = " . $_SESSION['user_id'];
$params = [];

if (!empty($search)) {
    $where_clause .= " AND (ut.description LIKE ? OR ut.bill_no LIKE ? OR c.name LIKE ?)";
    $search_term = "%$search%";
    $params = array_fill(0, 3, $search_term);
}

if (!empty($status_filter)) {
    if ($status_filter == 'overdue') {
        // Filter for overdue bills: due_date is in the past and status is not 'paid'
        $where_clause .= " AND ut.due_date < CURDATE() AND ut.status IN ('pending', 'partially_paid')";
    } elseif (in_array($status_filter, ['pending', 'partially_paid', 'paid'])) {
        $where_clause .= " AND ut.status = ?";
        $params[] = $status_filter;
    }
}

if (!empty($category_filter)) {
    $where_clause .= " AND ut.category = ?";
    $params[] = $category_filter;
}

// Get total udhar count
$count_query = "SELECT COUNT(*) as total FROM udhar_transactions ut JOIN customers c ON ut.customer_id = c.id $where_clause";
$count_stmt = $conn->prepare($count_query);
if (!empty($params)) {
    $types = str_repeat('s', count($params));
    $count_stmt->bind_param($types, ...$params);
}
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$total_udhar = $count_result->fetch_assoc()['total'];
$count_stmt->close();

// Pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;
$total_pages = ceil($total_udhar / $limit);

// Get udhar entries with pagination
$order_by = isset($_GET['order_by']) ? sanitizeInput($_GET['order_by']) : 'ut.transaction_date';
$order_dir = isset($_GET['order_dir']) ? sanitizeInput($_GET['order_dir']) : 'DESC';

$allowed_columns = ['ut.transaction_date', 'c.name', 'ut.amount', 'ut.status', 'ut.bill_no'];
$order_by = in_array($order_by, $allowed_columns) ? $order_by : 'ut.transaction_date';
$order_dir = in_array(strtoupper($order_dir), ['ASC', 'DESC']) ? strtoupper($order_dir) : 'DESC';

$query = "SELECT ut.*, c.name as customer_name, c.mobile as customer_mobile FROM udhar_transactions ut JOIN customers c ON ut.customer_id = c.id $where_clause ORDER BY $order_by $order_dir LIMIT ? OFFSET ?";
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
$udhar_list = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

if (!empty($udhar_list)) {
    $updateZeroStmt = $conn->prepare("UPDATE udhar_transactions SET total_amount = 0.00, grand_total = 0.00, amount = 0.00, cgst_amount = 0.00, sgst_amount = 0.00, igst_amount = 0.00, discount = 0.00, transportation_charge = 0.00, round_off = 0.00 WHERE id = ?");
    $updateTotalsStmt = $conn->prepare("UPDATE udhar_transactions SET total_amount = ?, grand_total = ?, amount = ? WHERE id = ?");

    $ids = array_map(function ($row) {
        return (int) ($row['id'] ?? 0);
    }, $udhar_list);
    $ids = array_values(array_filter($ids, function ($id) {
        return $id > 0;
    }));

    if (!empty($ids)) {
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $types = str_repeat('i', count($ids));

        $sql = "SELECT udhar_id, SUM(total_amount) AS items_total, COUNT(*) AS items_count FROM udhar_items WHERE udhar_id IN ($placeholders) AND quantity > 0 AND total_amount > 0 GROUP BY udhar_id";
        $sumStmt = $conn->prepare($sql);
        mysqliBindParamsDynamic($sumStmt, $types, $ids);
        $sumStmt->execute();
        $rows = $sumStmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $sumStmt->close();

        $byUdharId = [];
        foreach ($rows as $row) {
            $byUdharId[(int) $row['udhar_id']] = [
                'items_total' => (float) ($row['items_total'] ?? 0),
                'items_count' => (int) ($row['items_count'] ?? 0),
            ];
        }

        foreach ($udhar_list as &$entry) {
            $id = (int) ($entry['id'] ?? 0);
            $items_total = $byUdharId[$id]['items_total'] ?? 0.0;
            $items_count = $byUdharId[$id]['items_count'] ?? 0;

            if ($items_count === 0) {
                $entry['amount'] = 0.00;
                $entry['total_amount'] = 0.00;
                $entry['grand_total'] = 0.00;

                if ($updateZeroStmt) {
                    $updateZeroStmt->bind_param('i', $id);
                    $updateZeroStmt->execute();
                }
            } else {
                $discount_amount = (float) ($entry['discount'] ?? 0);
                $transportation_charge = (float) ($entry['transportation_charge'] ?? 0);
                $round_off = (float) ($entry['round_off'] ?? 0);

                $entry['total_amount'] = round($items_total, 2);
                $entry['grand_total'] = round($items_total - $discount_amount + $transportation_charge + $round_off, 2);
                $entry['amount'] = $entry['grand_total'];

                if ($updateTotalsStmt) {
                    $updateTotalsStmt->bind_param('dddi', $entry['total_amount'], $entry['grand_total'], $entry['amount'], $id);
                    $updateTotalsStmt->execute();
                }
            }
        }
        unset($entry);

        $udhar_list = array_values(array_filter($udhar_list, function ($entry) {
            return (float) ($entry['grand_total'] ?? 0) > 0;
        }));
    }

    if ($updateZeroStmt) {
        $updateZeroStmt->close();
    }
    if ($updateTotalsStmt) {
        $updateTotalsStmt->close();
    }
}

$page_title = "Udhar Entry Management";
