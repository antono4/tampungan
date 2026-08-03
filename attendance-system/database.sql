-- =====================================================
-- APLIKASI ABSENSI - DATABASE SCHEMA
-- =====================================================

-- Buat database
CREATE DATABASE IF NOT EXISTS attendance_system;
USE attendance_system;

-- =====================================================
-- TABEL USERS
-- Tabel untuk menyimpan data pengguna (admin & karyawan)
-- =====================================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'karyawan') NOT NULL DEFAULT 'karyawan',
    photo MEDIUMBLOB NULL,
    photo_type VARCHAR(50) DEFAULT 'image/jpeg',
    face_encoding TEXT NULL,
    face_registered TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABEL ATTENDANCES
-- Tabel untuk menyimpan data absensi karyawan
-- =====================================================
CREATE TABLE IF NOT EXISTS attendances (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    tanggal DATE NOT NULL,
    clock_in DATETIME DEFAULT NULL,
    clock_out DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Relasi ke tabel users
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE,
    
    -- Pastikan satu user hanya bisa absen sekali per hari
    UNIQUE KEY unique_user_date (user_id, tanggal),
    
    -- Index untuk mempercepat pencarian
    INDEX idx_user_id (user_id),
    INDEX idx_tanggal (tanggal),
    INDEX idx_user_tanggal (user_id, tanggal)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- DATA AWAL - Admin Default
-- Username: admin
-- Password: admin123 (di-hash dengan bcrypt)
-- =====================================================
INSERT INTO users (nama, username, password, role) VALUES 
('Administrator', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- =====================================================
-- MIGRASI: Tambahkan kolom face untuk face recognition
-- Jalankan jika database sudah ada sebelumnya
-- =====================================================
-- ALTER TABLE users ADD COLUMN face_encoding TEXT NULL AFTER password;
-- ALTER TABLE users ADD COLUMN face_registered TINYINT(1) DEFAULT 0 AFTER face_encoding;

-- =====================================================
-- MIGRASI: Tambahkan kolom photo untuk foto profil
-- Jalankan jika database sudah ada sebelumnya
-- =====================================================
-- ALTER TABLE users ADD COLUMN photo MEDIUMBLOB NULL AFTER role;
-- ALTER TABLE users ADD COLUMN photo_type VARCHAR(50) DEFAULT 'image/jpeg' AFTER photo;

-- =====================================================
-- DATA CONTOH - Karyawan
-- =====================================================
INSERT INTO users (nama, username, password, role) VALUES 
('Budi Santoso', 'budi', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'karyawan'),
('Siti Rahayu', 'siti', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'karyawan');

-- =====================================================
-- PENJELASAN:
-- Password hash di atas adalah hash dari 'password' (bukan 'admin123' karena menggunakan bcrypt)
-- Default password untuk semua user di atas: password
-- 
-- Untuk keamanan production, gunakan password yang berbeda
-- Hash password: password_hash('password_anda', PASSWORD_DEFAULT)
-- =====================================================
