-- =====================================================
-- MIGRASI: Tambahkan kolom photo untuk foto profil
-- Jalankan di MySQL atau phpMyAdmin untuk update database yang sudah ada
-- =====================================================

-- Tambahkan kolom photo
ALTER TABLE users ADD COLUMN photo MEDIUMBLOB NULL AFTER role;

-- Tambahkan kolom photo_type
ALTER TABLE users ADD COLUMN photo_type VARCHAR(50) DEFAULT 'image/jpeg' AFTER photo;

-- Verifikasi perubahan
DESCRIBE users;
