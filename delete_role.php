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
    session_destroy();
    header("Location: login.php");
    exit;
}
?>

<?php
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

$id = intval($data['id']);

$host = "localhost";
$dbname = "u125244766_system";
$dbuser = "u125244766_system";
$dbpass = "Com@1212";

$conn = new mysqli($host, $dbuser, $dbpass, $dbname);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'DB connection error']);
    exit;
}

$stmt = $conn->prepare("DELETE FROM roles WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to delete role']);
}
$stmt->close();
$conn->close();
