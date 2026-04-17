CREATE DATABASE IF NOT EXISTS smartqueue;
USE smartqueue;

-- Drop table for a fresh start so new columns are definitely added
DROP TABLE IF EXISTS users;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('client', 'admin', 'main_admin') DEFAULT 'client',
    establishment VARCHAR(255) NULL,
    sector VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Seed a default Main Admin (Director)
-- Password is 'admin123' (hashed using PASSWORD_DEFAULT)
INSERT INTO users (name, email, password, role) 
VALUES ('Directeur Général', 'admin@smartqueue.com', '$2y$12$NIbezcTGUoHIENYeG9dnU.IB16CXmi9btpTfbzBL779rGV2u.F/66', 'main_admin')
ON DUPLICATE KEY UPDATE role='main_admin';