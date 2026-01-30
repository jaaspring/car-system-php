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

/* ===== DELETE APPOINTMENT ===== */
if (isset($_GET['delete'])) {
    $del_id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM test_drive WHERE id = ?");
    $stmt->bind_param("i", $del_id);
    $stmt->execute();
    $stmt->close();
    header("Location: manage_appointments.php");
    exit();
}

/* ===== UPDATE STATUS ===== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $appointment_id = intval($_POST['appointment_id']);
    $status = $_POST['status'];

    $stmt = $conn->prepare("UPDATE test_drive SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $appointment_id);
    $stmt->execute();
    $stmt->close();
    header("Location: manage_appointments.php");
    exit();
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
$sql = "SELECT td.id, u.name, u.phone, u.email, td.location, td.showroom, td.date, td.time,
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
/* ... existing styles ... */
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
    max-width: 1400px; /* Increased width to fit more columns */
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

/* TABLE BUTTONS */
.table-btn {
    padding: 8px 18px;
    border-radius: 20px;
    border: none;
    color: #fff;
    font-weight: bold;
    cursor: pointer;
    font-size: 13px;
    text-transform: capitalize;
}
.table-btn.black { background: #000; }
.table-btn.green { background: #2ecc71; } /* Matching the 'green' buttons elsewhere */

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
/* MODAL STYLES */
.modal {
    display: none; 
    position: fixed; 
    z-index: 1000; 
    left: 0;
    top: 0;
    width: 100%; 
    height: 100%; 
    background-color: rgba(0,0,0,0.6); 
    justify-content: center;
    align-items: center;
}

.modal-content {
    background-color: #fff;
    padding: 30px;
    border-radius: 15px;
    width: 400px;
    max-width: 90%;
    position: relative;
    box-shadow: 0 10px 25px rgba(0,0,0,0.3);
    animation: fadeIn 0.3s;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-20px); }
    to { opacity: 1; transform: translateY(0); }
}

.close-btn {
    position: absolute;
    top: 15px;
    right: 20px;
    font-size: 24px;
    cursor: pointer;
    font-weight: bold;
    color: #555;
}

.modal-row {
    margin-bottom: 15px;
    border-bottom: 1px solid #eee;
    padding-bottom: 8px;
}

.modal-label {
    font-size: 12px;
    color: #777;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.modal-value {
    font-size: 16px;
    font-weight: bold;
    color: #000;
    margin-top: 4px;
}
</style>
</head>

<body>

<?php include('navigation.php'); ?>

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
    <th>Phone</th>
    <th>Email</th>
    <th>Location</th>
    <th>Showroom</th>
    <th>Car Model</th>
    <th>Date</th>
    <th>Time</th>
    <th>Status</th>
    <th>Action</th>
    <th>Details</th>
</tr>
</thead>
<tbody>
<?php if ($result->num_rows > 0): ?>
    <?php while ($row = $result->fetch_assoc()): ?>
    <tr>
        <td><?= htmlspecialchars($row['name']) ?></td>
        <td><?= htmlspecialchars($row['phone']) ?></td>
        <td><?= htmlspecialchars($row['email']) ?></td>
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
            <select name="status" style="padding: 6px; border-radius: 5px; border: 1px solid #ccc; margin-right: 5px;">
                <option value="Pending" <?= $row['status']=='Pending'?'selected':'' ?>>Pending</option>
                <option value="Completed" <?= $row['status']=='Completed'?'selected':'' ?>>Completed</option>
                <option value="Cancelled" <?= $row['status']=='Cancelled'?'selected':'' ?>>Cancelled</option>
            </select>
            <button type="submit" name="update_status" class="table-btn black">Save</button>
        </form>
    </td>

    <!-- VIEW BUTTON -->
    <td>
        <button type="button" 
                onclick='openModal(<?= json_encode($row) ?>)'
                class="table-btn green">
            View
        </button>
    </td>

    </tr>
    <?php endwhile; ?>
<?php else: ?>
    <tr><td colspan="11">No appointments found.</td></tr>
<?php endif; ?>
</tbody>
</table>

</div>
</div>

<!-- INFO MODAL -->
<div id="viewModal" class="modal">
    <div class="modal-content">
        <span class="close-btn" onclick="closeModal()">&times;</span>
        <h2 style="text-align:center; margin-bottom:20px;">Appointment Details</h2>
        
        <div id="modalBody">
            <!-- Content injected by JS -->
        </div>

        <div style="text-align:center; margin-top:20px;">
            <button onclick="closeModal()" style="width:100px;">Close</button>
        </div>
    </div>
</div>

<script>
function openModal(data) {
    const body = document.getElementById('modalBody');
    body.innerHTML = `
        <div class="modal-row"><div class="modal-label">Customer Name</div><div class="modal-value">${data.name}</div></div>
        <div class="modal-row"><div class="modal-label">Phone</div><div class="modal-value">${data.phone}</div></div>
        <div class="modal-row"><div class="modal-label">Email</div><div class="modal-value">${data.email}</div></div>
        <div class="modal-row"><div class="modal-label">Car Model</div><div class="modal-value">${data.car_model_variant}</div></div>
        <div class="modal-row"><div class="modal-label">Location</div><div class="modal-value">${data.location}</div></div>
        <div class="modal-row"><div class="modal-label">Showroom</div><div class="modal-value">${data.showroom}</div></div>
        <div class="modal-row"><div class="modal-label">Date & Time</div><div class="modal-value">${data.date} at ${data.time}</div></div>
        <div class="modal-row"><div class="modal-label">Status</div><div class="modal-value" style="color:${data.status==='Pending'?'orange':(data.status==='Completed'?'green':'red')}">${data.status}</div></div>
    `;
    
    document.getElementById('viewModal').style.display = 'flex';
}

function closeModal() {
    document.getElementById('viewModal').style.display = 'none';
}

// Close if clicked outside
window.onclick = function(event) {
    const modal = document.getElementById('viewModal');
    if (event.target == modal) {
        modal.style.display = "none";
    }
}
</script>

</body>
</html>
