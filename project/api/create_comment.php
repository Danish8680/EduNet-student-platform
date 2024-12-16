<?php
// api/create_comment.php

require_once '../includes/functions.php';

header('Content-Type: application/json');

if(!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);

// Validate input
if(empty($data['post_id']) || empty($data['content'])) {
    echo json_encode(['success' => false, 'message' => 'Post ID and content are required']);
    exit();
}

$conn = getDbConnection();

// Insert comment
$stmt = $conn->prepare("INSERT INTO comments (post_id, user_id, content) VALUES (?, ?, ?)");
$stmt->bind_param("iss", 
    $data['post_id'],
    $_SESSION['user_id'],
    $data['content']
);

if($stmt->execute()) {
    // Get comment details with user info
    $comment_id = $conn->insert_id;
    $query = "SELECT c.*, u.full_name 
              FROM comments c 
              JOIN users u ON c.user_id = u.user_id 
              WHERE c.comment_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $comment_id);
    $stmt->execute();
    $comment = $stmt->get_result()->fetch_assoc();

    echo json_encode([
        'success' => true,
        'comment' => [
            'id' => $comment['comment_id'],
            'content' => $comment['content'],
            'author' => $comment['full_name'],
            'created_at' => $comment['created_at']
        ]
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to create comment']);
}

// api/delete_comment.php
<?php
require_once '../includes/functions.php';

header('Content-Type: application/json');

if(!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$comment_id = isset($data['comment_id']) ? (int)$data['comment_id'] : 0;

$conn = getDbConnection();

// Check if user owns comment or is admin
$check_query = "SELECT user_id FROM comments WHERE comment_id = ?";
$stmt = $conn->prepare($check_query);
$stmt->bind_param("i", $comment_id);
$stmt->execute();
$result = $stmt->get_result();
$comment = $result->fetch_assoc();

if(!$comment || ($_SESSION['user_type'] != 'admin' && $comment['user_id'] != $_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Permission denied']);
    exit();
}

// Delete comment and its replies
$conn->begin_transaction();

try {
    // Delete replies first
    $stmt = $conn->prepare("DELETE FROM replies WHERE comment_id = ?");
    $stmt->bind_param("i", $comment_id);
    $stmt->execute();

    // Delete comment
    $stmt = $conn->prepare("DELETE FROM comments WHERE comment_id = ?");
    $stmt->bind_param("i", $comment_id);
    $stmt->execute();

    $conn->commit();
    echo json_encode(['success' => true]);
} catch(Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>