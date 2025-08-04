-- Setup script for access_requests1 database (User Management)
-- Run this in phpMyAdmin as root user

-- Create the access requests database
CREATE DATABASE IF NOT EXISTS access_requests1;

-- Grant permissions to Blaise user
-- This is a password I made up for the sake of the project it is not confidential
GRANT ALL PRIVILEGES ON access_requests1.* TO 'Blaise'@'localhost' IDENTIFIED BY 'Gitdead32!32';
FLUSH PRIVILEGES;

-- Use the access requests database
USE access_requests1;

-- Create the requests table (user management)
CREATE TABLE IF NOT EXISTS requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) UNIQUE NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    student_card VARCHAR(255) UNIQUE,
    reason TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    approved BOOLEAN DEFAULT FALSE,
    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_student_card (student_card)
);

-- Create access_logs table for user activity
CREATE TABLE IF NOT EXISTS access_logs (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    student_card VARCHAR(255),
    access_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    success BOOLEAN DEFAULT TRUE,
    reason TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT
);

-- Insert default admin user
-- Username: Admin123, Password: Admin123!
INSERT IGNORE INTO requests (username, email, password, student_card, reason, approved) 
VALUES (
    'Admin123', 
    'admin@elevator.local', 
    '$2y$10$7rLSvRVyTQORapkDOqmkhetjh6H.ZXPyDXRf08.8gXBlNTW1wKQjO',
    'ADMIN001', 
    'System administrator account', 
    TRUE
);

-- Insert your developer account as admin
INSERT IGNORE INTO requests (username, email, password, student_card, reason, approved) 
VALUES (
    'bswan', 
    'bswan8085@conestogac.on.ca', 
    '$2y$10$7rLSvRVyTQORapkDOqmkhetjh6H.ZXPyDXRf08.8gXBlNTW1wKQjO',
    'BSWAN001', 
    'Project developer account', 
    TRUE
);

-- Show what we created
SELECT 'access_requests1 database setup complete!' as status;
SHOW TABLES;
SELECT id, username, email, student_card, approved FROM requests;
