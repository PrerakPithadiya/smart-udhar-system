<?php
require_once 'config/database.php';

$conn = getDBConnection();

// Check total records in udhar_items
echo "<h2>Total udhar_items records:</h2>";
$result = $conn->query("SELECT COUNT(*) as count FROM udhar_items");
$row = $result->fetch_assoc();
echo "<p>Total records: " . $row['count'] . "</p>";

// Check items by udhar_id for the latest transactions
echo "<h2>Items for latest 5 transactions:</h2>";
$result = $conn->query("SELECT udhar_id, COUNT(*) as item_count, SUM(total_amount) as total FROM udhar_items WHERE udhar_id >= 51 GROUP BY udhar_id ORDER BY udhar_id DESC");
if ($result->num_rows > 0) {
    echo "<table border='1'>";
    echo "<tr><th>Udhar ID</th><th>Item Count</th><th>Total Amount</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['udhar_id'] . "</td>";
        echo "<td>" . $row['item_count'] . "</td>";
        echo "<td>" . $row['total'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No items found for recent transactions</p>";
}

// Check specific transaction details
echo "<h2>Sample transaction with items:</h2>";
$result = $conn->query("SELECT ui.*, ut.amount as transaction_amount FROM udhar_items ui JOIN udhar_transactions ut ON ui.udhar_id = ut.id WHERE ui.udhar_id = 55 LIMIT 5");
if ($result->num_rows > 0) {
    echo "<table border='1'>";
    echo "<tr><th>Item Name</th><th>Quantity</th><th>Unit Price</th><th>Total Amount</th><th>Transaction Amount</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['item_name'] . "</td>";
        echo "<td>" . $row['quantity'] . "</td>";
        echo "<td>" . $row['unit_price'] . "</td>";
        echo "<td>" . $row['total_amount'] . "</td>";
        echo "<td>" . $row['transaction_amount'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No items found for transaction ID 55</p>";
}

$conn->close();
?>
