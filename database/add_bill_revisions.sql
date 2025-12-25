-- Create bill revisions table to track all changes to bills
CREATE TABLE IF NOT EXISTS bill_revisions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    udhar_id INT NOT NULL,
    revision_number INT NOT NULL,
    user_id INT NOT NULL,
    
    -- Bill details snapshot
    customer_id INT NOT NULL,
    bill_no VARCHAR(50) NOT NULL,
    transaction_date DATE NOT NULL,
    due_date DATE,
    
    -- Financial details
    total_amount DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    cgst_amount DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    sgst_amount DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    igst_amount DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    discount DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    discount_type ENUM('fixed', 'percentage') DEFAULT 'fixed',
    round_off DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    grand_total DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    
    -- Additional info
    description TEXT,
    notes TEXT,
    status ENUM('pending', 'partially_paid', 'paid') DEFAULT 'pending',
    category VARCHAR(100),
    
    -- Items snapshot (JSON format)
    items_data JSON,
    
    -- Revision metadata
    change_reason TEXT,
    changed_by INT NOT NULL,
    changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (udhar_id) REFERENCES udhar_transactions(id) ON DELETE CASCADE,
    FOREIGN KEY (changed_by) REFERENCES users(id),
    INDEX idx_udhar_revision (udhar_id, revision_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Add revision tracking fields to udhar_transactions if not exists
ALTER TABLE udhar_transactions 
ADD COLUMN IF NOT EXISTS revision_number INT DEFAULT 1,
ADD COLUMN IF NOT EXISTS last_edited_by INT,
ADD COLUMN IF NOT EXISTS last_edited_at TIMESTAMP NULL,
ADD COLUMN IF NOT EXISTS grand_total DECIMAL(10, 2) GENERATED ALWAYS AS (
    total_amount + cgst_amount + sgst_amount + igst_amount - discount + round_off
) STORED;
