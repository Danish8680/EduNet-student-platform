<?php
// api/search.php
<?php
require_once '../includes/functions.php';

header('Content-Type: application/json');

if(!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$query = isset($_GET['q']) ? $_GET['q'] : '';
$type = isset($_GET['type']) ? $_GET['type'] : 'all';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$conn = getDbConnection();
$results = [];

switch($type) {
    case 'posts':
        $sql = "SELECT p.*, u.full_name 
                FROM posts p 
                JOIN users u ON p.user_id = u.user_id 
                WHERE p.title LIKE ? OR p.content LIKE ? 
                ORDER BY p.created_at DESC 
                LIMIT ? OFFSET ?";
        $search_param = "%$query%";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssii", $search_param, $search_param, $limit, $offset);
        break;
        
    case 'materials':
        $sql = "SELECT m.*, u.full_name 
                FROM materials m 
                JOIN users u ON m.uploaded_by = u.user_id 
                WHERE m.title LIKE ? 
                ORDER BY m.created_at DESC 
                LIMIT ? OFFSET ?";
        $search_param = "%$query%";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sii", $search_param, $limit, $offset);
        break;
        
    default:
        // Search all content types
        $results['posts'] = [];
        $results['materials'] = [];
        $results['users'] = [];
        
        // Posts
        $sql = "SELECT p.*, u.full_name 
                FROM posts p 
                JOIN users u ON p.user_id = u.user_id 
                WHERE p.title LIKE ? OR p.content LIKE ? 
                ORDER BY p.created_at DESC LIMIT 5";
        $search_param = "%$query%";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $search_param, $search_param);
        $stmt->execute();
        $results['posts'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        // Materials
        $sql = "SELECT m.*, u.full_name 
                FROM materials m 
                JOIN users u ON m.uploaded_by = u.user_id 
                WHERE m.title LIKE ? 
                ORDER BY m.created_at DESC LIMIT 5";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $search_param);
        $stmt->execute();
        $results['materials'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        // Users
        $sql = "SELECT user_id, full_name, department 
                FROM users 
                WHERE full_name LIKE ? AND user_type != 'admin' 
                LIMIT 5";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $search_param);
        $stmt->execute();
        $results['users'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        echo json_encode(['success' => true, 'results' => $results]);
        exit();
}

$stmt->execute();
$results = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
echo json_encode(['success' => true, 'results' => $results]);
?>