<?php
session_start();
$host = "localhost";
$dbname = "u125244766_system";
$username = "u125244766_system";
$password = "Com@1212";
$conn = new mysqli($host, $username, $password, $dbname);

if (!isset($_SESSION['user_id'])) {
    die("Unauthorized");
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$user_id = $_SESSION['user_id'];

$conn->query("DELETE FROM bookings WHERE id = $id AND user_id = $user_id");
$conn->query("DELETE FROM hotel_bookings WHERE booking_id = $id AND user_id = $user_id");
$conn->query("DELETE FROM visa_bookings WHERE booking_id = $id AND user_id = $user_id");
$conn->query("DELETE FROM appointment_bookings WHERE booking_id = $id AND user_id = $user_id");
$conn->query("DELETE FROM insurance_bookings WHERE booking_id = $id AND user_id = $user_id");
$conn->query("DELETE FROM entertainment_bookings WHERE booking_id = $id AND user_id = $user_id");
$conn->query("DELETE FROM transportation_bookings WHERE booking_id = $id AND user_id = $user_id");

header("Location: my-reservations.php");
exit;
?>
