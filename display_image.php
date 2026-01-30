<?php
require_once __DIR__ . '/db_connection.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    $stmt = $conn->prepare("SELECT image FROM car_details WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($imageData);
        $stmt->fetch();
        
        if (!empty($imageData)) {
            // Detect content type if possible, or default to jpeg/png
            // Simple approach: just output it. Browsers are good at sniffing.
            header("Content-Type: image/jpeg"); 
            echo $imageData;
        } else {
            // Serve a default placeholder or 404
            http_response_code(404);
        }
    } else {
        http_response_code(404);
    }
    $stmt->close();
}
?>
