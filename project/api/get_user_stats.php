<?php
// api/get_user_stats.php

require_once '../includes/functions.php';

header('Content-Type: application/json');

if(!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$user_id = isset($_GET['user_id']) ? $_GET['user_id'] : $_SESSION['user_id'];

$conn = getDbConnection();
$query = "SELECT 
    (SELECT COUNT(*) FROM posts WHERE user_id = ?) as total_posts,
    (SELECT COUNT(*) FROM comments WHERE user_id = ?) as total_comments,
    (SELECT COUNT(*) FROM replies WHERE user_id = ?) as total_replies";

$stmt = $conn->prepare($query);
$stmt->bind_param("sss", $user_id, $user_id, $user_id);
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();

echo json_encode(['success' => true, 'stats' => $stats]);
?>