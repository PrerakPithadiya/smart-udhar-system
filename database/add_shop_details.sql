-- Add shop details fields to users table for bill printing
-- These fields will be used in the tax invoice format

ALTER TABLE `users` 
ADD COLUMN `license_no` VARCHAR(100) DEFAULT NULL AFTER `address`,
ADD COLUMN `license_date` DATE DEFAULT NULL AFTER `license_no`,
ADD COLUMN `gst_no` VARCHAR(50) DEFAULT NULL AFTER `license_date`,
ADD COLUMN `registration_no` VARCHAR(100) DEFAULT NULL AFTER `gst_no`,
ADD COLUMN `registration_date` DATE DEFAULT NULL AFTER `registration_no`;

-- Add index for GST number for quick lookups
ALTER TABLE `users`
ADD INDEX `idx_users_gst` (`gst_no`);
