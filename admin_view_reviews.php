<?php
session_start();

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

require_once __DIR__ . '/db_connection.php';

$result = $conn->query(
    "SELECT r.rating, r.comment, r.created_at,
            u.name AS user_name,
            td.car_model_variant, td.date
     FROM test_drive_reviews r
     JOIN users u ON r.user_id = u.id
     JOIN test_drive td ON r.test_drive_id = td.id
     ORDER BY r.created_at DESC"
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>View Reviews</title>

<style>
body {
    font-family: 'Century Gothic', sans-serif;
    background: radial-gradient(circle, #f4d77e, #c89a3d);
    margin: 0;
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
    font-weight: bold;
    text-decoration: none;
}

.logout-btn:hover {
    background-color: #e63e00;
}

/* ===== CONTENT ===== */
.container {
    max-width: 1100px;
    margin: 60px auto;
    background: #fff;
    padding: 30px;
    border-radius: 25px;
}

h1 {
    text-align: center;
    margin-bottom: 30px;
}

table {
    width: 100%;
    border-collapse: collapse;
}

th, td {
    padding: 12px;
    border: 1px solid #ddd;
    text-align: center;
}

th {
    background: #000;
    color: #fff;
}

.rating {
    color: gold;
    font-weight: bold;
}
</style>
</head>

<body>

<!-- ===== HEADER ===== -->
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

<!-- ===== CONTENT ===== -->
<div class="container">
<h1>Customer Test Drive Reviews</h1>

<table>
<thead>
<tr>
    <th>User</th>
    <th>Car Model</th>
    <th>Date</th>
    <th>Rating</th>
    <th>Comment</th>
    <th>Submitted</th>
</tr>
</thead>
<tbody>

<?php if ($result->num_rows === 0): ?>
<tr><td colspan="6">No reviews yet.</td></tr>
<?php else: ?>
<?php while ($r = $result->fetch_assoc()): ?>
<tr>
    <td><?= htmlspecialchars($r['user_name']) ?></td>
    <td><?= htmlspecialchars($r['car_model_variant']) ?></td>
    <td><?= htmlspecialchars($r['date']) ?></td>
    <td class="rating"><?= str_repeat("★", $r['rating']) ?></td>
    <td><?= htmlspecialchars($r['comment']) ?></td>
    <td><?= $r['created_at'] ?></td>
</tr>
<?php endwhile; ?>
<?php endif; ?>

</tbody>
</table>
</div>

</body>
</html>
