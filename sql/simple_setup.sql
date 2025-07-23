-- Simple lockout setup - uses existing access_requests1 for users
-- 1. First create database: elevator_lockout_db
-- 2. Select the database
-- 3. Run this script in the SQL tab

USE elevator_lockout_db;

-- Create the lockout table
CREATE TABLE elevator_lockout (
    id INT AUTO_INCREMENT PRIMARY KEY,
    elevator_id INT DEFAULT 1,
    is_locked_out BOOLEAN DEFAULT FALSE,
    locked_by_user_id INT,
    locked_by_username VARCHAR(255),
    lockout_reason TEXT,
    lockout_timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    unlock_timestamp TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_elevator_lockout (elevator_id, is_locked_out)
);

-- Insert initial lockout record (unlocked state)
INSERT INTO elevator_lockout (elevator_id, is_locked_out, lockout_reason) 
VALUES (1, FALSE, 'System initialized');

-- Show what we created
SELECT 'Lockout table created successfully!' as status;
SHOW TABLES;
SELECT * FROM elevator_lockout;
