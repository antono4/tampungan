<?php
/**
 * APLIKASI ABSENSI - Attendance System
 * Full Stack PHP + MySQL + Tailwind CSS
 * 
 * File: index.php (Single Page Application)
 */

session_start();

// Include database functions
require_once 'db.php';

// =====================================================
// INISIALISASI VARIABEL
// =====================================================
$pdo = getDBConnection();
$error = '';
$success = '';
$currentPage = isset($_GET['page']) ? $_GET['page'] : '';

// =====================================================
// LOGIKA LOGOUT
// =====================================================
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header('Location: index.php');
    exit;
}

// =====================================================
// LOGIKA LOGIN
// =====================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Username dan Password harus diisi!';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if ($user && verifyPassword($password, $user['password'])) {
            // Login berhasil
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['nama'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['logged_in'] = true;
            
            header('Location: index.php');
            exit;
        } else {
            $error = 'Username atau Password salah!';
        }
    }
}

// =====================================================
// CEK SESSION - Unauthorized Access Protection
// =====================================================
$isLoggedIn = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
$isAdmin = $isLoggedIn && $_SESSION['user_role'] === 'admin';
$userId = $_SESSION['user_id'] ?? null;
$userName = $_SESSION['user_name'] ?? '';

// =====================================================
// LOGIKA POST ACTIONS
// =====================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $isLoggedIn) {
    
    // Clock In
    if (isset($_POST['clock_in'])) {
        $result = clockIn($pdo, $userId);
        if ($result['success']) {
            $success = $result['message'];
        } else {
            $error = $result['message'];
        }
    }
    
    // Clock Out
    if (isset($_POST['clock_out'])) {
        $result = clockOut($pdo, $userId);
        if ($result['success']) {
            $success = $result['message'];
        } else {
            $error = $result['message'];
        }
    }
    
    // Tambah User (Admin only)
    if (isset($_POST['add_user']) && $isAdmin) {
        $nama = trim($_POST['nama'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? 'karyawan';
        
        if (empty($nama) || empty($username) || empty($password)) {
            $error = 'Semua field harus diisi!';
        } elseif (strlen($password) < 6) {
            $error = 'Password minimal 6 karakter!';
        } else {
            $result = addUser($pdo, $nama, $username, $password, $role);
            if ($result['success']) {
                $success = $result['message'];
            } else {
                $error = $result['message'];
            }
        }
    }
    
    // Hapus User (Admin only)
    if (isset($_POST['delete_user']) && $isAdmin) {
        $deleteId = (int)($_POST['user_id'] ?? 0);
        $result = deleteUser($pdo, $deleteId);
        if ($result['success']) {
            $success = $result['message'];
        } else {
            $error = $result['message'];
        }
    }
}

// =====================================================
// AMBIL DATA BERDASARKAN ROLE
// =====================================================
$todayAttendance = $isLoggedIn ? getTodayAttendance($pdo, $userId) : null;
$userAttendances = $isLoggedIn ? getUserAttendances($pdo, $userId) : [];
$allUsers = $isAdmin ? getAllUsers($pdo) : [];
$allAttendances = $isAdmin ? getAllAttendances($pdo) : [];

// =====================================================
// TAMPILKAN HTML
// =====================================================
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Absensi - Aplikasi Absensi Karyawan</title>
    
    <!-- Tailwind CSS via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome untuk Icon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        'sans': ['Inter', 'sans-serif'],
                    },
                }
            }
        }
    </script>
    
    <style>
        /* Custom Styles */
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .glass-effect {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
        }
        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .btn-pulse {
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0%, 100% { box-shadow: 0 0 0 0 rgba(102, 126, 234, 0.4); }
            50% { box-shadow: 0 0 0 15px rgba(102, 126, 234, 0); }
        }
    </style>
