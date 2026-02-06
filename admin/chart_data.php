<?php
header('Content-Type: application/json');
require_once '../db_connection.php';
session_start();

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$data = [];

// 1. Appointments per Month (Line Chart)
// Assuming 'date' is YYYY-MM-DD
$months = [];
for ($i = 5; $i >= 0; $i--) {
    $months[] = date("Y-m", strtotime("-$i months"));
}
$appt_counts = [];
foreach ($months as $m) {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM test_drive WHERE date LIKE ?");
    $m_like = "$m%";
    $stmt->bind_param("s", $m_like);
    $stmt->execute();
    $stmt->bind_result($c);
    $stmt->fetch();
    $appt_counts[] = $c;
    $stmt->close();
}
$data['monthly_appointments'] = [
    'labels' => $months,
    'data' => $appt_counts
];

// 2. Most Popular Car Models (Bar Chart) - Top 5
$car_labels = [];
$car_data = [];
$res = $conn->query("SELECT car_model_variant, COUNT(*) as count FROM test_drive GROUP BY car_model_variant ORDER BY count DESC LIMIT 5");
while ($row = $res->fetch_assoc()) {
    $car_labels[] = $row['car_model_variant'];
    $car_data[] = $row['count'];
}
$data['popular_cars'] = [
    'labels' => $car_labels,
    'data' => $car_data
];

// 3. Appointments by Status (Pie Chart)
$status_labels = ['Pending', 'Completed', 'Cancelled'];
$status_data = [];
foreach ($status_labels as $s) {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM test_drive WHERE status = ?");
    $stmt->bind_param("s", $s);
    $stmt->execute();
    $stmt->bind_result($c);
    $stmt->fetch();
    $status_data[] = $c;
    $stmt->close();
}
$data['status_distribution'] = [
    'labels' => $status_labels,
    'data' => $status_data
];

echo json_encode($data);
?>
