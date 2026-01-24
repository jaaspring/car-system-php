<?php
session_start();

// Check if user is logged in and has the correct role
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

include('db_connection.php');

// Initialize filter variables
$date_filter = isset($_GET['date']) ? $_GET['date'] : '';
$time_filter = isset($_GET['time']) ? $_GET['time'] : '';
$location_filter = isset($_GET['location']) ? $_GET['location'] : '';
$showroom_filter = isset($_GET['showroom']) ? $_GET['showroom'] : '';

// Build the SQL query with filters
$sql = "SELECT td.id, u.name, td.location, td.showroom, td.date, td.time, td.car_model_variant
        FROM test_drive td
        JOIN users u ON td.user_id = u.id
        WHERE 1";

// Apply filters if they exist
if ($date_filter) {
    $sql .= " AND td.date = '$date_filter'";
}
if ($time_filter) {
    $sql .= " AND td.time = '$time_filter'";
}
if ($location_filter) {
    $sql .= " AND td.location LIKE '%$location_filter%'";
}
if ($showroom_filter) {
    $sql .= " AND td.showroom LIKE '%$showroom_filter%'";
}

// Execute the query
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Appointments - Admin Dashboard</title>
    <style>
        /* Add your CSS styling here (same as in other pages) */
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

        .main-content {
            flex: 1;
            background: radial-gradient(ellipse at center, #f4d77e 0%, #e6c770 25%, #d4a747 50%, #c89a3d 75%, #9d7730 100%);
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 60px 40px;
        }

        .filter-form {
            margin-bottom: 20px;
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .filter-form input,
        .filter-form select {
            padding: 10px;
            border-radius: 5px;
            font-size: 14px;
        }

        .appointments-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .appointments-table th, .appointments-table td {
            padding: 12px 15px;
            text-align: left;
            border: 1px solid #ddd;
        }

        .appointments-table th {
            background-color: #000;
            color: #fff;
        }

        .appointments-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .appointments-table tr:hover {
            background-color: #f1f1f1;
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
        <h1>Manage Test Drive Appointments</h1>

        <!-- Filter Form -->
        <form class="filter-form" method="GET" action="">
            <input type="date" name="date" value="<?php echo htmlspecialchars($date_filter); ?>" placeholder="Filter by Date">
            <input type="time" name="time" value="<?php echo htmlspecialchars($time_filter); ?>" placeholder="Filter by Time">
            <input type="text" name="location" value="<?php echo htmlspecialchars($location_filter); ?>" placeholder="Filter by Location">
            <input type="text" name="showroom" value="<?php echo htmlspecialchars($showroom_filter); ?>" placeholder="Filter by Showroom">
            <button type="submit">Filter</button>
        </form>

        <!-- Appointments Table -->
        <table class="appointments-table">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Location</th>
                    <th>Showroom</th>
                    <th>Car Model</th>
                    <th>Date</th>
                    <th>Time</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Display all appointments that match the filter criteria
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                                <td>" . htmlspecialchars($row['name']) . "</td>
                                <td>" . htmlspecialchars($row['location']) . "</td>
                                <td>" . htmlspecialchars($row['showroom']) . "</td>
                                <td>" . htmlspecialchars($row['car_model_variant']) . "</td>
                                <td>" . htmlspecialchars($row['date']) . "</td>
                                <td>" . htmlspecialchars($row['time']) . "</td>
                            </tr>";
                    }
                } else {
                    echo "<tr><td colspan='6'>No appointments found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>
