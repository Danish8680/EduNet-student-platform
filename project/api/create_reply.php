<?php
// api/create_reply.php

require_once '../includes/functions.php';

header('Content-Type: application/json');

if(!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);

// Validate input
if(empty($data['comment_id']) || empty($data['content'])) {
    echo json_encode(['success' => false, 'message' => 'Comment ID and content are required']);
    exit();
}

$conn = getDbConnection();

// Insert reply
$stmt = $conn->prepare("INSERT INTO replies (comment_id, user_id, content) VALUES (?, ?, ?)");
$stmt->bind_param("iss", 
    $data['comment_id'],
    $_SESSION['user_id'],
    $data['content']
);

if($stmt->execute()) {
    // Get reply with user info
    $reply_id = $conn->insert_id;
    $query = "SELECT r.*, u.full_name 
              FROM replies r 
              JOIN users u ON r.user_id = u.user_id 
              WHERE r.reply_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $reply_id);
    $stmt->execute();
    $reply = $stmt->get_result()->fetch_assoc();

    echo json_encode([
        'success' => true,
        'reply' => [
            'id' => $reply['reply_id'],
            'content' => $reply['content'],
            'author' => $reply['full_name'],
            'created_at' => $reply['created_at']
        ]
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to create reply']);
}
?>