<?php
session_start();
date_default_timezone_set('Africa/Cairo'); // ✅ تعديل مهم

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    exit;
}

$conn = new mysqli("localhost", "u125244766_system", "Com@1212", "u125244766_system");
if ($conn->connect_error) {
    http_response_code(500);
    exit;
}

$user = $_GET['user'] ?? '';
$range = $_GET['range'] ?? 'today';
$date = $_GET['date'] ?? '';

$whereClauses = [];
$params = [];
$types = '';

if ($user !== '') {
    $whereClauses[] = "username = ?";
    $params[] = $user;
    $types .= 's';
}

switch ($range) {
    case 'lastWeek':
        $whereClauses[] = "action_time >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
        break;
    case 'thisMonth':
        $whereClauses[] = "MONTH(action_time) = MONTH(CURDATE()) AND YEAR(action_time) = YEAR(CURDATE())";
        break;
            case 'thisYear':
        $whereClauses[] = "YEAR(action_time) = YEAR(CURDATE())";
        break;
    case 'custom':
        if ($date) {
            $whereClauses[] = "DATE(action_time) = ?";
            $params[] = $date;
            $types .= 's';
        }
        break;
    case 'today':
    default:
        $whereClauses[] = "DATE(action_time) = ?";
        $params[] = date('Y-m-d');
        $types .= 's';
}

$whereSQL = '';
if (count($whereClauses) > 0) {
    $whereSQL = 'WHERE ' . implode(' AND ', $whereClauses);
}

$sql = "SELECT * FROM attendance $whereSQL ORDER BY action_time ASC";

$stmt = $conn->prepare($sql);
if ($types !== '') {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

header('Content-Type: application/json');
echo json_encode($data);
$conn->close();
