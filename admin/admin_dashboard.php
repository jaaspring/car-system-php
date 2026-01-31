<?php
session_start();

// Check if user is logged in and has the correct role
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

require_once '../db_connection.php';

// Fetch Counts
$car_count = $conn->query("SELECT COUNT(*) FROM car_details")->fetch_row()[0];
$user_count = $conn->query("SELECT COUNT(*) FROM users WHERE role='user'")->fetch_row()[0];
$appt_count = $conn->query("SELECT COUNT(*) FROM test_drive WHERE status='Pending'")->fetch_row()[0];
$appt_total = $conn->query("SELECT COUNT(*) FROM test_drive")->fetch_row()[0];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard</title>

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Century Gothic', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
}

/* ===== HEADER ===== */
.header {
    background-color: #000;
    padding: 20px 40px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.header-left {
    display: flex;
    align-items: center;
    gap: 30px;
}

.logo-img {
    width: 150px;
    height: 40px;
}

.logo-img img {
    width: 100%;
    height: 100%;
    object-fit: contain;
}

.nav-menu {
    display: flex;
    gap: 35px;
}

.nav-link {
    color: #fff;
    text-decoration: none;
    font-size: 16px;
    font-weight: 600;
}

.nav-link:hover {
    opacity: 0.7;
}

.logout-btn {
    background-color: #ff4500;
    color: #fff;
    padding: 10px 25px;
    border-radius: 20px;
    text-decoration: none;
    font-weight: 700;
}

/* ===== MAIN CONTENT ===== */
.main-content {
    flex: 1;
    background: radial-gradient(
        ellipse at center,
        #f4d77e 0%,
        #e6c770 25%,
        #d4a747 50%,
        #c89a3d 75%,
        #9d7730 100%
    );
    padding: 60px 40px;
    display: flex;
    flex-direction: column;
    align-items: center;
}

/* ===== DASHBOARD ===== */
.dashboard-title {
    font-size: 30px;
    font-weight: 700;
    margin-bottom: 10px;
}

.dashboard-subtitle {
    font-size: 15px;
    margin-bottom: 40px;
}

/* ===== CARD GRID ===== */
.card-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 30px;
    width: 100%;
    max-width: 900px;
}

/* ===== CARD ===== */
.card {
    background: #000;
    border-radius: 22px;
    overflow: hidden;
    cursor: pointer;
    transition: transform 0.25s, box-shadow 0.25s;
    text-decoration: none;
}

.card:hover {
    transform: translateY(-6px);
    box-shadow: 0 15px 30px rgba(0,0,0,0.4);
}

/* IMAGE (70%) */
.card-img {
    height: 200px;
    background: #111;
}

.card-img img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

/* TEXT (30%) */
.card-title {
    padding: 18px;
    text-align: center;
    background: #000;
    color: #fff;
    font-size: 15px;
    font-weight: 700;
    letter-spacing: 1px;
}

/* ===== RESPONSIVE ===== */
@media (max-width: 900px) {
    .card-grid {
        grid-template-columns: 1fr;
        max-width: 420px;
    }
}
</style>
</head>

<body>

<?php include('../navigation.php'); ?>

<!-- MAIN -->
<div class="main-content">
    <h1 class="dashboard-title">Admin Dashboard</h1>
    <p class="dashboard-subtitle">
        Manage system data, users, and test drive appointments
    </p>

    <div class="card-grid">

        <!-- MANAGE CARS -->
        <a href="manage_cars.php" class="card">
            <div class="card-img">
                <img src="../Images/admin_manage_cars.png" alt="Manage Cars">
            </div>
            <div class="card-title">
                MANAGE CARS <br>
                <span style="font-size:12px; color:#2ecc71; font-weight:normal;">Total: <?php echo $car_count; ?> Cars</span>
            </div>
        </a>

        <!-- MANAGE USERS -->
        <a href="manage_users.php" class="card">
            <div class="card-img">
                <img src="../Images/admin_manage_users.png" alt="Manage Users">
            </div>
            <div class="card-title">
                MANAGE USERS <br>
                <span style="font-size:12px; color:#3498db; font-weight:normal;">Total: <?php echo $user_count; ?> Users</span>
            </div>
        </a>

        <!-- MANAGE APPOINTMENTS -->
        <a href="manage_appointments.php" class="card">
            <div class="card-img">
                <img src="../Images/admin_manage_appointments.png" alt="Manage Appointments">
            </div>
            <div class="card-title">
                MANAGE APPOINTMENTS <br>
                <span style="font-size:12px; color:#e74c3c; font-weight:normal;">
                    <?php echo $appt_count; ?> Pending / <?php echo $appt_total; ?> Total
                </span>
            </div>
        </a>

    </div>
</div>

</body>
</html>
