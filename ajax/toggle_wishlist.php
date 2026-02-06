<?php
session_start();
require_once '../db_connection.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$car_id = isset($data['car_id']) ? intval($data['car_id']) : 0;
$user_id = $_SESSION['user_id'];

if ($car_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid Vehicle']);
    exit();
}

// Check if already in wishlist
$check = $conn->prepare("SELECT id FROM user_wishlist WHERE user_id = ? AND car_id = ?");
$check->bind_param("ii", $user_id, $car_id);
$check->execute();
$result = $check->get_result();

if ($result->num_rows > 0) {
    // Remove
    $stmt = $conn->prepare("DELETE FROM user_wishlist WHERE user_id = ? AND car_id = ?");
    $stmt->bind_param("ii", $user_id, $car_id);
    $action = 'removed';
} else {
    // Add
    $stmt = $conn->prepare("INSERT INTO user_wishlist (user_id, car_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $user_id, $car_id);
    $action = 'added';
}

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'action' => $action]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>
