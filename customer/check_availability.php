<?php
require_once '../db_connection.php';

$date = $_GET['date'] ?? '';
$location = $_GET['location'] ?? '';
$showroom = $_GET['showroom'] ?? '';

$booked_times = [];

if ($date && $location && $showroom) {
    $stmt = $conn->prepare("SELECT time FROM test_drive WHERE date = ? AND location = ? AND showroom = ? AND status != 'Cancelled'");
    $stmt->bind_param("sss", $date, $location, $showroom);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        // Format time to HH:MM to match the dropdown values
        $booked_times[] = substr($row['time'], 0, 5);
    }
    $stmt->close();
}

header('Content-Type: application/json');
echo json_encode($booked_times);
