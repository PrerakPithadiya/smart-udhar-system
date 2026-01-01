<?php
// File: api/search_customers.php
header('Content-Type: application/json');
require_once '../config/database.php';
require_once '../includes/functions.php'; // Contains requireLogin()

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

$conn = getDBConnection();

// Get search query
$query = isset($_GET['q']) ? trim($_GET['q']) : '';

if (strlen($query) < 2 && strlen($query) > 0) {
    echo json_encode(['suggestions' => []]);
    exit;
}

// Search in customers table for current user
$searchTerm = strlen($query) > 0 ? "%{$query}%" : "%";
$limit = strlen($query) > 0 ? 15 : 10; // Show fewer results when showing all

$stmt = $conn->prepare("
    SELECT 
        c.id,
        c.name,
        c.mobile,
        c.email,
        c.address,
        COALESCE(
            (SELECT SUM(remaining_amount) 
             FROM udhar_transactions 
             WHERE customer_id = c.id AND status IN ('pending', 'partially_paid')
            ), 0
        ) as balance
    FROM customers c
    WHERE c.user_id = ? 
      AND (c.name LIKE ? OR c.mobile LIKE ?)
    ORDER BY c.name
    LIMIT ?
");

$stmt->bind_param("issi", $_SESSION['user_id'], $searchTerm, $searchTerm, $limit);
$stmt->execute();
$result = $stmt->get_result();

$suggestions = [];
while ($row = $result->fetch_assoc()) {
    $suggestions[] = [
        'id' => $row['id'],
        'name' => $row['name'],
        'mobile' => $row['mobile'],
        'email' => $row['email'],
        'address' => $row['address'],
        'balance' => $row['balance']
    ];
}

echo json_encode(['suggestions' => $suggestions]);

$stmt->close();
$conn->close();
