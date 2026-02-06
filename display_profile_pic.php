<?php
// display_profile_pic.php
require_once __DIR__ . '/db_connection.php';
session_start();

// Use logged in user ID if no ID provided, or specific ID if provided
$id = isset($_GET['id']) ? intval($_GET['id']) : (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0);

if ($id > 0) {
    $stmt = $conn->prepare("SELECT profile_pic FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($imageData);
        $stmt->fetch();
        
        if (!empty($imageData)) {
            header("Content-Type: image/jpeg"); // Default assumption
            echo $imageData;
            exit;
        }
    }
    $stmt->close();
}

// Fallback to default user icon if no image found
header("Content-Type: image/png");
// You might want to have a default image file, creating a simple colored square transparently if not exists
// For now, redirect to a placeholder service or output nothing (broken image)
// Better: Read a local default image
$default_image = __DIR__ . '/Images/default_user.png';
if (file_exists($default_image)) {
    readfile($default_image);
} else {
    // 1x1 Transparent pixel
    echo base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=');
}
?>
