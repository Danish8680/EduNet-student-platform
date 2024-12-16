<?php
// download.php
<?php
require_once 'includes/functions.php';

if(!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header('Location: login.php');
    exit();
}

$material_id = (int)$_GET['id'];
$conn = getDbConnection();

$query = "SELECT file_path, title FROM materials WHERE material_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $material_id);
$stmt->execute();
$material = $stmt->get_result()->fetch_assoc();

if(!$material) {
    die('File not found');
}

$file_path = 'uploads/materials/' . $material['file_path'];
if(!file_exists($file_path)) {
    die('File not found');
}

$file_extension = pathinfo($file_path, PATHINFO_EXTENSION);
$content_types = [
    'pdf' => 'application/pdf',
    'doc' => 'application/msword',
    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'ppt' => 'application/vnd.ms-powerpoint',
    'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation'
];

$content_type = isset($content_types[$file_extension]) ? 
                $content_types[$file_extension] : 
                'application/octet-stream';

header('Content-Type: ' . $content_type);
header('Content-Disposition: attachment; filename="' . $material['title'] . '.' . $file_extension . '"');
header('Content-Length: ' . filesize($file_path));
header('Cache-Control: private');

readfile($file_path);
exit();
?>