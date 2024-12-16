<?php
// includes/Session.php
<?php
class Session {
    public static function start() {
        if(session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    public static function set($key, $value) {
        $_SESSION[$key] = $value;
    }
    
    public static function get($key, $default = null) {
        return $_SESSION[$key] ?? $default;
    }
    
    public static function destroy() {
        session_destroy();
        $_SESSION = [];
    }
    
    public static function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
    
    public static function requireLogin() {
        if(!self::isLoggedIn()) {
            header('Location: login.php');
            exit();
        }
    }
    
    public static function requireAdmin() {
        self::requireLogin();
        if($_SESSION['user_type'] !== 'admin') {
            header('Location: index.php');
            exit();
        }
    }
}