<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Site settings
define('SITE_NAME', 'Grace Fellowship Church');

// Manually define the base URL to avoid dynamic inconsistency issues
define('BASE_URL', 'http://localhost/grace-fellowship-website');

// Define base path for includes if needed
define('BASE_PATH', $_SERVER['DOCUMENT_ROOT'] . '/grace-fellowship-website');

// Database configuration
$db_host = 'localhost';
$db_name = 'grace_fellowship';
$db_user = 'root';
$db_pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$db_host;dbname=$db_name;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $conn = new PDO($dsn, $db_user, $db_pass, $options);
    $pdo = $conn; // Make both $conn and $pdo available for compatibility
} catch (\PDOException $e) {
    error_log('Database Connection Error: ' . $e->getMessage());
    die("A database error occurred. Please check logs for details.");
}

// Include utility functions
require_once __DIR__ . '/functions.php';
