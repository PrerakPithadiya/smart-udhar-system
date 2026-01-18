<?php
require_once '../config/database.php';

$conn = getDBConnection();

// Test the GROUP_CONCAT query on a sample bill
$udhar_id = 55; // Use a known bill ID

echo "<h2>Testing GROUP_CONCAT query for bill ID: $udhar_id</h2>";

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
           ) FROM udhar_items ui WHERE ui.udhar_id = ut.id) as items_data
    FROM udhar_transactions ut
    WHERE ut.id = ?
");

$stmt->bind_param("i", $udhar_id);
$stmt->execute();
$bill = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($bill) {
    echo "<h3>Bill Data:</h3>";
    echo "<p>Bill ID: " . $bill['id'] . "</p>";
    echo "<p>Customer ID: " . $bill['customer_id'] . "</p>";
    echo "<p>Amount: " . $bill['amount'] . "</p>";
    
    echo "<h3>Items Data:</h3>";
    if (!empty($bill['items_data'])) {
        $items = explode(';;', $bill['items_data']);
        echo "<table border='1'>";
        echo "<tr><th>Item Name</th><th>Quantity</th><th>Unit Price</th><th>Total</th></tr>";
        
        foreach ($items as $item_data) {
            $fields = explode('|', $item_data);
            $item_array = [];
            foreach ($fields as $field) {
                if (strpos($field, ':') !== false) {
                    list($key, $value) = explode(':', $field, 2);
                    $item_array[$key] = $value;
                }
            }
            
            if (!empty($item_array['item_name'])) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($item_array['item_name']) . "</td>";
                echo "<td>" . $item_array['quantity'] . "</td>";
                echo "<td>" . $item_array['unit_price'] . "</td>";
                echo "<td>" . $item_array['total_amount'] . "</td>";
                echo "</tr>";
            }
        }
        echo "</table>";
    } else {
        echo "<p>No items found</p>";
    }
} else {
    echo "<p>Bill not found</p>";
}

$conn->close();
echo "<p><a href='../edit_bill.php?id=$udhar_id'>Test Edit Bill Page</a></p>";
?>
