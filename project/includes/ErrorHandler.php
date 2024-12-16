<?php
// includes/ErrorHandler.php
<?php
class ErrorHandler {
    private static $logFile = '../logs/error.log';
    
    public static function initialize() {
        error_reporting(E_ALL);
        set_error_handler([self::class, 'handleError']);
        set_exception_handler([self::class, 'handleException']);
        
        if(!is_dir('../logs')) {
            mkdir('../logs', 0777, true);
        }
    }
    
    public static function handleError($errno, $errstr, $errfile, $errline) {
        $error = date('Y-m-d H:i:s') . " [$errno] $errstr in $errfile on line $errline\n";
        error_log($error, 3, self::$logFile);
        
        if(ini_get('display_errors')) {
            echo "<div class='error'>An error occurred. Please try again later.</div>";
        }
        
        return true;
    }
    
    public static function handleException($exception) {
        $error = date('Y-m-d H:i:s') . " [Exception] " . $exception->getMessage() . 
                " in " . $exception->getFile() . " on line " . $exception->getLine() . "\n";
        error_log($error, 3, self::$logFile);
        
        http_response_code(500);
        if(ini_get('display_errors')) {
            echo "<div class='error'>An error occurred. Please try again later.</div>";
        }
    }
}