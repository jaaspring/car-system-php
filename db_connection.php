<?php
// db_connection.php
$servername = "localhost";
$username = "root"; // use your MySQL username
$password = ""; // use your MySQL password
$dbname = "car"; // database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to UTF-8
$conn->set_charset("utf8mb4");

// Increase wait timeout to prevent "MySQL server has gone away" errors
$conn->query("SET SESSION wait_timeout = 600");

