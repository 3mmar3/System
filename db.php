<?php
$host = "localhost";
$dbname = "u125244766_system";
$dbuser = "u125244766_system";
$dbpass = "Com@1212";

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $dbuser, $dbpass);
    // Set Error Mode
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Database Connection Failed: " . $e->getMessage();
    exit;
}
?>
