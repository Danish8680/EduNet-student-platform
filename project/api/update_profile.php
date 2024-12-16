<?php
// api/update_profile.php

require_once '../includes/functions.php';

header('Content-Type: application/json');

if(!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);

$conn = getDbConnection();
$stmt = $conn->prepare("UPDATE users SET description = ?, department = ? WHERE user_id = ?");
$stmt->bind_param("sss", 
    $data['description'],
    $data['department'],
    $_SESSION['user_id']
);

if($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Update failed']);
}
?>