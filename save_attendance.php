<?php
// إعداد الاتصال بقاعدة البيانات
$host = "localhost";
$db = "u125244766_system";
$user = "u125244766_system";
$pass = "Com@1212";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// استقبال البيانات من POST
$username = $_POST['username'] ?? '';
$action = $_POST['action'] ?? '';

if (!$username || !$action) {
  die("Missing data");
}

// تسجيل الوقت الحالي
$now = date('Y-m-d H:i:s');

$sql = "INSERT INTO attendance (username, action_type, action_time)
        VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $username, $action, $now);

if ($stmt->execute()) {
  echo "success";
} else {
  echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
