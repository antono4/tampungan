<?php
/**
 * APLIKASI ABSENSI - Face Recognition Login
 * Full Stack PHP + MySQL + Tailwind CSS + face-api.js
 * 
 * File: face-login.php (Face Recognition Login Page)
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
$message = '';

// =====================================================
// LOGIKA LOGOUT
// =====================================================
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header('Location: face-login.php');
    exit;
}

// =====================================================
// LOGIKA LOGIN DENGAN FACE RECOGNITION
// =====================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['face_login'])) {
    $userId = (int)($_POST['user_id'] ?? 0);
    
    if ($userId > 0) {
        $result = faceLogin($pdo, $userId);
        if ($result['success']) {
            $user = $result['user'];
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['nama'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['logged_in'] = true;
            $_SESSION['login_method'] = 'face';
            
            header('Location: index.php');
            exit;
        } else {
            $error = $result['message'];
        }
    } else {
        $error = 'User tidak valid!';
    }
}

// =====================================================
// LOGIKA SAVE FACE ENCODING
// =====================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_face'])) {
    if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
        $error = 'Anda harus login terlebih dahulu!';
    } else {
        $userId = $_SESSION['user_id'];
        $faceEncoding = $_POST['face_encoding'] ?? '';
        
        if (empty($faceEncoding)) {
            $error = 'Face encoding tidak boleh kosong!';
        } else {
            $result = saveFaceEncoding($pdo, $userId, $faceEncoding);
            if ($result['success']) {
                $success = $result['message'];
            } else {
                $error = $result['message'];
            }
        }
    }
}

// =====================================================
// LOGIKA DELETE FACE ENCODING
// =====================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_face'])) {
    if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
        $error = 'Anda harus login terlebih dahulu!';
    } else {
        $userId = $_SESSION['user_id'];
        $result = deleteFaceEncoding($pdo, $userId);
        if ($result['success']) {
            $success = $result['message'];
        } else {
            $error = $result['message'];
        }
    }
}

// =====================================================
// CEK SESSION
// =====================================================
$isLoggedIn = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
$userId = $_SESSION['user_id'] ?? null;
$userName = $_SESSION['user_name'] ?? '';

// =====================================================
// AMBIL DATA USER DAN FACE STATUS
// =====================================================
$currentUserFace = null;
$allUsersWithFace = [];

if ($isLoggedIn && $userId) {
    $currentUserFace = getFaceEncoding($pdo, $userId);
    $allUsersWithFace = getAllUsersWithFace($pdo);
}

// =====================================================
// TAMPILKAN HTML
// =====================================================
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Face Login - Sistem Absensi</title>
    
    <!-- Tailwind CSS via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome untuk Icon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Face-api.js -->
    <script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
    
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
        #video-container {
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
        #overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
        }
        .face-box {
            position: absolute;
            border: 3px solid #22c55e;
            border-radius: 8px;
            box-shadow: 0 0 20px rgba(34, 197, 94, 0.5);
        }
        .loading-spinner {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #667eea;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .status-indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            display: inline-block;
        }
        .status-active {
            background-color: #22c55e;
            animation: pulse-status 2s infinite;
        }
        @keyframes pulse-status {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
    </style>
</head>
<body class="min-h-screen bg-gray-100 font-sans">

    <?php if (!$isLoggedIn): ?>
    <!-- =====================================================
         HALAMAN LOGIN DENGAN FACE RECOGNITION
         ===================================================== -->
    <div class="min-h-screen gradient-bg flex items-center justify-center p-4">
        <div class="glass-effect rounded-2xl shadow-2xl w-full max-w-2xl p-8 fade-in">
            <div class="text-center mb-8">
                <div class="w-20 h-20 bg-indigo-600 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-face-smile text-4xl text-white"></i>
                </div>
                <h1 class="text-3xl font-bold text-gray-800">Login dengan Face Recognition</h1>
                <p class="text-gray-500 mt-2">Aktifkan kamera dan deteksi wajah Anda untuk login</p>
            </div>
            
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
                <!-- Kamera Section -->
                <div class="text-center">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4">
                        <i class="fas fa-camera mr-2 text-indigo-500"></i>Kamera
                    </h3>
                    
                    <div class="relative inline-block mb-4">
                        <div id="video-container" class="mx-auto">
                            <video id="webcam" autoplay playsinline muted></video>
                            <canvas id="overlay"></canvas>
                        </div>
                        <div id="camera-status" class="mt-3 text-sm text-gray-600">
                            <i class="fas fa-circle-notch fa-spin mr-1"></i> Memuat model face detection...
                        </div>
                    </div>
                    
                    <div class="space-x-2">
                        <button id="start-btn" type="button" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                            <i class="fas fa-play mr-1"></i> Mulai Kamera
                        </button>
                        <button id="stop-btn" type="button" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition hidden">
                            <i class="fas fa-stop mr-1"></i> Stop Kamera
                        </button>
                    </div>
                </div>
                
                <!-- Login Form Section -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-700 mb-4">
                        <i class="fas fa-user-check mr-2 text-indigo-500"></i>Pilih User untuk Login
                    </h3>
                    
                    <div id="detection-result" class="mb-4 p-4 bg-gray-50 rounded-lg border-2 border-dashed border-gray-300 text-center">
                        <i class="fas fa-user-secret text-4xl text-gray-400 mb-2"></i>
                        <p class="text-gray-500">Arahkan wajah ke kamera untuk deteksi</p>
                    </div>
                    
                    <div id="detected-user-form" class="hidden">
                        <form method="POST" action="" class="space-y-4">
                            <input type="hidden" name="face_login" value="1">
                            <input type="hidden" name="user_id" id="selected-user-id">
                            
                            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center mr-3">
                                        <i class="fas fa-check text-white"></i>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-green-800">Wajah Terdeteksi!</p>
                                        <p class="text-sm text-green-600" id="detected-user-name">User Name</p>
                                    </div>
                                </div>
                            </div>
                            
                            <button type="submit" class="w-full px-4 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-medium">
                                <i class="fas fa-sign-in-alt mr-2"></i> Login dengan Face
                            </button>
                        </form>
                    </div>
                    
                    <div class="mt-6 text-center">
                        <a href="index.php" class="text-indigo-600 hover:text-indigo-800 text-sm">
                            <i class="fas fa-arrow-left mr-1"></i> Kembali ke Login Manual
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php else: ?>
    <!-- =====================================================
         HALAMAN FACE REGISTRATION (SETELAH LOGIN)
         ===================================================== -->
    <div class="min-h-screen bg-gray-100">
        <!-- Header -->
        <nav class="bg-white shadow-md">
            <div class="max-w-7xl mx-auto px-4 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <i class="fas fa-face-smile text-2xl text-indigo-600 mr-3"></i>
                        <span class="text-xl font-bold text-gray-800">Face Recognition</span>
                    </div>
                    <div class="flex items-center space-x-4">
                        <span class="text-gray-600">
                            <i class="fas fa-user mr-1"></i> <?= htmlspecialchars($userName) ?>
                        </span>
                        <a href="face-login.php?action=logout" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition text-sm">
                            <i class="fas fa-sign-out-alt mr-1"></i> Logout
                        </a>
                    </div>
                </div>
            </div>
        </nav>
        
        <main class="max-w-7xl mx-auto px-4 py-8">
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
                <!-- Face Registration Section -->
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-6">
                        <i class="fas fa-id-card mr-2 text-indigo-500"></i>
                        <?= $currentUserFace && $currentUserFace['face_registered'] ? 'Face Sudah Terdaftar' : 'Registrasi Wajah' ?>
                    </h2>
                    
                    <?php if ($currentUserFace && $currentUserFace['face_registered']): ?>
                    <!-- Face Already Registered -->
                    <div class="text-center py-8">
                        <div class="w-24 h-24 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-check-circle text-5xl text-green-500"></i>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-700 mb-2">Wajah Anda sudah terdaftar!</h3>
                        <p class="text-gray-500 mb-6">Anda bisa login menggunakan face recognition.</p>
                        
                        <form method="POST" action="">
                            <input type="hidden" name="delete_face" value="1">
                            <button type="submit" class="px-6 py-3 bg-red-500 text-white rounded-lg hover:bg-red-600 transition" onclick="return confirm('Yakin ingin menghapus data wajah? Anda tidak bisa login dengan face recognition setelah ini.')">
                                <i class="fas fa-trash mr-2"></i> Hapus Data Wajah
                            </button>
                        </form>
                    </div>
                    <?php else: ?>
                    <!-- Face Registration Form -->
                    <div class="text-center">
                        <div class="relative inline-block mb-4">
                            <div id="video-container" class="mx-auto">
                                <video id="webcam" autoplay playsinline muted></video>
                                <canvas id="overlay"></canvas>
                            </div>
                            <div id="camera-status" class="mt-3 text-sm text-gray-600">
                                <i class="fas fa-circle-notch fa-spin mr-1"></i> Memuat model...
                            </div>
                        </div>
                        
                        <form method="POST" action="" id="face-form" class="hidden">
                            <input type="hidden" name="save_face" value="1">
                            <input type="hidden" name="face_encoding" id="face-encoding-input">
                            
                            <div id="registration-status" class="mb-4 p-3 bg-gray-50 rounded-lg">
                                <p class="text-gray-600"><i class="fas fa-info-circle mr-1"></i> <span id="status-text">Posisikan wajah Anda di depan kamera</span></p>
                            </div>
                            
                            <button type="button" id="capture-btn" class="px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition mb-4" disabled>
                                <i class="fas fa-camera mr-2"></i> Ambil Foto Wajah
                            </button>
                            
                            <button type="submit" id="save-btn" class="hidden px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                                <i class="fas fa-save mr-2"></i> Simpan Data Wajah
                            </button>
                        </form>
                        
                        <div class="mt-4">
                            <a href="index.php" class="text-indigo-600 hover:text-indigo-800 text-sm">
                                <i class="fas fa-arrow-left mr-1"></i> Kembali ke Dashboard
                            </a>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Registered Users Section -->
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-6">
                        <i class="fas fa-users mr-2 text-indigo-500"></i>
                        User dengan Face Terdaftar
                    </h2>
                    
                    <?php if (empty($allUsersWithFace)): ?>
                    <div class="text-center py-8">
                        <i class="fas fa-users-slash text-4xl text-gray-400 mb-3"></i>
                        <p class="text-gray-500">Belum ada user yang terdaftar dengan face recognition.</p>
                    </div>
                    <?php else: ?>
                    <div class="space-y-3">
                        <?php foreach ($allUsersWithFace as $user): ?>
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center mr-3">
                                    <i class="fas fa-user text-indigo-600"></i>
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-800"><?= htmlspecialchars($user['nama']) ?></p>
                                    <p class="text-sm text-gray-500"><?= htmlspecialchars($user['username']) ?></p>
                                </div>
                            </div>
                            <span class="status-indicator status-active" title="Face Terdaftar"></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
        
        <!-- Footer -->
        <footer class="bg-white border-t mt-8 py-6">
            <div class="max-w-7xl mx-auto px-4 text-center text-gray-500 text-sm">
                <p>&copy; <?= date('Y') ?> Sistem Absensi - Face Recognition Login</p>
            </div>
        </footer>
    </div>
    <?php endif; ?>

    <script>
        // =====================================================
        // FACE RECOGNITION JAVASCRIPT
        // =====================================================
        
        let isModelLoaded = false;
        let isCameraActive = false;
        let labeledFaceDescriptors = null;
        let faceMatcher = null;
        let videoStream = null;
        
        const webcamElement = document.getElementById('webcam');
        const overlayElement = document.getElementById('overlay');
        const startBtn = document.getElementById('start-btn');
        const stopBtn = document.getElementById('stop-btn');
        const cameraStatus = document.getElementById('camera-status');
        
        async function loadModels() {
            // Model source: Use CDN directly
            const MODEL_URL = 'https://justadudwohl.github.io/face-api.js/models';
            
            cameraStatus.innerHTML = '<i class="fas fa-circle-notch fa-spin mr-1"></i> Memuat model face detection...';
            
            try {
                // Load all required models from CDN
                await faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL);
                await faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL);
                await faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL);
                
                isModelLoaded = true;
                cameraStatus.innerHTML = '<i class="fas fa-check-circle text-green-500 mr-1"></i> Model siap (CDN)! Klik "Mulai Kamera" untuk memulai';
                startBtn.disabled = false;
                
                // Load registered faces
                await loadRegisteredFaces();
                
            } catch (error) {
                console.error('Failed to load models:', error);
                cameraStatus.innerHTML = '<i class="fas fa-exclamation-circle text-red-500 mr-1"></i> Gagal memuat model.<br>';
                cameraStatus.innerHTML += '<span class="text-xs">Error: ' + (error.message || 'Unknown error') + '</span><br>';
                cameraStatus.innerHTML += '<button onclick="loadModels()" class="mt-2 px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">Coba Lagi</button>';
                cameraStatus.innerHTML += '<br><br><span class="text-xs">Pastikan koneksi internet stabil.</span>';
            }
        }
        
        async function loadRegisteredFaces() {
            try {
                // Fetch all users with face registered
                const response = await fetch('api/get-face-data.php');
                if (!response.ok) return;
                
                const users = await response.json();
                
                if (users.length === 0) {
                    labeledFaceDescriptors = [];
                    return;
                }
                
                // Create labeled face descriptors
                const descriptors = [];
                
                for (const user of users) {
                    if (user.face_encoding) {
                        try {
                            const encoding = JSON.parse(user.face_encoding);
                            const float32Array = new Float32Array(encoding);
                            descriptors.push(new faceapi.LabeledFaceDescriptors(
                                user.id.toString(),
                                [float32Array]
                            ));
                        } catch (e) {
                            console.error('Error parsing face encoding for user', user.id, e);
                        }
                    }
                }
                
                if (descriptors.length > 0) {
                    labeledFaceDescriptors = descriptors;
                    faceMatcher = new faceapi.FaceMatcher(labeledFaceDescriptors, 0.6);
                }
            } catch (error) {
                console.error('Error loading registered faces:', error);
            }
        }
        
        async function startCamera() {
            try {
                videoStream = await navigator.mediaDevices.getUserMedia({ 
                    video: { 
                        width: 320, 
                        height: 240,
                        facingMode: 'user'
                    } 
                });
                
                webcamElement.srcObject = videoStream;
                isCameraActive = true;
                
                startBtn.classList.add('hidden');
                stopBtn.classList.remove('hidden');
                
                // Wait for video to be ready
                webcamElement.onloadedmetadata = () => {
                    startDetection();
                };
            } catch (error) {
                console.error('Error accessing camera:', error);
                cameraStatus.innerHTML = '<i class="fas fa-exclamation-circle text-red-500 mr-1"></i> Tidak dapat mengakses kamera. Izinkan akses kamera.';
            }
        }
        
        function stopCamera() {
            if (videoStream) {
                videoStream.getTracks().forEach(track => track.stop());
                videoStream = null;
            }
            
            webcamElement.srcObject = null;
            isCameraActive = false;
            
            startBtn.classList.remove('hidden');
            stopBtn.classList.add('hidden');
            cameraStatus.innerHTML = '<i class="fas fa-check-circle text-gray-500 mr-1"></i> Kamera berhenti';
        }
        
        async function startDetection() {
            if (!isCameraActive || !isModelLoaded) return;
            
            const options = new faceapi.TinyFaceDetectorOptions({
                inputSize: 320,
                scoreThreshold: 0.5
            });
            
            async function detect() {
                if (!isCameraActive) return;
                
                const detections = await faceapi.detectAllFaces(webcamElement, options)
                    .withFaceLandmarks()
                    .withFaceDescriptors();
                
                // Clear overlay
                const ctx = overlayElement.getContext('2d');
                ctx.clearRect(0, 0, overlayElement.width, overlayElement.height);
                
                // Resize overlay to match video
                const displaySize = { width: webcamElement.videoWidth, height: webcamElement.videoHeight };
                faceapi.matchDimensions(overlayElement, displaySize);
                
                if (detections.length > 0) {
                    // Draw face boxes
                    const resizedDetections = faceapi.resizeResults(detections, displaySize);
                    
                    resizedDetections.forEach(detection => {
                        const box = detection.detection.box;
                        ctx.strokeStyle = '#22c55e';
                        ctx.lineWidth = 3;
                        ctx.strokeRect(box.x, box.y, box.width, box.height);
                    });
                    
                    // Try to match face
                    if (faceMatcher && resizedDetections.length > 0) {
                        const match = faceMatcher.findBestMatch(resizedDetections[0].descriptor);
                        
                        if (match.label !== 'unknown') {
                            handleFaceDetected(match.label, match.distance);
                        } else {
                            handleUnknownFace();
                        }
                    } else {
                        // Registration mode - capture single face
                        handleFaceForRegistration(detections[0].descriptor);
                    }
                } else {
                    handleNoFace();
                }
                
                requestAnimationFrame(detect);
            }
            
            detect();
        }
        
        function handleFaceDetected(userId, distance) {
            const resultDiv = document.getElementById('detection-result');
            const formDiv = document.getElementById('detected-user-form');
            const selectedUserId = document.getElementById('selected-user-id');
            const detectedUserName = document.getElementById('detected-user-name');
            
            if (resultDiv) resultDiv.classList.add('hidden');
            if (formDiv) {
                formDiv.classList.remove('hidden');
                selectedUserId.value = userId;
                
                // Get username from registered users
                fetch('api/get-face-data.php')
                    .then(res => res.json())
                    .then(users => {
                        const user = users.find(u => u.id.toString() === userId);
                        if (user) {
                            detectedUserName.textContent = user.nama + ' (' + user.username + ')';
                        }
                    });
            }
        }
        
        function handleUnknownFace() {
            const resultDiv = document.getElementById('detection-result');
            const formDiv = document.getElementById('detected-user-form');
            
            if (resultDiv) {
                resultDiv.classList.remove('hidden');
                resultDiv.innerHTML = `
                    <i class="fas fa-question-circle text-4xl text-yellow-500 mb-2"></i>
                    <p class="text-yellow-600">Wajah tidak dikenal. Silakan login manual.</p>
                `;
            }
            if (formDiv) formDiv.classList.add('hidden');
        }
        
        function handleNoFace() {
            const resultDiv = document.getElementById('detection-result');
            const formDiv = document.getElementById('detected-user-form');
            
            if (resultDiv) {
                resultDiv.classList.remove('hidden');
                resultDiv.innerHTML = `
                    <i class="fas fa-user-secret text-4xl text-gray-400 mb-2"></i>
                    <p class="text-gray-500">Arahkan wajah ke kamera untuk deteksi</p>
                `;
            }
            if (formDiv) formDiv.classList.add('hidden');
        }
        
        function handleFaceForRegistration(descriptor) {
            const captureBtn = document.getElementById('capture-btn');
            const saveBtn = document.getElementById('save-btn');
            const faceEncodingInput = document.getElementById('face-encoding-input');
            const statusText = document.getElementById('status-text');
            
            if (captureBtn && !captureBtn.disabled) {
                statusText.textContent = 'Wajah terdeteksi! Klik "Ambil Foto Wajah" untuk menyimpan.';
                
                // Store descriptor for later use
                window.capturedDescriptor = descriptor;
            }
        }
        
        // Capture button click handler
        if (document.getElementById('capture-btn')) {
            document.getElementById('capture-btn').addEventListener('click', function() {
                if (window.capturedDescriptor) {
                    const faceEncodingInput = document.getElementById('face-encoding-input');
                    const saveBtn = document.getElementById('save-btn');
                    const captureBtn = document.getElementById('capture-btn');
                    const statusText = document.getElementById('status-text');
                    
                    // Convert descriptor to array and store
                    const descriptorArray = Array.from(window.capturedDescriptor);
                    faceEncodingInput.value = JSON.stringify(descriptorArray);
                    
                    saveBtn.classList.remove('hidden');
                    captureBtn.disabled = true;
                    captureBtn.innerHTML = '<i class="fas fa-check mr-2"></i> Wajah Tersimpan';
                    statusText.textContent = 'Wajah berhasil disimpan! Klik "Simpan Data Wajah" untuk menyelesaikan.';
                }
            });
        }
        
        // Event listeners
        if (startBtn) startBtn.addEventListener('click', startCamera);
        if (stopBtn) stopBtn.addEventListener('click', stopCamera);
        
        // Initialize
        loadModels();
    </script>
</body>
</html>
