<?php
header('Content-Type: application/json');
$conn = new mysqli("localhost", "u125244766_system", "Com@1212", "u125244766_system");
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}
$conn->set_charset("utf8mb4");

$data = json_decode(file_get_contents('php://input'), true);
if (!$data) {
    echo json_encode(['success' => false, 'message' => 'No data received or invalid JSON']);
    exit;
}

$id = intval($data['id'] ?? 0);
$roleName = trim($data['roleName'] ?? '');
$permissions = $data['permissions'] ?? [];

if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid role ID']);
    exit;
}
if ($roleName === '') {
    echo json_encode(['success' => false, 'message' => 'Role name cannot be empty']);
    exit;
}

$permissionsStr = implode(',', array_map('trim', $permissions));

$stmtCheck = $conn->prepare("SELECT id FROM roles WHERE role_name = ? AND id != ?");
$stmtCheck->bind_param("si", $roleName, $id);
$stmtCheck->execute();
$stmtCheck->store_result();
if ($stmtCheck->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Role name already exists']);
    exit;
}
$stmtCheck->close();

$stmt = $conn->prepare("UPDATE roles SET role_name = ?, permissions = ? WHERE id = ?");
$stmt->bind_param("ssi", $roleName, $permissionsStr, $id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database update failed']);
}
$stmt->close();
$conn->close();
