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

-- ============================================================
--  Pre-seeded Main Admins (Director per Establishment)
--  Default password for all: admin123
--  Hash: password_hash('admin123', PASSWORD_DEFAULT)
-- ============================================================

-- BANQUES
INSERT INTO users (name, email, password, role, establishment, sector) VALUES 
('Directeur BIAT Marina', 'marina@smartqueue.com', '$2y$12$NIbezcTGUoHIENYeG9dnU.IB16CXmi9btpTfbzBL779rGV2u.F/66', 'main_admin', 'BIAT Marina', 'banque'),
('Directeur BH Faculté', 'bh_fac@smartqueue.com', '$2y$12$NIbezcTGUoHIENYeG9dnU.IB16CXmi9btpTfbzBL779rGV2u.F/66', 'main_admin', 'BH Faculté', 'banque'),
('Directeur BIAT Sousse', 'biat_sousse@smartqueue.com', '$2y$12$NIbezcTGUoHIENYeG9dnU.IB16CXmi9btpTfbzBL779rGV2u.F/66', 'main_admin', 'BIAT Sousse', 'banque');

-- CINÉMAS
INSERT INTO users (name, email, password, role, establishment, sector) VALUES 
('Directeur Pathé', 'pathe@smartqueue.com', '$2y$12$NIbezcTGUoHIENYeG9dnU.IB16CXmi9btpTfbzBL779rGV2u.F/66', 'main_admin', 'Cinéma Pathé', 'cinema'),
('Directeur Colisée', 'colisee@smartqueue.com', '$2y$12$NIbezcTGUoHIENYeG9dnU.IB16CXmi9btpTfbzBL779rGV2u.F/66', 'main_admin', 'Le Colisée', 'cinema');

-- RESTO U
INSERT INTO users (name, email, password, role, establishment, sector) VALUES 
('Directeur RU Campus', 'ru_campus@smartqueue.com', '$2y$12$NIbezcTGUoHIENYeG9dnU.IB16CXmi9btpTfbzBL779rGV2u.F/66', 'main_admin', 'RU Campus Monastir', 'resto'),
('Directeur RU Sahloul', 'ru_sahloul@smartqueue.com', '$2y$12$NIbezcTGUoHIENYeG9dnU.IB16CXmi9btpTfbzBL779rGV2u.F/66', 'main_admin', 'RU Sahloul Sousse', 'resto');

-- ADMINISTRATION
INSERT INTO users (name, email, password, role, establishment, sector) VALUES 
('Directeur Poste Tunis', 'poste_tunis@smartqueue.com', '$2y$12$NIbezcTGUoHIENYeG9dnU.IB16CXmi9btpTfbzBL779rGV2u.F/66', 'main_admin', 'La Poste - Tunis Centre', 'administration'),
('Directeur Municipalité Sousse', 'municipalite_sousse@smartqueue.com', '$2y$12$NIbezcTGUoHIENYeG9dnU.IB16CXmi9btpTfbzBL779rGV2u.F/66', 'main_admin', 'Municipalité de Sousse', 'administration');

-- ============================================================
--  Tickets Table (for queue management)
-- ============================================================
DROP TABLE IF EXISTS tickets;

CREATE TABLE tickets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    ticket_number VARCHAR(20) NOT NULL,
    agency VARCHAR(255) NOT NULL,
    location VARCHAR(255) NOT NULL,
    service_key VARCHAR(50) NOT NULL,
    establishment_key VARCHAR(255) NOT NULL,
    wait_time VARCHAR(50),
    people_ahead INT DEFAULT 0,
    status ENUM('waiting', 'served', 'cancelled') DEFAULT 'waiting',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    served_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX (user_id),
    INDEX (establishment_key),
    INDEX (status)
);