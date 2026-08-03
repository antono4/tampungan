<?php
/**
 * API Endpoint untuk menampilkan foto user
 */

session_start();
require_once 'db.php';

$pdo = getDBConnection();

$userId = $_GET['user_id'] ?? 0;

if ($userId > 0) {
    $photoData = getPhoto($pdo, $userId);
    
    if ($photoData && !empty($photoData['photo'])) {
        header('Content-Type: ' . ($photoData['photo_type'] ?? 'image/jpeg'));
        header('Cache-Control: max-age=3600');
        echo $photoData['photo'];
        exit;
    }
}

// Return placeholder image
header('Content-Type: image/svg+xml');
echo '<svg xmlns="http://www.w3.org/2000/svg" width="200" height="200" viewBox="0 0 200 200">
    <rect width="200" height="200" fill="#e5e7eb"/>
    <circle cx="100" cy="80" r="40" fill="#9ca3af"/>
    <path d="M 40 180 Q 40 120 100 120 Q 160 120 160 180" fill="#9ca3af"/>
</svg>';