</head>
<body class="min-h-screen bg-gray-100 font-sans">

    <?php if (!$isLoggedIn): ?>
    <!-- =====================================================
         HALAMAN LOGIN
         ===================================================== -->
    <div class="min-h-screen gradient-bg flex items-center justify-center p-4">
        <div class="glass-effect rounded-2xl shadow-2xl w-full max-w-md p-8 fade-in">
            <div class="text-center mb-8">
                <div class="w-20 h-20 bg-indigo-600 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-fingerprint text-4xl text-white"></i>
                </div>
                <h1 class="text-3xl font-bold text-gray-800">Sistem Absensi</h1>
                <p class="text-gray-500 mt-2">Silakan login untuk melanjutkan</p>
            </div>
            
            <?php if ($error): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-lg mb-6">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle mr-3"></i>
                    <span><?= htmlspecialchars($error) ?></span>
                </div>
            </div>
            <?php endif; ?>
            
            <form method="POST" action="" class="space-y-6">
                <div>
                    <label class="block text-gray-700 font-medium mb-2">
                        <i class="fas fa-user mr-2 text-indigo-500"></i>Username
                    </label>
                    <input type="text" name="username" required 
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition"
                           placeholder="Masukkan username" autofocus>
                </div>
                
                <div>
                    <label class="block text-gray-700 font-medium mb-2">
                        <i class="fas fa-lock mr-2 text-indigo-500"></i>Password
                    </label>
                    <input type="password" name="password" required 
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition"
                           placeholder="Masukkan password">
                </div>
                
                <button type="submit" name="login" 
                        class="w-full bg-indigo-600 text-white py-3 px-4 rounded-lg font-semibold hover:bg-indigo-700 focus:ring-4 focus:ring-indigo-200 transition btn-pulse">
                    <i class="fas fa-sign-in-alt mr-2"></i>Masuk
                </button>
                
                <div class="relative">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-300"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-2 bg-white text-gray-500">Atau</span>
                    </div>
                </div>
                
                <a href="face-login.php" 
                   class="w-full flex items-center justify-center bg-green-600 text-white py-3 px-4 rounded-lg font-semibold hover:bg-green-700 focus:ring-4 focus:ring-green-200 transition">
                    <i class="fas fa-face-smile mr-2"></i>Login dengan Face Recognition
                </a>
            </form>
            
            <div class="mt-6 p-4 bg-blue-50 rounded-lg">
                <p class="text-sm text-gray-600">
                    <i class="fas fa-info-circle text-blue-500 mr-2"></i>
                    <strong>Demo Account:</strong><br>
                    Admin: admin / password<br>
                    Karyawan: budi / password
                </p>
            </div>
        </div>
    </div>

    <?php else: ?>
    <!-- =====================================================
         HALAMAN DASHBOARD (SETELAH LOGIN)
         ===================================================== -->
    
    <!-- Header/Navbar -->
    <nav class="bg-white shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-indigo-600 rounded-lg flex items-center justify-center">
                        <i class="fas fa-fingerprint text-white"></i>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-gray-800">Sistem Absensi</h1>
                        <p class="text-xs text-gray-500"><?= $isAdmin ? 'Mode Administrator' : 'Dashboard Karyawan' ?></p>
                    </div>
                </div>
                
                <div class="flex items-center space-x-4">
                    <a href="upload-photo.php" 
                       class="flex items-center space-x-2 px-3 py-2 bg-green-100 text-green-700 rounded-lg hover:bg-green-200 transition text-sm">
                        <i class="fas fa-camera"></i>
                        <span>Upload Foto</span>
                    </a>
                    <a href="face-login.php" 
                       class="flex items-center space-x-2 px-3 py-2 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition text-sm">
                        <i class="fas fa-face-smile"></i>
                        <span>Face Recognition</span>
                    </a>
                    <div class="text-right">
                        <p class="text-sm font-medium text-gray-800"><?= htmlspecialchars($userName) ?></p>
                        <p class="text-xs text-gray-500 capitalize"><?= htmlspecialchars($_SESSION['user_role']) ?></p>
                    </div>
                    <div class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center overflow-hidden">
                        <img src="view-photo.php?user_id=<?= $userId ?>" 
                             alt="Avatar" 
                             class="w-full h-full object-cover"
                             onerror="this.style.display='none'; this.parentElement.innerHTML='<i class=\'fas fa-user text-gray-600\'></i>'">
                    </div>
                    <a href="?action=logout" 
                       class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                        <i class="fas fa-sign-out-alt mr-2"></i>Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>
    
    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 py-8">
        
        <!-- Alert Messages -->
        <?php if ($error): ?>
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-lg mb-6 fade-in">
            <div class="flex items-center">
                <i class="fas fa-exclamation-circle mr-3 text-xl"></i>
                <span class="font-medium"><?= htmlspecialchars($error) ?></span>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-lg mb-6 fade-in">
            <div class="flex items-center">
                <i class="fas fa-check-circle mr-3 text-xl"></i>
                <span class="font-medium"><?= htmlspecialchars($success) ?></span>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if ($isAdmin): ?>
        <!-- =====================================================
             DASHBOARD ADMIN
             ===================================================== -->
        
        <!-- Tab Navigation -->
        <div class="bg-white rounded-xl shadow-sm mb-6">
            <div class="flex border-b">
                <button onclick="showTab('dashboard')" id="tab-dashboard"
                        class="tab-btn px-6 py-4 font-medium text-indigo-600 border-b-2 border-indigo-600 flex items-center">
                    <i class="fas fa-chart-line mr-2"></i>Dashboard
                </button>
                <button onclick="showTab('users')" id="tab-users"
                        class="tab-btn px-6 py-4 font-medium text-gray-500 hover:text-gray-700 flex items-center">
                    <i class="fas fa-users mr-2"></i>Manajemen User
                </button>
                <button onclick="showTab('attendance')" id="tab-attendance"
                        class="tab-btn px-6 py-4 font-medium text-gray-500 hover:text-gray-700 flex items-center">
                    <i class="fas fa-calendar-check mr-2"></i>Rekapan Absensi
                </button>
            </div>
        </div>
        
        <!-- Tab Content: Dashboard -->
        <div id="content-dashboard" class="tab-content">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Card: Total Karyawan -->
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm">Total Karyawan</p>
                            <p class="text-3xl font-bold text-gray-800">
                                <?= count(array_filter($allUsers, fn($u) => $u['role'] === 'karyawan')) ?>
                            </p>
                        </div>
                        <div class="w-14 h-14 bg-blue-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-users text-blue-600 text-xl"></i>
                        </div>
                    </div>
                </div>
                
                <!-- Card: Total Admin -->
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm">Total Admin</p>
                            <p class="text-3xl font-bold text-gray-800">
                                <?= count(array_filter($allUsers, fn($u) => $u['role'] === 'admin')) ?>
                            </p>
                        </div>
                        <div class="w-14 h-14 bg-purple-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-user-shield text-purple-600 text-xl"></i>
                        </div>
                    </div>
                </div>
                
                <!-- Card: Hadir Hari Ini -->
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <?php 
                    $today = date('Y-m-d');
                    $hadirHariIni = count(array_filter($allAttendances, fn($a) => $a['tanggal'] === $today && $a['clock_in']));
                    ?>
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm">Hadir Hari Ini</p>
                            <p class="text-3xl font-bold text-green-600"><?= $hadirHariIni ?></p>
                        </div>
                        <div class="w-14 h-14 bg-green-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-check-circle text-green-600 text-xl"></i>
                        </div>
                    </div>
                </div>
                
                <!-- Card: Belum Clock Out -->
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <?php 
                    $belumPulang = count(array_filter($allAttendances, fn($a) => $a['tanggal'] === $today && $a['clock_in'] && !$a['clock_out']));
                    ?>
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm">Belum Pulang</p>
                            <p class="text-3xl font-bold text-orange-600"><?= $belumPulang ?></p>
                        </div>
                        <div class="w-14 h-14 bg-orange-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-clock text-orange-600 text-xl"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Recent Attendance Table -->
            <div class="bg-white rounded-xl shadow-sm">
                <div class="p-6 border-b">
                    <h2 class="text-xl font-semibold text-gray-800">
                        <i class="fas fa-history mr-2 text-indigo-500"></i>Absensi Terbaru
                    </h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Karyawan</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Clock In</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Clock Out</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php 
                            $recentAttendances = array_slice($allAttendances, 0, 10);
                            foreach ($recentAttendances as $i => $attendance): 
                            ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= $i + 1 ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 bg-indigo-100 rounded-full flex items-center justify-center mr-3">
                                            <i class="fas fa-user text-indigo-600 text-sm"></i>
                                        </div>
                                        <span class="font-medium text-gray-800"><?= htmlspecialchars($attendance['nama']) ?></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    <?= date('d/m/Y', strtotime($attendance['tanggal'])) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    <?= $attendance['clock_in'] ? date('H:i:s', strtotime($attendance['clock_in'])) : '<span class="text-red-500">-</span>' ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    <?= $attendance['clock_out'] ? date('H:i:s', strtotime($attendance['clock_out'])) : '<span class="text-orange-500">Belum</span>' ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if ($attendance['clock_out']): ?>
                                    <span class="px-2 py-1 bg-green-100 text-green-700 text-xs font-medium rounded-full">
                                        <i class="fas fa-check mr-1"></i>Selesai
                                    </span>
                                    <?php else: ?>
                                    <span class="px-2 py-1 bg-yellow-100 text-yellow-700 text-xs font-medium rounded-full">
                                        <i class="fas fa-clock mr-1"></i>Belum Pulang
                                    </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($recentAttendances)): ?>
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                    <i class="fas fa-inbox text-4xl mb-3"></i>
                                    <p>Belum ada data absensi</p>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Tab Content: Manajemen User -->
        <div id="content-users" class="tab-content hidden">
            <!-- Form Tambah User -->
            <div class="bg-white rounded-xl shadow-sm mb-6">
                <div class="p-6 border-b">
                    <h2 class="text-xl font-semibold text-gray-800">
                        <i class="fas fa-user-plus mr-2 text-indigo-500"></i>Tambah Karyawan Baru
                    </h2>
                </div>
                <form method="POST" action="" class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nama Lengkap</label>
                            <input type="text" name="nama" required 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                   placeholder="Masukkan nama">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Username</label>
                            <input type="text" name="username" required 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                   placeholder="Masukkan username">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                            <input type="password" name="password" required 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                   placeholder="Minimal 6 karakter">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Role</label>
                            <select name="role" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="karyawan">Karyawan</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                    </div>
                    <div class="mt-4">
                        <button type="submit" name="add_user" 
                                class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg font-medium transition">
                            <i class="fas fa-plus mr-2"></i>Tambah User
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Tabel Daftar User -->
            <div class="bg-white rounded-xl shadow-sm">
                <div class="p-6 border-b">
                    <h2 class="text-xl font-semibold text-gray-800">
                        <i class="fas fa-list mr-2 text-indigo-500"></i>Daftar Semua User
                    </h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Username</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Dibuat</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php foreach ($allUsers as $i => $user): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= $i + 1 ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 bg-indigo-100 rounded-full flex items-center justify-center mr-3">
                                            <i class="fas fa-user text-indigo-600 text-sm"></i>
                                        </div>
                                        <span class="font-medium text-gray-800"><?= htmlspecialchars($user['nama']) ?></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    @<?= htmlspecialchars($user['username']) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if ($user['role'] === 'admin'): ?>
                                    <span class="px-2 py-1 bg-purple-100 text-purple-700 text-xs font-medium rounded-full">
                                        <i class="fas fa-shield-alt mr-1"></i>Admin
                                    </span>
                                    <?php else: ?>
                                    <span class="px-2 py-1 bg-blue-100 text-blue-700 text-xs font-medium rounded-full">
                                        <i class="fas fa-user mr-1"></i>Karyawan
                                    </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    <?= date('d/m/Y H:i', strtotime($user['created_at'])) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if ($user['role'] !== 'admin' || $user['id'] !== $userId): ?>
                                    <form method="POST" action="" class="inline" onsubmit="return confirm('Yakin ingin menghapus user ini?');">
                                        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                        <button type="submit" name="delete_user" 
                                                class="text-red-600 hover:text-red-800 font-medium">
                                            <i class="fas fa-trash mr-1"></i>Hapus
                                        </button>
                                    </form>
                                    <?php else: ?>
                                    <span class="text-gray-400 text-sm">
                                        <i class="fas fa-lock mr-1"></i>Tidak dapat dihapus
                                    </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Tab Content: Rekapan Absensi -->
        <div id="content-attendance" class="tab-content hidden">
            <div class="bg-white rounded-xl shadow-sm">
                <div class="p-6 border-b flex justify-between items-center">
                    <h2 class="text-xl font-semibold text-gray-800">
                        <i class="fas fa-table mr-2 text-indigo-500"></i>Rekapan Absensi Keseluruhan
                    </h2>
                    <div class="text-sm text-gray-500">
                        Total: <?= count($allAttendances) ?> records
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Karyawan</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Clock In</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Clock Out</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Durasi Kerja</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php foreach ($allAttendances as $i => $attendance): 
                                $durasi = '';
                                if ($attendance['clock_in'] && $attendance['clock_out']) {
                                    $in = new DateTime($attendance['clock_in']);
                                    $out = new DateTime($attendance['clock_out']);
                                    $diff = $in->diff($out);
                                    $durasi = $diff->h . 'j ' . $diff->i . 'm';
                                }
                            ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= $i + 1 ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 bg-indigo-100 rounded-full flex items-center justify-center mr-3">
                                            <i class="fas fa-user text-indigo-600 text-sm"></i>
                                        </div>
                                        <span class="font-medium text-gray-800"><?= htmlspecialchars($attendance['nama']) ?></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    <?= date('d/m/Y', strtotime($attendance['tanggal'])) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    <?= $attendance['clock_in'] ? date('H:i:s', strtotime($attendance['clock_in'])) : '<span class="text-red-500">-</span>' ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    <?= $attendance['clock_out'] ? date('H:i:s', strtotime($attendance['clock_out'])) : '<span class="text-orange-500">Belum</span>' ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    <?= $durasi ?: '<span class="text-gray-400">-</span>' ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if ($attendance['clock_out']): ?>
                                    <span class="px-2 py-1 bg-green-100 text-green-700 text-xs font-medium rounded-full">
                                        <i class="fas fa-check mr-1"></i>Selesai
                                    </span>
                                    <?php else: ?>
                                    <span class="px-2 py-1 bg-yellow-100 text-yellow-700 text-xs font-medium rounded-full">
                                        <i class="fas fa-clock mr-1"></i>Belum Pulang
                                    </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($allAttendances)): ?>
                            <tr>
                                <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                                    <i class="fas fa-inbox text-4xl mb-3"></i>
                                    <p>Belum ada data absensi</p>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <?php else: ?>
        <!-- =====================================================
             DASHBOARD KARYAWAN
             ===================================================== -->
        
        <!-- Jam Real-time -->
        <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div>
                    <p class="text-gray-500 text-sm">Tanggal Hari Ini</p>
                    <p class="text-2xl font-bold text-gray-800" id="current-date">
                        <?= date('d F Y') ?>
                    </p>
                </div>
                <div class="text-center mt-4 md:mt-0">
                    <p class="text-gray-500 text-sm">Jam Server</p>
                    <p class="text-4xl font-bold text-indigo-600" id="current-time">
                        <?= date('H:i:s') ?>
                    </p>
                </div>
            </div>
        </div>
        
        <!-- Status Absensi & Tombol Clock In/Out -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Status Card -->
            <div class="bg-white rounded-xl shadow-sm p-8">
                <h2 class="text-xl font-semibold text-gray-800 mb-6">
                    <i class="fas fa-user-clock mr-2 text-indigo-500"></i>Status Absensi Hari Ini
                </h2>
                
                <div class="space-y-4">
                    <div class="flex justify-between items-center p-4 bg-gray-50 rounded-lg">
                        <span class="text-gray-600">
                            <i class="fas fa-sign-in-alt mr-2 text-green-500"></i>Clock In
                        </span>
                        <span class="font-semibold <?= $todayAttendance && $todayAttendance['clock_in'] ? 'text-green-600' : 'text-gray-400' ?>">
                            <?= $todayAttendance && $todayAttendance['clock_in'] 
                                ? date('H:i:s', strtotime($todayAttendance['clock_in'])) 
                                : 'Belum' ?>
                        </span>
                    </div>
                    <div class="flex justify-between items-center p-4 bg-gray-50 rounded-lg">
                        <span class="text-gray-600">
                            <i class="fas fa-sign-out-alt mr-2 text-red-500"></i>Clock Out
                        </span>
                        <span class="font-semibold <?= $todayAttendance && $todayAttendance['clock_out'] ? 'text-red-600' : 'text-gray-400' ?>">
                            <?= $todayAttendance && $todayAttendance['clock_out'] 
                                ? date('H:i:s', strtotime($todayAttendance['clock_out'])) 
                                : 'Belum' ?>
                        </span>
                    </div>
                    <div class="flex justify-between items-center p-4 bg-gray-50 rounded-lg">
                        <span class="text-gray-600">
                            <i class="fas fa-tasks mr-2 text-blue-500"></i>Status
                        </span>
                        <span>
                            <?php if ($todayAttendance && $todayAttendance['clock_out']): ?>
                            <span class="px-3 py-1 bg-green-100 text-green-700 text-sm font-medium rounded-full">
                                <i class="fas fa-check-circle mr-1"></i>Selesai
                            </span>
                            <?php elseif ($todayAttendance && $todayAttendance['clock_in']): ?>
                            <span class="px-3 py-1 bg-yellow-100 text-yellow-700 text-sm font-medium rounded-full">
                                <i class="fas fa-clock mr-1"></i>Bekerja
                            </span>
                            <?php else: ?>
                            <span class="px-3 py-1 bg-gray-100 text-gray-600 text-sm font-medium rounded-full">
                                <i class="fas fa-moon mr-1"></i>Belum Absen
                            </span>
                            <?php endif; ?>
                        </span>
                    </div>
                </div>
            </div>
            
            <!-- Tombol Clock In/Out -->
            <div class="bg-white rounded-xl shadow-sm p-8">
                <h2 class="text-xl font-semibold text-gray-800 mb-6">
                    <i class="fas fa-hand-pointer mr-2 text-indigo-500"></i>Aksi Absensi
                </h2>
                
                <div class="space-y-4">
                    <?php if (!$todayAttendance || ($todayAttendance && !$todayAttendance['clock_in'])): ?>
                    <!-- Tombol Clock In -->
                    <form method="POST" action="" class="block">
                        <button type="submit" name="clock_in" 
                                class="w-full bg-green-500 hover:bg-green-600 text-white py-6 rounded-xl font-bold text-xl transition btn-pulse">
                            <i class="fas fa-sign-in-alt mr-3"></i>CLOCK IN
                        </button>
                        <p class="text-center text-gray-500 text-sm mt-3">
                            Tekan untuk mencatat waktu masuk
                        </p>
                    </form>
                    <?php elseif ($todayAttendance && $todayAttendance['clock_in'] && !$todayAttendance['clock_out']): ?>
                    <!-- Tombol Clock Out -->
                    <form method="POST" action="" class="block">
                        <button type="submit" name="clock_out" 
                                class="w-full bg-red-500 hover:bg-red-600 text-white py-6 rounded-xl font-bold text-xl transition btn-pulse">
                            <i class="fas fa-sign-out-alt mr-3"></i>CLOCK OUT
                        </button>
                        <p class="text-center text-gray-500 text-sm mt-3">
                            Tekan untuk mencatat waktu pulang
                        </p>
                    </form>
                    <?php else: ?>
                    <!-- Sudah完成 -->
                    <div class="text-center p-8 bg-gray-100 rounded-xl">
                        <i class="fas fa-check-double text-6xl text-green-500 mb-4"></i>
                        <p class="text-xl font-semibold text-gray-700">Absensi Hari Ini Selesai!</p>
                        <p class="text-gray-500 mt-2">Sampai jumpa besok</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Riwayat Absensi -->
        <div class="bg-white rounded-xl shadow-sm">
            <div class="p-6 border-b">
                <h2 class="text-xl font-semibold text-gray-800">
                    <i class="fas fa-history mr-2 text-indigo-500"></i>Riwayat Absensi Saya
                </h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Clock In</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Clock Out</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Durasi</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php foreach ($userAttendances as $i => $attendance): 
                            $durasi = '';
                            if ($attendance['clock_in'] && $attendance['clock_out']) {
                                $in = new DateTime($attendance['clock_in']);
                                $out = new DateTime($attendance['clock_out']);
                                $diff = $in->diff($out);
                                $durasi = $diff->h . 'j ' . $diff->i . 'm';
                            }
                        ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= $i + 1 ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-800">
                                <?= date('d/m/Y', strtotime($attendance['tanggal'])) ?>
                                <?php if ($attendance['tanggal'] === date('Y-m-d')): ?>
                                <span class="ml-2 px-2 py-0.5 bg-indigo-100 text-indigo-700 text-xs rounded">Hari Ini</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                <?= $attendance['clock_in'] ? date('H:i:s', strtotime($attendance['clock_in'])) : '<span class="text-red-500">-</span>' ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                <?= $attendance['clock_out'] ? date('H:i:s', strtotime($attendance['clock_out'])) : '<span class="text-orange-500">Belum</span>' ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                <?= $durasi ?: '<span class="text-gray-400">-</span>' ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if ($attendance['clock_out']): ?>
                                <span class="px-2 py-1 bg-green-100 text-green-700 text-xs font-medium rounded-full">
                                    <i class="fas fa-check mr-1"></i>Selesai
                                </span>
                                <?php else: ?>
                                <span class="px-2 py-1 bg-yellow-100 text-yellow-700 text-xs font-medium rounded-full">
                                    <i class="fas fa-clock mr-1"></i>Belum Pulang
                                </span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($userAttendances)): ?>
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                <i class="fas fa-inbox text-4xl mb-3"></i>
                                <p>Belum ada riwayat absensi</p>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
        
    </main>
    
    <!-- Footer -->
    <footer class="bg-white border-t mt-8 py-6">
        <div class="max-w-7xl mx-auto px-4 text-center text-gray-500 text-sm">
            <p>&copy; <?= date('Y') ?> Sistem Absensi - Dibuat dengan <i class="fas fa-heart text-red-500"></i> menggunakan PHP & MySQL</p>
        </div>
    </footer>
    
    <script>
        // =====================================================
        // JAVASCRIPT UNTUK FUNGSIONALITAS
        // =====================================================
        
        // Update jam secara real-time
        function updateTime() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('id-ID', { hour12: false });
            document.getElementById('current-time').textContent = timeString;
        }
        setInterval(updateTime, 1000);
        
        // Tab functionality untuk Admin
        function showTab(tabName) {
            // Hide all tab contents
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.add('hidden');
            });
            
            // Remove active state from all tab buttons
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('text-indigo-600', 'border-b-2', 'border-indigo-600');
                btn.classList.add('text-gray-500');
            });
            
            // Show selected tab content
            const selectedContent = document.getElementById('content-' + tabName);
            if (selectedContent) {
                selectedContent.classList.remove('hidden');
            }
            
            // Add active state to selected tab button
            const selectedBtn = document.getElementById('tab-' + tabName);
            if (selectedBtn) {
                selectedBtn.classList.remove('text-gray-500');
                selectedBtn.classList.add('text-indigo-600', 'border-b-2', 'border-indigo-600');
            }
            
            // Update URL
            const url = new URL(window.location);
            url.searchParams.set('page', tabName);
            window.history.pushState({}, '', url);
        }
        
        // Handle browser back/forward
        window.addEventListener('popstate', () => {
            const url = new URL(window.location);
            const page = url.searchParams.get('page') || 'dashboard';
            showTab(page);
        });
        
        // Initialize tab from URL
        document.addEventListener('DOMContentLoaded', () => {
            const url = new URL(window.location);
            const page = url.searchParams.get('page');
            if (page) {
                showTab(page);
            }
        });
        
        // Auto-hide alerts after 5 seconds
        setTimeout(() => {
            const alerts = document.querySelectorAll('[class*="bg-green-100"], [class*="bg-red-100"]');
            alerts.forEach(alert => {
                alert.style.transition = 'opacity 0.5s';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            });
        }, 5000);
    </script>
    <?php endif; ?>

</body>
</html>
