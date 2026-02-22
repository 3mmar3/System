<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

$host = "localhost";
$dbname = "u125244766_system";
$username = "u125244766_system";
$password = "Com@1212";

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(['error' => 'Connection failed: ' . $conn->connect_error]);
    exit;
}

$booking_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$user_id = $_SESSION['user_id'];

if ($booking_id <= 0) {
    echo json_encode(['error' => 'Invalid booking ID']);
    exit;
}

$stmt = $conn->prepare("SELECT * FROM bookings WHERE id = ? AND user_id = ?");
if ($stmt === false) {
    echo json_encode(['error' => 'Prepare failed: ' . $conn->error]);
    exit;
}
$stmt->bind_param("ii", $booking_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$booking = $result->fetch_assoc();
$stmt->close();

$hotel_booking = $visa_booking = $appointment_booking = $insurance_booking = $entertainment_booking = $transportation_booking = $flight_booking = $cruise_booking = null;

if ($booking) {
    $booking_types = explode(',', $booking['booking_types']);
    
    if (in_array('hotel', $booking_types)) {
        $stmt = $conn->prepare("SELECT * FROM hotel_bookings WHERE booking_id = ? AND user_id = ?");
        $stmt->bind_param("ii", $booking_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $hotel_booking = $result->fetch_assoc();
        $stmt->close();
    }
    if (in_array('visa', $booking_types)) {
        $stmt = $conn->prepare("SELECT * FROM visa_bookings WHERE booking_id = ? AND user_id = ?");
        $stmt->bind_param("ii", $booking_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $visa_booking = $result->fetch_assoc();
        $stmt->close();
    }
    if (in_array('appointment', $booking_types)) {
        $stmt = $conn->prepare("SELECT * FROM appointment_bookings WHERE booking_id = ? AND user_id = ?");
        $stmt->bind_param("ii", $booking_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $appointment_booking = $result->fetch_assoc();
        $stmt->close();
    }
    if (in_array('insurance', $booking_types)) {
        $stmt = $conn->prepare("SELECT * FROM insurance_bookings WHERE booking_id = ? AND user_id = ?");
        $stmt->bind_param("ii", $booking_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $insurance_booking = $result->fetch_assoc();
        $stmt->close();
    }
    if (in_array('entertainment', $booking_types)) {
        $stmt = $conn->prepare("SELECT * FROM entertainment_bookings WHERE booking_id = ? AND user_id = ?");
        $stmt->bind_param("ii", $booking_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $entertainment_booking = $result->fetch_assoc();
        $stmt->close();
    }
    if (in_array('transportation', $booking_types)) {
        $stmt = $conn->prepare("SELECT * FROM transportation_bookings WHERE booking_id = ? AND user_id = ?");
        $stmt->bind_param("ii", $booking_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $transportation_booking = $result->fetch_assoc();
        $stmt->close();
    }
    if (in_array('flight', $booking_types)) {  // إضافة الطيران
        $stmt = $conn->prepare("SELECT * FROM flight_bookings WHERE booking_id = ? AND user_id = ?");
        $stmt->bind_param("ii", $booking_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $flight_booking = $result->fetch_assoc();
        $stmt->close();
    }
    if (in_array('cruise', $booking_types)) {  // إضافة الكروز
        $stmt = $conn->prepare("SELECT * FROM cruise_bookings WHERE booking_id = ? AND user_id = ?");
        $stmt->bind_param("ii", $booking_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $cruise_booking = $result->fetch_assoc();
        $stmt->close();
    }
} else {
    echo json_encode(['error' => 'Booking not found for ID: ' . $booking_id]);
    exit;
}

echo json_encode([
    'booking' => $booking,
    'hotel_booking' => $hotel_booking,
    'visa_booking' => $visa_booking,
    'appointment_booking' => $appointment_booking,
    'insurance_booking' => $insurance_booking,
    'entertainment_booking' => $entertainment_booking,
    'transportation_booking' => $transportation_booking,
    'flight_booking' => $flight_booking,  // إضافة الطيران
    'cruise_booking' => $cruise_booking   // إضافة الكروز
]);

$conn->close();
exit;
?>