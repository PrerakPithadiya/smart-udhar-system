<?php
// File: smart-udhar-system/setup_bill_editing.php
// One-time setup script to create bill_revisions table and update udhar_transactions

require_once 'config/database.php';
requireLogin();

// Only allow admin/first user to run this
if ($_SESSION['user_id'] != 1) {
    die("Only the system administrator can run this setup.");
}

$conn = getDBConnection();

echo "<!DOCTYPE html>
<html>
<head>
    <title>Bill Editing Setup</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css' rel='stylesheet'>
</head>
<body>
<div class='container mt-5'>
    <div class='card'>
        <div class='card-header bg-primary text-white'>
            <h3>Bill Editing Feature Setup</h3>
        </div>
        <div class='card-body'>";

try {
    echo "<div class='alert alert-info'>Starting database setup...</div>";

    // Create bill_revisions table
    echo "<p>Creating bill_revisions table...</p>";
    $sql = "CREATE TABLE IF NOT EXISTS bill_revisions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        udhar_id INT NOT NULL,
        revision_number INT NOT NULL,
        user_id INT NOT NULL,
        
        customer_id INT NOT NULL,
        bill_no VARCHAR(50) NOT NULL,
        transaction_date DATE NOT NULL,
        due_date DATE,
        
        total_amount DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
        cgst_amount DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
        sgst_amount DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
        igst_amount DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
        discount DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
        discount_type ENUM('fixed', 'percentage') DEFAULT 'fixed',
        round_off DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
        grand_total DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
        
        description TEXT,
        notes TEXT,
        status ENUM('pending', 'partially_paid', 'paid') DEFAULT 'pending',
        category VARCHAR(100),
        
        items_data JSON,
        
        change_reason TEXT,
        changed_by INT NOT NULL,
        changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        
        INDEX idx_udhar_revision (udhar_id, revision_number)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

    if ($conn->query($sql)) {
        echo "<div class='alert alert-success'>✓ bill_revisions table created successfully</div>";
    } else {
        throw new Exception("Error creating bill_revisions table: " . $conn->error);
    }

    // Check if columns already exist before adding them
    echo "<p>Checking udhar_transactions table structure...</p>";

    // Check for revision_number column
    $result = $conn->query("SHOW COLUMNS FROM udhar_transactions LIKE 'revision_number'");
    if ($result->num_rows == 0) {
        echo "<p>Adding revision_number column...</p>";
        $sql = "ALTER TABLE udhar_transactions ADD COLUMN revision_number INT DEFAULT 1";
        if ($conn->query($sql)) {
            echo "<div class='alert alert-success'>✓ revision_number column added</div>";
        } else {
            echo "<div class='alert alert-warning'>⚠ Could not add revision_number: " . $conn->error . "</div>";
        }
    } else {
        echo "<div class='alert alert-info'>✓ revision_number column already exists</div>";
    }

    // Check for last_edited_by column
    $result = $conn->query("SHOW COLUMNS FROM udhar_transactions LIKE 'last_edited_by'");
    if ($result->num_rows == 0) {
        echo "<p>Adding last_edited_by column...</p>";
        $sql = "ALTER TABLE udhar_transactions ADD COLUMN last_edited_by INT NULL";
        if ($conn->query($sql)) {
            echo "<div class='alert alert-success'>✓ last_edited_by column added</div>";
        } else {
            echo "<div class='alert alert-warning'>⚠ Could not add last_edited_by: " . $conn->error . "</div>";
        }
    } else {
        echo "<div class='alert alert-info'>✓ last_edited_by column already exists</div>";
    }

    // Check for last_edited_at column
    $result = $conn->query("SHOW COLUMNS FROM udhar_transactions LIKE 'last_edited_at'");
    if ($result->num_rows == 0) {
        echo "<p>Adding last_edited_at column...</p>";
        $sql = "ALTER TABLE udhar_transactions ADD COLUMN last_edited_at TIMESTAMP NULL";
        if ($conn->query($sql)) {
            echo "<div class='alert alert-success'>✓ last_edited_at column added</div>";
        } else {
            echo "<div class='alert alert-warning'>⚠ Could not add last_edited_at: " . $conn->error . "</div>";
        }
    } else {
        echo "<div class='alert alert-info'>✓ last_edited_at column already exists</div>";
    }

    // Check for category column
    $result = $conn->query("SHOW COLUMNS FROM udhar_transactions LIKE 'category'");
    if ($result->num_rows == 0) {
        echo "<p>Adding category column...</p>";
        $sql = "ALTER TABLE udhar_transactions ADD COLUMN category VARCHAR(100) NULL";
        if ($conn->query($sql)) {
            echo "<div class='alert alert-success'>✓ category column added</div>";
        } else {
            echo "<div class='alert alert-warning'>⚠ Could not add category: " . $conn->error . "</div>";
        }
    } else {
        echo "<div class='alert alert-info'>✓ category column already exists</div>";
    }

    echo "<div class='alert alert-success mt-4'>
        <h4>✓ Setup Complete!</h4>
        <p>The bill editing feature is now ready to use.</p>
        <p><strong>Next steps:</strong></p>
        <ul>
            <li>Go to the Udhar Entry page</li>
            <li>Click the edit (pencil) icon on any bill</li>
            <li>Make your changes and provide a reason</li>
            <li>The system will track all revisions automatically</li>
        </ul>
    </div>";

    echo "<div class='mt-3'>
        <a href='udhar.php' class='btn btn-primary'>Go to Udhar Entry Page</a>
        <a href='dashboard.php' class='btn btn-secondary'>Go to Dashboard</a>
    </div>";
} catch (Exception $e) {
    echo "<div class='alert alert-danger'>
        <h4>Error during setup:</h4>
        <p>" . $e->getMessage() . "</p>
    </div>";
}

echo "</div></div></div></body></html>";

$conn->close();
