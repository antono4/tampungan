# 📋 Sistem Absensi (Attendance System)

Aplikasi Absensi karyawan menggunakan PHP dan MySQL dengan antarmuka modern menggunakan Tailwind CSS.

## ✨ Fitur

### 🔐 Sistem Login & Keamanan
- Login dengan session yang aman
- Password di-hash menggunakan bcrypt
- Proteksi SQL Injection dengan Prepared Statements
- Proteksi Unauthorized Access dengan role checking

### 👨‍💼 Mode Admin
- Dashboard dengan statistik real-time
- Manajemen user (CRUD: Create, Read, Delete)
- Melihat semua data absensi karyawan
- Filter dan rekapan absensi

### 👤 Mode Karyawan
- Dashboard personal dengan jam server real-time
- Tombol Clock In & Clock Out dengan proteksi dobel absen
- Riwayat absensi pribadi

## 🚀 Cara Instalasi

### 1. Persyaratan
- PHP 7.4+ dengan ekstensi PDO
- MySQL 5.7+ atau MariaDB 10.3+
- Web server (Apache/Nginx) atau XAMPP/WAMP/MAMP

### 2. Langkah Instalasi

#### Menggunakan XAMPP (Recommended untuk Windows):
1. Salin folder `attendance-system` ke `C:\xampp\htdocs\`
2. Buka phpMyAdmin (`http://localhost/phpmyadmin`)
3. Import file `database.sql`
4. Edit file `db.php` jika diperlukan:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'attendance_system');
   define('DB_USER', 'root');
   define('DB_PASS', '');  // Sesuaikan dengan password MySQL Anda
   ```
5. Buka browser, akses `http://localhost/attendance-system/`

#### Menggunakan Command Line:
```bash
# 1. Masuk ke MySQL
mysql -u root -p

# 2. Buat database dan import schema
source database.sql

# 3. Keluar dari MySQL
exit

# 4. Jalankan PHP built-in server
php -S localhost:8000
```

### 3. Default Login Credentials

| Role | Username | Password |
|------|----------|----------|
| Admin | admin | password |
| Karyawan | budi | password |
| Karyawan | siti | password |

> ⚠️ **Penting**: Segera ubah password default setelah login pertama kali!

## 📁 Struktur File

```
attendance-system/
├── database.sql      # Schema database dan data awal
├── db.php            # Koneksi database dan fungsi CRUD
├── index.php         # Main application (UI + Logic)
└── README.md         # Dokumentasi
```

## 🗄️ Struktur Database

### Tabel `users`
| Field | Tipe | Deskripsi |
|-------|------|-----------|
| id | INT (PK) | ID unik user |
| nama | VARCHAR(100) | Nama lengkap |
| username | VARCHAR(50) | Username unik untuk login |
| password | VARCHAR(255) | Password yang sudah di-hash |
| role | ENUM | 'admin' atau 'karyawan' |
| created_at | TIMESTAMP | Tanggal pembuatan |
| updated_at | TIMESTAMP | Tanggal update terakhir |

### Tabel `attendances`
| Field | Tipe | Deskripsi |
|-------|------|-----------|
| id | INT (PK) | ID unik absensi |
| user_id | INT (FK) | ID user (relasi ke users) |
| tanggal | DATE | Tanggal absensi |
| clock_in | DATETIME | Waktu masuk |
| clock_out | DATETIME | Waktu pulang |
| created_at | TIMESTAMP | Tanggal pembuatan |
| updated_at | TIMESTAMP | Tanggal update terakhir |

## 🔒 Keamanan

### SQL Injection Protection
- Semua query menggunakan **Prepared Statements**
- Tidak ada concatenation query SQL

### Session Security
- Session dimulai dengan `session_start()`
- Role-based access control di setiap halaman
- Unauthorized access redirect ke login

### Password Security
- Password di-hash dengan **bcrypt**
- Cost factor: 10
- Menggunakan `password_verify()` untuk login

## 📝 Catatan Pengembangan

### Menambah User Baru via Code
```php
require_once 'db.php';
$pdo = getDBConnection();
$result = addUser($pdo, 'Nama Baru', 'username_baru', 'password123', 'karyawan');
```

### Reset Password User
```php
require_once 'db.php';
$pdo = getDBConnection();
$newHash = password_hash('password_baru', PASSWORD_BCRYPT, ['cost' => 10]);
$stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
$stmt->execute([$newHash, $userId]);
```

## 🛠️ Troubleshooting

### Error: "Koneksi database gagal"
- Pastikan MySQL server sedang berjalan
- Periksa kredensial di `db.php`
- Pastikan database `attendance_system` sudah dibuat

### Error: "Session already started"
- Pastikan tidak ada output sebelum `session_start()`
- Hapus semua `echo` atau HTML sebelum `<?php`

### Page blank setelah login
- Aktifkan error reporting:
  ```php
  error_reporting(E_ALL);
  ini_set('display_errors', 1);
  ```
- Periksa file `db.php` dan `index.php`

## 📜 Lisensi

Project ini bebas digunakan untuk keperluan pembelajaran dan development.

---

Dibuat dengan ❤️ menggunakan PHP, MySQL, dan Tailwind CSS
