<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    exit;
}

$conn = new mysqli("localhost", "u125244766_system", "Com@1212", "u125244766_system");
if ($conn->connect_error) {
    http_response_code(500);
    exit;
}

date_default_timezone_set('Africa/Cairo');

// --- 1. جلب كل المستخدمين lowercase
$users = [];
$resUsers = $conn->query("SELECT LOWER(username) as username FROM users");
while ($row = $resUsers->fetch_assoc()) {
    $users[$row['username']] = 'offline'; // الحالة الافتراضية
}

// --- 2. جلب الحركات آخر 12 ساعة بترتيب أحدث الأول
$sql = "
  SELECT LOWER(username) as username, action_type, action_time
  FROM attendance
  WHERE action_time >= NOW() - INTERVAL 12 HOUR
  ORDER BY username, action_time DESC
";
$res = $conn->query($sql);

// --- 3. نخزن أحدث حركة بس لكل يوزر
$events = [];

while ($row = $res->fetch_assoc()) {
    $u = $row['username'];
    if (!isset($events[$u])) {
        $events[$u] = $row['action_type'];
    }
}

// --- 4. تحديد الحالة لكل يوزر لوحده (بدون تأثير على الباقي)
$output = [];

foreach ($users as $user => $status) {
    $finalStatus = 'offline'; // الحالة الافتراضية

    if (isset($events[$user])) {
        $action = $events[$user];

if ($action === 'start' || $action === 'break_end') {
            $finalStatus = 'online';
        } elseif ($action === 'break_start') {
            $finalStatus = 'break';
        } elseif ($action === 'end') {
            $finalStatus = 'offline';
        }
    }

    $output[] = ['username' => $user, 'status' => $finalStatus];
}

// --- 5. إخراج JSON
header('Content-Type: application/json');
echo json_encode($output);
$conn->close();
?>
