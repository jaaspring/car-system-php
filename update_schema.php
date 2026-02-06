<?php
include('db_connection.php');

// Add reset_token_hash and reset_token_expires_at columns to users table
$sql = "SHOW COLUMNS FROM users LIKE 'reset_token_hash'";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    $sql = "ALTER TABLE users 
            ADD COLUMN reset_token_hash VARCHAR(64) NULL DEFAULT NULL,
            ADD COLUMN reset_token_expires_at DATETIME NULL DEFAULT NULL,
            ADD UNIQUE INDEX (reset_token_hash)";
    
    if ($conn->query($sql) === TRUE) {
        echo "Table users updated successfully.";
    } else {
        echo "Error updating table: " . $conn->error;
    }
} else {
    echo "Columns already exist.";
}

$conn->close();
?>
