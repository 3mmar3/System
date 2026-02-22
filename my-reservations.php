<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    die("You must be logged in to view reservations.");
}

$host = "localhost";
$dbname = "u125244766_system";
$username = "u125244766_system";
$password = "Com@1212";

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error . " - Please check your database credentials.");
}

$action = isset($_GET['action']) ? $_GET['action'] : '';
$booking_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Initial data fetch for pre-population
$booking = null;
$hotel_booking = $visa_booking = $appointment_booking = $insurance_booking = $entertainment_booking = $transportation_booking = $flight_booking = $cruise_booking = null;

if ($booking_id > 0 && ($action === 'view' || $action === 'edit')) {
    $stmt = $conn->prepare("SELECT * FROM bookings WHERE id = ? AND user_id = ?");
    if ($stmt === false) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("ii", $booking_id, $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $booking = $result->fetch_assoc();
    $stmt->close();

    if ($booking) {
        $booking_types = explode(',', $booking['booking_types']);
        if (in_array('hotel', $booking_types)) {
            $stmt = $conn->prepare("SELECT * FROM hotel_bookings WHERE booking_id = ? AND user_id = ?");
            $stmt->bind_param("ii", $booking_id, $_SESSION['user_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            $hotel_booking = $result->fetch_assoc();
            $stmt->close();
        }
        if (in_array('visa', $booking_types)) {
            $stmt = $conn->prepare("SELECT * FROM visa_bookings WHERE booking_id = ? AND user_id = ?");
            $stmt->bind_param("ii", $booking_id, $_SESSION['user_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            $visa_booking = $result->fetch_assoc();
            $stmt->close();
        }
        if (in_array('appointment', $booking_types)) {
            $stmt = $conn->prepare("SELECT * FROM appointment_bookings WHERE booking_id = ? AND user_id = ?");
            $stmt->bind_param("ii", $booking_id, $_SESSION['user_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            $appointment_booking = $result->fetch_assoc();
            $stmt->close();
        }
        if (in_array('insurance', $booking_types)) {
            $stmt = $conn->prepare("SELECT * FROM insurance_bookings WHERE booking_id = ? AND user_id = ?");
            $stmt->bind_param("ii", $booking_id, $_SESSION['user_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            $insurance_booking = $result->fetch_assoc();
            $stmt->close();
        }
        if (in_array('entertainment', $booking_types)) {
            $stmt = $conn->prepare("SELECT * FROM entertainment_bookings WHERE booking_id = ? AND user_id = ?");
            $stmt->bind_param("ii", $booking_id, $_SESSION['user_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            $entertainment_booking = $result->fetch_assoc();
            $stmt->close();
        }
        if (in_array('transportation', $booking_types)) {
            $stmt = $conn->prepare("SELECT * FROM transportation_bookings WHERE booking_id = ? AND user_id = ?");
            $stmt->bind_param("ii", $booking_id, $_SESSION['user_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            $transportation_booking = $result->fetch_assoc();
            $stmt->close();
        }
        if (in_array('flight', $booking_types)) {
            $stmt = $conn->prepare("SELECT * FROM flight_bookings WHERE booking_id = ? AND user_id = ?");
            $stmt->bind_param("ii", $booking_id, $_SESSION['user_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            $flight_booking = $result->fetch_assoc();
            $stmt->close();
        }
        if (in_array('cruise', $booking_types)) {
            $stmt = $conn->prepare("SELECT * FROM cruise_bookings WHERE booking_id = ? AND user_id = ?");
            $stmt->bind_param("ii", $booking_id, $_SESSION['user_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            $cruise_booking = $result->fetch_assoc();
            $stmt->close();
        }
    }
}

$user_id = $_SESSION['user_id'];
$filter_status = isset($_GET['status']) ? $_GET['status'] : '';
$search_query = isset($_GET['search']) ? $_GET['search'] : '';

$sql = "SELECT * FROM bookings WHERE user_id = ?";
$params = [$user_id];
$types = "i";

if ($filter_status && $filter_status !== '') {
    $sql .= " AND status = ?";
    $params[] = $filter_status;
    $types .= "s";
}
if ($search_query) {
    $sql .= " AND (customer_name LIKE ? OR status LIKE ? OR booking_types LIKE ?)";
    $params[] = "%$search_query%";
    $params[] = "%$search_query%";
    $params[] = "%$search_query%";
    $types .= "sss";
}

$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
if (isset($_POST['add_booking']) && $_POST['add_booking'] == '1') {
        $customer_name = $_POST['customer_name'];
        $customer_phone = $_POST['customer_phone'];
        $booking_types = $_POST['booking_types'] ?? [];
        $sell_price = floatval($_POST['sell_price']);
        $payment_fees = floatval($_POST['payment_fees']);
        $status = $_POST['status'];
        $notes = $_POST['notes'];
        $cost = floatval($_POST['cost']);
        $profit = $sell_price - $payment_fees - $cost;

        $stmt = $conn->prepare("INSERT INTO bookings (user_id, customer_name, customer_phone, booking_types, sell_price, payment_fees, cost, profit, status, notes, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("isssddddss", $user_id, $customer_name, $customer_phone, implode(",", $booking_types), $sell_price, $payment_fees, $cost, $profit, $status, $notes);

        if (!$stmt->execute()) {
            die("Insert failed: " . $stmt->error);
        }
        $booking_id = $conn->insert_id;
        $stmt->close();

        if (in_array("hotel", $booking_types)) {
            $hotel_name = $_POST['hotel_name'];
            $num_guests = $_POST['num_guests'];
            $num_rooms = $_POST['num_rooms'];
            $check_in = $_POST['check_in'];
            $check_out = $_POST['check_out'];
            $hotel_booking_number = $_POST['hotel_booking_number'];
            $hotel_agent_name = $_POST['hotel_agent_name'];
            $hotel_payment_status = $_POST['hotel_payment_status'];
            $hotel_payment_due = $_POST['hotel_payment_due'];
            $hotel_notes = $_POST['hotel_notes'];

            $stmt = $conn->prepare("INSERT INTO hotel_bookings (booking_id, user_id, hotel_name, num_guests, num_rooms, check_in, check_out, booking_number, agent_name, payment_status, payment_due, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("iisisissssss", $booking_id, $user_id, $hotel_name, $num_guests, $num_rooms, $check_in, $check_out, $hotel_booking_number, $hotel_agent_name, $hotel_payment_status, $hotel_payment_due, $hotel_notes);
            if (!$stmt->execute()) {
                die("Insert hotel failed: " . $stmt->error);
            }
            $stmt->close();
        }
        if (in_array("visa", $booking_types)) {
            $visa_type = $_POST['visa_type'];
            $application_date = $_POST['visa_application_date'];
            $visa_status = $_POST['visa_status'];
            $visa_notes = $_POST['visa_notes'];

            $stmt = $conn->prepare("INSERT INTO visa_bookings (booking_id, user_id, visa_type, application_date, status, notes) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("iissss", $booking_id, $user_id, $visa_type, $application_date, $visa_status, $visa_notes);
            if (!$stmt->execute()) {
                die("Insert visa failed: " . $stmt->error);
            }
            $stmt->close();
        }
        if (in_array("appointment", $booking_types)) {
            $appointment_type = $_POST['appointment_type'];
            $appointment_date = $_POST['appointment_date'];
            $appointment_location = $_POST['appointment_location'];
            $appointment_status = $_POST['appointment_status'];
            $appointment_notes = $_POST['appointment_notes'];

            $stmt = $conn->prepare("INSERT INTO appointment_bookings (booking_id, user_id, appointment_type, appointment_date, location, status, notes) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("iisssss", $booking_id, $user_id, $appointment_type, $appointment_date, $appointment_location, $appointment_status, $appointment_notes);
            if (!$stmt->execute()) {
                die("Insert appointment failed: " . $stmt->error);
            }
            $stmt->close();
        }
        if (in_array("insurance", $booking_types)) {
            $insurance_type = $_POST['insurance_type'];
            $insurance_provider = $_POST['insurance_provider'];
            $insurance_start_date = $_POST['insurance_start_date'];
            $insurance_end_date = $_POST['insurance_end_date'];
            $insurance_status = $_POST['insurance_status'];
            $insurance_notes = $_POST['insurance_notes'];

            $stmt = $conn->prepare("INSERT INTO insurance_bookings (booking_id, user_id, insurance_type, provider, start_date, end_date, status, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("iissssss", $booking_id, $user_id, $insurance_type, $insurance_provider, $insurance_start_date, $insurance_end_date, $insurance_status, $insurance_notes);
            if (!$stmt->execute()) {
                die("Insert insurance failed: " . $stmt->error);
            }
            $stmt->close();
        }
        if (in_array("entertainment", $booking_types)) {
            $event_name = $_POST['event_name'];
            $event_date = $_POST['event_date'];
            $ticket_count = $_POST['ticket_count'];
            $entertainment_supplier = $_POST['entertainment_supplier'];
            $entertainment_status = $_POST['entertainment_status'];
            $entertainment_notes = $_POST['entertainment_notes'];

            $stmt = $conn->prepare("INSERT INTO entertainment_bookings (booking_id, user_id, event_name, event_date, ticket_count, supplier_name, status, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("iississs", $booking_id, $user_id, $event_name, $event_date, $ticket_count, $entertainment_supplier, $entertainment_status, $entertainment_notes);
            if (!$stmt->execute()) {
                die("Insert entertainment failed: " . $stmt->error);
            }
            $stmt->close();
        }
        if (in_array("transportation", $booking_types)) {
            $transport_type = $_POST['transport_type'];
            $pickup_location = $_POST['pickup_location'];
            $dropoff_location = $_POST['dropoff_location'];
            $transport_date = $_POST['transport_date'];
            $transport_supplier = $_POST['transport_supplier'];
            $transport_status = $_POST['transport_status'];
            $transport_notes = $_POST['transport_notes'];

            $stmt = $conn->prepare("INSERT INTO transportation_bookings (booking_id, user_id, transport_type, pickup_location, dropoff_location, transport_date, supplier_name, status, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("iisssssss", $booking_id, $user_id, $transport_type, $pickup_location, $dropoff_location, $transport_date, $transport_supplier, $transport_status, $transport_notes);
            if (!$stmt->execute()) {
                die("Insert transportation failed: " . $stmt->error);
            }
            $stmt->close();
        }
        if (in_array("flight", $booking_types)) {
            $flight_number = $_POST['flight_number'];
            $flight_departure_date = $_POST['flight_departure_date'];
            $flight_arrival_date = $_POST['flight_arrival_date'];
            $flight_from = $_POST['flight_from'];
            $flight_to = $_POST['flight_to'];
            $flight_supplier = $_POST['flight_supplier'];
            $flight_status = $_POST['flight_status'];
            $flight_notes = $_POST['flight_notes'];

            $stmt = $conn->prepare("INSERT INTO flight_bookings (booking_id, user_id, flight_number, departure_date, arrival_date, `from`, `to`, supplier_name, status, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("iissssssss", $booking_id, $user_id, $flight_number, $flight_departure_date, $flight_arrival_date, $flight_from, $flight_to, $flight_supplier, $flight_status, $flight_notes);
            if (!$stmt->execute()) {
                die("Insert flight failed: " . $stmt->error);
            }
            $stmt->close();
        }
        if (in_array("cruise", $booking_types)) {
            $cruise_name = $_POST['cruise_name'];
            $cruise_departure_date = $_POST['cruise_departure_date'];
            $cruise_return_date = $_POST['cruise_return_date'];
            $cruise_departure_port = $_POST['cruise_departure_port'];
            $cruise_arrival_port = $_POST['cruise_arrival_port'];
            $cruise_supplier = $_POST['cruise_supplier'];
            $cruise_status = $_POST['cruise_status'];
            $cruise_notes = $_POST['cruise_notes'];

            $stmt = $conn->prepare("INSERT INTO cruise_bookings (booking_id, user_id, cruise_name, departure_date, return_date, departure_port, arrival_port, supplier_name, status, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("iissssssss", $booking_id, $user_id, $cruise_name, $cruise_departure_date, $cruise_return_date, $cruise_departure_port, $cruise_arrival_port, $cruise_supplier, $cruise_status, $cruise_notes);
            if (!$stmt->execute()) {
                die("Insert cruise failed: " . $stmt->error);
            }
            $stmt->close();
        }

        echo "<script>alert('Booking saved successfully!'); window.location.href='my-reservations.php';</script>";
        exit;
} elseif (isset($_POST['edit_booking']) && $_POST['edit_booking'] == '1') {
        $id = intval($_POST['id']);
        $customer_name = $_POST['customer_name'];
        $customer_phone = $_POST['customer_phone'];
        $booking_types = $_POST['booking_types'] ?? [];
        $sell_price = floatval($_POST['sell_price']);
        $payment_fees = floatval($_POST['payment_fees']);
        $status = $_POST['status'];
        $notes = $_POST['notes'];
        $cost = floatval($_POST['cost']);
        $profit = $sell_price - $payment_fees - $cost;

        $stmt = $conn->prepare("UPDATE bookings SET customer_name = ?, customer_phone = ?, booking_types = ?, sell_price = ?, payment_fees = ?, cost = ?, profit = ?, status = ?, notes = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param("sssddddssii", $customer_name, $customer_phone, implode(",", $booking_types), $sell_price, $payment_fees, $cost, $profit, $status, $notes, $id, $user_id);
        if (!$stmt->execute()) {
            die("Update failed: " . $stmt->error);
        }
        $stmt->close();

        if (in_array("hotel", $booking_types)) {
            $hotel_name = $_POST['hotel_name'];
            $num_guests = $_POST['num_guests'];
            $num_rooms = $_POST['num_rooms'];
            $check_in = $_POST['check_in'];
            $check_out = $_POST['check_out'];
            $hotel_booking_number = $_POST['hotel_booking_number'];
            $hotel_agent_name = $_POST['hotel_agent_name'];
            $hotel_payment_status = $_POST['hotel_payment_status'];
            $hotel_payment_due = $_POST['hotel_payment_due'];
            $hotel_notes = $_POST['hotel_notes'];

            $stmt = $conn->prepare("SELECT * FROM hotel_bookings WHERE booking_id = ? AND user_id = ?");
            $stmt->bind_param("ii", $id, $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $stmt = $conn->prepare("UPDATE hotel_bookings SET hotel_name = ?, num_guests = ?, num_rooms = ?, check_in = ?, check_out = ?, booking_number = ?, agent_name = ?, payment_status = ?, payment_due = ?, notes = ? WHERE booking_id = ? AND user_id = ?");
                $stmt->bind_param("sisssssssssi", $hotel_name, $num_guests, $num_rooms, $check_in, $check_out, $hotel_booking_number, $hotel_agent_name, $hotel_payment_status, $hotel_payment_due, $hotel_notes, $id, $user_id);
            } else {
                $stmt = $conn->prepare("INSERT INTO hotel_bookings (booking_id, user_id, hotel_name, num_guests, num_rooms, check_in, check_out, booking_number, agent_name, payment_status, payment_due, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("iisisissssss", $id, $user_id, $hotel_name, $num_guests, $num_rooms, $check_in, $check_out, $hotel_booking_number, $hotel_agent_name, $hotel_payment_status, $hotel_payment_due, $hotel_notes);
            }
            if (!$stmt->execute()) {
                die("Hotel booking update/insert failed: " . $stmt->error);
            }
            $stmt->close();
        } else {
            $stmt = $conn->prepare("DELETE FROM hotel_bookings WHERE booking_id = ? AND user_id = ?");
            $stmt->bind_param("ii", $id, $user_id);
            $stmt->execute();
            $stmt->close();
        }

        if (in_array("visa", $booking_types)) {
            $visa_type = $_POST['visa_type'];
            $application_date = $_POST['visa_application_date'];
            $visa_status = $_POST['visa_status'];
            $visa_notes = $_POST['visa_notes'];

            $stmt = $conn->prepare("SELECT * FROM visa_bookings WHERE booking_id = ? AND user_id = ?");
            $stmt->bind_param("ii", $id, $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $stmt = $conn->prepare("UPDATE visa_bookings SET visa_type = ?, application_date = ?, status = ?, notes = ? WHERE booking_id = ? AND user_id = ?");
                $stmt->bind_param("ssssii", $visa_type, $application_date, $visa_status, $visa_notes, $id, $user_id);
            } else {
                $stmt = $conn->prepare("INSERT INTO visa_bookings (booking_id, user_id, visa_type, application_date, status, notes) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("iissss", $id, $user_id, $visa_type, $application_date, $visa_status, $visa_notes);
            }
            if (!$stmt->execute()) {
                die("Visa booking update/insert failed: " . $stmt->error);
            }
            $stmt->close();
        } else {
            $stmt = $conn->prepare("DELETE FROM visa_bookings WHERE booking_id = ? AND user_id = ?");
            $stmt->bind_param("ii", $id, $user_id);
            $stmt->execute();
            $stmt->close();
        }

        if (in_array("appointment", $booking_types)) {
            $appointment_type = $_POST['appointment_type'];
            $appointment_date = $_POST['appointment_date'];
            $appointment_location = $_POST['appointment_location'];
            $appointment_status = $_POST['appointment_status'];
            $appointment_notes = $_POST['appointment_notes'];

            $stmt = $conn->prepare("SELECT * FROM appointment_bookings WHERE booking_id = ? AND user_id = ?");
            $stmt->bind_param("ii", $id, $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $stmt = $conn->prepare("UPDATE appointment_bookings SET appointment_type = ?, appointment_date = ?, location = ?, status = ?, notes = ? WHERE booking_id = ? AND user_id = ?");
                $stmt->bind_param("sssssii", $appointment_type, $appointment_date, $appointment_location, $appointment_status, $appointment_notes, $id, $user_id);
            } else {
                $stmt = $conn->prepare("INSERT INTO appointment_bookings (booking_id, user_id, appointment_type, appointment_date, location, status, notes) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("iisssss", $id, $user_id, $appointment_type, $appointment_date, $appointment_location, $appointment_status, $appointment_notes);
            }
            if (!$stmt->execute()) {
                die("Appointment booking update/insert failed: " . $stmt->error);
            }
            $stmt->close();
        } else {
            $stmt = $conn->prepare("DELETE FROM appointment_bookings WHERE booking_id = ? AND user_id = ?");
            $stmt->bind_param("ii", $id, $user_id);
            $stmt->execute();
            $stmt->close();
        }

        if (in_array("insurance", $booking_types)) {
            $insurance_type = $_POST['insurance_type'];
            $insurance_provider = $_POST['insurance_provider'];
            $insurance_start_date = $_POST['insurance_start_date'];
            $insurance_end_date = $_POST['insurance_end_date'];
            $insurance_status = $_POST['insurance_status'];
            $insurance_notes = $_POST['insurance_notes'];

            $stmt = $conn->prepare("SELECT * FROM insurance_bookings WHERE booking_id = ? AND user_id = ?");
            $stmt->bind_param("ii", $id, $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $stmt = $conn->prepare("UPDATE insurance_bookings SET insurance_type = ?, provider = ?, start_date = ?, end_date = ?, status = ?, notes = ? WHERE booking_id = ? AND user_id = ?");
                $stmt->bind_param("ssssssii", $insurance_type, $insurance_provider, $insurance_start_date, $insurance_end_date, $insurance_status, $insurance_notes, $id, $user_id);
            } else {
                $stmt = $conn->prepare("INSERT INTO insurance_bookings (booking_id, user_id, insurance_type, provider, start_date, end_date, status, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("iissssss", $id, $user_id, $insurance_type, $insurance_provider, $insurance_start_date, $insurance_end_date, $insurance_status, $insurance_notes);
            }
            if (!$stmt->execute()) {
                die("Insurance booking update/insert failed: " . $stmt->error);
            }
            $stmt->close();
        } else {
            $stmt = $conn->prepare("DELETE FROM insurance_bookings WHERE booking_id = ? AND user_id = ?");
            $stmt->bind_param("ii", $id, $user_id);
            $stmt->execute();
            $stmt->close();
        }

        if (in_array("entertainment", $booking_types)) {
            $event_name = $_POST['event_name'];
            $event_date = $_POST['event_date'];
            $ticket_count = $_POST['ticket_count'];
            $entertainment_supplier = $_POST['entertainment_supplier'];
            $entertainment_status = $_POST['entertainment_status'];
            $entertainment_notes = $_POST['entertainment_notes'];

            $stmt = $conn->prepare("SELECT * FROM entertainment_bookings WHERE booking_id = ? AND user_id = ?");
            $stmt->bind_param("ii", $id, $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $stmt = $conn->prepare("UPDATE entertainment_bookings SET event_name = ?, event_date = ?, ticket_count = ?, supplier_name = ?, status = ?, notes = ? WHERE booking_id = ? AND user_id = ?");
                $stmt->bind_param("ssisssii", $event_name, $event_date, $ticket_count, $entertainment_supplier, $entertainment_status, $entertainment_notes, $id, $user_id);
            } else {
                $stmt = $conn->prepare("INSERT INTO entertainment_bookings (booking_id, user_id, event_name, event_date, ticket_count, supplier_name, status, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("iississs", $id, $user_id, $event_name, $event_date, $ticket_count, $entertainment_supplier, $entertainment_status, $entertainment_notes);
            }
            if (!$stmt->execute()) {
                die("Entertainment booking update/insert failed: " . $stmt->error);
            }
            $stmt->close();
        } else {
            $stmt = $conn->prepare("DELETE FROM entertainment_bookings WHERE booking_id = ? AND user_id = ?");
            $stmt->bind_param("ii", $id, $user_id);
            $stmt->execute();
            $stmt->close();
        }

        if (in_array("transportation", $booking_types)) {
            $transport_type = $_POST['transport_type'];
            $pickup_location = $_POST['pickup_location'];
            $dropoff_location = $_POST['dropoff_location'];
            $transport_date = $_POST['transport_date'];
            $transport_supplier = $_POST['transport_supplier'];
            $transport_status = $_POST['transport_status'];
            $transport_notes = $_POST['transport_notes'];

            $stmt = $conn->prepare("SELECT * FROM transportation_bookings WHERE booking_id = ? AND user_id = ?");
            $stmt->bind_param("ii", $id, $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $stmt = $conn->prepare("UPDATE transportation_bookings SET transport_type = ?, pickup_location = ?, dropoff_location = ?, transport_date = ?, supplier_name = ?, status = ?, notes = ? WHERE booking_id = ? AND user_id = ?");
                $stmt->bind_param("sssssssii", $transport_type, $pickup_location, $dropoff_location, $transport_date, $transport_supplier, $transport_status, $transport_notes, $id, $user_id);
            } else {
                $stmt = $conn->prepare("INSERT INTO transportation_bookings (booking_id, user_id, transport_type, pickup_location, dropoff_location, transport_date, supplier_name, status, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("iisssssss", $id, $user_id, $transport_type, $pickup_location, $dropoff_location, $transport_date, $transport_supplier, $transport_status, $transport_notes);
            }
            if (!$stmt->execute()) {
                die("Transportation booking update/insert failed: " . $stmt->error);
            }
            $stmt->close();
        } else {
            $stmt = $conn->prepare("DELETE FROM transportation_bookings WHERE booking_id = ? AND user_id = ?");
            $stmt->bind_param("ii", $id, $user_id);
            $stmt->execute();
            $stmt->close();
        }

        if (in_array("flight", $booking_types)) {
            $flight_number = $_POST['flight_number'];
            $flight_departure_date = $_POST['flight_departure_date'];
            $flight_arrival_date = $_POST['flight_arrival_date'];
            $flight_from = $_POST['flight_from'];
            $flight_to = $_POST['flight_to'];
            $flight_supplier = $_POST['flight_supplier'];
            $flight_status = $_POST['flight_status'];
            $flight_notes = $_POST['flight_notes'];

            $stmt = $conn->prepare("SELECT * FROM flight_bookings WHERE booking_id = ? AND user_id = ?");
            $stmt->bind_param("ii", $id, $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $stmt = $conn->prepare("UPDATE flight_bookings SET flight_number = ?, departure_date = ?, arrival_date = ?, `from` = ?, `to` = ?, supplier_name = ?, status = ?, notes = ? WHERE booking_id = ? AND user_id = ?");
                $stmt->bind_param("sssssssii", $flight_number, $flight_departure_date, $flight_arrival_date, $flight_from, $flight_to, $flight_supplier, $flight_status, $flight_notes, $id, $user_id);
            } else {
                $stmt = $conn->prepare("INSERT INTO flight_bookings (booking_id, user_id, flight_number, departure_date, arrival_date, `from`, `to`, supplier_name, status, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("iissssssss", $id, $user_id, $flight_number, $flight_departure_date, $flight_arrival_date, $flight_from, $flight_to, $flight_supplier, $flight_status, $flight_notes);
            }
            if (!$stmt->execute()) {
                die("Flight booking update/insert failed: " . $stmt->error);
            }
            $stmt->close();
        } else {
            $stmt = $conn->prepare("DELETE FROM flight_bookings WHERE booking_id = ? AND user_id = ?");
            $stmt->bind_param("ii", $id, $user_id);
            $stmt->execute();
            $stmt->close();
        }

        if (in_array("cruise", $booking_types)) {
            $cruise_name = $_POST['cruise_name'];
            $cruise_departure_date = $_POST['cruise_departure_date'];
            $cruise_return_date = $_POST['cruise_return_date'];
            $cruise_departure_port = $_POST['cruise_departure_port'];
            $cruise_arrival_port = $_POST['cruise_arrival_port'];
            $cruise_supplier = $_POST['cruise_supplier'];
            $cruise_status = $_POST['cruise_status'];
            $cruise_notes = $_POST['cruise_notes'];

            $stmt = $conn->prepare("SELECT * FROM cruise_bookings WHERE booking_id = ? AND user_id = ?");
            $stmt->bind_param("ii", $id, $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $stmt = $conn->prepare("UPDATE cruise_bookings SET cruise_name = ?, departure_date = ?, return_date = ?, departure_port = ?, arrival_port = ?, supplier_name = ?, status = ?, notes = ? WHERE booking_id = ? AND user_id = ?");
                $stmt->bind_param("sssssssii", $cruise_name, $cruise_departure_date, $cruise_return_date, $cruise_departure_port, $cruise_arrival_port, $cruise_supplier, $cruise_status, $cruise_notes, $id, $user_id);
            } else {
                $stmt = $conn->prepare("INSERT INTO cruise_bookings (booking_id, user_id, cruise_name, departure_date, return_date, departure_port, arrival_port, supplier_name, status, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("iissssssss", $id, $user_id, $cruise_name, $cruise_departure_date, $cruise_return_date, $cruise_departure_port, $cruise_arrival_port, $cruise_supplier, $cruise_status, $cruise_notes);
            }
            if (!$stmt->execute()) {
                die("Cruise booking update/insert failed: " . $stmt->error);
            }
            $stmt->close();
        } else {
            $stmt = $conn->prepare("DELETE FROM cruise_bookings WHERE booking_id = ? AND user_id = ?");
            $stmt->bind_param("ii", $id, $user_id);
            $stmt->execute();
            $stmt->close();
        }

        echo "<script>alert('Booking updated successfully!'); window.location.href='my-reservations.php';</script>";
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Reservations</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
    
    td.status,
  td.types {
    width: 120px;
    text-align: center;
  }

  .badge {
    padding: 4px 10px;
    display: inline-block;
    text-align: center;
    min-width: 60px;
    border-radius: 8px;
    background-color: #f0f0f0;
  }

  td .badge-yellow {
    background-color: #facc15; /* لون الـ Hold */
    color: black;
  }

  td .badge-gray {
    background-color: #e5e7eb; /* لون Hotel و Flight */
    color: black;
  }

  td .td-badges {
    display: flex;
    gap: 5px;
    justify-content: center;
    flex-wrap: wrap;
  }

  th,
  td {
    text-align: center;
    vertical-align: middle;
  }
  
        body {
            background: linear-gradient(135deg, #667eea, #764ba2);
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
            color: #fff;
        }
        .container {
            color: #333;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            max-width: 1400px;
            margin: auto;
        }
        h2 {
            color: #333;
            font-weight: 700;
            margin-bottom: 30px;
            text-align: center;
        }
        .table-container {
            margin-top: 20px;
            overflow-x: auto;
        }
        .table {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        .table th {
            background: #333;
            color: #fff;
            font-weight: 600;
            padding: 15px;
            text-transform: uppercase;
            font-size: 0.9rem;
            letter-spacing: 1px;
        }
        .table td {
            vertical-align: middle;
            padding: 15px;
            color: #333;
            font-size: 0.95rem;
        }
        .table tr {
            transition: all 0.3s ease;
        }
        .table tr:hover {
            background: #f8f9fa;
            transform: scale(1.01);
        }
        .status-hold { background: #fff3cd; }
        .status-issued { background: #d4edda; }
        .status-cancelled { background: #f8d7da; }
        .btn-modern {
            border-radius: 25px;
            padding: 8px 20px;
            font-weight: 500;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        .btn-modern:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.2);
        }
        .btn-group {
            display: flex;
            gap: 8px;
            justify-content: center;
        }
        .filter-section {
            margin-bottom: 30px;
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            align-items: center;
        }
        .filter-section .form-select,
        .filter-section .form-control {
            border-radius: 25px;
            padding: 10px 20px;
            border: none;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        .add-button {
            margin-bottom: 30px;
            text-align: right;
        }
        .booking-type-group {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 20px;
        }
        .booking-type-btn {
            border: none;
            background: #e9ecef;
    padding: 9px 18px;
    border-radius: 20px;
            font-size: 0.85rem;
            cursor: pointer;
            color: #333;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        .booking-type-btn.selected {
            background: #28a745;
            color: #fff;
            font-weight: 600;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }
        .form-section {
            display: none;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .form-section h5 {
            color: #333;
            font-weight: 600;
            margin-bottom: 20px;
        }
        .modal-dialog {
            max-width: 900px;
        }
        .modal-content {
            border-radius: 15px;
            border: none;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
        }
        .modal-header {
            background: #333;
            color: #fff;
            border-top-left-radius: 15px;
            border-top-right-radius: 15px;
        }
        .modal-body {
            max-height: 70vh;
            overflow-y: auto;
            padding: 30px;
        }
        .form-control,
        .form-select {
            border-radius: 10px;
            border: 1px solid #ced4da;
            padding: 10px;
        }
        .form-control:focus,
        .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.2);
        }
        .financial-row .form-label {
            font-weight: 500;
            color: #333;
        }
        .separator {
            margin: 30px 0;
            border-top: 2px dashed #ced4da;
        }
        .booking-types {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
        .booking-type-tag {
            background: #e9ecef;
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 0.85rem;
            color: #333;
        }
        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }
            .table th, .table td {
                font-size: 0.85rem;
                padding: 10px;
            }
            .filter-section {
                flex-direction: column;
            }
            .add-button {
                text-align: center;
            }
            .modal-dialog {
                margin: 10px;
            }
        }
    
    </style>
</head>
<body>
    <div class="container">
        <h2>My Reservations</h2>
        <div class="add-button">
            <button type="button" class="btn btn-success btn-modern" data-bs-toggle="modal" data-bs-target="#reservationModal" data-action="add">Add New Reservation</button>
        </div>

        <div class="filter-section">
            <form method="get" class="w-100">
                <div class="row g-3">
                    <div class="col-md-3 col-sm-6">
                        <select name="status" class="form-select" onchange="this.form.submit()">
                            <option value="" <?php echo !$filter_status ? 'selected' : ''; ?>>All Statuses</option>
                            <option value="Hold" <?php echo $filter_status == 'Hold' ? 'selected' : ''; ?>>Hold</option>
                            <option value="Issued" <?php echo $filter_status == 'Issued' ? 'selected' : ''; ?>>Issued</option>
                            <option value="Cancelled" <?php echo $filter_status == 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <input type="text" name="search" class="form-control" placeholder="Search any field" value="<?php echo htmlspecialchars($search_query); ?>" onchange="this.form.submit()">
                    </div>
                </div>
            </form>
        </div>
        <div class="table-container">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Status</th>
                        <th>Types</th>
                        <th>Sell</th>
                        <th>Profit</th>
                        <th>Date</th>
                        <th>Notes</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $booking_types_labels = [
                        'hotel' => 'Hotel',
                        'flight' => 'Flight',
                        'cruise' => 'Cruise',
                        'visa' => 'Visa',
                        'appointment' => 'Appointment',
                        'insurance' => 'Insurance',
                        'entertainment' => 'Tickets',
                        'transportation' => 'Transportation'
                    ];
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $status_class = '';
                            if ($row['status'] == 'Hold') {
                                $status_class = 'status-hold';
                            } elseif ($row['status'] == 'Issued') {
                                $status_class = 'status-issued';
                            } elseif ($row['status'] == 'Cancelled') {
                                $status_class = 'status-cancelled';
                            }
                            $created_date = date('Y-m-d', strtotime($row['created_at']));
                            $booking_types = explode(',', $row['booking_types']);
                            ?>
                            <tr class="<?php echo $status_class; ?>">
                                <td><?php echo $row['id']; ?></td>
                                <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['customer_phone']); ?></td>
<td>
    <?php
        $status = $row['status'];
        $badgeClass = '';
        switch ($status) {
            case 'Hold':
                $badgeClass = 'bg-warning text-dark';
                break;
            case 'Issued':
                $badgeClass = 'bg-success';
                break;
            case 'Cancelled':
                $badgeClass = 'bg-danger';
                break;
        }
        echo '<span class="badge rounded-pill ' . $badgeClass . '">' . $status . '</span>';
    ?>
</td>

                                <td>
                                    
                                    <div class="booking-types">
                                        <?php
                                        foreach ($booking_types as $type) {
                                            if (isset($booking_types_labels[$type])) {
                                                echo '<span class="booking-type-tag">' . $booking_types_labels[$type] . '</span>';
                                            }
                                        }
                                        ?>
                                    </div>
                                </td>
                                <td><?php echo number_format($row['sell_price'], 2); ?></td>
                                <td><?php echo number_format($row['profit'], 2); ?></td>
                                <td><?php echo $created_date; ?></td>
                                <td><?php echo htmlspecialchars($row['notes']); ?></td>
                                <td>
<div class="d-flex gap-1 justify-content-center">
    <button type="button" class="btn btn-sm action-btn view" data-bs-toggle="modal" data-bs-target="#reservationModal" data-action="view" data-id="<?php echo $row['id']; ?>" title="View">
        <i class="bi bi-eye"></i>
    </button>
    <button type="button" class="btn btn-sm action-btn edit" data-bs-toggle="modal" data-bs-target="#reservationModal" data-action="edit" data-id="<?php echo $row['id']; ?>" title="Edit">
        <i class="bi bi-pencil"></i>
    </button>
    <a href="delete.php?id=<?php echo $row['id']; ?>" class="btn btn-sm action-btn delete" onclick="return confirm('Are you sure?');" title="Delete">
        <i class="bi bi-trash"></i>
    </a>
</div>



                                </td>
                            </tr>
                        <?php }
                    } else {
                        echo "<tr><td colspan='10'>No reservations found.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <div class="modal fade" id="reservationModal" tabindex="-1" aria-labelledby="reservationModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="reservationModalLabel"></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form method="post" id="reservationForm" oninput="calculateProfit()">
                            <input type="hidden" name="id" id="booking_id">
                            <input type="hidden" name="add_booking" id="add_booking" value="">
                            <input type="hidden" name="edit_booking" id="edit_booking" value="">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Customer Name</label>
                                    <input type="text" class="form-control" name="customer_name" id="customer_name" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Phone Number</label>
                                    <input type="text" class="form-control" name="customer_phone" id="customer_phone">
                                </div>
                            </div>
                            <label class="form-label">Booking Types</label>
                            <div class="booking-type-group">
                                <?php
                                $types = [
                                    'hotel' => 'Hotel',
                                    'flight' => 'Flight',
                                    'cruise' => 'Cruise',
                                    'visa' => 'Visa',
                                    'appointment' => 'Appointment',
                                    'insurance' => 'Insurance',
                                    'entertainment' => 'Tickets',
                                    'transportation' => 'Transportation'
                                ];
                                foreach ($types as $type => $label) {
                                    echo '<input type="checkbox" name="booking_types[]" value="' . $type . '" id="type_' . $type . '" style="display:none;">';
                                    echo '<button type="button" id="btn_' . $type . '" class="booking-type-btn" onclick="toggleType(this, \'' . $type . '\')">' . $label . '</button>';
                                }
                                ?>
                            </div>
                            <div id="hotel_section" class="form-section">
                                <h5>Hotel Information</h5>
                                <div class="mb-3">
                                    <label>Hotel Name</label>
                                    <input type="text" class="form-control" name="hotel_name" id="hotel_name">
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label>Number of Guests</label>
                                        <input type="number" class="form-control" name="num_guests" id="num_guests">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label>Number of Rooms</label>
                                        <input type="number" class="form-control" name="num_rooms" id="num_rooms">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label>Check-in Date</label>
                                        <input type="date" class="form-control" name="check_in" id="check_in">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label>Check-out Date</label>
                                        <input type="date" class="form-control" name="check_out" id="check_out">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label>Booking Number</label>
                                    <input type="text" class="form-control" name="hotel_booking_number" id="hotel_booking_number">
                                </div>
                                <div class="mb-3">
                                    <label>Supplier Name</label>
                                    <input type="text" class="form-control" name="hotel_agent_name" id="hotel_agent_name">
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label>Supplier Payment Status</label>
                                        <select class="form-select" name="hotel_payment_status" id="hotel_payment_status">
                                            <option value="Paid">Paid</option>
                                            <option value="Not Paid">Not Paid</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label>Supplier Payment Due Date</label>
                                        <input type="date" class="form-control" name="hotel_payment_due" id="hotel_payment_due">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label>Notes</label>
                                    <textarea class="form-control" name="hotel_notes" id="hotel_notes" rows="3"></textarea>
                                </div>
                            </div>
                            <div id="flight_section" class="form-section">
                                <h5>Flight Information</h5>
                                <div class="mb-3">
                                    <label>Flight Number</label>
                                    <input type="text" class="form-control" name="flight_number" id="flight_number">
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label>Departure Date</label>
                                        <input type="datetime-local" class="form-control" name="flight_departure_date" id="flight_departure_date">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label>Arrival Date</label>
                                        <input type="datetime-local" class="form-control" name="flight_arrival_date" id="flight_arrival_date">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label>From</label>
                                    <input type="text" class="form-control" name="flight_from" id="flight_from">
                                </div>
                                <div class="mb-3">
                                    <label>To</label>
                                    <input type="text" class="form-control" name="flight_to" id="flight_to">
                                </div>
                                <div class="mb-3">
                                    <label>Supplier Name</label>
                                    <input type="text" class="form-control" name="flight_supplier" id="flight_supplier">
                                </div>
                                <div class="mb-3">
                                    <label>Status</label>
                                    <select class="form-select" name="flight_status" id="flight_status">
                                        <option value="Confirmed">Confirmed</option>
                                        <option value="Pending">Pending</option>
                                        <option value="Cancelled">Cancelled</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label>Notes</label>
                                    <textarea class="form-control" name="flight_notes" id="flight_notes" rows="3"></textarea>
                                </div>
                            </div>
                            <div id="cruise_section" class="form-section">
                                <h5>Cruise Information</h5>
                                <div class="mb-3">
                                    <label>Cruise Name</label>
                                    <input type="text" class="form-control" name="cruise_name" id="cruise_name">
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label>Departure Date</label>
                                        <input type="date" class="form-control" name="cruise_departure_date" id="cruise_departure_date">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label>Return Date</label>
                                        <input type="date" class="form-control" name="cruise_return_date" id="cruise_return_date">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label>Departure Port</label>
                                    <input type="text" class="form-control" name="cruise_departure_port" id="cruise_departure_port">
                                </div>
                                <div class="mb-3">
                                    <label>Arrival Port</label>
                                    <input type="text" class="form-control" name="cruise_arrival_port" id="cruise_arrival_port">
                                </div>
                                <div class="mb-3">
                                    <label>Supplier Name</label>
                                    <input type="text" class="form-control" name="cruise_supplier" id="cruise_supplier">
                                </div>
                                <div class="mb-3">
                                    <label>Status</label>
                                    <select class="form-select" name="cruise_status" id="cruise_status">
                                        <option value="Confirmed">Confirmed</option>
                                        <option value="Pending">Pending</option>
                                        <option value="Cancelled">Cancelled</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label>Notes</label>
                                    <textarea class="form-control" name="cruise_notes" id="cruise_notes" rows="3"></textarea>
                                </div>
                            </div>
                            <div id="visa_section" class="form-section">
                                <h5>Visa Information</h5>
                                <div class="mb-3">
                                    <label>Visa Type</label>
                                    <input type="text" class="form-control" name="visa_type" id="visa_type">
                                </div>
                                <div class="mb-3">
                                    <label>Application Date</label>
                                    <input type="date" class="form-control" name="visa_application_date" id="visa_application_date">
                                </div>
                                <div class="mb-3">
                                    <label>Status</label>
                                    <select class="form-select" name="visa_status" id="visa_status">
                                        <option value="Pending">Pending</option>
                                        <option value="Approved">Approved</option>
                                        <option value="Rejected">Rejected</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label>Notes</label>
                                    <textarea class="form-control" name="visa_notes" id="visa_notes" rows="3"></textarea>
                                </div>
                            </div>
                            <div id="appointment_section" class="form-section">
                                <h5>Appointment Information</h5>
                                <div class="mb-3">
                                    <label>Appointment Type</label>
                                    <input type="text" class="form-control" name="appointment_type" id="appointment_type">
                                </div>
                                <div class="mb-3">
                                    <label>Appointment Date</label>
                                    <input type="datetime-local" class="form-control" name="appointment_date" id="appointment_date">
                                </div>
                                <div class="mb-3">
                                    <label>Location</label>
                                    <input type="text" class="form-control" name="appointment_location" id="appointment_location">
                                </div>
                                <div class="mb-3">
                                    <label>Status</label>
                                    <select class="form-select" name="appointment_status" id="appointment_status">
                                        <option value="Scheduled">Scheduled</option>
                                        <option value="Completed">Completed</option>
                                        <option value="Cancelled">Cancelled</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label>Notes</label>
                                    <textarea class="form-control" name="appointment_notes" id="appointment_notes" rows="3"></textarea>
                                </div>
                            </div>
                            <div id="insurance_section" class="form-section">
                                <h5>Insurance Information</h5>
                                <div class="mb-3">
                                    <label>Insurance Type</label>
                                    <input type="text" class="form-control" name="insurance_type" id="insurance_type">
                                </div>
                                <div class="mb-3">
                                    <label>Provider</label>
                                    <input type="text" class="form-control" name="insurance_provider" id="insurance_provider">
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label>Start Date</label>
                                        <input type="date" class="form-control" name="insurance_start_date" id="insurance_start_date">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label>End Date</label>
                                        <input type="date" class="form-control" name="insurance_end_date" id="insurance_end_date">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label>Status</label>
                                    <select class="form-select" name="insurance_status" id="insurance_status">
                                        <option value="Active">Active</option>
                                        <option value="Expired">Expired</option>
                                        <option value="Cancelled">Cancelled</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label>Notes</label>
                                    <textarea class="form-control" name="insurance_notes" id="insurance_notes" rows="3"></textarea>
                                </div>
                            </div>
                            <div id="entertainment_section" class="form-section">
                                <h5>Entertainment Tickets Information</h5>
                                <div class="mb-3">
                                    <label>Event Name</label>
                                    <input type="text" class="form-control" name="event_name" id="event_name">
                                </div>
                                <div class="mb-3">
                                    <label>Event Date</label>
                                    <input type="date" class="form-control" name="event_date" id="event_date">
                                </div>
                                <div class="mb-3">
                                    <label>Ticket Count</label>
                                    <input type="number" class="form-control" name="ticket_count" id="ticket_count">
                                </div>
                                <div class="mb-3">
                                    <label>Supplier Name</label>
                                    <input type="text" class="form-control" name="entertainment_supplier" id="entertainment_supplier">
                                </div>
                                <div class="mb-3">
                                    <label>Status</label>
                                    <select class="form-select" name="entertainment_status" id="entertainment_status">
                                        <option value="Confirmed">Confirmed</option>
                                        <option value="Pending">Pending</option>
                                        <option value="Cancelled">Cancelled</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label>Notes</label>
                                    <textarea class="form-control" name="entertainment_notes" id="entertainment_notes" rows="3"></textarea>
                                </div>
                            </div>
                            <div id="transportation_section" class="form-section">
                                <h5>Transportation Information</h5>
                                <div class="mb-3">
                                    <label>Transport Type</label>
                                    <input type="text" class="form-control" name="transport_type" id="transport_type">
                                </div>
                                <div class="mb-3">
                                    <label>Pickup Location</label>
                                    <input type="text" class="form-control" name="pickup_location" id="pickup_location">
                                </div>
                                <div class="mb-3">
                                    <label>Dropoff Location</label>
                                    <input type="text" class="form-control" name="dropoff_location" id="dropoff_location">
                                </div>
                                <div class="mb-3">
                                    <label>Transport Date</label>
                                    <input type="datetime-local" class="form-control" name="transport_date" id="transport_date">
                                </div>
                                <div class="mb-3">
                                    <label>Supplier Name</label>
                                    <input type="text" class="form-control" name="transport_supplier" id="transport_supplier">
                                </div>
                                <div class="mb-3">
                                    <label>Status</label>
                                    <select class="form-select" name="transport_status" id="transport_status">
                                        <option value="Confirmed">Confirmed</option>
                                        <option value="Pending">Pending</option>
                                        <option value="Cancelled">Cancelled</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label>Notes</label>
                                    <textarea class="form-control" name="transport_notes" id="transport_notes" rows="3"></textarea>
                                </div>
                            </div>
                            <div class="separator"></div>
                            <div class="row financial-row">
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Sell Price</label>
                                    <input type="number" class="form-control" step="0.01" name="sell_price" id="sell_price">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Payment Fees</label>
                                    <input type="number" class="form-control" step="0.01" name="payment_fees" id="payment_fees">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Cost</label>
                                    <input type="number" class="form-control" step="0.01" name="cost" id="cost">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Net Profit</label>
                                    <input type="text" class="form-control" id="profit" readonly>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Booking Status</label>
                                <select class="form-select" name="status" id="status">
                                    <option value="Hold">Hold</option>
                                    <option value="Issued">Issued</option>
                                    <option value="Cancelled">Cancelled</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Notes</label>
                                <textarea class="form-control" name="notes" id="notes" rows="3"></textarea>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary btn-modern" data-bs-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-primary btn-modern" id="submitButton">Save</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php $stmt->close(); $conn->close(); ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleType(btn, type) {
            btn.classList.toggle('selected');
            var checkbox = document.getElementById("type_" + type);
            checkbox.checked = !checkbox.checked;
            document.getElementById(type + '_section').style.display = checkbox.checked ? 'block' : 'none';
        }

        function calculateProfit() {
            let sell = parseFloat(document.querySelector('[name="sell_price"]').value) || 0;
            let fees = parseFloat(document.querySelector('[name="payment_fees"]').value) || 0;
            let cost = parseFloat(document.querySelector('[name="cost"]').value) || 0;
            let profit = sell - fees - cost;
            document.getElementById("profit").value = profit.toFixed(2);
        }

        document.addEventListener('DOMContentLoaded', function () {
            var reservationModal = document.getElementById('reservationModal');
            if (!reservationModal) {
                console.error('Reservation modal not found');
                alert('Modal initialization failed');
                return;
            }

            reservationModal.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget;
                if (!button) {
                    console.error('No trigger button found');
                    alert('Modal trigger failed');
                    return;
                }

                var action = button.getAttribute('data-action');
                var id = button.getAttribute('data-id');
                var modalTitle = reservationModal.querySelector('.modal-title');
                var form = reservationModal.querySelector('#reservationForm');
                var inputs = form.querySelectorAll('input, select, textarea');
                var submitButton = document.getElementById('submitButton');
                var addBookingInput = document.getElementById('add_booking');
                var editBookingInput = document.getElementById('edit_booking');

                if (!modalTitle || !form || !submitButton || !addBookingInput || !editBookingInput) {
                    console.error('Modal elements not found');
                    alert('Modal form initialization failed');
                    return;
                }

                // Reset hidden inputs
                addBookingInput.value = '';
                editBookingInput.value = '';

                // Set modal title and submit button text
                if (action === 'add') {
                    modalTitle.textContent = 'Add New Reservation';
                    submitButton.textContent = 'Save Booking';
                    submitButton.style.display = 'inline-block';
                    addBookingInput.value = '1';
                    // Clear form and enable inputs
                    inputs.forEach(input => {
                        input.value = '';
                        input.readOnly = false;
                        input.disabled = false;
                        input.classList.remove('form-control-plaintext');
                    });
                    // Reset booking types
                    var typeButtons = document.querySelectorAll('.booking-type-btn');
                    typeButtons.forEach(btn => {
                        var type = btn.id.replace('btn_', '');
                        btn.classList.remove('selected');
                        document.getElementById('type_' + type).checked = false;
                        document.getElementById(type + '_section').style.display = 'none';
                    });
                    calculateProfit();
                } else if (action === 'view' || action === 'edit') {
                    modalTitle.textContent = action === 'view' ? 'View Reservation' : 'Edit Reservation';
                    submitButton.textContent = 'Save Changes';
                    submitButton.style.display = action === 'edit' ? 'inline-block' : 'none';
                    editBookingInput.value = action === 'edit' ? '1' : '';
                    addBookingInput.value = ''; // مهم علشان ميحسبهاش إضافة


                    // Set read-only state for view mode
                    inputs.forEach(input => {
                        input.readOnly = action === 'view';
                        input.disabled = action === 'view';
                        if (action === 'view') {
                            input.classList.add('form-control-plaintext');
                        } else {
                            input.classList.remove('form-control-plaintext');
                        }
                    });

                    // Populate form with booking data
                    <?php if ($booking): ?>
                        var data = {
                            booking: <?php echo json_encode($booking); ?>,
                            hotel_booking: <?php echo json_encode($hotel_booking); ?>,
                            visa_booking: <?php echo json_encode($visa_booking); ?>,
                            appointment_booking: <?php echo json_encode($appointment_booking); ?>,
                            insurance_booking: <?php echo json_encode($insurance_booking); ?>,
                            entertainment_booking: <?php echo json_encode($entertainment_booking); ?>,
                            transportation_booking: <?php echo json_encode($transportation_booking); ?>,
                            flight_booking: <?php echo json_encode($flight_booking); ?>,
                            cruise_booking: <?php echo json_encode($cruise_booking); ?>
                        };
                        document.getElementById('booking_id').value = id;
                        document.getElementById('customer_name').value = data.booking.customer_name || '';
                        document.getElementById('customer_phone').value = data.booking.customer_phone || '';
                        document.getElementById('sell_price').value = data.booking.sell_price || '';
                        document.getElementById('payment_fees').value = data.booking.payment_fees || '';
                        document.getElementById('cost').value = data.booking.cost || '';
                        document.getElementById('profit').value = data.booking.profit || '';
                        document.getElementById('status').value = data.booking.status || 'Hold';
                        document.getElementById('notes').value = data.booking.notes || '';

                        // Reset booking types
                        var typeButtons = document.querySelectorAll('.booking-type-btn');
                        typeButtons.forEach(btn => {
                            var type = btn.id.replace('btn_', '');
                            btn.classList.remove('selected');
                            document.getElementById('type_' + type).checked = false;
                            document.getElementById(type + '_section').style.display = 'none';
                        });

                        // Set booking types
                        var types = (data.booking.booking_types || '').split(',').filter(type => type);
                        types.forEach(type => {
                            var btn = document.getElementById('btn_' + type);
                            if (btn) {
                                btn.classList.add('selected');
                                document.getElementById('type_' + type).checked = true;
                                document.getElementById(type + '_section').style.display = 'block';
                            }
                        });

                        if (data.hotel_booking) {
                            document.getElementById('hotel_name').value = data.hotel_booking.hotel_name || '';
                            document.getElementById('num_guests').value = data.hotel_booking.num_guests || '';
                            document.getElementById('num_rooms').value = data.hotel_booking.num_rooms || '';
                            document.getElementById('check_in').value = data.hotel_booking.check_in || '';
                            document.getElementById('check_out').value = data.hotel_booking.check_out || '';
                            document.getElementById('hotel_booking_number').value = data.hotel_booking.booking_number || '';
                            document.getElementById('hotel_agent_name').value = data.hotel_booking.agent_name || '';
                            document.getElementById('hotel_payment_status').value = data.hotel_booking.payment_status || 'Not Paid';
                            document.getElementById('hotel_payment_due').value = data.hotel_booking.payment_due || '';
                            document.getElementById('hotel_notes').value = data.hotel_booking.notes || '';
                        }
                        if (data.visa_booking) {
                            document.getElementById('visa_type').value = data.visa_booking.visa_type || '';
                            document.getElementById('visa_application_date').value = data.visa_booking.application_date || '';
                            document.getElementById('visa_status').value = data.visa_booking.status || 'Pending';
                            document.getElementById('visa_notes').value = data.visa_booking.notes || '';
                        }
                        if (data.appointment_booking) {
                            document.getElementById('appointment_type').value = data.appointment_booking.appointment_type || '';
                            document.getElementById('appointment_date').value = data.appointment_booking.appointment_date ? data.appointment_booking.appointment_date.replace(' ', 'T') : '';
                            document.getElementById('appointment_location').value = data.appointment_booking.location || '';
                            document.getElementById('appointment_status').value = data.appointment_booking.status || 'Scheduled';
                            document.getElementById('appointment_notes').value = data.appointment_booking.notes || '';
                        }
                        if (data.insurance_booking) {
                            document.getElementById('insurance_type').value = data.insurance_booking.insurance_type || '';
                            document.getElementById('insurance_provider').value = data.insurance_booking.provider || '';
                            document.getElementById('insurance_start_date').value = data.insurance_booking.start_date || '';
                            document.getElementById('insurance_end_date').value = data.insurance_booking.end_date || '';
                            document.getElementById('insurance_status').value = data.insurance_booking.status || 'Active';
                            document.getElementById('insurance_notes').value = data.insurance_booking.notes || '';
                        }
                        if (data.entertainment_booking) {
                            document.getElementById('event_name').value = data.entertainment_booking.event_name || '';
                            document.getElementById('event_date').value = data.entertainment_booking.event_date || '';
                            document.getElementById('ticket_count').value = data.entertainment_booking.ticket_count || '';
                            document.getElementById('entertainment_supplier').value = data.entertainment_booking.supplier_name || '';
                            document.getElementById('entertainment_status').value = data.entertainment_booking.status || 'Confirmed';
                            document.getElementById('entertainment_notes').value = data.entertainment_booking.notes || '';
                        }
                        if (data.transportation_booking) {
                            document.getElementById('transport_type').value = data.transportation_booking.transport_type || '';
                            document.getElementById('pickup_location').value = data.transportation_booking.pickup_location || '';
                            document.getElementById('dropoff_location').value = data.transportation_booking.dropoff_location || '';
<?php
// Previous PHP code remains the same until the JavaScript section
// ... (Previous PHP code from your provided file up to the end of the transportation_booking population)
?>

                            document.getElementById('transport_date').value = data.transportation_booking.transport_date ? data.transportation_booking.transport_date.replace(' ', 'T') : '';
                            document.getElementById('transport_supplier').value = data.transportation_booking.supplier_name || '';
                            document.getElementById('transport_status').value = data.transportation_booking.status || 'Confirmed';
                            document.getElementById('transport_notes').value = data.transportation_booking.notes || '';
                        }
                        if (data.flight_booking) {
                            document.getElementById('flight_number').value = data.flight_booking.flight_number || '';
                            document.getElementById('flight_departure_date').value = data.flight_booking.departure_date ? data.flight_booking.departure_date.replace(' ', 'T') : '';
                            document.getElementById('flight_arrival_date').value = data.flight_booking.arrival_date ? data.flight_booking.arrival_date.replace(' ', 'T') : '';
                            document.getElementById('flight_from').value = data.flight_booking.from || '';
                            document.getElementById('flight_to').value = data.flight_booking.to || '';
                            document.getElementById('flight_supplier').value = data.flight_booking.supplier_name || '';
                            document.getElementById('flight_status').value = data.flight_booking.status || 'Confirmed';
                            document.getElementById('flight_notes').value = data.flight_booking.notes || '';
                        }
                        if (data.cruise_booking) {
                            document.getElementById('cruise_name').value = data.cruise_booking.cruise_name || '';
                            document.getElementById('cruise_departure_date').value = data.cruise_booking.departure_date || '';
                            document.getElementById('cruise_return_date').value = data.cruise_booking.return_date || '';
                            document.getElementById('cruise_departure_port').value = data.cruise_booking.departure_port || '';
                            document.getElementById('cruise_arrival_port').value = data.cruise_booking.arrival_port || '';
                            document.getElementById('cruise_supplier').value = data.cruise_booking.supplier_name || '';
                            document.getElementById('cruise_status').value = data.cruise_booking.status || 'Confirmed';
                            document.getElementById('cruise_notes').value = data.cruise_booking.notes || '';
                        }
                        calculateProfit();
                    <?php else: ?>
                        // Fetch data if no PHP data is available
                        fetch(`fetch_booking.php?id=${id}`)
                            .then(response => {
                                if (!response.ok) {
                                    throw new Error('Network response was not ok: ' + response.statusText);
                                }
                                return response.json();
                            })
                            .then(data => {
                                if (data.error) {
                                    alert('Error: ' + data.error);
                                    return;
                                }
                                document.getElementById('booking_id').value = id;
                                document.getElementById('customer_name').value = data.booking.customer_name || '';
                                document.getElementById('customer_phone').value = data.booking.customer_phone || '';
                                document.getElementById('sell_price').value = data.booking.sell_price || '';
                                document.getElementById('payment_fees').value = data.booking.payment_fees || '';
                                document.getElementById('cost').value = data.booking.cost || '';
                                document.getElementById('profit').value = data.booking.profit || '';
                                document.getElementById('status').value = data.booking.status || 'Hold';
                                document.getElementById('notes').value = data.booking.notes || '';

                                // Reset booking types
                                var typeButtons = document.querySelectorAll('.booking-type-btn');
                                typeButtons.forEach(btn => {
                                    var type = btn.id.replace('btn_', '');
                                    btn.classList.remove('selected');
                                    document.getElementById('type_' + type).checked = false;
                                    document.getElementById(type + '_section').style.display = 'none';
                                });

                                // Set booking types
                                var types = (data.booking.booking_types || '').split(',').filter(type => type);
                                types.forEach(type => {
                                    var btn = document.getElementById('btn_' + type);
                                    if (btn) {
                                        btn.classList.add('selected');
                                        document.getElementById('type_' + type).checked = true;
                                        document.getElementById(type + '_section').style.display = 'block';
                                    }
                                });

                                if (data.hotel_booking) {
                                    document.getElementById('hotel_name').value = data.hotel_booking.hotel_name || '';
                                    document.getElementById('num_guests').value = data.hotel_booking.num_guests || '';
                                    document.getElementById('num_rooms').value = data.hotel_booking.num_rooms || '';
                                    document.getElementById('check_in').value = data.hotel_booking.check_in || '';
                                    document.getElementById('check_out').value = data.hotel_booking.check_out || '';
                                    document.getElementById('hotel_booking_number').value = data.hotel_booking.booking_number || '';
                                    document.getElementById('hotel_agent_name').value = data.hotel_booking.agent_name || '';
                                    document.getElementById('hotel_payment_status').value = data.hotel_booking.payment_status || 'Not Paid';
                                    document.getElementById('hotel_payment_due').value = data.hotel_booking.payment_due || '';
                                    document.getElementById('hotel_notes').value = data.hotel_booking.notes || '';
                                }
                                if (data.visa_booking) {
                                    document.getElementById('visa_type').value = data.visa_booking.visa_type || '';
                                    document.getElementById('visa_application_date').value = data.visa_booking.application_date || '';
                                    document.getElementById('visa_status').value = data.visa_booking.status || 'Pending';
                                    document.getElementById('visa_notes').value = data.visa_booking.notes || '';
                                }
                                if (data.appointment_booking) {
                                    document.getElementById('appointment_type').value = data.appointment_booking.appointment_type || '';
                                    document.getElementById('appointment_date').value = data.appointment_booking.appointment_date ? data.appointment_booking.appointment_date.replace(' ', 'T') : '';
                                    document.getElementById('appointment_location').value = data.appointment_booking.location || '';
                                    document.getElementById('appointment_status').value = data.appointment_booking.status || 'Scheduled';
                                    document.getElementById('appointment_notes').value = data.appointment_booking.notes || '';
                                }
                                if (data.insurance_booking) {
                                    document.getElementById('insurance_type').value = data.insurance_booking.insurance_type || '';
                                    document.getElementById('insurance_provider').value = data.insurance_booking.provider || '';
                                    document.getElementById('insurance_start_date').value = data.insurance_booking.start_date || '';
                                    document.getElementById('insurance_end_date').value = data.insurance_booking.end_date || '';
                                    document.getElementById('insurance_status').value = data.insurance_booking.status || 'Active';
                                    document.getElementById('insurance_notes').value = data.insurance_booking.notes || '';
                                }
                                if (data.entertainment_booking) {
                                    document.getElementById('event_name').value = data.entertainment_booking.event_name || '';
                                    document.getElementById('event_date').value = data.entertainment_booking.event_date || '';
                                    document.getElementById('ticket_count').value = data.entertainment_booking.ticket_count || '';
                                    document.getElementById('entertainment_supplier').value = data.entertainment_booking.supplier_name || '';
                                    document.getElementById('entertainment_status').value = data.entertainment_booking.status || 'Confirmed';
                                    document.getElementById('entertainment_notes').value = data.entertainment_booking.notes || '';
                                }
                                if (data.transportation_booking) {
                                    document.getElementById('transport_type').value = data.transportation_booking.transport_type || '';
                                    document.getElementById('pickup_location').value = data.transportation_booking.pickup_location || '';
                                    document.getElementById('dropoff_location').value = data.transportation_booking.dropoff_location || '';
                                    document.getElementById('transport_date').value = data.transportation_booking.transport_date ? data.transportation_booking.transport_date.replace(' ', 'T') : '';
                                    document.getElementById('transport_supplier').value = data.transportation_booking.supplier_name || '';
                                    document.getElementById('transport_status').value = data.transportation_booking.status || 'Confirmed';
                                    document.getElementById('transport_notes').value = data.transportation_booking.notes || '';
                                }
                                if (data.flight_booking) {
                                    document.getElementById('flight_number').value = data.flight_booking.flight_number || '';
                                    document.getElementById('flight_departure_date').value = data.flight_booking.departure_date ? data.flight_booking.departure_date.replace(' ', 'T') : '';
                                    document.getElementById('flight_arrival_date').value = data.flight_booking.arrival_date ? data.flight_booking.arrival_date.replace(' ', 'T') : '';
                                    document.getElementById('flight_from').value = data.flight_booking.from || '';
                                    document.getElementById('flight_to').value = data.flight_booking.to || '';
                                    document.getElementById('flight_supplier').value = data.flight_booking.supplier_name || '';
                                    document.getElementById('flight_status').value = data.flight_booking.status || 'Confirmed';
                                    document.getElementById('flight_notes').value = data.flight_booking.notes || '';
                                }
                                if (data.cruise_booking) {
                                    document.getElementById('cruise_name').value = data.cruise_booking.cruise_name || '';
                                    document.getElementById('cruise_departure_date').value = data.cruise_booking.departure_date || '';
                                    document.getElementById('cruise_return_date').value = data.cruise_booking.return_date || '';
                                    document.getElementById('cruise_departure_port').value = data.cruise_booking.departure_port || '';
                                    document.getElementById('cruise_arrival_port').value = data.cruise_booking.arrival_port || '';
                                    document.getElementById('cruise_supplier').value = data.cruise_booking.supplier_name || '';
                                    document.getElementById('cruise_status').value = data.cruise_booking.status || 'Confirmed';
                                    document.getElementById('cruise_notes').value = data.cruise_booking.notes || '';
                                }
                                calculateProfit();
                            })
                            .catch(error => {
                                console.error('Fetch error:', error);
                                alert('Error fetching data: ' + error.message);
                            });
                    <?php endif; ?>
                }
            });

            // Auto-open modal if action and id are in URL
            <?php if ($action === 'view' || $action === 'edit'): ?>
                var modal = new bootstrap.Modal(document.getElementById('reservationModal'));
                if (modal) {
                    var triggerButton = document.createElement('button');
                    triggerButton.setAttribute('data-bs-toggle', 'modal');
                    triggerButton.setAttribute('data-bs-target', '#reservationModal');
                    triggerButton.setAttribute('data-action', '<?php echo $action; ?>');
                    triggerButton.setAttribute('data-id', '<?php echo $booking_id; ?>');
                    triggerButton.style.display = 'none';
                    document.body.appendChild(triggerButton);
                    triggerButton.click();
                    document.body.removeChild(triggerButton);
                } else {
                    console.error('Modal not initialized');
                    alert('Modal failed to initialize');
                }
            <?php endif; ?>
        });
    </script>
</body>
</html>