<?php
require_once '../config/database.php';

$conn = getDBConnection();

echo "<h2>Fixing Zero Amount Records</h2>";

// Get some sample items to use
$items_result = $conn->query("SELECT id, item_name, price FROM items WHERE user_id = 5 LIMIT 10");
$sample_items = [];
while ($row = $items_result->fetch_assoc()) {
    $sample_items[] = $row;
}

if (empty($sample_items)) {
    echo "<p>No items found. Please add items first.</p>";
    exit;
}

// Get transactions with zero amounts and no items
$result = $conn->query("SELECT ut.id, ut.customer_id FROM udhar_transactions ut JOIN customers c ON ut.customer_id = c.id WHERE c.user_id = 5 AND ut.amount = 0");
$zero_amount_transactions = $result->fetch_all(MYSQLI_ASSOC);

$updated_count = 0;

foreach ($zero_amount_transactions as $transaction) {
    $udhar_id = $transaction['id'];
    
    // Add 1-2 random items for this transaction
    $num_items = rand(1, 2);
    $total_amount = 0;
    
    for ($i = 0; $i < $num_items; $i++) {
        $item = $sample_items[array_rand($sample_items)];
        $quantity = rand(1, 3);
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

echo "<h3>Fixed $updated_count transactions</h3>";

$conn->close();
echo "<p><a href='../udhar.php'>Go to Udhar page</a> to see the results</p>";
?>
