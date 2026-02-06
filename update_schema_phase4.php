<?php
require_once 'db_connection.php';

// 1. Audit Logs
$sql1 = "CREATE TABLE IF NOT EXISTS audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(255),
    details TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
)";
if ($conn->query($sql1) === TRUE) echo "Table 'audit_logs' created.<br>";
else echo "Error creating audit_logs: " . $conn->error . "<br>";

// 2. Static Pages
$sql2 = "CREATE TABLE IF NOT EXISTS static_pages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(50) UNIQUE,
    title VARCHAR(100),
    content LONGTEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";
if ($conn->query($sql2) === TRUE) echo "Table 'static_pages' created.<br>";
else echo "Error creating static_pages: " . $conn->error . "<br>";

// 3. Admin Reply Column
$check = $conn->query("SHOW COLUMNS FROM test_drive_reviews LIKE 'admin_reply'");
if ($check->num_rows == 0) {
    $sql3 = "ALTER TABLE test_drive_reviews ADD COLUMN admin_reply TEXT";
    if ($conn->query($sql3) === TRUE) echo "Column 'admin_reply' added.<br>";
    else echo "Error adding column: " . $conn->error . "<br>";
} else {
    echo "Column 'admin_reply' already exists.<br>";
}

// 4. Default Pages (About, Contact) if empty
$checkPages = $conn->query("SELECT * FROM static_pages");
if ($checkPages->num_rows == 0) {
    $conn->query("INSERT INTO static_pages (slug, title, content) VALUES ('about', 'About Us', '<h1>About Our Dealership</h1><p>We are the best Proton dealership in town.</p>')");
    $conn->query("INSERT INTO static_pages (slug, title, content) VALUES ('contact', 'Contact Us', '<h1>Contact Us</h1><p>Email: support@proton.com<br>Phone: +60 123 456 789</p>')");
    echo "Default static pages inserted.<br>";
}

echo "Phase 4 Database Update Complete.";
?>
