<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

// الاتصال بقاعدة البيانات
$host = "localhost";
$dbname = "u125244766_system";
$dbuser = "u125244766_system";
$dbpass = "Com@1212";

$conn = new mysqli($host, $dbuser, $dbpass, $dbname);
if ($conn->connect_error) {
    die("Database connection failed");
}

// تحقق إذا المستخدم لا زال موجود في قاعدة البيانات
$stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
$stmt->bind_param("s", $_SESSION['username']);
$stmt->execute();
$stmt->bind_result($count);
$stmt->fetch();
$stmt->close();

if ($count == 0) {
    // المستخدم محذوف => انهاء الجلسة واعادة التوجيه للصفحة الرئيسية
    session_destroy();
    header("Location: login.php");
    exit;
}
?>


<?php
// إعداد الاتصال بقاعدة البيانات
$host = "localhost";
$dbname = "u125244766_system";
$username = "u125244766_system";
$password = "Com@1212";

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

// قراءة البيانات من POST
$data = json_decode(file_get_contents("php://input"), true);
if (!isset($data['userId'])) {
    echo json_encode(['success' => false, 'message' => 'User ID missing']);
    exit;
}

$userId = intval($data['userId']);

// تنفيذ حذف المستخدم
$stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to delete user']);
}

$stmt->close();
$conn->close();
?>
