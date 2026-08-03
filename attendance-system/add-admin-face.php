<?php
/**
 * Script untuk menambahkan face encoding ke user admin
 */

require_once 'db.php';

$pdo = getDBConnection();

// Sample face encoding (128-dimensional descriptor)
// This is a placeholder - in production, this should be captured from the actual user's face
$sampleFaceEncoding = [
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
];

try {
    $faceEncodingJson = json_encode($sampleFaceEncoding);
    
    $stmt = $pdo->prepare("UPDATE users SET face_encoding = ?, face_registered = 1 WHERE username = 'admin'");
    $stmt->execute([$faceEncodingJson]);
    
    if ($stmt->rowCount() > 0) {
        echo "Berhasil menambahkan face encoding untuk admin!\n";
        
        // Verify
        $stmt = $pdo->prepare("SELECT id, nama, username, face_registered FROM users WHERE username = 'admin'");
        $stmt->execute();
        $user = $stmt->fetch();
        
        echo "\nData admin:\n";
        echo "ID: " . $user['id'] . "\n";
        echo "Nama: " . $user['nama'] . "\n";
        echo "Username: " . $user['username'] . "\n";
        echo "Face Registered: " . ($user['face_registered'] ? 'Ya' : 'Tidak') . "\n";
    } else {
        echo "Tidak ada perubahan. Admin mungkin tidak ditemukan.\n";
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
