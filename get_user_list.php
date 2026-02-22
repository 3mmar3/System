<?php
header('Content-Type: application/json');

// بيانات الاتصال بقاعدة البيانات
$host = "localhost";
$dbname = "u125244766_system";
$dbuser = "u125244766_system";
$dbpass = "Com@1212";

// إنشاء اتصال
$conn = new mysqli($host, $dbuser, $dbpass, $dbname);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["error" => "Failed to connect to database"]);
    exit;
}

// استعلام لجلب بيانات المستخدمين
$sql = "SELECT id, username, role FROM users ORDER BY username ASC";
$result = $conn->query($sql);

$users = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
    echo json_encode($users);
} else {
    http_response_code(500);
    echo json_encode(["error" => "Query execution failed"]);
}

$conn->close();
