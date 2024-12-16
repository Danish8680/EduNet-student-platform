<?php
// api/upload_material.php

require_once '../includes/functions.php';

header('Content-Type: application/json');

if(!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$allowed_types = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
$max_size = 10 * 1024 * 1024; // 10MB

if(!isset($_FILES['material_file']) || $_FILES['material_file']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'File upload error']);
    exit();
}

if(!in_array($_FILES['material_file']['type'], $allowed_types)) {
    echo json_encode(['success' => false, 'message' => 'Invalid file type']);
    exit();
}

if($_FILES['material_file']['size'] > $max_size) {
    echo json_encode(['success' => false, 'message' => 'File too large']);
    exit();
}

$file_name = time() . '_' . $_FILES['material_file']['name'];
$upload_path = '../uploads/materials/' . $file_name;

if(!move_uploaded_file($_FILES['material_file']['tmp_name'], $upload_path)) {
    echo json_encode(['success' => false, 'message' => 'File upload failed']);
    exit();
}

$conn = getDbConnection();
$stmt = $conn->prepare("INSERT INTO materials (title, file_path, course_type, year_sem, uploaded_by) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("sssss", $_POST['title'], $file_name, $_POST['course_type'], $_POST['year_sem'], $_SESSION['user_id']);

if($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    unlink($upload_path); // Delete uploaded file if database insert fails
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>