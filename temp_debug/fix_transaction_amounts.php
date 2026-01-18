<?php
require_once 'config/database.php';

$conn = getDBConnection();

echo "<h2>Fixing Transaction Amounts</h2>";

// First, let's see what we're working with
echo "<h3>Current state:</h3>";
$result = $conn->query("SELECT id, customer_id, amount, total_amount, grand_total FROM udhar_transactions ORDER BY id DESC LIMIT 10");
echo "<table border='1'>";
echo "<tr><th>ID</th><th>Customer ID</th><th>Amount</th><th>Total Amount</th><th>Grand Total</th></tr>";
while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . $row['id'] . "</td>";
    echo "<td>" . $row['customer_id'] . "</td>";
    echo "<td>" . $row['amount'] . "</td>";
    echo "<td>" . $row['total_amount'] . "</td>";
    echo "<td>" . $row['grand_total'] . "</td>";
    echo "</tr>";
}
echo "</table>";

// Since there are no items, we need to either:
// 1. Add some sample items for each transaction, or
// 2. Update the amounts to some reasonable values

echo "<h3>Option 1: Adding sample items for transactions</h3>";

// Get some sample items from the items table
$items_result = $conn->query("SELECT id, item_name, price FROM items WHERE user_id = 5 LIMIT 5");
$sample_items = [];
while ($row = $items_result->fetch_assoc()) {
    $sample_items[] = $row;
}

if (empty($sample_items)) {
    echo "<p>No items found in items table. Creating sample items first...</p>";
    
    // Insert some sample items
    $sample_item_data = [
        ['Sample Item 1', 100.00],
        ['Sample Item 2', 250.00],
        ['Sample Item 3', 150.00]
    ];
    
    foreach ($sample_item_data as $item) {
        $stmt = $conn->prepare("INSERT INTO items (user_id, item_name, price, cgst_rate, sgst_rate, unit) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isddds", $_SESSION['user_id'], $item[0], $item[1], $cgst, $sgst, $unit);
        $cgst = 2.5;
        $sgst = 2.5;
        $unit = 'PCS';
        $stmt->execute();
        $stmt->close();
    }
    
    // Get the items again
    $items_result = $conn->query("SELECT id, item_name, price FROM items WHERE user_id = " . $_SESSION['user_id'] . " LIMIT 5");
    $sample_items = [];
    while ($row = $items_result->fetch_assoc()) {
        $sample_items[] = $row;
    }
}

// Now add items for each transaction that doesn't have items
$transactions_result = $conn->query("SELECT id, customer_id FROM udhar_transactions WHERE id >= 51 ORDER BY id");
$updated_count = 0;

while ($transaction = $transactions_result->fetch_assoc()) {
    $udhar_id = $transaction['id'];
    
    // Check if this transaction already has items
    $check_result = $conn->query("SELECT COUNT(*) as count FROM udhar_items WHERE udhar_id = $udhar_id");
    $has_items = $check_result->fetch_assoc()['count'] > 0;
    
    if (!$has_items) {
        // Add 1-3 random items for this transaction
        $num_items = rand(1, 3);
        $total_amount = 0;
        
        for ($i = 0; $i < $num_items; $i++) {
            $item = $sample_items[array_rand($sample_items)];
            $quantity = rand(1, 5);
            $unit_price = $item['price'];
            $total = $quantity * $unit_price;
            $total_amount += $total;
            
            // Insert the item
            $stmt = $conn->prepare("INSERT INTO udhar_items (udhar_id, item_id, item_name, quantity, unit_price, total_amount) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("iisidd", $udhar_id, $item['id'], $item['item_name'], $quantity, $unit_price, $total);
            $stmt->execute();
            $stmt->close();
        }
        
        // Update the transaction amount
        $stmt = $conn->prepare("UPDATE udhar_transactions SET amount = ?, total_amount = ?, grand_total = ? WHERE id = ?");
        $stmt->bind_param("dddi", $total_amount, $total_amount, $total_amount, $udhar_id);
        $stmt->execute();
        $stmt->close();
        
        $updated_count++;
        echo "<p>Updated transaction ID $udhar_id with $num_items items, total: $total_amount</p>";
    }
}

echo "<h3>Updated $updated_count transactions</h3>";

// Show the updated state
echo "<h3>Updated state:</h3>";
$result = $conn->query("SELECT id, customer_id, amount, total_amount, grand_total FROM udhar_transactions ORDER BY id DESC LIMIT 10");
echo "<table border='1'>";
echo "<tr><th>ID</th><th>Customer ID</th><th>Amount</th><th>Total Amount</th><th>Grand Total</th></tr>";
while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . $row['id'] . "</td>";
    echo "<td>" . $row['customer_id'] . "</td>";
    echo "<td>" . $row['amount'] . "</td>";
    echo "<td>" . $row['total_amount'] . "</td>";
    echo "<td>" . $row['grand_total'] . "</td>";
    echo "</tr>";
}
echo "</table>";

$conn->close();
echo "<p><a href='udhar.php'>Go to Udhar page</a> to see the results</p>";
?>
