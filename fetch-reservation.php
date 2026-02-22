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
    die("Connection failed: " . $conn->connect_error);
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Reservations</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f6f9; }
        .table th, .table td { vertical-align: middle; }
        .btn-modern { border-radius: 10px; padding: 5px 15px; font-weight: 500; transition: all 0.3s; }
        .btn-modern:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
    </style>
</head>
<body>
<div class="container my-5">
    <h2>My Reservations</h2>

    <form method="get" class="mb-3 row g-3">
        <div class="col-auto">
            <select name="status" class="form-select" onchange="this.form.submit()">
                <option value="">All Statuses</option>
                <option value="Hold" <?php echo $filter_status == 'Hold' ? 'selected' : ''; ?>>Hold</option>
                <option value="Issued" <?php echo $filter_status == 'Issued' ? 'selected' : ''; ?>>Issued</option>
                <option value="Cancelled" <?php echo $filter_status == 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
            </select>
        </div>
        <div class="col-auto">
            <input type="text" name="search" class="form-control" value="<?php echo htmlspecialchars($search_query); ?>" placeholder="Search..." onchange="this.form.submit()">
        </div>
    </form>

    <table class="table table-bordered table-hover">
        <thead class="table-dark">
        <tr>
            <th>ID</th>
            <th>Customer</th>
            <th>Phone</th>
            <th>Types</th>
            <th>Sell</th>
            <th>Cost</th>
            <th>Profit</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['id']; ?></td>
                <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                <td><?php echo htmlspecialchars($row['customer_phone']); ?></td>
                <td><?php echo ucwords(str_replace(",", ", ", htmlspecialchars($row['booking_types']))); ?></td>
                <td><?php echo number_format($row['sell_price'], 2); ?></td>
                <td><?php echo number_format($row['cost'], 2); ?></td>
                <td><?php echo number_format($row['profit'], 2); ?></td>
                <td><?php echo htmlspecialchars($row['status']); ?></td>
                <td>
                    <a href="index.php?action=view&id=<?php echo $row['id']; ?>" class="btn btn-primary btn-sm btn-modern">View</a>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php $conn->close(); ?>