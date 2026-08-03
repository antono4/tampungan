<?php
/**
 * Database Connection Configuration
 * Menggunakan PDO untuk keamanan dari SQL Injection
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'attendance_system');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

/**
 * Fungsi untuk mendapatkan koneksi database
 * Menggunakan Singleton Pattern untuk efisiensi
 */
function getDBConnection() {
    static $pdo = null;
    
    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
        ];
        
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die("Koneksi database gagal: " . $e->getMessage());
        }
    }
    
    return $pdo;
}

/**
 * Fungsi untuk hash password dengan bcrypt
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
}

/**
 * Fungsi untuk verifikasi password
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Fungsi untuk mendapatkan semua user (hanya untuk admin)
 */
function getAllUsers($pdo) {
    $stmt = $pdo->prepare("SELECT id, nama, username, role, created_at FROM users ORDER BY created_at DESC");
    $stmt->execute();
    return $stmt->fetchAll();
}

/**
 * Fungsi untuk mendapatkan user berdasarkan ID
 */
function getUserById($pdo, $id) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

/**
 * Fungsi untuk mendapatkan semua absensi
 */
function getAllAttendances($pdo) {
    $stmt = $pdo->prepare("
        SELECT a.*, u.nama, u.username 
        FROM attendances a 
        JOIN users u ON a.user_id = u.id 
        ORDER BY a.tanggal DESC, a.clock_in DESC
    ");
    $stmt->execute();
    return $stmt->fetchAll();
}

/**
 * Fungsi untuk mendapatkan absensi user tertentu
 */
function getUserAttendances($pdo, $userId) {
    $stmt = $pdo->prepare("
        SELECT * FROM attendances 
        WHERE user_id = ? 
        ORDER BY tanggal DESC
    ");
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

/**
 * Fungsi untuk mendapatkan absensi hari ini untuk user tertentu
 */
function getTodayAttendance($pdo, $userId) {
    $today = date('Y-m-d');
    $stmt = $pdo->prepare("SELECT * FROM attendances WHERE user_id = ? AND tanggal = ?");
    $stmt->execute([$userId, $today]);
    return $stmt->fetch();
}

/**
 * Fungsi untuk clock in
 */
function clockIn($pdo, $userId) {
    $today = date('Y-m-d');
    $now = date('Y-m-d H:i:s');
    
    // Cek apakah sudah ada absensi hari ini
    $existing = getTodayAttendance($pdo, $userId);
    if ($existing) {
        return ['success' => false, 'message' => 'Anda sudah melakukan Clock In hari ini!'];
    }
    
    try {
        $stmt = $pdo->prepare("INSERT INTO attendances (user_id, tanggal, clock_in) VALUES (?, ?, ?)");
        $stmt->execute([$userId, $today, $now]);
        return ['success' => true, 'message' => 'Clock In berhasil pada ' . date('d/m/Y H:i:s')];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
}

/**
 * Fungsi untuk clock out
 */
function clockOut($pdo, $userId) {
    $today = date('Y-m-d');
    $now = date('Y-m-d H:i:s');
    
    // Cek apakah sudah clock in hari ini
    $existing = getTodayAttendance($pdo, $userId);
    if (!$existing) {
        return ['success' => false, 'message' => 'Anda belum melakukan Clock In hari ini!'];
    }
    
    if ($existing['clock_out']) {
        return ['success' => false, 'message' => 'Anda sudah melakukan Clock Out hari ini!'];
    }
    
    try {
        $stmt = $pdo->prepare("UPDATE attendances SET clock_out = ? WHERE user_id = ? AND tanggal = ?");
        $stmt->execute([$now, $userId, $today]);
        return ['success' => true, 'message' => 'Clock Out berhasil pada ' . date('d/m/Y H:i:s')];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
}

/**
 * Fungsi untuk menambahkan user baru
 */
function addUser($pdo, $nama, $username, $password, $role = 'karyawan') {
    // Cek apakah username sudah ada
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        return ['success' => false, 'message' => 'Username sudah digunakan!'];
    }
    
    try {
        $hashedPassword = hashPassword($password);
        $stmt = $pdo->prepare("INSERT INTO users (nama, username, password, role) VALUES (?, ?, ?, ?)");
        $stmt->execute([$nama, $username, $hashedPassword, $role]);
        return ['success' => true, 'message' => 'User berhasil ditambahkan!'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
}

/**
 * Fungsi untuk menghapus user
 */
function deleteUser($pdo, $id) {
    // Cegah penghapusan user sendiri
    if ($id == $_SESSION['user_id']) {
        return ['success' => false, 'message' => 'Anda tidak dapat menghapus akun sendiri!'];
    }
    
    try {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role != 'admin'");
        $stmt->execute([$id]);
        if ($stmt->rowCount() > 0) {
            return ['success' => true, 'message' => 'User berhasil dihapus!'];
        } else {
            return ['success' => false, 'message' => 'User tidak ditemukan atau tidak dapat dihapus!'];
        }
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
}
