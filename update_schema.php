<?php
include('db_connection.php');

// 1. Check/Add reset_token_hash
$sql = "SHOW COLUMNS FROM users LIKE 'reset_token_hash'";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    $sql = "ALTER TABLE users 
            ADD COLUMN reset_token_hash VARCHAR(64) NULL DEFAULT NULL,
            ADD COLUMN reset_token_expires_at DATETIME NULL DEFAULT NULL,
            ADD UNIQUE INDEX (reset_token_hash)";
    if ($conn->query($sql) === TRUE) echo "Added reset_token columns.<br>";
    else echo "Error adding reset_token columns: " . $conn->error . "<br>";
}

// 2. Check/Add profile_pic
$sql = "SHOW COLUMNS FROM users LIKE 'profile_pic'";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    $sql = "ALTER TABLE users ADD COLUMN profile_pic LONGBLOB NULL DEFAULT NULL";
    if ($conn->query($sql) === TRUE) echo "Added profile_pic column.<br>";
    else echo "Error adding profile_pic column: " . $conn->error . "<br>";
}

echo "Schema update check complete.";
$conn->close();
?>
