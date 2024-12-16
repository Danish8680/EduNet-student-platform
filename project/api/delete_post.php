<?php
// api/delete_post.php
require_once '../includes/functions.php';

header('Content-Type: application/json');

if(!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$post_id = isset($data['post_id']) ? (int)$data['post_id'] : 0;

$conn = getDbConnection();

// Check if user is admin or post owner
$check_query = "SELECT user_id FROM posts WHERE post_id = ?";
$stmt = $conn->prepare($check_query);
$stmt->bind_param("i", $post_id);
$stmt->execute();
$result = $stmt->get_result();
$post = $result->fetch_assoc();

if(!$post || ($_SESSION['user_type'] != 'admin' && $post['user_id'] != $_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Permission denied']);
    exit();
}

// Delete related comments first
$delete_comments = "DELETE FROM comments WHERE post_id = ?";
$stmt = $conn->prepare($delete_comments);
$stmt->bind_param("i", $post_id);
$stmt->execute();

// Delete post
$delete_post = "DELETE FROM posts WHERE post_id = ?";
$stmt = $conn->prepare($delete_post);
$stmt->bind_param("i", $post_id);

if($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>