<?php
// Script to populate missing bill numbers for udhar_transactions with NULL bill_no
require_once 'config/database.php';

$conn = getDBConnection();

// Fetch all udhar_transactions with NULL bill_no
$sql = "SELECT id, customer_id, transaction_date FROM udhar_transactions WHERE bill_no IS NULL ORDER BY transaction_date, id";
$result = $conn->query($sql);

if (!$result) {
    die('Query failed: ' . $conn->error);
}

$prefix = 'BILL';
$last_bill_num = [];
$updated = 0;

while ($row = $result->fetch_assoc()) {
    $user_id = null;
    // Find user_id for this customer
    $stmt = $conn->prepare("SELECT user_id FROM customers WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $row['customer_id']);
    $stmt->execute();
    $stmt->bind_result($user_id);
    $stmt->fetch();
    $stmt->close();
    if (!$user_id) continue;

    $date = $row['transaction_date'];
    $year = date('Y', strtotime($date));
    $month = date('m', strtotime($date));
    $key = $user_id . '-' . $year . $month;
    if (!isset($last_bill_num[$key])) {
        // Get last bill_no for this user, year, month
        $like_pattern = $prefix . '-' . $year . $month . '-%';
        $stmt = $conn->prepare("SELECT bill_no FROM udhar_transactions WHERE bill_no LIKE ? AND customer_id IN (SELECT id FROM customers WHERE user_id = ?) ORDER BY id DESC LIMIT 1");
        $stmt->bind_param("si", $like_pattern, $user_id);
        $stmt->execute();
        $stmt->bind_result($last_bill);
        $stmt->fetch();
        $stmt->close();
        if ($last_bill) {
            $last_num = intval(substr($last_bill, -4));
        } else {
            $last_num = 0;
        }
        $last_bill_num[$key] = $last_num;
    }
    $last_bill_num[$key]++;
    $new_bill_no = $prefix . '-' . $year . $month . '-' . str_pad($last_bill_num[$key], 4, '0', STR_PAD_LEFT);
    // Update the row
    $update = $conn->prepare("UPDATE udhar_transactions SET bill_no = ? WHERE id = ?");
    $update->bind_param("si", $new_bill_no, $row['id']);
    if ($update->execute()) {
        $updated++;
    }
    $update->close();
}

$conn->close();
echo "Updated $updated bill numbers.\n";
