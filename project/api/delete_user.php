<?php
// api/delete_user.php

require_once '../includes/functions.php';

header('Content-Type: application/json');

if(!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$user_id = isset($data['user_id']) ? $data['user_id'] : '';

$conn = getDbConnection();

// Start transaction
$conn->begin_transaction();

try {
    // Delete user's comments
    $stmt = $conn->prepare("DELETE FROM comments WHERE user_id = ?");
    $stmt->bind_param("s", $user_id);
    $stmt->execute();

    // Delete user's posts
    $stmt = $conn->prepare("DELETE FROM posts WHERE user_id = ?");
    $stmt->bind_param("s", $user_id);
    $stmt->execute();

    // Delete user
    $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ? AND user_type != 'admin'");
    $stmt->bind_param("s", $user_id);
    $stmt->execute();

    $conn->commit();
    echo json_encode(['success' => true]);
} catch(Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>