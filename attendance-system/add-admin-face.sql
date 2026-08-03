-- =====================================================
-- Script untuk menambahkan face encoding ke admin
-- Jalankan di MySQL: mysql -u root -p attendance_system < add-admin-face.sql
-- =====================================================

-- Sample face encoding (128-dimensional descriptor)
-- CATATAN: Ini adalah placeholder. Untuk produksi, encoding harus diambil dari wajah asli user.
SET @face_encoding = '[
    0.0123, 0.0456, 0.0789, 0.0234, 0.0567, 0.0890, 0.0345, 0.0678,
    0.0901, 0.0456, 0.0789, 0.0123, 0.0567, 0.0890, 0.0234, 0.0678,
    0.0901, 0.0456, 0.0789, 0.0123, 0.0567, 0.0890, 0.0234, 0.0678,
    0.0901, 0.0456, 0.0789, 0.0123, 0.0567, 0.0890, 0.0234, 0.0678,
    0.0901, 0.0456, 0.0789, 0.0123, 0.0567, 0.0890, 0.0234, 0.0678,
    0.0901, 0.0456, 0.0789, 0.0123, 0.0567, 0.0890, 0.0234, 0.0678,
    0.0901, 0.0456, 0.0789, 0.0123, 0.0567, 0.0890, 0.0234, 0.0678,
    0.0901, 0.0456, 0.0789, 0.0123, 0.0567, 0.0890, 0.0234, 0.0678,
    0.0901, 0.0456, 0.0789, 0.0123, 0.0567, 0.0890, 0.0234, 0.0678,
    0.0901, 0.0456, 0.0789, 0.0123, 0.0567, 0.0890, 0.0234, 0.0678,
    0.0901, 0.0456, 0.0789, 0.0123, 0.0567, 0.0890, 0.0234, 0.0678,
    0.0901, 0.0456, 0.0789, 0.0123, 0.0567, 0.0890, 0.0234, 0.0678,
    0.0901, 0.0456, 0.0789, 0.0123, 0.0567, 0.0890, 0.0234, 0.0678,
    0.0901, 0.0456, 0.0789, 0.0123, 0.0567, 0.0890, 0.0234, 0.0678,
    0.0901, 0.0456, 0.0789, 0.0123, 0.0567, 0.0890, 0.0234, 0.0678,
    0.0901, 0.0456, 0.0789
]';

-- Update admin user dengan face encoding
UPDATE users 
SET face_encoding = @face_encoding, 
    face_registered = 1 
WHERE username = 'admin';

-- Verifikasi
SELECT id, nama, username, face_registered, 
       CASE WHEN face_encoding IS NOT NULL THEN 'ADA' ELSE 'TIDAK ADA' END as face_data
FROM users 
WHERE username = 'admin';
