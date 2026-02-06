<?php
require_once 'db_connection.php';

// 1. Create Wishlist Table
$sql1 = "CREATE TABLE IF NOT EXISTS user_wishlist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    car_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (car_id) REFERENCES car_details(id) ON DELETE CASCADE,
    UNIQUE KEY unique_wishlist (user_id, car_id)
)";

if ($conn->query($sql1) === TRUE) {
    echo "Table 'user_wishlist' created successfully or already exists.<br>";
} else {
    echo "Error creating table: " . $conn->error . "<br>";
}

// 2. Add plan_name to loan_history
// Check if column exists first
$check = $conn->query("SHOW COLUMNS FROM loan_history LIKE 'plan_name'");
if ($check->num_rows == 0) {
    $sql2 = "ALTER TABLE loan_history ADD COLUMN plan_name VARCHAR(50) DEFAULT 'My Plan'";
    if ($conn->query($sql2) === TRUE) {
        echo "Column 'plan_name' added to 'loan_history'.<br>";
    } else {
        echo "Error adding column: " . $conn->error . "<br>";
    }
} else {
    echo "Column 'plan_name' already exists in 'loan_history'.<br>";
}

echo "Phase 3 Database Update Complete.";
?>
