<?php
require_once '../config/database.php';

$conn = getDBConnection();

echo "<h2>🗑️ Removing Bill Revision System</h2>";

try {
    // 1. Drop bill_revisions table
    echo "<h3>1. Dropping bill_revisions table...</h3>";
    $result = $conn->query("DROP TABLE IF EXISTS bill_revisions");
    if ($result) {
        echo "<p>✅ bill_revisions table dropped successfully</p>";
    } else {
        echo "<p>❌ Error dropping bill_revisions: " . $conn->error . "</p>";
    }

    // 2. Remove revision columns from udhar_transactions
    echo "<h3>2. Removing revision columns from udhar_transactions...</h3>";
    
    $columns_to_remove = [
        'revision_number',
        'last_edited_by', 
        'last_edited_at'
    ];
    
    foreach ($columns_to_remove as $column) {
        $result = $conn->query("ALTER TABLE udhar_transactions DROP COLUMN IF EXISTS $column");
        if ($result) {
            echo "<p>✅ Column '$column' removed from udhar_transactions</p>";
        } else {
            echo "<p>❌ Error removing column '$column': " . $conn->error . "</p>";
        }
    }

    // 3. Delete setup file
    echo "<h3>3. Removing setup files...</h3>";
    if (file_exists('../setup_bill_editing.php')) {
        if (unlink('../setup_bill_editing.php')) {
            echo "<p>✅ setup_bill_editing.php deleted</p>";
        } else {
            echo "<p>❌ Error deleting setup_bill_editing.php</p>";
        }
    } else {
        echo "<p>✅ setup_bill_editing.php does not exist</p>";
    }

    echo "<h3>✅ Cleanup Complete!</h3>";
    echo "<p>All bill revision functionality has been removed.</p>";
    echo "<p>Your bills will now update directly in the database without keeping old versions.</p>";

} catch (Exception $e) {
    echo "<p>❌ Error during cleanup: " . $e->getMessage() . "</p>";
}

$conn->close();
?>
