-- EditX Studio Database Setup for Hosting
-- For filemanager.ai or similar hosting platforms
-- 
-- Database: editning
-- Only contains ADMINS table - no JSON needed for MySQL

-- ============================================================================
-- CREATE DATABASE
-- ============================================================================

-- Create database if it doesn't exist
CREATE DATABASE IF NOT EXISTS editning;
USE editning;

-- ============================================================================
-- ADMINS TABLE (for login only)
-- ============================================================================

CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================================
-- INSERT DEFAULT ADMIN USER
-- ============================================================================

-- Default credentials:
-- Username: admin
-- Password: admin123
-- 
-- Password is hashed using bcrypt: $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi

INSERT INTO admins (username, password) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi')
ON DUPLICATE KEY UPDATE username = username;

-- ============================================================================
-- CREATE INDEXES for better performance
-- ============================================================================

CREATE INDEX idx_admin_username ON admins(username);

-- ============================================================================
-- NOTES
-- ============================================================================
-- 
-- BOOKINGS & CONTACTS:
-- - Stored in JSON files (bookings.json, contact_messages.json)
-- - No MySQL tables needed for these
-- - All PHP files use db-functions.php to manage JSON data
-- 
-- GALLERIES & OFFERS:
-- - Stored in JSON files (gallery.json, offers.json)
-- - No MySQL tables needed for these
-- 
-- TO RESET ADMIN PASSWORD:
-- If you forget the admin password, run this query:
-- UPDATE admins SET password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' WHERE username = 'admin';
-- Then login with: admin / admin123

