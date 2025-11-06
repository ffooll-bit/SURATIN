-- Create database and schema without sample data
CREATE DATABASE IF NOT EXISTS suratin_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE suratin_db;

-- Drop tables if exists
DROP TABLE IF EXISTS ticket_logs;
DROP TABLE IF EXISTS tickets;
DROP TABLE IF EXISTS admins;

-- Create admins table
CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    role ENUM('admin','super') DEFAULT 'admin',
    active TINYINT(1) DEFAULT 1,
    last_login DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_role (role),
    INDEX idx_active (active)
) ENGINE=InnoDB;

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
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_ticket_code (ticket_code),
    INDEX idx_status (status),
    INDEX idx_jenis_surat (jenis_surat),
    INDEX idx_npm (npm),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB;

-- Create ticket_logs table
CREATE TABLE ticket_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ticket_id INT NOT NULL,
    admin_id INT DEFAULT NULL,
    action ENUM('submitted','in_review','valid','rejected','generated') NOT NULL,
    note TEXT DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    -- Foreign keys
    FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,
    FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE RESTRICT,
    
    -- Indexes
    INDEX idx_ticket_id (ticket_id),
    INDEX idx_admin_id (admin_id),
    INDEX idx_action (action),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB;
