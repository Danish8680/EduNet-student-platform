<?php
// config/config.php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Environment detection
$is_development = ($_SERVER['SERVER_NAME'] === 'localhost');

// Base paths
define('SITE_NAME', 'Edunet Student Platform');
define('BASE_URL', 'http://localhost/project');
define('ROOT_PATH', rtrim(str_replace('\\', '/', dirname(dirname(__FILE__))), '/') . '/');
define('UPLOAD_PATH', ROOT_PATH . 'uploads/');

// Database configuration
if (!defined('DB_HOST')) {
    define('DB_HOST', 'localhost');
}
if (!defined('DB_USER')) {
    define('DB_USER', 'root');
}
if (!defined('DB_PASS')) {
    define('DB_PASS', '');
}
if (!defined('DB_NAME')) {
    define('DB_NAME', 'edunet_platform');
}

// Upload limits and allowed types
define('UPLOAD_MAX_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif']);
define('ALLOWED_DOC_TYPES', [
    'application/pdf',
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
]);

// Configure error reporting
if ($is_development) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
}

// Set timezone
date_default_timezone_set('Asia/Kolkata');

// Create required directories
$directories = [
    UPLOAD_PATH,
    UPLOAD_PATH . 'materials',
    UPLOAD_PATH . 'posts',
    UPLOAD_PATH . 'profiles',
    ROOT_PATH . 'logs'
];

foreach ($directories as $dir) {
    if (!file_exists($dir)) {
        if (!mkdir($dir, 0777, true)) {
            die("Failed to create directory: $dir");
        }
    }
}

// Database connection function
if (!function_exists('getDbConnection')) {
    function getDbConnection() {
        static $conn = null;
        if ($conn === null) {
            $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }
            $conn->set_charset("utf8mb4");
        }
        return $conn;
    }
}

// Clean up function
function cleanUp() {
    if ($conn = getDbConnection()) {
        $conn->close();
    }
}

// Register cleanup
register_shutdown_function('cleanUp');

// Initialize database connection
getDbConnection();