<?php
header('Content-Type: application/json');

$host = "localhost";
$dbname = "u125244766_system";
$dbuser = "u125244766_system";
$dbpass = "Com@1212";

$conn = new mysqli($host, $dbuser, $dbpass, $dbname);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["error" => "Failed to connect to database"]);
    exit;
}

$sql = "SELECT id, role_name, permissions FROM roles ORDER BY role_name ASC";
$result = $conn->query($sql);

$roles = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        // حول String لصيغة Array
        $row['permissions'] = array_filter(array_map('trim', explode(',', $row['permissions'])));
        $roles[] = $row;
    }
    echo json_encode($roles);
} else {
    http_response_code(500);
    echo json_encode(["error" => "Query execution failed"]);
}

$conn->close();
?>
