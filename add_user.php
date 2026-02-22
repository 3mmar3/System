

<?php
header('Content-Type: application/json');
session_start();

// تأكد من أن المستخدم مسجل دخول أو له صلاحية - يمكنك تعديل حسب نظامك
// if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
//     echo json_encode(['success' => false, 'message' => 'Unauthorized']);
//     exit;
// }

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$host = "localhost";
$dbname = "u125244766_system";
$dbuser = "u125244766_system";
$dbpass = "Com@1212";

$conn = new mysqli($host, $dbuser, $dbpass, $dbname);
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';
$role = $_POST['role'] ?? '';

if (empty($username) || empty($password) || empty($role)) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit;
}

// تحقق من عدم وجود المستخدم مسبقاً
$stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Username already exists']);
    $stmt->close();
    $conn->close();
    exit;
}
$stmt->close();

// تشفير الباسورد
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

$stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $username, $hashedPassword, $role);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to add user']);
}

$stmt->close();
$conn->close();
