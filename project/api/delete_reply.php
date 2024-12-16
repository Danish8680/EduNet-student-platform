<?php
// api/delete_reply.php

require_once '../includes/functions.php';

header('Content-Type: application/json');

if(!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$reply_id = isset($data['reply_id']) ? (int)$data['reply_id'] : 0;

$conn = getDbConnection();

// Check if user owns reply or is admin
$check_query = "SELECT user_id FROM replies WHERE reply_id = ?";
$stmt = $conn->prepare($check_query);
$stmt->bind_param("i", $reply_id);
$stmt->execute();
$result = $stmt->get_result();
$reply = $result->fetch_assoc();

if(!$reply || ($_SESSION['user_type'] != 'admin' && $reply['user_id'] != $_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Permission denied']);
    exit();
}

$stmt = $conn->prepare("DELETE FROM replies WHERE reply_id = ?");
$stmt->bind_param("i", $reply_id);

if($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to delete reply']);
}
?>