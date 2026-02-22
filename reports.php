<?php
require_once 'config.php';
require_once 'functions.php';

if (!isset($_SESSION['user_id']) || !checkPermission($_SESSION['role'], 'editor')) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$start_date = validateInput($_GET['start_date'] ?? '', 'date');
$end_date = validateInput($_GET['end_date'] ?? '', 'date');
$filter_type = validateInput($_GET['type'] ?? '');
$filter_status = validateInput($_GET['status'] ?? '');

$sql = "SELECT b.*, bs.status_name, bt.type_name 
        FROM bookings b 
        JOIN booking_statuses bs ON b.status_id = bs.id 
        JOIN booking_type_mappings btm ON b.id = btm.booking_id 
        JOIN booking_types bt ON btm.booking_type_id = bt.id 
        WHERE b.user_id = ?";
$params = [$user_id];
$types = "i";

if ($start_date && $end_date) {
    $sql .= " AND b.created_at BETWEEN ? AND ?";
    $params[] = $start_date;
    $params[] = $end_date;
    $types .= "ss";
}
if ($filter_type) {
    $sql .= " AND bt.type_name = ?";
    $params[] = $filter_type;
    $types .= "s";
}
if ($filter_status) {
    $sql .= " AND bs.status_name = ?";
    $params[] = $filter_status;
    $types .= "s";
}

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$results = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Aggregate data for report
$report_data = [];
foreach ($results as $row) {
    $type = $row['type_name'];
    if (!isset($report_data[$type])) {
        $report_data[$type] = [
            'total_bookings' => 0,
            'total_sell_price' => 0,
            'total_profit' => 0,
            'total_payment_fees' => 0
        ];
    }
    $report_data[$type]['total_bookings']++;
    $report_data[$type]['total_sell_price'] += $row['sell_price'];
    $report_data[$type]['total_profit'] += $row['profit'];
    $report_data[$type]['total_payment_fees'] += $row['payment_fees'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reports</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/styles.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h2 class="my-4">Reports</h2>
        <div class="filter-section">
            <form id="filterForm" class="row g-3">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <div class="col-auto">
                    <input type="date" name="start_date" class="form-control" value="<?php echo htmlspecialchars($start_date); ?>">
                </div>
                <div class="col-auto">
                    <input type="date" name="end_date" class="form-control" value="<?php echo htmlspecialchars($end_date); ?>">
                </div>
                <div class="col-auto">
                    <select name="type" class="form-select">
                        <option value="">All Types</option>
                        <?php foreach (getBookingTypes($conn) as $type): ?>
                            <option value="<?php echo $type['type_name']; ?>" <?php echo $filter_type == $type['type_name'] ? 'selected' : ''; ?>><?php echo ucfirst($type['type_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-auto">
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <?php foreach (getBookingStatuses($conn) as $status): ?>
                            <option value="<?php echo $status['status_name']; ?>" <?php echo $filter_status == $status['status_name'] ? 'selected' : ''; ?>><?php echo $status['status_name']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary btn-modern">Filter</button>
                </div>
            </form>
        </div>

        <div class="table-container mt-4">
            <table class="table table-bordered table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Booking Type</th>
                        <th>Total Bookings</th>
                        <th>Total Sell Price</th>
                        <th>Total Profit</th>
                        <th>Total Payment Fees</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($report_data as $type => $data): ?>
                        <tr>
                            <td><?php echo ucfirst($type); ?></td>
                            <td><?php echo $data['total_bookings']; ?></td>
                            <td><?php echo number_format($data['total_sell_price'], 2); ?></td>
                            <td><?php echo number_format($data['total_profit'], 2); ?></td>
                            <td><?php echo number_format($data['total_payment_fees'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($report_data)): ?>
                        <tr><td colspan="5">No data available.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <a href="reservations.php" class="btn btn-secondary btn-modern mt-3">Back to Reservations</a>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <?php $conn->close(); ?>
</body>
</html>