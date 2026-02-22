<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    exit;
}

$admin = $_SESSION['username'];

$conn = new mysqli('localhost', 'u125244766_system', 'Com@1212', 'u125244766_system');
if ($conn->connect_error) {
    http_response_code(500);
    exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$status = isset($_POST['status']) ? $_POST['status'] : '';

if ($id <= 0 || ($status !== 'approved' && $status !== 'rejected')) {
    http_response_code(400);
    exit;
}

$stmt = $conn->prepare("UPDATE leave_requests SET status = ?, action_by_admin = ? WHERE id = ?");
$stmt->bind_param("ssi", $status, $admin, $id);
$stmt->execute();
$stmt->close();

echo "OK";
?>
