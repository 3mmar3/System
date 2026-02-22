<?php
session_start();

if (!isset($_SESSION['username']) || strtolower($_SESSION['role']) !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

$host = "localhost";
$dbname = "u125244766_system";
$dbuser = "u125244766_system";
$dbpass = "Com@1212";

$conn = new mysqli($host, $dbuser, $dbpass, $dbname);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'DB Connection failed']);
    exit;
}

// آخر وقت تم الفحص فيه (أو 10 دقائق سابقًا بشكل افتراضي)
$lastCheck = isset($_GET['last_check']) ? $conn->real_escape_string($_GET['last_check']) : date('Y-m-d H:i:s', time() - 600);

$new_events = [];

// استعلام الحضور والانصراف والبريك
$sqlAttendance = "SELECT username, action_type AS event_type, action_time AS event_time
                  FROM attendance
                  WHERE action_time > '$lastCheck'";

// استعلام طلبات الإجازة الجديدة (الطلبات المرسلة بعد آخر فحص)
$sqlLeave = "SELECT username, 'leave_request' AS event_type, request_time AS event_time
             FROM leave_requests
             WHERE request_time > '$lastCheck' AND status = 'pending'";

$results = [];

$result1 = $conn->query($sqlAttendance);
if ($result1 && $result1->num_rows > 0) {
    while ($row = $result1->fetch_assoc()) {
        $results[] = $row;
    }
}

$result2 = $conn->query($sqlLeave);
if ($result2 && $result2->num_rows > 0) {
    while ($row = $result2->fetch_assoc()) {
        $results[] = $row;
    }
}

// فرز النتائج حسب الوقت تصاعديًا
usort($results, function($a, $b) {
    return strtotime($a['event_time']) - strtotime($b['event_time']);
});

foreach ($results as $row) {
    $new_events[] = [
        'type' => $row['event_type'],
        'user' => $row['username'],
        'time' => $row['event_time'],
    ];
}

echo json_encode(['new_events' => $new_events]);

$conn->close();
?>
