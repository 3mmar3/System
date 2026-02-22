<?php
header('Content-Type: application/json');

$host = "localhost";
$dbname = "u125244766_system";
$username = "u125244766_system";
$password = "Com@1212";

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}
$conn->set_charset("utf8mb4");

$data = json_decode(file_get_contents('php://input'), true);
if (!$data || empty($data['roleName'])) {
    echo json_encode(['success' => false, 'message' => 'Role name is required']);
    exit;
}

$roleName = trim($data['roleName']);
$permissionsArray = isset($data['permissions']) && is_array($data['permissions']) ? $data['permissions'] : [];
$permissionsStr = implode(',', array_map('trim', $permissionsArray));

// تحقق إذا الدور موجود مسبقاً
$stmtCheck = $conn->prepare("SELECT id FROM roles WHERE role_name = ?");
$stmtCheck->bind_param("s", $roleName);
$stmtCheck->execute();
$stmtCheck->store_result();
if ($stmtCheck->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Role name already exists']);
    exit;
}
$stmtCheck->close();

// إدخال الدور الجديد
$stmtInsert = $conn->prepare("INSERT INTO roles (role_name, permissions) VALUES (?, ?)");
$stmtInsert->bind_param("ss", $roleName, $permissionsStr);

if ($stmtInsert->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to add role']);
}

$stmtInsert->close();
$conn->close();
?>
