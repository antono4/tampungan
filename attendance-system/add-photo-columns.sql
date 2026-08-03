-- =====================================================
-- MIGRASI: Tambahkan kolom photo ke tabel users
-- =====================================================

-- Jalankan query ini di phpMyAdmin atau MySQL:

ALTER TABLE users ADD COLUMN photo MEDIUMBLOB NULL AFTER role;
ALTER TABLE users ADD COLUMN photo_type VARCHAR(50) DEFAULT 'image/jpeg' AFTER photo;

-- =====================================================
-- SETELAH JALANKAN, VERIFIKASI DENGAN:
-- =====================================================

DESCRIBE users;

-- =====================================================
-- HASIL YANG BENAR:
-- photo       | mediumblob     | YES  | MUL NULL
-- photo_type  | varchar(50)   | YES  | MUL NULL    | image/jpeg
-- =====================================================
