<?php
session_start();

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

require_once '../db_connection.php';

// Capture filter values
$rating_filter = $_GET['rating'] ?? '';
$car_filter = $_GET['car_model'] ?? '';
$location_filter = $_GET['location'] ?? '';
$showroom_filter = $_GET['showroom'] ?? '';

// Fetch all car models for the filter dropdown
$car_models_res = $conn->query("SELECT DISTINCT car_model_variant FROM test_drive ORDER BY car_model_variant ASC");
$car_models = [];
while ($cm = $car_models_res->fetch_assoc()) {
    $car_models[] = $cm['car_model_variant'];
}

// Fetch Locations and Showrooms
$locations = [];
$showrooms = [];
$locRes = $conn->query("SELECT DISTINCT location FROM test_drive WHERE location IS NOT NULL");
while ($l = $locRes->fetch_assoc()) { $locations[] = $l['location']; }
$showRes = $conn->query("SELECT DISTINCT showroom FROM test_drive WHERE showroom IS NOT NULL");
while ($s = $showRes->fetch_assoc()) { $showrooms[] = $s['showroom']; }

// Build Query
$sql = "SELECT r.rating, r.comment, r.created_at,
               u.name AS user_name,
               td.car_model_variant, td.date, td.location, td.showroom
        FROM test_drive_reviews r
        JOIN users u ON r.user_id = u.id
        JOIN test_drive td ON r.test_drive_id = td.id
        WHERE 1=1";

if ($rating_filter !== '') {
    $sql .= " AND r.rating = " . intval($rating_filter);
}
if ($car_filter !== '') {
    $sql .= " AND td.car_model_variant = '" . $conn->real_escape_string($car_filter) . "'";
}
if ($location_filter !== '') {
    $sql .= " AND td.location = '" . $conn->real_escape_string($location_filter) . "'";
}
if ($showroom_filter !== '') {
    $sql .= " AND td.showroom = '" . $conn->real_escape_string($showroom_filter) . "'";
}

$sql .= " ORDER BY r.created_at DESC";
$result = $conn->query($sql);
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
    max-width: 1300px;
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

/* ===== FILTER FORM ===== */
.filter-form {
    display: flex;
    gap: 15px;
    justify-content: center;
    margin-bottom: 25px;
    flex-wrap: wrap;
}

.filter-form select {
    padding: 10px;
    border-radius: 8px;
    border: 1px solid #ccc;
    font-family: inherit;
    min-width: 150px;
}

.filter-form button {
    padding: 10px 25px;
    border-radius: 20px;
    border: none;
    background: #000;
    color: #fff;
    font-weight: bold;
    cursor: pointer;
    transition: opacity 0.3s;
}

.filter-form button:hover { opacity: 0.8; }
.filter-form .clear-btn { background: #6c757d; text-decoration: none; display: flex; align-items: center; padding: 0 15px; border-radius: 20px; color: #fff; font-size: 14px; font-weight: bold; }
</style>
</head>

<body>

<?php include('../navigation.php'); ?>

<!-- ===== CONTENT ===== -->
<div class="container">
<h1>Customer Test Drive Reviews</h1>

<form class="filter-form" method="GET">
    <select name="rating">
        <option value="">All Ratings</option>
        <?php for ($i=5; $i>=1; $i--): ?>
            <option value="<?= $i ?>" <?= $rating_filter == $i ? 'selected' : '' ?>>
                <?= $i ?> Stars
            </option>
        <?php endfor; ?>
    </select>

    <select name="car_model">
        <option value="">All Cars</option>
        <?php foreach ($car_models as $m): ?>
            <option value="<?= htmlspecialchars($m) ?>" <?= $car_filter === $m ? 'selected' : '' ?>>
                <?= htmlspecialchars($m) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <select name="location">
        <option value="">All Locations</option>
        <?php foreach ($locations as $loc): ?>
            <option value="<?= htmlspecialchars($loc) ?>" <?= $location_filter === $loc ? 'selected' : '' ?>>
                <?= htmlspecialchars($loc) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <select name="showroom">
        <option value="">All Showrooms</option>
        <?php foreach ($showrooms as $show): ?>
            <option value="<?= htmlspecialchars($show) ?>" <?= $showroom_filter === $show ? 'selected' : '' ?>>
                <?= htmlspecialchars($show) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <button type="submit">Filter</button>
    <?php if ($rating_filter !== '' || $car_filter !== '' || $location_filter !== '' || $showroom_filter !== ''): ?>
        <a href="admin_view_reviews.php" class="clear-btn">Clear</a>
    <?php endif; ?>
</form>

<table>
<thead>
<tr>
    <th>User</th>
    <th>Car Model</th>
    <th>Location</th>
    <th>Showroom</th>
    <th>Date</th>
    <th>Rating</th>
    <th>Comment</th>
    <th>Submitted</th>
</tr>
</thead>
<tbody>

<?php if ($result->num_rows === 0): ?>
<tr><td colspan="8">No reviews yet.</td></tr>
<?php else: ?>
<?php while ($r = $result->fetch_assoc()): ?>
<tr>
    <td><?= htmlspecialchars($r['user_name']) ?></td>
    <td><?= htmlspecialchars($r['car_model_variant']) ?></td>
    <td><?= htmlspecialchars($r['location']) ?></td>
    <td><?= htmlspecialchars($r['showroom']) ?></td>
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
