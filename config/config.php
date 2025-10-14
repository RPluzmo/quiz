<?php
/**
 * Application Configuration
 */

// Enable errors (for debugging)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session globally
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Define base paths
define('BASE_PATH', dirname(__DIR__));
define('BASE_URL', 'http://localhost');

// Autoload classes from /classes
spl_autoload_register(function ($class_name) {
    $file = BASE_PATH . '/classes/' . $class_name . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// Include database configuration
require_once BASE_PATH . '/config/database.php';

// Timezone
date_default_timezone_set('UTC');
?>
