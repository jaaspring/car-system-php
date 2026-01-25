<?php
session_start();

// Check if user is logged in and has the correct role
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

include('db_connection.php');

// Initialize filter variables
$date_filter     = isset($_GET['date']) ? $_GET['date'] : '';
$time_filter     = isset($_GET['time']) ? $_GET['time'] : '';
$location_filter = isset($_GET['location']) ? $_GET['location'] : '';
$showroom_filter = isset($_GET['showroom']) ? $_GET['showroom'] : '';

/* ===== UPDATE STATUS ===== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $appointment_id = intval($_POST['appointment_id']);
    $status = $_POST['status'];

    $stmt = $conn->prepare("UPDATE test_drive SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $appointment_id);
    $stmt->execute();
    $stmt->close();
}

/* ===== FETCH DROPDOWN DATA ===== */
$locations  = [];
$showrooms  = [];

$locRes = $conn->query("SELECT DISTINCT location FROM test_drive WHERE location IS NOT NULL");
while ($l = $locRes->fetch_assoc()) {
    $locations[] = $l['location'];
}

$showRes = $conn->query("SELECT DISTINCT showroom FROM test_drive WHERE showroom IS NOT NULL");
while ($s = $showRes->fetch_assoc()) {
    $showrooms[] = $s['showroom'];
}

/* ===== BUILD QUERY ===== */
$sql = "SELECT td.id, u.name, td.location, td.showroom, td.date, td.time,
       td.car_model_variant, td.status
        FROM test_drive td
        JOIN users u ON td.user_id = u.id
        WHERE 1";

if ($date_filter) {
    $sql .= " AND td.date = '$date_filter'";
}
if ($time_filter) {
    $sql .= " AND td.time = '$time_filter'";
}
if ($location_filter) {
    $sql .= " AND td.location = '$location_filter'";
}
if ($showroom_filter) {
    $sql .= " AND td.showroom = '$showroom_filter'";
}

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Appointments</title>

<style>
* { box-sizing: border-box; margin: 0; padding: 0; }

body {
    font-family: 'Century Gothic', sans-serif;
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

.logo-img img {
    height: 45px;
}

.nav-menu {
    display: flex;
    gap: 30px;
}

.nav-link {
    color: #fff;
    text-decoration: none;
    font-weight: 600;
}

.nav-link:hover { opacity: .7; }

.logout-btn {
    background: #ff4500;
    color: #fff;
    padding: 10px 25px;
    border-radius: 20px;
    text-decoration: none;
    font-weight: bold;
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
}

.container {
    max-width: 1200px;
    margin: auto;
    background: #fff;
    padding: 35px;
    border-radius: 25px;
    box-shadow: 0 20px 40px rgba(0,0,0,.25);
}

h1 {
    text-align: center;
    margin-bottom: 30px;
}

/* ===== FILTER ===== */
.filter-form {
    display: flex;
    gap: 15px;
    justify-content: center;
    flex-wrap: wrap;
    margin-bottom: 25px;
}

.filter-form input,
.filter-form select {
    padding: 10px;
    border-radius: 8px;
    border: 1px solid #ccc;
}

.filter-form button {
    padding: 10px 22px;
    border-radius: 20px;
    border: none;
    background: #000;
    color: #fff;
    font-weight: bold;
    cursor: pointer;
}

/* ===== TABLE ===== */
table {
    width: 100%;
    border-collapse: collapse;
}

th, td {
    padding: 12px;
    border: 1px solid #ddd;
    text-align: center;
    font-size: 14px;
}

th {
    background: #000;
    color: #fff;
}

tr:nth-child(even) { background: #f9f9f9; }
tr:hover { background: #f1f1f1; }
</style>
</head>

<body>

<!-- HEADER -->
<div class="header">
    <div class="header-left">
        <div class="logo-img">
            <img src="Images/proton.png" alt="Proton Logo">
        </div>
        <nav class="nav-menu">
            <a href="admin_dashboard.php" class="nav-link">Dashboard</a>
            <a href="manage_cars.php" class="nav-link">Manage Cars</a>
            <a href="manage_users.php" class="nav-link">Manage Users</a>
            <a href="manage_appointments.php" class="nav-link">Manage Appointments</a>
            <a href="admin_view_reviews.php" class="nav-link">Manage Reviews</a>
        </nav>
    </div>
    <a href="logout.php" class="logout-btn">Logout</a>
</div>

<!-- CONTENT -->
<div class="main-content">
<div class="container">

<h1>Manage Test Drive Appointments</h1>

<form class="filter-form" method="GET">
    <input type="date" name="date" value="<?= htmlspecialchars($date_filter) ?>">
    <input type="time" name="time" value="<?= htmlspecialchars($time_filter) ?>">

    <!-- LOCATION DROPDOWN -->
    <select name="location">
        <option value="">All Locations</option>
        <?php foreach ($locations as $loc): ?>
            <option value="<?= htmlspecialchars($loc) ?>"
                <?= $loc === $location_filter ? 'selected' : '' ?>>
                <?= htmlspecialchars($loc) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <!-- SHOWROOM DROPDOWN -->
    <select name="showroom">
        <option value="">All Showrooms</option>
        <?php foreach ($showrooms as $show): ?>
            <option value="<?= htmlspecialchars($show) ?>"
                <?= $show === $showroom_filter ? 'selected' : '' ?>>
                <?= htmlspecialchars($show) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <button type="submit">Filter</button>
</form>

<table>
<thead>
<tr>
    <th>User</th>
    <th>Location</th>
    <th>Showroom</th>
    <th>Car Model</th>
    <th>Date</th>
    <th>Time</th>
    <th>Status</th>
    <th>Action</th>

</tr>
</thead>
<tbody>
<?php if ($result->num_rows > 0): ?>
    <?php while ($row = $result->fetch_assoc()): ?>
    <tr>
        <td><?= htmlspecialchars($row['name']) ?></td>
        <td><?= htmlspecialchars($row['location']) ?></td>
        <td><?= htmlspecialchars($row['showroom']) ?></td>
        <td><?= htmlspecialchars($row['car_model_variant']) ?></td>
        <td><?= htmlspecialchars($row['date']) ?></td>
        <td><?= htmlspecialchars($row['time']) ?></td>
        <!-- STATUS -->
    <td>
        <strong><?= htmlspecialchars($row['status']) ?></strong>
    </td>

    <!-- UPDATE STATUS -->
    <td>
        <form method="POST" style="display:inline;">
            <input type="hidden" name="appointment_id" value="<?= $row['id'] ?>">
            <select name="status">
                <option value="Pending" <?= $row['status']=='Pending'?'selected':'' ?>>Pending</option>
                <option value="Completed" <?= $row['status']=='Completed'?'selected':'' ?>>Completed</option>
                <option value="Cancelled" <?= $row['status']=='Cancelled'?'selected':'' ?>>Cancelled</option>
            </select>
            <button type="submit" name="update_status">Save</button>
        </form>
    </td>
    </tr>
    <?php endwhile; ?>
<?php else: ?>
    <tr><td colspan="6">No appointments found.</td></tr>
<?php endif; ?>
</tbody>
</table>

</div>
</div>

</body>
</html>
