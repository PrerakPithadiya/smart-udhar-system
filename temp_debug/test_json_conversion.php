<?php
require_once '../config/database.php';

$conn = getDBConnection();

// Test the JSON conversion for bill revision
$udhar_id = 55; // Use a known bill ID

echo "<h2>Testing JSON Conversion for Bill Revision: $udhar_id</h2>";

// Get the raw data using GROUP_CONCAT
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

if ($bill) {
    echo "<h3>Raw Data:</h3>";
    echo "<pre>" . htmlspecialchars($bill['items_raw']) . "</pre>";
    
    // Convert to JSON
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
    
    echo "<h3>Converted JSON:</h3>";
    echo "<pre>" . htmlspecialchars($items_json) . "</pre>";
    
    // Test if JSON is valid
    if (json_decode($items_json) !== null) {
        echo "<h3>✅ JSON is valid!</h3>";
    } else {
        echo "<h3>❌ JSON is invalid!</h3>";
    }
    
    // Test inserting into bill_revisions
    echo "<h3>Testing Bill Revision Insert:</h3>";
    try {
        $revision_num = 1;
        $test_stmt = $conn->prepare("
            INSERT INTO bill_revisions (
                udhar_id, revision_number, user_id, customer_id, bill_no,
                transaction_date, due_date, total_amount, cgst_amount, sgst_amount,
                igst_amount, discount, discount_type, round_off, grand_total,
                description, notes, status, category, items_data, change_reason, changed_by
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
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
        $category = $bill['category'] ?? '';
        $change_reason = 'Test revision';
        $changed_by = 5;
        
        $test_stmt->bind_param(
            "iiissssdddddssdssssssi",
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
            $category,
            $items_json,
            $change_reason,
            $changed_by
        );
        
        if ($test_stmt->execute()) {
            echo "✅ Bill revision insert successful!";
            $test_stmt->close();
            
            // Clean up test record
            $delete_stmt = $conn->prepare("DELETE FROM bill_revisions WHERE udhar_id = ? AND change_reason = 'Test revision'");
            $delete_stmt->bind_param("i", $udhar_id);
            $delete_stmt->execute();
            $delete_stmt->close();
        } else {
            echo "❌ Bill revision insert failed: " . $test_stmt->error;
        }
    } catch (Exception $e) {
        echo "❌ Error: " . $e->getMessage();
    }
} else {
    echo "<p>Bill not found</p>";
}

$conn->close();
echo "<p><a href='../edit_bill.php?id=$udhar_id'>Test Edit Bill Page</a></p>";
?>
