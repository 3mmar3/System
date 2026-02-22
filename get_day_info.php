<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['username']) || !isset($_GET['date'])) {
    echo json_encode(['error' => 'unauthorized']);
    exit;
}

$username = $_SESSION['username'];
$date     = $_GET['date'];
$conn     = new mysqli('localhost', 'u125244766_system', 'Com@1212', 'u125244766_system');
if ($conn->connect_error) {
    echo json_encode(['error' => 'db']);
    exit;
}

function getStatus(mysqli $c, string $u, string $d): array {
    $q = "SELECT action_type, action_time
          FROM attendance
          WHERE username = ? AND DATE(action_time) = ?
          ORDER BY action_time ASC";
    $s = $c->prepare($q);
    $s->bind_param('ss', $u, $d);
    $s->execute();
    $r = $s->get_result();

    $start = $end = $breakStart = null;
    $breakTotal = 0;
    while ($row = $r->fetch_assoc()) {
        $t = strtotime($row['action_time']);
        switch ($row['action_type']) {
            case 'start':       $start      = $t; break;
            case 'end':         $end        = $t; break;
            case 'break_start': $breakStart = $t; break;
            case 'break_end':
                if ($breakStart) {
                    $breakTotal += $t - $breakStart;
                    $breakStart = null;
                }
                break;
        }
    }
    if ($breakStart && !$end) {
        $breakTotal += time() - $breakStart;
    }

    return [
        'start'      => $start,
        'end'        => $end,
        'breakStart' => $breakStart,
        'totalBreak' => $breakTotal
    ];
}

echo json_encode(getStatus($conn, $username, $date));
$conn->close();
?>