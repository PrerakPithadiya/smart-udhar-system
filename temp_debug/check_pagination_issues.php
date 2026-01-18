<?php
require_once '../config/database.php';

$conn = getDBConnection();

// Get total count
$result = $conn->query("SELECT COUNT(*) as total FROM udhar_transactions ut JOIN customers c ON ut.customer_id = c.id WHERE c.user_id = 5");
$total = $result->fetch_assoc()['total'];
$limit = 10;
$total_pages = ceil($total / $limit);

echo "<h2>Pagination Analysis</h2>";
echo "<p>Total records: $total</p>";
echo "<p>Limit per page: $limit</p>";
echo "<p>Calculated total pages: $total_pages</p>";

// Check what's on each page
for ($page = 1; $page <= $total_pages; $page++) {
    $offset = ($page - 1) * $limit;
    $query = "SELECT ut.*, c.name as customer_name FROM udhar_transactions ut JOIN customers c ON ut.customer_id = c.id WHERE c.user_id = 5 ORDER BY ut.transaction_date DESC LIMIT $limit OFFSET $offset";
    $result = $conn->query($query);
    $count = $result->num_rows;
    
    echo "<h3>Page $page ($count records)</h3>";
    if ($count > 0) {
        echo "<table border='1'>";
        echo "<tr><th>ID</th><th>Customer</th><th>Amount</th><th>Date</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td>" . $row['customer_name'] . "</td>";
            echo "<td>" . $row['amount'] . "</td>";
            echo "<td>" . $row['transaction_date'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No records on this page</p>";
    }
}

// Check for records with zero amounts
echo "<h2>Records with zero amounts:</h2>";
$result = $conn->query("SELECT ut.*, c.name as customer_name FROM udhar_transactions ut JOIN customers c ON ut.customer_id = c.id WHERE c.user_id = 5 AND ut.amount = 0 ORDER BY ut.transaction_date DESC");
if ($result->num_rows > 0) {
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Customer</th><th>Amount</th><th>Has Items</th></tr>";
    while ($row = $result->fetch_assoc()) {
        // Check if this transaction has items
        $items_result = $conn->query("SELECT COUNT(*) as count FROM udhar_items WHERE udhar_id = " . $row['id']);
        $has_items = $items_result->fetch_assoc()['count'] > 0;
        
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['customer_name'] . "</td>";
        echo "<td>" . $row['amount'] . "</td>";
        echo "<td>" . ($has_items ? 'Yes' : 'No') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No records with zero amounts</p>";
}

$conn->close();
?>
