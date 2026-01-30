-- Add 'Cancelled' status to test_drive table
-- Run this SQL in your phpMyAdmin or MySQL client

-- If the status column is an ENUM, modify it to include 'Cancelled'
ALTER TABLE `test_drive` 
MODIFY COLUMN `status` ENUM('Pending', 'Completed', 'Cancelled') 
DEFAULT 'Pending';

-- If the status column is VARCHAR, you don't need to run this
-- The 'Cancelled' value will work automatically
