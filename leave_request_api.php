<?php
session_start();
if (!isset($_SESSION['username'])) {
  http_response_code(401);
  echo "Unauthorized. Please login.";
  exit;
}

$username = $_SESSION['username'];

$conn = new mysqli("localhost", "u125244766_system", "Com@1212", "u125244766_system");
if ($conn->connect_error) {
  http_response_code(500);
  echo "DB Connection error: " . $conn->connect_error;
  exit;
}

// ----------- تقديم طلب اجازة ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $type = $_POST['leave_type'] ?? '';
  $date = $_POST['leave_date'] ?? '';
  $reason = $_POST['leave_reason'] ?? '';

  if (!$type || !$date) {
    http_response_code(400);
    echo "Leave type and date are required.";
    exit;
  }

  $today = date('Y-m-d');
  if ($date < $today) {
    http_response_code(400);
    echo "Leave date cannot be earlier than today.";
    exit;
  }

  $stmt = $conn->prepare("INSERT INTO leave_requests (username, leave_type, leave_date, notes) VALUES (?, ?, ?, ?)");
  $stmt->bind_param("ssss", $username, $type, $date, $reason);
  if ($stmt->execute()) {
    echo "OK";
  } else {
    http_response_code(500);
    echo "DB error: " . $stmt->error;
  }
  $stmt->close();
  exit;
}

// ----------- عرض طلبات الموظف فقط ----------
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  $userEscaped = $conn->real_escape_string($username);
  $query = "SELECT id, username, leave_type, leave_date, status, notes AS reason, action_by_admin 
            FROM leave_requests 
            WHERE username = '{$userEscaped}'
            ORDER BY leave_date DESC";
  $result = $conn->query($query);

  $leaves = [];
  while ($row = $result->fetch_assoc()) {
    $leaves[] = $row;
  }
  header('Content-Type: application/json');
  echo json_encode($leaves);
  exit;
}

http_response_code(405);
echo "Method not allowed";
exit;
?>
