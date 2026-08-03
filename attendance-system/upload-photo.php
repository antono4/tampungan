<?php
/**
 * APLIKASI ABSENSI - Upload Photo
 * Halaman untuk upload dan mengelola foto profil user
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

// =====================================================
// CEK LOGIN
// =====================================================
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    header('Location: index.php');
    exit;
}

$userId = $_SESSION['user_id'];
$userName = $_SESSION['user_name'];
$userRole = $_SESSION['user_role'];

// =====================================================
// LOGIKA UPLOAD FOTO
// =====================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_photo'])) {
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['photo'];
        
        // Validasi file
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $maxSize = 5 * 1024 * 1024; // 5MB
        
        $fileType = mime_content_type($file['tmp_name']);
        $fileSize = $file['size'];
        
        if (!in_array($fileType, $allowedTypes)) {
            $error = 'Tipe file tidak diizinkan! Gunakan JPG, PNG, GIF, atau WebP.';
        } elseif ($fileSize > $maxSize) {
            $error = 'Ukuran file terlalu besar! Maksimal 5MB.';
        } else {
            // Baca file sebagai binary
            $photoData = file_get_contents($file['tmp_name']);
            
            // Simpan ke database
            $result = savePhoto($pdo, $userId, $photoData, $fileType);
            
            if ($result['success']) {
                $success = 'Foto berhasil diupload!';
            } else {
                $error = $result['message'];
            }
        }
    } else {
        $error = 'Gagal mengupload foto. Silakan coba lagi.';
    }
}

// =====================================================
// LOGIKA HAPUS FOTO
// =====================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_photo'])) {
    $result = deletePhoto($pdo, $userId);
    if ($result['success']) {
        $success = 'Foto berhasil dihapus!';
    } else {
        $error = $result['message'];
    }
}

// =====================================================
// LOGIKA AMBIL FOTO DARI KAMERA
// =====================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['capture_photo'])) {
    $photoData = $_POST['photo_data'] ?? '';
    
    if (empty($photoData)) {
        $error = 'Data foto tidak valid!';
    } else {
        // Hapus prefix data URL jika ada
        if (strpos($photoData, ',') !== false) {
            $photoData = explode(',', $photoData)[1];
        }
        
        $photoBinary = base64_decode($photoData);
        $photoType = 'image/jpeg';
        
        // Validasi ukuran
        if (strlen($photoBinary) > 5 * 1024 * 1024) {
            $error = 'Ukuran foto terlalu besar! Maksimal 5MB.';
        } else {
            $result = savePhoto($pdo, $userId, $photoBinary, $photoType);
            
            if ($result['success']) {
                $success = 'Foto dari kamera berhasil disimpan!';
            } else {
                $error = $result['message'];
            }
        }
    }
}

// =====================================================
// AMBIL DATA FOTO USER
// =====================================================
$userPhoto = getPhoto($pdo, $userId);
$hasPhoto = $userPhoto && !empty($userPhoto['photo']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Foto - Sistem Absensi</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
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
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .photo-preview {
            width: 200px;
            height: 200px;
            object-fit: cover;
            border-radius: 50%;
            border: 4px solid #667eea;
        }
        #camera-container {
            position: relative;
            width: 320px;
            height: 240px;
            background: #1a1a2e;
            border-radius: 12px;
            overflow: hidden;
        }
        #webcam {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transform: scaleX(-1);
        }
        #canvas-capture {
            display: none;
        }
    </style>
</head>
<body class="min-h-screen bg-gray-100 font-sans">

    <!-- Header -->
    <nav class="bg-white shadow-md sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-indigo-600 rounded-lg flex items-center justify-center">
                        <i class="fas fa-camera text-white"></i>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-gray-800">Upload Foto</h1>
                        <p class="text-xs text-gray-500"><?= $userRole === 'admin' ? 'Mode Administrator' : 'Dashboard Karyawan' ?></p>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="index.php" class="text-gray-600 hover:text-indigo-600">
                        <i class="fas fa-home mr-1"></i> Dashboard
                    </a>
                    <a href="index.php?action=logout" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition text-sm">
                        <i class="fas fa-sign-out-alt mr-1"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-4xl mx-auto px-4 py-8">
        <!-- Messages -->
        <?php if ($error): ?>
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-lg mb-6">
            <div class="flex items-center">
                <i class="fas fa-exclamation-circle mr-3"></i>
                <span><?= htmlspecialchars($error) ?></span>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-lg mb-6">
            <div class="flex items-center">
                <i class="fas fa-check-circle mr-3"></i>
                <span><?= htmlspecialchars($success) ?></span>
            </div>
        </div>
        <?php endif; ?>

        <div class="grid md:grid-cols-2 gap-8">
            <!-- Photo Preview & Upload -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-6">
                    <i class="fas fa-user-circle mr-2 text-indigo-500"></i>
                    Foto Profil
                </h2>
                
                <!-- Current Photo -->
                <div class="text-center mb-6">
                    <?php if ($hasPhoto): ?>
                    <img src="view-photo.php?user_id=<?= $userId ?>" 
                         alt="Foto Profil" 
                         class="photo-preview mx-auto shadow-lg">
                    <p class="mt-3 text-sm text-gray-500">
                        <i class="fas fa-check-circle text-green-500 mr-1"></i> Foto sudah diupload
                    </p>
                    <?php else: ?>
                    <div class="w-48 h-48 mx-auto rounded-full bg-gray-200 flex items-center justify-center shadow-lg">
                        <i class="fas fa-user text-6xl text-gray-400"></i>
                    </div>
                    <p class="mt-3 text-sm text-gray-500">
                        <i class="fas fa-info-circle text-blue-500 mr-1"></i> Belum ada foto
                    </p>
                    <?php endif; ?>
                </div>
                
                <!-- Upload Form -->
                <form method="POST" action="" enctype="multipart/form-data" class="space-y-4">
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">
                            <i class="fas fa-image mr-2 text-indigo-500"></i>Pilih Foto
                        </label>
                        <input type="file" name="photo" accept="image/jpeg,image/png,image/gif,image/webp" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <p class="mt-1 text-xs text-gray-500">Format: JPG, PNG, GIF, WebP. Maksimal: 5MB</p>
                    </div>
                    
                    <button type="submit" name="upload_photo" 
                            class="w-full px-4 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition font-medium">
                        <i class="fas fa-upload mr-2"></i>Upload Foto
                    </button>
                </form>
                
                <!-- Delete Photo -->
                <?php if ($hasPhoto): ?>
                <form method="POST" action="" class="mt-4">
                    <button type="submit" name="delete_photo" 
                            class="w-full px-4 py-3 bg-red-500 text-white rounded-lg hover:bg-red-600 transition font-medium"
                            onclick="return confirm('Yakin ingin menghapus foto?')">
                        <i class="fas fa-trash mr-2"></i>Hapus Foto
                    </button>
                </form>
                <?php endif; ?>
            </div>
            
            <!-- Camera Capture -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-6">
                    <i class="fas fa-camera-retro mr-2 text-indigo-500"></i>
                    Ambil Foto dengan Kamera
                </h2>
                
                <div class="text-center">
                    <div id="camera-container" class="mx-auto mb-4">
                        <video id="webcam" autoplay playsinline muted></video>
                    </div>
                    <canvas id="canvas-capture"></canvas>
                    
                    <div id="camera-status" class="mb-4 text-sm text-gray-600">
                        <i class="fas fa-circle-notch fa-spin mr-1"></i> Klik "Mulai Kamera" untuk memulai
                    </div>
                    
                    <div class="space-x-2">
                        <button type="button" id="start-camera-btn" class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                            <i class="fas fa-play mr-2"></i>Mulai Kamera
                        </button>
                        <button type="button" id="stop-camera-btn" class="px-6 py-3 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition hidden">
                            <i class="fas fa-stop mr-2"></i>Stop Kamera
                        </button>
                    </div>
                    
                    <div id="capture-section" class="mt-4 hidden">
                        <button type="button" id="capture-btn" class="px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                            <i class="fas fa-camera mr-2"></i>Ambil Foto
                        </button>
                    </div>
                    
                    <!-- Preview captured photo -->
                    <div id="preview-section" class="mt-4 hidden">
                        <img id="preview-image" class="w-48 h-48 object-cover rounded-lg mx-auto shadow-lg border-2 border-indigo-500">
                        <p class="mt-2 text-sm text-gray-500">Preview Foto</p>
                        <form method="POST" action="" id="save-camera-form" class="mt-4">
                            <input type="hidden" name="capture_photo" value="1">
                            <input type="hidden" name="photo_data" id="photo-data-input">
                            <button type="submit" class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                                <i class="fas fa-save mr-2"></i>Simpan Foto
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Info Box -->
        <div class="mt-8 bg-blue-50 border-l-4 border-blue-500 p-4 rounded-lg">
            <h3 class="font-semibold text-blue-800 mb-2">
                <i class="fas fa-lightbulb mr-2"></i>Tips Foto yang Baik
            </h3>
            <ul class="text-sm text-blue-700 space-y-1">
                <li>• Gunakan pencahayaan yang cukup (hindari backlight)</li>
                <li>• Posisi wajah menghadap kamera secara langsung</li>
                <li>• Hapus aksesoris seperti topi atau kacamata hitam</li>
                <li>• Pastikan wajah terlihat jelas dan tidak terhalang</li>
                <li>• Foto akan digunakan untuk absensi dan face recognition</li>
            </ul>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t mt-8 py-6">
        <div class="max-w-7xl mx-auto px-4 text-center text-gray-500 text-sm">
            <p>&copy; <?= date('Y') ?> Sistem Absensi - Upload Foto Profil</p>
        </div>
    </footer>

    <script>
        // Camera functionality
        let videoStream = null;
        const webcam = document.getElementById('webcam');
        const canvas = document.getElementById('canvas-capture');
        const startBtn = document.getElementById('start-camera-btn');
        const stopBtn = document.getElementById('stop-camera-btn');
        const captureSection = document.getElementById('capture-section');
        const previewSection = document.getElementById('preview-section');
        const previewImage = document.getElementById('preview-image');
        const photoDataInput = document.getElementById('photo-data-input');
        const cameraStatus = document.getElementById('camera-status');
        
        startBtn.addEventListener('click', async function() {
            try {
                videoStream = await navigator.mediaDevices.getUserMedia({ 
                    video: { 
                        width: 640, 
                        height: 480,
                        facingMode: 'user'
                    } 
                });
                
                webcam.srcObject = videoStream;
                startBtn.classList.add('hidden');
                stopBtn.classList.remove('hidden');
                captureSection.classList.remove('hidden');
                cameraStatus.innerHTML = '<i class="fas fa-check-circle text-green-500 mr-1"></i> Kamera aktif!';
            } catch (error) {
                console.error('Error accessing camera:', error);
                cameraStatus.innerHTML = '<i class="fas fa-exclamation-circle text-red-500 mr-1"></i> Tidak dapat mengakses kamera';
                alert('Tidak dapat mengakses kamera. Pastikan izin kamera diberikan.');
            }
        });
        
        stopBtn.addEventListener('click', function() {
            if (videoStream) {
                videoStream.getTracks().forEach(track => track.stop());
                videoStream = null;
            }
            
            webcam.srcObject = null;
            startBtn.classList.remove('hidden');
            stopBtn.classList.add('hidden');
            captureSection.classList.add('hidden');
            previewSection.classList.add('hidden');
            cameraStatus.innerHTML = '<i class="fas fa-circle-notch fa-spin mr-1"></i> Klik "Mulai Kamera" untuk memulai';
        });
        
        document.getElementById('capture-btn').addEventListener('click', function() {
            canvas.width = webcam.videoWidth;
            canvas.height = webcam.videoHeight;
            
            const ctx = canvas.getContext('2d');
            ctx.drawImage(webcam, 0, 0);
            
            const dataUrl = canvas.toDataURL('image/jpeg', 0.8);
            previewImage.src = dataUrl;
            photoDataInput.value = dataUrl;
            
            previewSection.classList.remove('hidden');
        });
        
        // Auto-hide alerts
        setTimeout(() => {
            const alerts = document.querySelectorAll('[class*="bg-green-100"], [class*="bg-red-100"]');
            alerts.forEach(alert => {
                alert.style.transition = 'opacity 0.5s';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            });
        }, 5000);
    </script>
</body>
</html>
