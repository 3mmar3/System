<?php
$host = "localhost";
$dbname = "u125244766_system";
$username = "u125244766_system";
$password = "Com@1212";

// الاتصال بقاعدة البيانات
$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Database connection failed"]);
    exit;
}

// قراءة بيانات JSON من الطلب
$data = json_decode(file_get_contents("php://input"), true);
if (!$data || !isset($data['userId']) || !isset($data['newRole'])) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Invalid input"]);
    exit;
}

$userId = intval($data['userId']);
$newRole = $conn->real_escape_string(strtolower(trim($data['newRole'])));

// جلب الأدوار المتاحة من جدول roles
$rolesResult = $conn->query("SELECT role_name FROM roles");
$allowedRoles = [];

if ($rolesResult) {
    while ($row = $rolesResult->fetch_assoc()) {
        $allowedRoles[] = strtolower($row['role_name']); // تأكد من تطابق الحروف
    }
}

if (!in_array($newRole, $allowedRoles)) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Invalid role value"]);
    exit;
}

// تنفيذ التحديث
$stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
$stmt->bind_param("si", $newRole, $userId);
if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Failed to update role"]);
}
$stmt->close();
$conn->close();
?>
