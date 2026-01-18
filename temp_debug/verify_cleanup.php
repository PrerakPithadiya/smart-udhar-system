<?php
require_once '../config/database.php';

$conn = getDBConnection();

echo "<h2>✅ Verification - Bill Revision System Removed</h2>";

// Check 1: bill_revisions table should be gone
echo "<h3>1. Bill Revisions Table Status:</h3>";
$result = $conn->query("SHOW TABLES LIKE 'bill_revisions'");
if ($result->num_rows > 0) {
    echo "<p>❌ bill_revisions table still exists</p>";
} else {
    echo "<p>✅ bill_revisions table successfully removed</p>";
}

// Check 2: Revision columns should be gone
echo "<h3>2. Revision Columns Status:</h3>";
$columns = ['revision_number', 'last_edited_by', 'last_edited_at'];
$all_gone = true;
foreach ($columns as $column) {
    $result = $conn->query("SHOW COLUMNS FROM udhar_transactions LIKE '$column'");
    if ($result->num_rows > 0) {
        echo "<p>❌ Column '$column' still exists</p>";
        $all_gone = false;
    } else {
        echo "<p>✅ Column '$column' successfully removed</p>";
    }
}

// Check 3: edit_bill.php should be clean
echo "<h3>3. Edit Bill File Status:</h3>";
$edit_bill_content = file_get_contents('../edit_bill.php');
$issues = [];

if (strpos($edit_bill_content, 'createBillRevision') !== false) {
    $issues[] = "createBillRevision function still exists";
}

if (strpos($edit_bill_content, 'bill_revisions') !== false) {
    $issues[] = "bill_revisions references still exist";
}

if (strpos($edit_bill_content, 'revision_number') !== false) {
    $issues[] = "revision_number references still exist";
}

if (strpos($edit_bill_content, 'change_reason') !== false) {
    $issues[] = "change_reason validation still exists";
}

if (empty($issues)) {
    echo "<p>✅ edit_bill.php successfully cleaned</p>";
} else {
    echo "<p>❌ Issues found in edit_bill.php:</p>";
    foreach ($issues as $issue) {
        echo "<p>   - $issue</p>";
    }
}

// Check 4: Setup files should be gone
echo "<h3>4. Setup Files Status:</h3>";
if (file_exists('../setup_bill_editing.php')) {
    echo "<p>❌ setup_bill_editing.php still exists</p>";
} else {
    echo "<p>✅ setup_bill_editing.php successfully removed</p>";
}

// Final summary
echo "<h3>🎯 Final Status:</h3>";
if ($result->num_rows == 0 && $all_gone && empty($issues) && !file_exists('../setup_bill_editing.php')) {
    echo "<p><strong>✅ SUCCESS: All bill revision functionality completely removed!</strong></p>";
    echo "<p>Your bills will now update directly in the database without keeping old versions.</p>";
    echo "<p>✅ No more duplicate copies</p>";
    echo "<p>✅ No more outdated versions</p>";
    echo "<p>✅ Direct database updates only</p>";
} else {
    echo "<p>❌ Some cleanup may be incomplete. Please review the issues above.</p>";
}

echo "<p><a href='../edit_bill.php?id=55'>Test Clean Edit Bill Page</a></p>";

$conn->close();
?>
