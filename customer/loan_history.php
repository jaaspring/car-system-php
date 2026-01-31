<?php
session_start();

if (!isset($_SESSION['username'], $_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

require_once '../db_connection.php';

$userId = $_SESSION['user_id'];

/* =========================
   HANDLE DELETE
   ========================= */
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = mysqli_prepare($conn, "DELETE FROM loan_history WHERE id = ? AND user_id = ?");
    mysqli_stmt_bind_param($stmt, "ii", $id, $userId);
    mysqli_stmt_execute($stmt);
    header("Location: loan_history.php?toast_msg=" . urlencode("Successfully Deleted") . "&toast_type=success");
    exit();
}

/* =========================
   FETCH HISTORY
   ========================= */
$stmt = mysqli_prepare($conn, "SELECT * FROM loan_history WHERE user_id = ? ORDER BY id DESC");
mysqli_stmt_bind_param($stmt, "i", $userId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Loan History</title>

<style>
body {
    font-family: 'Century Gothic', sans-serif;
    background: radial-gradient(circle, #f4d77e, #c89a3d);
    margin: 0;
}

/* ===== HEADER (DASHBOARD STYLE) ===== */
.header {
    background: #000;
    padding: 20px 40px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.header-left {
    display: flex;
    align-items: center;
    gap: 40px;
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
.logout-btn:hover { background: #e63e00; }

/* ===== BLUR WHEN MODAL OPEN ===== */
body.modal-open .container {
    filter: blur(6px);
    pointer-events: none;
}

/* ===== CONTAINER ===== */
.container {
    max-width: 1100px;
    margin: 50px auto;
    padding: 30px;
    transition: filter 0.3s ease;
}

h2 {
    text-align: center;
    margin-bottom: 30px;
}

.top-bar {
    display: flex;
    justify-content: space-between;
    margin-bottom: 15px;
}

.btn {
    padding: 8px 18px;
    border-radius: 20px;
    border: none;
    background: #000;
    color: #fff;
    font-weight: bold;
    cursor: pointer;
    text-decoration: none;
}

.btn.green { background: #2ecc71; }
.btn.exit  { background: #c0392b; }
.btn.cancel{ background: #7f8c8d; }

table {
    width: 100%;
    border-collapse: collapse;
    background: #fff;
}

th, td {
    padding: 10px;
    border: 1px solid #ddd;
    text-align: center;
    font-size: 13px;
}

th {
    background: #000;
    color: #fff;
}

.actions a {
    color: red;
    font-weight: bold;
    text-decoration: none;
}

.bottom-bar {
    margin-top: 25px;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}

/* ===== MODAL ===== */
.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.55);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 999;
}

.modal {
    background: #fff;
    padding: 30px;
    border-radius: 18px;
    width: 380px;
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

.modal .btn {
    flex: 1;
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

<div class="container">

    <h2>CALCULATION HISTORY</h2>

    <?php if (isset($_GET['toast_msg'])): ?>
        <div class="alert <?= $_GET['toast_type'] === 'success' ? 'success' : 'error' ?>">
            <?= htmlspecialchars($_GET['toast_msg']) ?>
        </div>
    <?php endif; ?>

    <div class="top-bar">
        <a href="loan_calculator.php" class="btn green">Add New</a>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Vehicle</th>
                <th>Variant</th>
                <th>Paint Type</th>
                <th>Terms</th>
                <th>Down Payment</th>
                <th>Interest</th>
                <th>Monthly</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php if (mysqli_num_rows($result) === 0): ?>
            <tr><td colspan="9">No history found.</td></tr>
        <?php else: ?>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['vehicle']) ?></td>
                <td><?= htmlspecialchars($row['variant']) ?></td>
                <td><?= htmlspecialchars($row['paint_type']) ?></td>
                <td><?= $row['terms'] ?></td>
                <td>RM <?= number_format($row['down_payment'], 2) ?></td>
                <td><?= number_format($row['interest_rate'], 2) ?>%</td>
                <td>RM <?= number_format($row['monthly_installment'], 2) ?></td>
                <td class="actions">
                    <a href="#"
                       onclick="event.preventDefault(); confirmDelete(<?= $row['id'] ?>)">Delete</a>
                </td>
            </tr>
            <?php endwhile; ?>
        <?php endif; ?>
        </tbody>
    </table>

    <div class="bottom-bar">
        <button class="btn" onclick="openBackModal()">Back</button>
    </div>

</div>

<!-- ===== BACK MODAL ===== -->
<div class="modal-overlay" id="backModal">
    <div class="modal">
        <h3>Where would you like to go?</h3>
        <p>Please choose your next destination.</p>

        <div class="modal-buttons">
            <button class="btn" onclick="goLoanCalculator()">Loan Calculator</button>
            <button class="btn green" onclick="goDashboard()">Homepage</button>
            <button class="btn cancel" onclick="closeBackModal()">Cancel</button>
        </div>
    </div>
</div>

<script>
function openBackModal() {
    document.getElementById("backModal").style.display = "flex";
    document.body.classList.add("modal-open");
}

function closeBackModal() {
    document.getElementById("backModal").style.display = "none";
    document.body.classList.remove("modal-open");
}

function goLoanCalculator() {
    window.location.href = "loan_calculator.php";
}

function goDashboard() {
    window.location.href = "user_dashboard.php";
}

function confirmDelete(recordId) {
    showConfirm('Are you sure you want to delete', function() {
        window.location.href = '?delete=' + recordId;
    });
}
</script>

<?php include('../confirm_modal.php'); ?>

</body>
</html>
