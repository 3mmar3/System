<?php
header('Content-Type: application/json');

// بيانات الاتصال بقاعدة البيانات - عدّل حسب بياناتك
$host = "localhost";
$dbname = "u125244766_system";
$dbuser = "u125244766_system";
$dbpass = "Com@1212";

$conn = new mysqli($host, $dbuser, $dbpass, $dbname);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed"]);
    exit;
}

// استعلام لجلب كل المستخدمين مع الأعمدة id و username و role
$sql = "SELECT id, username, role FROM users ORDER BY username ASC";
$result = $conn->query($sql);

$users = [];

if ($result) {
    while($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
    echo json_encode($users);
} else {
    http_response_code(500);
    echo json_encode(["error" => "Query failed"]);
}

$conn->close();
