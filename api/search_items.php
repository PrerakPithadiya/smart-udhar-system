<?php
// File: api/search_items.php
header('Content-Type: application/json');
require_once '../config/database.php';
// Functions are included in config/database.php if it follows the pattern of search_customers.php
// But search_customers.php shows require_once '../includes/functions.php'

// Check session
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

$conn = getDBConnection();

// Get search query
$query = isset($_GET['q']) ? trim($_GET['q']) : '';

if (strlen($query) < 1 && strlen($query) > 0) {
    echo json_encode(['suggestions' => []]);
    exit;
}

// Search in items table for current user
$searchTerm = strlen($query) > 0 ? "%{$query}%" : "%";
$limit = 15;

$stmt = $conn->prepare("
    SELECT 
        id, 
        item_name, 
        item_code, 
        hsn_code, 
        price, 
        cgst_rate, 
        sgst_rate, 
        igst_rate, 
        unit 
    FROM items 
    WHERE user_id = ? 
      AND status = 'active'
      AND (item_name LIKE ? OR item_code LIKE ? OR hsn_code LIKE ?)
    ORDER BY item_name
    LIMIT ?
");

$stmt->bind_param("isssi", $_SESSION['user_id'], $searchTerm, $searchTerm, $searchTerm, $limit);
$stmt->execute();
$result = $stmt->get_result();

$suggestions = [];
while ($row = $result->fetch_assoc()) {
    $suggestions[] = [
        'id' => $row['id'],
        'name' => $row['item_name'],
        'item_name' => $row['item_name'],
        'item_code' => $row['item_code'],
        'hsn_code' => $row['hsn_code'],
        'price' => $row['price'],
        'cgst_rate' => $row['cgst_rate'],
        'sgst_rate' => $row['sgst_rate'],
        'igst_rate' => $row['igst_rate'],
        'unit' => $row['unit']
    ];
}

echo json_encode(['suggestions' => $suggestions]);

$stmt->close();
$conn->close();
