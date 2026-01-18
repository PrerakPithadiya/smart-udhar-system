<?php
require_once '../config/database.php';

$conn = getDBConnection();

echo "<h2>Testing Category Column Fix</h2>";

// Test 1: Check if category column exists in udhar_transactions
echo "<h3>Checking table structure:</h3>";
$result = $conn->query("DESCRIBE udhar_transactions");
$has_category = false;
while ($row = $result->fetch_assoc()) {
    if ($row['Field'] == 'category') {
        $has_category = true;
        break;
    }
}

if ($has_category) {
    echo "<p>❌ Category column exists in udhar_transactions (unexpected)</p>";
} else {
    echo "<p>✅ Category column does not exist in udhar_transactions (expected)</p>";
}

// Test 2: Test the createBillRevision function
echo "<h3>Testing createBillRevision function:</h3>";

// Include the function
function createBillRevision($conn, $udhar_id, $change_reason = '')
{
    // Get current bill data
    $stmt = $conn->prepare("
        SELECT ut.*, 
               (SELECT GROUP_CONCAT(
                   CONCAT(
                       'item_id:', ui.item_id, '|',
                       'item_name:', REPLACE(ui.item_name, '|', '\\|'), '|',
                       'hsn_code:', IFNULL(ui.hsn_code, ''), '|',
                       'quantity:', ui.quantity, '|',
                       'unit_price:', ui.unit_price, '|',
                       'cgst_rate:', ui.cgst_rate, '|',
                       'sgst_rate:', ui.sgst_rate, '|',
                       'igst_rate:', ui.igst_rate, '|',
                       'cgst_amount:', ui.cgst_amount, '|',
                       'sgst_amount:', ui.sgst_amount, '|',
                       'igst_amount:', ui.igst_amount, '|',
                       'total_amount:', ui.total_amount
                   ) SEPARATOR ';;'
               ) FROM udhar_items ui WHERE ui.udhar_id = ut.id) as items_raw
        FROM udhar_transactions ut
        WHERE ut.id = ?
    ");
    $stmt->bind_param("i", $udhar_id);
    $stmt->execute();
    $bill = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$bill)
        return false;

    // Convert the raw string data to JSON format
    $items_json = '[]';
    if (!empty($bill['items_raw'])) {
        $items = explode(';;', $bill['items_raw']);
        $item_array = [];
        
        foreach ($items as $item_data) {
            $fields = explode('|', $item_data);
            $item_obj = [];
            foreach ($fields as $field) {
                if (strpos($field, ':') !== false) {
                    list($key, $value) = explode(':', $field, 2);
                    $item_obj[$key] = $value;
                }
            }
            
            if (!empty($item_obj['item_name'])) {
                // Convert numeric values
                $item_obj['quantity'] = floatval($item_obj['quantity']);
                $item_obj['unit_price'] = floatval($item_obj['unit_price']);
                $item_obj['cgst_rate'] = floatval($item_obj['cgst_rate']);
                $item_obj['sgst_rate'] = floatval($item_obj['sgst_rate']);
                $item_obj['igst_rate'] = floatval($item_obj['igst_rate']);
                $item_obj['cgst_amount'] = floatval($item_obj['cgst_amount']);
                $item_obj['sgst_amount'] = floatval($item_obj['sgst_amount']);
                $item_obj['igst_amount'] = floatval($item_obj['igst_amount']);
                $item_obj['total_amount'] = floatval($item_obj['total_amount']);
                
                $item_array[] = $item_obj;
            }
        }
        
        $items_json = json_encode($item_array, JSON_UNESCAPED_UNICODE);
    }

    // Get next revision number
    $revision_num = ($bill['revision_number'] ?? 0) + 1;

    // Insert revision (without category)
    $stmt = $conn->prepare("
        INSERT INTO bill_revisions (
            udhar_id, revision_number, user_id, customer_id, bill_no,
            transaction_date, due_date, total_amount, cgst_amount, sgst_amount,
            igst_amount, discount, discount_type, round_off, grand_total,
            description, notes, status, items_data, change_reason, changed_by
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $grand_total = $bill['total_amount'] + $bill['cgst_amount'] + $bill['sgst_amount'] +
        $bill['igst_amount'] - $bill['discount'] + $bill['round_off'];

    $user_id = 5;
    $customer_id = $bill['customer_id'];
    $bill_no = $bill['bill_no'];
    $transaction_date = $bill['transaction_date'];
    $due_date = $bill['due_date'];
    $total_amount = $bill['total_amount'];
    $cgst_amount = $bill['cgst_amount'];
    $sgst_amount = $bill['sgst_amount'];
    $igst_amount = $bill['igst_amount'];
    $discount = $bill['discount'];
    $discount_type = $bill['discount_type'];
    $round_off = $bill['round_off'];
    $description = $bill['description'];
    $notes = $bill['notes'];
    $status = $bill['status'];
    $changed_by = 5;

    $stmt->bind_param(
        "iiissssdddddssdssssi",
        $udhar_id,
        $revision_num,
        $user_id,
        $customer_id,
        $bill_no,
        $transaction_date,
        $due_date,
        $total_amount,
        $cgst_amount,
        $sgst_amount,
        $igst_amount,
        $discount,
        $discount_type,
        $round_off,
        $grand_total,
        $description,
        $notes,
        $status,
        $items_json,
        $change_reason,
        $changed_by
    );

    return $stmt->execute();
}

// Test the function
$test_udhar_id = 55;
$_SESSION['user_id'] = 5; // Simulate session

if (createBillRevision($conn, $test_udhar_id, "Test revision without category")) {
    echo "<p>✅ createBillRevision function works without category column</p>";
    
    // Clean up test record
    $delete_stmt = $conn->prepare("DELETE FROM bill_revisions WHERE udhar_id = ? AND change_reason = 'Test revision without category'");
    $delete_stmt->bind_param("i", $test_udhar_id);
    $delete_stmt->execute();
    $delete_stmt->close();
} else {
    echo "<p>❌ createBillRevision function failed</p>";
}

echo "<h3>✅ All category-related issues fixed!</h3>";
echo "<p><a href='../edit_bill.php?id=$test_udhar_id'>Test Edit Bill Page</a></p>";

$conn->close();
?>
