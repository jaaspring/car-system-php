<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    exit('Unauthorized');
}

require_once '../db_connection.php';

// Fetch Filters from GET if applied (optional - for now let's export all or same logic)
$date_filter     = isset($_GET['date']) ? $_GET['date'] : '';
$status_filter   = isset($_GET['status_filter']) ? $_GET['status_filter'] : '';

$sql = "SELECT td.id, u.name, u.phone, u.email, td.location, td.showroom, td.car_model_variant, td.date, td.time, td.status
        FROM test_drive td
        JOIN users u ON td.user_id = u.id
        WHERE 1";

if ($date_filter) $sql .= " AND td.date = '$date_filter'";
if ($status_filter) $sql .= " AND td.status = '$status_filter'";

$result = $conn->query($sql);

$filename = "appointments_export_" . date('Ymd') . ".csv";

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$output = fopen('php://output', 'w');

// Header Row
fputcsv($output, ['ID', 'Customer Name', 'Phone', 'Email', 'Location', 'Showroom', 'Car Model', 'Date', 'Time', 'Status']);

// Data Rows
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, $row);
    }
}

fclose($output);
exit();
?>
