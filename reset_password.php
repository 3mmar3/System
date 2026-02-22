<?php
session_start();

// تأكد إن المستخدم مسجل دخول وكأدمن
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}

header('Content-Type: application/json');

// استقبال البيانات من JSON
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['userId'], $data['newPassword'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing parameters']);
    exit;
}

$userId = (int)$data['userId'];
$newPassword = trim($data['newPassword']);

// تحقق من قوة الباسورد (8 حروف على الأقل وحرف كبير ورقم)
if (strlen($newPassword) < 8 || 
    !preg_match('/[A-Z]/', $newPassword) || 
    !preg_match('/[0-9]/', $newPassword)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Password does not meet criteria']);
    exit;
}

// إعداد اتصال قاعدة البيانات
$host = "localhost";
$dbname = "u125244766_system";
$dbUser = "u125244766_system";
$dbPass = "Com@1212";

$conn = new mysqli($host, $dbUser, $dbPass, $dbname);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

// تحضير تحديث كلمة المرور مع هاش آمن
$hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

$stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
$stmt->bind_param("si", $hashedPassword, $userId);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to update password']);
}

$stmt->close();
$conn->close();
