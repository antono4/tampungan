<?php
/**
 * Test page untuk cek apakah models sudah terdownload
 */
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Check Models</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .success { color: green; }
        .error { color: red; }
        .info { color: blue; }
        ul { line-height: 1.8; }
    </style>
</head>
<body>
    <h1>🔍 Model Check</h1>
    
    <h2>Lokasi Folder Models:</h2>
    <p><strong>Path:</strong> <?= __DIR__ ?>/models/</p>
    
    <h2>Status File:</h2>
    <ul>
        <?php
        $files = [
            'tiny_face_detector_model-weights_manifest.json',
            'tiny_face_detector_model-shard1',
            'face_landmark_68_model-weights_manifest.json',
            'face_landmark_68_model-shard1',
            'face_recognition_model-weights_manifest.json',
            'face_recognition_model-shard1'
        ];
        
        $allOk = true;
        foreach ($files as $file):
            $path = __DIR__ . '/models/' . $file;
            $exists = file_exists($path);
            $size = $exists ? filesize($path) : 0;
            if (!$exists) $allOk = false;
        ?>
            <li class="<?= $exists ? 'success' : 'error' ?>">
                <?= $exists ? '✅' : '❌' ?> <?= $file ?>
                <?php if ($exists): ?>
                    (<?= number_format($size) ?> bytes)
                <?php else: ?>
                    - FILE TIDAK ADA!
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>
    
    <h2>HTTP Access Test:</h2>
    <ul>
        <?php
        $testUrls = [
            'models/tiny_face_detector_model-weights_manifest.json',
            'models/face_landmark_68_model-weights_manifest.json',
            'models/face_recognition_model-weights_manifest.json'
        ];
        
        foreach ($testUrls as $url):
            $fullUrl = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . '/' . $url;
        ?>
            <li>
                <a href="<?= $fullUrl ?>" target="_blank"><?= $url ?></a>
                <?php if (file_exists(__DIR__ . '/' . $url)): ?>
                    <span class="success">✅ Bisa diakses</span>
                <?php else: ?>
                    <span class="error">❌ File tidak ada</span>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>
    
    <?php if ($allOk): ?>
        <p class="success"><strong>🎉 Semua model sudah terdownload dengan benar!</strong></p>
        <p>Silakan refresh halaman face-login.php</p>
    <?php else: ?>
        <p class="error"><strong>⚠️ Ada file yang belum terdownload!</strong></p>
        <p>Jalankan file: <code>models\download-models.bat</code></p>
    <?php endif; ?>
</body>
</html>
