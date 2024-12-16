<?php
// api/create_post.php (updated version)
<?php
require_once '../includes/functions.php';

header('Content-Type: application/json');

if(!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Handle both regular POST and JSON data
$title = isset($_POST['title']) ? $_POST['title'] : '';
$content = isset($_POST['content']) ? $_POST['content'] : '';

if(empty($title) || empty($content)) {
    echo json_encode(['success' => false, 'message' => 'Title and content are required']);
    exit();
}

$image_path = null;
if(isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $max_size = 5 * 1024 * 1024; // 5MB

    if(!in_array($_FILES['image']['type'], $allowed_types)) {
        echo json_encode(['success' => false, 'message' => 'Invalid file type']);
        exit();
    }

    if($_FILES['image']['size'] > $max_size) {
        echo json_encode(['success' => false, 'message' => 'File too large']);
        exit();
    }

    $upload_dir = '../uploads/posts/';
    if(!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
    $file_name = uniqid() . '.' . $file_extension;
    $upload_path = $upload_dir . $file_name;

    if(move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
        $image_path = 'uploads/posts/' . $file_name;
    }
}

$conn = getDbConnection();
$stmt = $conn->prepare("INSERT INTO posts (user_id, title, content, image_path) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", 
    $_SESSION['user_id'],
    $title,
    $content,
    $image_path
);

if($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'post_id' => $conn->insert_id,
        'message' => 'Post created successfully'
    ]);
} else {
    if($image_path) {
        unlink('../' . $image_path);
    }
    echo json_encode([
        'success' => false,
        'message' => 'Failed to create post'
    ]);
}
?>