-- Create database and schema without sample data
CREATE DATABASE IF NOT EXISTS suratin_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE suratin_db;

-- Drop table if exists
DROP TABLE IF EXISTS tickets;

-- Create tickets table
CREATE TABLE tickets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ticket_code VARCHAR(32) UNIQUE NOT NULL,
    nama VARCHAR(255) NOT NULL,
    npm VARCHAR(50) NOT NULL,
    prodi VARCHAR(100) NOT NULL,
    jenis_surat VARCHAR(100) NOT NULL,
    data JSON DEFAULT NULL,
    attachments JSON DEFAULT NULL,
    email VARCHAR(255) NOT NULL,
    wa VARCHAR(30) DEFAULT NULL,
    status ENUM('submitted','in_review','valid','rejected','generated') DEFAULT 'submitted',
    admin_note TEXT DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_ticket_code (ticket_code),
    INDEX idx_status (status),
    INDEX idx_jenis_surat (jenis_surat),
    INDEX idx_npm (npm),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB;
