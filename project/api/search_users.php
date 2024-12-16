<?php
// api/search_users.php

require_once '../includes/functions.php';

header('Content-Type: application/json');

if(!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$search = isset($_GET['q']) ? $_GET['q'] : '';
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;

$conn = getDbConnection();
$query = "SELECT user_id, full_name, department 
          FROM users 
          WHERE (full_name LIKE ? OR user_id LIKE ?) 
          AND user_type != 'admin'
          LIMIT ?";

$search_param = "%$search%";
$stmt = $conn->prepare($query);
$stmt->bind_param("ssi", $search_param, $search_param, $limit);
$stmt->execute();
$result = $stmt->get_result();

$users = [];
while($row = $result->fetch_assoc()) {
    $users[] = $row;
}

echo json_encode(['success' => true, 'users' => $users]);
?>