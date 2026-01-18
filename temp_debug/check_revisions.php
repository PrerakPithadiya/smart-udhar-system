<?php
require_once '../config/database.php';

$conn = getDBConnection();

echo "<h2>Current Project State - Bill Revision System</h2>";

// Check 1: Does bill_revisions table exist?
echo "<h3>1. Bill Revisions Table:</h3>";
$result = $conn->query("SHOW TABLES LIKE 'bill_revisions'");
if ($result->num_rows > 0) {
    echo "<p>❌ bill_revisions table EXISTS (needs to be removed)</p>";
    
    // Count records
    $count_result = $conn->query("SELECT COUNT(*) as count FROM bill_revisions");
    $count = $count_result->fetch_assoc()['count'];
    echo "<p>Found $count revision records</p>";
} else {
    echo "<p>✅ bill_revisions table does not exist</p>";
}

// Check 2: Does udhar_transactions have revision columns?
echo "<h3>2. Udhar Transactions Revision Columns:</h3>";
$columns = ['revision_number', 'last_edited_by', 'last_edited_at'];
foreach ($columns as $column) {
    $result = $conn->query("SHOW COLUMNS FROM udhar_transactions LIKE '$column'");
    if ($result->num_rows > 0) {
        echo "<p>❌ Column '$column' EXISTS in udhar_transactions (needs to be removed)</p>";
    } else {
        echo "<p>✅ Column '$column' does not exist in udhar_transactions</p>";
    }
}

// Check 3: Check edit_bill.php for revision functionality
echo "<h3>3. Edit Bill Revision Code:</h3>";
$edit_bill_content = file_get_contents('../edit_bill.php');
if (strpos($edit_bill_content, 'createBillRevision') !== false) {
    echo "<p>❌ createBillRevision function FOUND in edit_bill.php (needs to be removed)</p>";
} else {
    echo "<p>✅ createBillRevision function not found in edit_bill.php</p>";
}

if (strpos($edit_bill_content, 'bill_revisions') !== false) {
    echo "<p>❌ bill_revisions table references FOUND in edit_bill.php (needs to be removed)</p>";
} else {
    echo "<p>✅ No bill_revisions references in edit_bill.php</p>";
}

// Check 4: Any revision-related files
echo "<h3>4. Revision-Related Files:</h3>";
$revision_files = [
    'setup_bill_editing.php',
    'view_revisions.php',
    'bill_history.php'
];

foreach ($revision_files as $file) {
    if (file_exists("../$file")) {
        echo "<p>❌ File '$file' EXISTS (should be removed)</p>";
    } else {
        echo "<p>✅ File '$file' does not exist</p>";
    }
}

echo "<h3>📋 Summary:</h3>";
echo "<p><strong>Action Required:</strong> Remove all bill revision functionality</p>";
echo "<p><strong>Desired State:</strong> Direct database updates without keeping old versions</p>";

$conn->close();
?>
