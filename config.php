<?php
session_start();
session_regenerate_id(true);
date_default_timezone_set('Africa/Cairo');

// Database configuration
$host = "localhost";
$dbname = "u125244766_system";
$username = "u125244766_system";
$password = "Com@1212";

try {
    $conn = new mysqli($host, $username, $password, $dbname);
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    $conn->set_charset("utf8mb4");
} catch (Exception $e) {
    die(json_encode(['error' => $e->getMessage()]));
}

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Session timeout (30 minutes)
$timeout = 1800;
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit;
}
$_SESSION['last_activity'] = time();
?>