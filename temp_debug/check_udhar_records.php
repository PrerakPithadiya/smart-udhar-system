<?php
require_once 'config/database.php';

$conn = getDBConnection();

// Check total records in udhar_transactions
echo "<h2>Total udhar_transactions records:</h2>";
$result = $conn->query("SELECT COUNT(*) as count FROM udhar_transactions");
$row = $result->fetch_assoc();
echo "<p>Total records: " . $row['count'] . "</p>";

// Check records with user_id filter (if applicable)
echo "<h2>Records by user (if user_id column exists):h2>";
if ($conn->query("DESCRIBE udhar_transactions")->fetch_assoc()['Field'] == 'user_id') {
    $result = $conn->query("SELECT user_id, COUNT(*) as count FROM udhar_transactions GROUP BY user_id");
    while ($row = $result->fetch_assoc()) {
        echo "<p>User ID " . $row['user_id'] . ": " . $row['count'] . " records</p>";
    }
} else {
    echo "<p>No user_id column in udhar_transactions</p>";
}

// Check the latest 5 records
echo "<h2>Latest 5 records:</h2>";
$result = $conn->query("SELECT * FROM udhar_transactions ORDER BY id DESC LIMIT 5");
if ($result->num_rows > 0) {
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Customer ID</th><th>Amount</th><th>Date</th><th>Status</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['customer_id'] . "</td>";
        echo "<td>" . $row['amount'] . "</td>";
        echo "<td>" . $row['transaction_date'] . "</td>";
        echo "<td>" . $row['status'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No records found</p>";
}

// Check if there are customers linked to these transactions
echo "<h2>Customer information:</h2>";
$result = $conn->query("SELECT ut.id, ut.customer_id, c.name as customer_name FROM udhar_transactions ut LEFT JOIN customers c ON ut.customer_id = c.id ORDER BY ut.id DESC LIMIT 5");
if ($result->num_rows > 0) {
    echo "<table border='1'>";
    echo "<tr><th>Transaction ID</th><th>Customer ID</th><th>Customer Name</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['customer_id'] . "</td>";
        echo "<td>" . ($row['customer_name'] ?? 'NOT FOUND') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

$conn->close();
?>
