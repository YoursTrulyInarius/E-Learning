<?php
// Base Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'littlelearners');

// Start Session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database Connection
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database Connection Failed: " . $e->getMessage());
}

// Core functions
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isTeacher() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'teacher';
}

function isStudent() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'student';
}

function getGradeLevel() {
    return isset($_SESSION['grade_level']) ? $_SESSION['grade_level'] : null;
}

function redirect($path) {
    header("Location: /E-Learning/" . ltrim($path, '/'));
    exit;
}

// Ensure base upload directories exist
$upload_dir = __DIR__ . '/uploads/videos';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}
?>
