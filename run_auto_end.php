<?php
session_start();
date_default_timezone_set('Africa/Cairo');

// 1️⃣ لازم يكون الأدمن مسجل دخول
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: /404.php");
    exit;
}

// 2️⃣ لازم يكون فيه Password في URL
$secret = 'Lavida_!EndShift2025';
if (!isset($_GET['key']) || $_GET['key'] !== $secret) {
    header("Location: /404.php");
    exit;
}

$host = "localhost";
$user = "u125244766_system";
$password = "Com@1212";
$database = "u125244766_system";

$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->query("SET time_zone = 'Africa/Cairo';");

$sql = "
INSERT INTO attendance (username, action_type, action_time)
SELECT a.username, 'end', NOW()
FROM (
    SELECT username, MAX(action_time) AS last_start
    FROM attendance
    WHERE action_type = 'start'
    GROUP BY username
) AS a
LEFT JOIN (
    SELECT username, MAX(action_time) AS last_end
    FROM attendance
    WHERE action_type = 'end'
    GROUP BY username
) AS b ON a.username = b.username
WHERE b.last_end IS NULL OR a.last_start > b.last_end
";

if ($conn->query($sql) === TRUE) {
    echo "✅ Shifts closed successfully at " . date('Y-m-d H:i:s');
    file_put_contents('cron_end_log.txt', "Executed at: " . date('Y-m-d H:i:s') . "\n", FILE_APPEND);
} else {
    echo "❌ Error closing shifts: " . $conn->error;
    file_put_contents('cron_end_log.txt', "Error at: " . date('Y-m-d H:i:s') . " - " . $conn->error . "\n", FILE_APPEND);
}

$conn->close();
?>
