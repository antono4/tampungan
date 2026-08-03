<?php
/**
 * API Endpoint untuk Face Recognition Data
 * Mengembalikan data user dengan face yang terdaftar
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

try {
    require_once '../db.php';
    
    $pdo = getDBConnection();
    $users = getAllUsersWithFace($pdo);
    
    // Return only necessary data (without face encoding for listing)
    $safeUsers = array_map(function($user) {
        return [
            'id' => $user['id'],
            'nama' => $user['nama'],
            'username' => $user['username'],
            'face_registered' => $user['face_registered']
        ];
    }, $users);
    
    echo json_encode($safeUsers);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
}
