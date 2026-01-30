<?php
session_start();

if (!isset($_SESSION['username'], $_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once __DIR__ . '/db_connection.php';

$userId = $_SESSION['user_id'];

// Handle Cancellation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_id'])) {
    $cancelId = intval($_POST['cancel_id']);
    
    // Only allow cancelling if status is Pending (security check)
    $upd = $conn->prepare("UPDATE test_drive SET status = 'Cancelled' WHERE id = ? AND user_id = ? AND status = 'Pending'");
    $upd->bind_param("ii", $cancelId, $userId);
    
    if ($upd->execute()) {
        header("Location: test_drive_history.php?toast_msg=" . urlencode("Booking cancelled successfully") . "&toast_type=success");
        exit();
    }
}

$stmt = $conn->prepare(
    "SELECT id, car_model_variant, location, showroom, date, time, status
     FROM test_drive
     WHERE user_id = ?
     ORDER BY date DESC, time DESC"
);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>My Test Drive History</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="toast.css">

<style>
* { box-sizing: border-box; margin: 0; padding: 0; }

body {
    font-family: 'Century Gothic', sans-serif;
    background: radial-gradient(
        ellipse at center,
        #f4d77e 0%,
        #e6c770 25%,
        #d4a747 50%,
        #c89a3d 75%,
        #9d7730 100%
    );
    min-height: 100vh;
}

/* ===== BLUR WHEN MODAL OPEN ===== */
body.modal-open .container {
    filter: blur(6px);
    pointer-events: none;
}

/* ===== HEADER ===== */
.header {
    background: #000;
    padding: 20px 40px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.header-left {
    display: flex;
    gap: 40px;
    align-items: center;
}
.logo-img img { height: 45px; }
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

/* ===== CONTENT ===== */
.container {
    max-width: 1100px;
    margin: 50px auto;
    padding: 20px;
    transition: filter .3s;
}
.page-title {
    text-align: center;
    font-size: 26px;
    margin-bottom: 40px;
}

/* ===== TICKET (UNCHANGED) ===== */
.ticket {
    background: rgba(255,255,255,0.95);
    border-radius: 20px;
    padding: 25px 30px;
    margin-bottom: 25px;
    box-shadow: 0 15px 35px rgba(0,0,0,.25);
    position: relative;
}
.ticket::before {
    content: "";
    position: absolute;
    left: 0;
    top: 0;
    width: 6px;
    height: 100%;
    background: #000;
}
.ticket-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.car-name { font-size: 18px; font-weight: bold; }

.status {
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: bold;
    color: #fff;
}
.status.Pending { background: #f39c12; }
.status.Completed { background: #27ae60; }
.status.Cancelled { background: #c0392b; }

.details {
    display: grid;
    grid-template-columns: repeat(2,1fr);
    gap: 12px;
    margin-top: 15px;
    font-size: 14px;
}
.detail span { font-weight: bold; }

.ticket-footer {
    margin-top: 20px;
    display: flex;
    justify-content: flex-end;
}

/* ===== BUTTONS ===== */
.btn {
    padding: 10px 22px;
    border-radius: 20px;
    background: #000;
    color: #fff;
    text-decoration: none;
    font-size: 13px;
    font-weight: bold;
    border: none;
    cursor: pointer;
}

.btn.green { background: #2ecc71; }
.btn.grey  { background: #aaa; color: #000; }

.bottom-bar {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    margin-top: 30px;
}

/* ===== MODAL (MATCH LOAN HISTORY) ===== */
.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,.55);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 999;
}

.modal {
    background: #fff;
    padding: 30px;
    border-radius: 18px;
    width: 360px;
    text-align: center;
}

.modal h3 {
    margin-bottom: 15px;
}

.modal p {
    font-size: 14px;
    margin-bottom: 25px;
}

.modal-buttons {
    display: flex;
    gap: 12px;
}

.modal-buttons .btn {
    flex: 1;
}

@media(max-width:768px){
    .details { grid-template-columns: 1fr; }
}
</style>
</head>

<body>

<?php include('navigation.php'); ?>

<div class="container">
<h1 class="page-title">My Test Drive Bookings</h1>

<?php if ($result->num_rows === 0): ?>
    <div style="text-align:center">No test drive bookings yet.</div>
<?php else: ?>
<?php while ($row = $result->fetch_assoc()): ?>
<div class="ticket">
    <div class="ticket-header">
        <div class="car-name"><?= htmlspecialchars($row['car_model_variant']) ?></div>
        <div class="status <?= $row['status'] ?>"><?= $row['status'] ?></div>
    </div>

    <div class="details">
        <div class="detail"><span>Location:</span> <?= htmlspecialchars($row['location']) ?></div>
        <div class="detail"><span>Showroom:</span> <?= htmlspecialchars($row['showroom']) ?></div>
        <div class="detail"><span>Date:</span> <?= $row['date'] ?></div>
        <div class="detail"><span>Time:</span> <?= substr($row['time'],0,5) ?></div>
    </div>

    <?php if ($row['status']==='Completed'): ?>
    <div class="ticket-footer">
        <a class="btn"
           href="rating.php?test_drive_id=<?= $row['id'] ?>&car=<?= urlencode($row['car_model_variant']) ?>">
           Leave Rating
        </a>
    </div>
    <?php elseif ($row['status']==='Pending'): ?>
    <div class="ticket-footer">
        <form method="POST" onsubmit="return confirm('Are you sure you want to cancel this booking?');">
            <input type="hidden" name="cancel_id" value="<?= $row['id'] ?>">
            <button class="btn" style="background:#c0392b">Cancel Booking</button>
        </form>
    </div>
    <?php endif; ?>
</div>
<?php endwhile; endif; ?>

<div class="bottom-bar">
    <button class="btn" onclick="openModal()">Back</button>
</div>
</div>

<!-- MODAL -->
<div class="modal-overlay" id="backModal">
    <div class="modal">
        <h3>Where would you like to go?</h3>
        <p>Please choose your next destination.</p>

        <div class="modal-buttons">
            <button class="btn" onclick="goTestDrive()">Book Test Drive</button>
            <button class="btn green" onclick="goHome()">Homepage</button>
            <button class="btn grey" onclick="closeModal()">Cancel</button>
        </div>
    </div>
</div>

<script>
function openModal() {
    document.getElementById("backModal").style.display = "flex";
    document.body.classList.add("modal-open");
}
function closeModal() {
    document.getElementById("backModal").style.display = "none";
    document.body.classList.remove("modal-open");
}
function goHome() {
    window.location.href = "user_dashboard.php";
}
function goTestDrive() {
    window.location.href = "test_drive.php";
}
</script>
<script src="toast.js"></script>

</body>
</html>
