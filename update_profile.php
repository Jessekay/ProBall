<?php
header('Content-Type: application/json');
require_once 'db_config.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$userId = $_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE users SET email = ?, first_name = ?, last_name = ?, phone = ? WHERE id = ?");
    $stmt->execute([
        $data['email'],
        $data['first_name'],
        $data['last_name'],
        $data['phone'],
        $userId
    ]);

    echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>