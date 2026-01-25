<?php
session_start();

// Auth check
if (!isset($_SESSION['username'], $_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once __DIR__ . '/db_connection.php';

$user_id  = $_SESSION['user_id'];
$username = $_SESSION['username'];

$error_message = '';
$success_message = '';

/* =========================
   FETCH USER INFO (DISPLAY)
   ========================= */
$name = $phone = $email = '';

$user_stmt = $conn->prepare(
    "SELECT name, phone, email FROM users WHERE id = ?"
);
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();

if ($user_result->num_rows > 0) {
    $u = $user_result->fetch_assoc();
    $name  = $u['name'];
    $phone = $u['phone'];
    $email = $u['email'];
}
$user_stmt->close();

/* =========================
   FETCH CAR MODELS
   ========================= */
$car_models = [];
$res = $conn->query(
    "SELECT model, variant FROM car_details ORDER BY model ASC"
);
while ($row = $res->fetch_assoc()) {
    $car_models[] = $row['model'] . " - " . $row['variant'];
}

/* =========================
   HANDLE BOOKING
   ========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $car_model = trim($_POST['car_model'] ?? '');
    $location  = trim($_POST['location'] ?? '');
    $showroom  = trim($_POST['showroom'] ?? '');
    $date      = $_POST['date'] ?? '';
    $time      = $_POST['time'] ?? '';

    if (
        empty($car_model) || empty($location) ||
        empty($showroom) || empty($date) || empty($time)
    ) {
        $error_message = "All fields are required.";
    } else {

        if (strlen($time) === 5) {
            $time .= ":00";
        }

        /* CHECK TIME AVAILABILITY */
        $check_stmt = $conn->prepare(
            "SELECT id FROM test_drive
             WHERE date = ? AND time = ? AND showroom = ?"
        );
        $check_stmt->bind_param("sss", $date, $time, $showroom);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            $error_message =
                "Sorry, this time slot is already booked. Please choose another time.";
        } else {

            $insert_stmt = $conn->prepare(
                "INSERT INTO test_drive
                 (user_id, car_model_variant, location, showroom, date, time)
                 VALUES (?, ?, ?, ?, ?, ?)"
            );

            $insert_stmt->bind_param(
                "isssss",
                $user_id,
                $car_model,
                $location,
                $showroom,
                $date,
                $time
            );

            if ($insert_stmt->execute()) {
                $success_message =
                    "Your test drive has been booked successfully!";
            } else {
                $error_message = "Booking failed. Please try again.";
            }

            $insert_stmt->close();
        }

        $check_stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Book Test Drive</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>
* { box-sizing: border-box; margin: 0; padding: 0; }

body {
    font-family: 'Century Gothic', sans-serif;
    background: radial-gradient(circle, #f4d77e, #c89a3d);
    min-height: 100vh;
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

.nav-link:hover { opacity: 0.7; }

.logout-btn {
    background: #ff4500;
    color: #fff;
    padding: 10px 25px;
    border-radius: 20px;
    text-decoration: none;
    font-weight: bold;
}

.logout-btn:hover {
    background: #e63e00;
}

/* ===== FORM CARD ===== */
.container {
    max-width: 520px;
    background: #fff;
    margin: 60px auto;
    padding: 35px;
    border-radius: 25px;
}

h1 {
    text-align: center;
    margin-bottom: 20px;
}

.alert {
    padding: 12px;
    border-radius: 6px;
    margin-bottom: 15px;
    font-weight: bold;
}
.alert.error { background: #e74c3c; color: #fff; }
.alert.success { background: #2ecc71; color: #fff; }

label {
    font-weight: bold;
    margin-top: 15px;
    display:block;
}

input, select {
    width: 100%;
    padding: 10px;
    margin-top: 6px;
}

button {
    margin-top: 25px;
    width: 100%;
    padding: 14px;
    border: none;
    border-radius: 25px;
    background: #000;
    color: #fff;
    font-weight: bold;
    cursor: pointer;
}

.secondary-btn {
    margin-top: 12px;
    background: transparent;
    color: #000;
    border: 2px solid #000;
}

.secondary-btn:hover {
    background: #000;
    color: #fff;
}
</style>
</head>

<body>

<!-- HEADER -->
<div class="header">
    <div class="header-left">
        <div class="logo-img">
            <img src="Images/proton.png" alt="Proton">
        </div>
        <nav class="nav-menu">
            <a href="user_dashboard.php" class="nav-link">Home Page</a>
            <a href="models.php" class="nav-link">Models</a>
            <a href="loan_calculator.php" class="nav-link">Loan Calculator</a>
            <a href="loan_history.php" class="nav-link">Loan History</a>
            <a href="compare_models.php" class="nav-link">Compare Models</a>
            <a href="test_drive.php" class="nav-link">Book Test Drive</a>
            <a href="rating.php" class="nav-link">Rating</a>
        </nav>
    </div>
    <a href="logout.php" class="logout-btn">Logout</a>
</div>

<div class="container">
<h1>Book Test Drive</h1>

<?php if ($error_message): ?>
    <div class="alert error"><?= htmlspecialchars($error_message) ?></div>
<?php endif; ?>

<?php if ($success_message): ?>
    <div class="alert success"><?= htmlspecialchars($success_message) ?></div>
<?php endif; ?>

<form method="POST">

<label>Car Model</label>
<select name="car_model" required>
    <option value="">Select</option>
    <?php foreach ($car_models as $m): ?>
        <option value="<?= htmlspecialchars($m) ?>">
            <?= htmlspecialchars($m) ?>
        </option>
    <?php endforeach; ?>
</select>

<label>Name</label>
<input type="text" value="<?= htmlspecialchars($name) ?>" disabled>

<label>Phone</label>
<input type="text" value="<?= htmlspecialchars($phone) ?>" disabled>

<label>Email</label>
<input type="email" value="<?= htmlspecialchars($email) ?>" disabled>

<label>Location</label>
<select name="location" required>
    <option value="">Select</option>
    <option>Kuala Lumpur</option>
    <option>Penang</option>
    <option>Johor Bahru</option>
</select>

<label>Showroom</label>
<select name="showroom" required>
    <option value="">Select</option>
    <option>Showroom 1</option>
    <option>Showroom 2</option>
</select>

<label>Date</label>
<input type="date" name="date" required>

<label>Time</label>
<select name="time" required>
    <option value="">Select</option>
    <option>09:00</option>
    <option>10:00</option>
    <option>11:00</option>
</select>

<button type="submit">BOOK TEST DRIVE</button>

<a href="test_drive_history.php">
    <button type="button" class="secondary-btn">
        View Test Drive History
    </button>
</a>

</form>
</div>

</body>
</html>
