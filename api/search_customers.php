<?php
require_once '../config/database.php';

// Set content type to JSON
header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Get search query
$query = isset($_GET['q']) ? sanitizeInput($_GET['q']) : '';

if (empty($query)) {
    echo json_encode(['suggestions' => []]);
    exit;
}

try {
    $conn = getDBConnection();
    
    // Search for customers by name, mobile, or email (case-insensitive)
    $stmt = $conn->prepare("
        SELECT id, name, mobile, email, balance 
        FROM customers 
        WHERE user_id = ? 
        AND (name LIKE ? OR mobile LIKE ? OR email LIKE ?)
        ORDER BY name ASC
        LIMIT 10
    ");
    
    $search_param = "%$query%";
    $stmt->bind_param("isss", $_SESSION['user_id'], $search_param, $search_param, $search_param);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $suggestions = [];
    while ($row = $result->fetch_assoc()) {
        $suggestions[] = [
            'id' => $row['id'],
            'name' => $row['name'],
            'mobile' => $row['mobile'],
            'email' => $row['email'],
            'balance' => $row['balance']
        ];
    }
    
    echo json_encode(['suggestions' => $suggestions]);
} catch (Exception $e) {
    echo json_encode(['error' => 'Database error occurred']);
}
?>