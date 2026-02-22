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

$user = isset($_GET['user']) ? $conn->real_escape_string($_GET['user']) : '';
$date = isset($_GET['date']) ? $conn->real_escape_string($_GET['date']) : '';

$sql = "SELECT 
            id,
            username,
            leave_date,
            leave_type,
            notes AS reason,
            status,
            action_by_admin AS admin
        FROM leave_requests
        WHERE 1=1";

if (!empty($user)) {
    $sql .= " AND username = '{$user}'";
}

if (!empty($date)) {
    $sql .= " AND leave_date = '{$date}'";
}

$sql .= " ORDER BY leave_date DESC";

$result = $conn->query($sql);
$data = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}

header('Content-Type: application/json');
echo json_encode($data);
$conn->close();
?>
