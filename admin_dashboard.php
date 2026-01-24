<?php
session_start();

// Check if user is logged in and has the correct role
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

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

        /* Header */
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

        .logo {
            display: flex;
            align-items: center;
            gap: 15px;
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

        /* Navigation */
        .nav-menu {
            display: flex;
            gap: 35px;
            align-items: center;
        }

        .nav-link {
            color: #fff;
            text-decoration: none;
            font-size: 16px;
            font-weight: 600;
            transition: opacity 0.3s;
            cursor: pointer;
        }

        .nav-link:hover {
            opacity: 0.7;
        }

        /* Logout Button */
        .logout-btn {
            background-color: #ff4500;
            color: #fff;
            border: none;
            padding: 10px 25px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: background-color 0.3s;
            text-decoration: none;
            display: inline-block;
        }

        .logout-btn:hover {
            background-color: #e63e00;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            background: radial-gradient(ellipse at center, #f4d77e 0%, #e6c770 25%, #d4a747 50%, #c89a3d 75%, #9d7730 100%);
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 60px 40px;
        }

        /* Dashboard Section */
        .dashboard-section {
            width: 100%;
            max-width: 900px;
            margin-top: 50px;
            text-align: center;
        }

        .dashboard-title {
            font-size: 28px;
            font-weight: 700;
            color: #000;
            margin-bottom: 30px;
        }

        /* Button */
        .manage-btn {
            padding: 12px 30px;
            background-color: #000;
            color: #fff;
            border: none;
            border-radius: 25px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.3s;
            margin-bottom: 20px;
            width: 250px;
        }

        .manage-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.4);
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-left">
            <div class="logo">
                <div class="logo-img">
                    <img src="Images/proton.png" alt="Proton Logo">
                </div>
            </div>
            <nav class="nav-menu">
                <a href="admin_dashboard.php" class="nav-link">Dashboard</a>
                <a href="manage_cars.php" class="nav-link">Manage Cars</a>
                <a href="manage_users.php" class="nav-link">Manage Users</a>
                <a href="manage_appointments.php" class="nav-link">Manage Appointments</a>
            </nav>
        </div>
        <a href="logout.php" class="logout-btn">Logout</a>
    </div>

    <div class="main-content">
        <div class="dashboard-section">
            <h1 class="dashboard-title">Admin Dashboard</h1>
            <p>Welcome to the admin area. From here you can manage cars, users, and appointments.</p>
            <a href="manage_cars.php" class="manage-btn">Manage Cars</a>
            <a href="manage_users.php" class="manage-btn">Manage Users</a>
            <a href="manage_appointments.php" class="manage-btn">Manage Appointments</a>
        </div>
    </div>
</body>
</html>
