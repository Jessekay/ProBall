<?php
header('Content-Type: application/json');
require_once 'db_config.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$userId = $_SESSION['user_id'];

if (!isset($_FILES['avatar'])) {
    echo json_encode(['success' => false, 'message' => 'No file uploaded']);
    exit;
}

$avatar = $_FILES['avatar'];

// Validate file
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
if (!in_array($avatar['type'], $allowedTypes)) {
    echo json_encode(['success' => false, 'message' => 'Only JPG, PNG, and GIF images are allowed']);
    exit;
}

if ($avatar['size'] > 2 * 1024 * 1024) {
    echo json_encode(['success' => false, 'message' => 'Image size should be less than 2MB']);
    exit;
}

// Create uploads directory if it doesn't exist
if (!file_exists('uploads')) {
    mkdir('uploads', 0777, true);
}

// Generate unique filename
$extension = pathinfo($avatar['name'], PATHINFO_EXTENSION);
$filename = 'avatar_' . $userId . '_' . time() . '.' . $extension;
$filepath = 'uploads/' . $filename;

// Move uploaded file
if (move_uploaded_file($avatar['tmp_name'], $filepath)) {
    try {
        // Update database with new avatar path
        $stmt = $pdo->prepare("UPDATE users SET avatar = ? WHERE id = ?");
        $stmt->execute([$filename, $userId]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Avatar updated successfully',
            'avatarUrl' => $filepath
        ]);
    } catch (PDOException $e) {
        unlink($filepath); // Delete the uploaded file if DB update fails
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to upload avatar']);
}
?>