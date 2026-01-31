<?php
session_start();

// Check if user is logged in and has the correct role
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

include('../db_connection.php');

// Initialize filter variables
$date_filter     = isset($_GET['date']) ? $_GET['date'] : '';
$time_filter     = isset($_GET['time']) ? $_GET['time'] : '';
$location_filter = isset($_GET['location']) ? $_GET['location'] : '';
$showroom_filter = isset($_GET['showroom']) ? $_GET['showroom'] : '';
$status_filter   = isset($_GET['status_filter']) ? $_GET['status_filter'] : '';

/* ===== DELETE APPOINTMENT ===== */
if (isset($_GET['delete'])) {
    $del_id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM test_drive WHERE id = ?");
    $stmt->bind_param("i", $del_id);
    $stmt->execute();
    $stmt->close();
    header("Location: manage_appointments.php?toast_msg=" . urlencode("Successfully Deleted") . "&toast_type=success");
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
    header("Location: manage_appointments.php?toast_msg=" . urlencode("Successfully Update") . "&toast_type=success");
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
    $sql .= " AND td.time LIKE '$time_filter%'";
}
if ($location_filter) {
    $sql .= " AND td.location = '$location_filter'";
}
if ($showroom_filter) {
    $sql .= " AND td.showroom = '$showroom_filter'";
}
if ($status_filter) {
    $sql .= " AND td.status = '$status_filter'";
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

/* CLEAR BTN */
.clear-btn {
    background: #6c757d;
    color: #fff;
    padding: 10px 20px;
    border-radius: 20px;
    text-decoration: none;
    font-weight: bold;
    font-size: 14px;
    display: inline-flex;
    align-items: center;
    transition: opacity 0.3s;
}
.clear-btn:hover { opacity: 0.8; }

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

/* FLEX ALIGNMENT FOR TABLE CELLS */
.action-cell {
    display: flex;
    gap: 10px;
    align-items: center;
    justify-content: center;
}

.details-cell {
    display: flex;
    gap: 8px;
    align-items: center;
    justify-content: center;
}

/* SUCCESS ALERT */
.alert {
    padding: 12px;
    margin-bottom: 25px;
    border-radius: 6px;
    font-weight: bold;
    text-align: center;
}
.alert.success { background:#2ecc71; color:#fff; }
.alert.error { background:#e74c3c; color:#fff; }
</style>
</head>

<body>

<?php include('../navigation.php'); ?>

<!-- CONTENT -->
<div class="main-content">
<div class="container">

<h1>Manage Test Drive Appointments</h1>

<?php if (isset($_GET['toast_msg'])): ?>
    <div class="alert <?= $_GET['toast_type'] === 'success' ? 'success' : 'error' ?>">
        <?= htmlspecialchars($_GET['toast_msg']) ?>
    </div>
<?php endif; ?>

<form class="filter-form" method="GET">
    <input type="date" name="date" value="<?= htmlspecialchars($date_filter) ?>">
    
    <select name="time">
        <option value="">All Times</option>
        <?php 
        $slots = ['09:00', '10:00', '11:00', '12:00', '14:00', '15:00', '16:00'];
        foreach ($slots as $slot): 
        ?>
            <option value="<?= $slot ?>" <?= $time_filter === $slot ? 'selected' : '' ?>>
                <?= $slot ?>
            </option>
        <?php endforeach; ?>
    </select>

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

    <!-- STATUS DROPDOWN -->
    <select name="status_filter">
        <option value="">All Status</option>
        <option value="Pending" <?= $status_filter === 'Pending' ? 'selected' : '' ?>>Pending</option>
        <option value="Completed" <?= $status_filter === 'Completed' ? 'selected' : '' ?>>Completed</option>
        <option value="Cancelled" <?= $status_filter === 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
    </select>

    <button type="submit">Filter</button>
    <?php if ($date_filter || $time_filter || $location_filter || $showroom_filter || $status_filter): ?>
        <a href="manage_appointments.php" class="clear-btn">Clear</a>
    <?php endif; ?>
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
        <form method="POST" class="action-cell" id="updateForm_<?= $row['id'] ?>">
            <input type="hidden" name="appointment_id" value="<?= $row['id'] ?>">
            <select name="status" style="padding: 6px; border-radius: 5px; border: 1px solid #ccc;">
                <option value="Pending" <?= $row['status']=='Pending'?'selected':'' ?>>Pending</option>
                <option value="Completed" <?= $row['status']=='Completed'?'selected':'' ?>>Completed</option>
                <option value="Cancelled" <?= $row['status']=='Cancelled'?'selected':'' ?>>Cancelled</option>
            </select>
            <button type="button" 
                    onclick="confirmUpdate(<?= $row['id'] ?>)" 
                    class="table-btn black">
                Save
            </button>
            <input type="hidden" name="update_status" value="1">
        </form>
    </td>

    <!-- VIEW & DELETE BUTTONS -->
    <td class="details-cell">
        <button type="button" 
                onclick='openModal(<?= json_encode($row) ?>)'
                class="table-btn green">
            View
        </button>
        <button type="button" 
                onclick="confirmDelete(<?= $row['id'] ?>)"
                class="table-btn"
                style="background: #c0392b;">
            Delete
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

<?php include('../confirm_modal.php'); ?>

<script>
function confirmUpdate(id) {
    showConfirm('Are you sure to update?', function() {
        document.getElementById('updateForm_' + id).submit();
    });
}

function confirmDelete(appointmentId) {
    showConfirm('Are you sure you want to delete', function() {
        window.location.href = '?delete=' + appointmentId;
    });
}
</script>

</body>
</html>
