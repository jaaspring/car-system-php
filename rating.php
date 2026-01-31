<?php
session_start();

/* =========================
   AUTH CHECK
   ========================= */
if (!isset($_SESSION['username'], $_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once __DIR__ . '/db_connection.php';

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$name = $_SESSION['name'] ?? $username;

$error_message = '';
$success_message = '';

/* =========================
   FETCH COMPLETED TEST DRIVES ONLY
   ========================= */
$completedDrives = [];

$stmt = $conn->prepare(
    "SELECT id, car_model_variant, date
     FROM test_drive
     WHERE user_id = ?
       AND status = 'Completed'
       AND id NOT IN (
            SELECT test_drive_id FROM test_drive_reviews
       )
     ORDER BY date DESC"
);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();

while ($row = $res->fetch_assoc()) {
    $completedDrives[] = $row;
}
$stmt->close();

/* =========================
   HANDLE SUBMISSION
   ========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $test_drive_id = intval($_POST['test_drive_id'] ?? 0);
    $rating        = intval($_POST['rating'] ?? 0);
    $comment       = trim($_POST['comment'] ?? '');

    if ($test_drive_id <= 0) {
        $error_message = "Please select a completed test drive.";
    } elseif ($rating < 1 || $rating > 5) {
        $error_message = "Please select a star rating.";
    } elseif (empty($comment)) {
        $error_message = "Please write a comment.";
    } else {

        $stmt = $conn->prepare(
            "INSERT INTO test_drive_reviews (test_drive_id, user_id, rating, comment)
             VALUES (?, ?, ?, ?)"
        );
        $stmt->bind_param("iiis", $test_drive_id, $user_id, $rating, $comment);

        if ($stmt->execute()) {
            header("Location: test_drive_history.php?toast_msg=" . urlencode("Feedback submitted! Thank you.") . "&toast_type=success");
            exit();
        } else {
            $error_message = "Submission failed. Please try again.";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Test Drive Feedback</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>
/* ⚠️ UNCHANGED CSS (exactly as you requested) */
* { box-sizing: border-box; margin: 0; padding: 0; }

body {
    font-family: 'Century Gothic', sans-serif;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
}

/* ===== DASHBOARD HEADER ===== */
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
    gap: 50px;
}

.logo-img {
    width: 180px;
    height: 50px;
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
    font-weight: 600;
}

.nav-link:hover { opacity: 0.7; }

.logout-btn {
    background: #ff4500;
    color: #fff;
    padding: 10px 25px;
    border-radius: 20px;
    font-weight: bold;
    text-decoration: none;
}

.logout-btn:hover { background: #e63e00; }

/* ===== MAIN ===== */
.main-content {
    flex: 1;
    background: radial-gradient(
        ellipse at center,
        #f4d77e 0%, #e6c770 25%, #d4a747 50%,
        #c89a3d 75%, #9d7730 100%
    );
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 60px 20px;
}

/* ===== CARD ===== */
.card {
    background: rgba(255,255,255,0.96);
    width: 100%;
    max-width: 520px;
    padding: 35px;
    border-radius: 25px;
    box-shadow: 0 20px 45px rgba(0,0,0,0.35);
    text-align: center;
}

.card h1 {
    margin-bottom: 25px;
}

.alert {
    padding: 12px;
    border-radius: 10px;
    font-weight: bold;
    margin-bottom: 20px;
}

.alert.error { background: #e74c3c; color: #fff; }
.alert.success { background: #2ecc71; color: #fff; }

.form-group {
    margin-bottom: 20px;
    text-align: left;
}

label {
    font-weight: bold;
    font-size: 14px;
}

select, textarea {
    width: 100%;
    padding: 10px;
    margin-top: 6px;
    border-radius: 6px;
    border: 1px solid #ccc;
}

textarea { resize: none; height: 100px; }

.rating {
    display: flex;
    justify-content: center;
    gap: 10px;
    margin-top: 10px;
}

.star {
    width: 38px;
    height: 38px;
    background: #ccc;
    clip-path: polygon(
        50% 0%, 61% 35%, 98% 35%, 68% 57%,
        79% 91%, 50% 70%, 21% 91%,
        32% 57%, 2% 35%, 39% 35%
    );
    cursor: pointer;
}

.star.selected { background: gold; }

.submit-btn {
    width: 100%;
    padding: 14px;
    border: none;
    border-radius: 25px;
    background: #000;
    color: #fff;
    font-weight: bold;
    cursor: pointer;
}
</style>
</head>

<body>

<?php include('navigation.php'); ?>

<div class="main-content">
<div class="card">

<h1>Test Drive Feedback</h1>

<?php if ($error_message): ?>
<div class="alert error"><?= htmlspecialchars($error_message) ?></div>
<?php endif; ?>

<?php if ($success_message): ?>
<div class="alert success"><?= htmlspecialchars($success_message) ?></div>
<?php endif; ?>

<?php if (empty($completedDrives)): ?>
<div class="alert error">You have no completed test drives to review.</div>
<?php else: ?>

<form method="POST">

<div class="form-group">
<label>Select Completed Test Drive</label>
<select name="test_drive_id" required>
<option value="">-- Select --</option>
<?php foreach ($completedDrives as $td): ?>
<option value="<?= $td['id'] ?>">
<?= htmlspecialchars($td['car_model_variant']) ?> (<?= $td['date'] ?>)
</option>
<?php endforeach; ?>
</select>
</div>

<div class="form-group">
<label>Rate Your Experience</label>
<div class="rating">
<?php for ($i=1;$i<=5;$i++): ?>
<div class="star" data-value="<?= $i ?>"></div>
<?php endfor; ?>
</div>
<input type="hidden" name="rating" id="rating">
</div>

<div class="form-group">
<label>Your Comment</label>
<textarea name="comment" required></textarea>
</div>

<button class="submit-btn" style="margin-bottom: 20px;">Submit Feedback</button>

<div style="text-align: center;">
    <a href="test_drive_history.php" style="color: #666; font-size: 14px; text-decoration: none; font-weight: bold;">
        &larr; Back to Booking History
    </a>
</div>

</form>
<?php endif; ?>

</div>
</div>

<script>
const stars = document.querySelectorAll('.star');
const ratingInput = document.getElementById('rating');

stars.forEach(star=>{
star.addEventListener('click',()=>{
ratingInput.value = star.dataset.value;
stars.forEach(s=>s.classList.toggle('selected',s.dataset.value<=ratingInput.value));
});
});
</script>

</body>
</html>
