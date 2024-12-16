<?php
// api/get_replies.php

require_once '../includes/functions.php';

header('Content-Type: application/json');

if(!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$comment_id = isset($_GET['comment_id']) ? (int)$_GET['comment_id'] : 0;

$conn = getDbConnection();
$query = "SELECT r.*, u.full_name, u.user_id 
          FROM replies r 
          JOIN users u ON r.user_id = u.user_id 
          WHERE r.comment_id = ? 
          ORDER BY r.created_at ASC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $comment_id);
$stmt->execute();
$result = $stmt->get_result();

$replies = [];
while($row = $result->fetch_assoc()) {
    $replies[] = [
        'id' => $row['reply_id'],
        'content' => $row['content'],
        'author' => $row['full_name'],
        'user_id' => $row['user_id'],
        'created_at' => $row['created_at'],
        'can_delete' => ($_SESSION['user_type'] == 'admin' || $row['user_id'] == $_SESSION['user_id'])
    ];
}

echo json_encode(['success' => true, 'replies' => $replies]);
?>