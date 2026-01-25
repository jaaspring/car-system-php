<?php
session_start();

if (!isset($_SESSION['username'], $_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once __DIR__ . '/db_connection.php';

/* =========================
   FETCH CAR DETAILS
   ========================= */
$sql = "SELECT model, variant, price, engine, transmission, chassis, performance
        FROM car_details
        ORDER BY id ASC";

$result = mysqli_query($conn, $sql);

$carDetails = [];
$options = [];

while ($row = mysqli_fetch_assoc($result)) {
    $key = $row['model'] . " - " . $row['variant'];

    $carDetails[$key] = [
        "price"        => $row['price'],
        "engine"       => $row['engine'],
        "transmission" => $row['transmission'],
        "chassis"      => $row['chassis'],
        "performance"  => $row['performance'],
        "image"        => "Images/" . strtolower($row['model']) . ".png"
    ];

    $options[] = $key;
}

/* =========================
   PRESELECT FROM URL
   ========================= */
$preselect = $_GET['car'] ?? "";
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Compare Car Models</title>

<style>
/* ===== GLOBAL ===== */
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
    gap: 40px;
    align-items: center;
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

.nav-link:hover {
    opacity: 0.7;
}

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
    max-width: 1400px;
    margin: auto;
    padding: 50px;
}

h1 {
    text-align: center;
    margin-bottom: 40px;
}

.compare-wrapper {
    display: flex;
    gap: 50px;
    justify-content: center;
}

.car-box {
    background: #fff;
    border-radius: 20px;
    padding: 25px;
    width: 420px;
    text-align: center;
    box-shadow: 0 15px 35px rgba(0,0,0,0.25);
}

.car-box img {
    width: 100%;
    max-height: 220px;
    object-fit: contain;
    margin-bottom: 15px;
}

.label {
    font-size: 13px;
    margin-top: 12px;
}

.value {
    font-size: 14px;
    font-weight: bold;
}

select {
    width: 100%;
    padding: 8px;
    margin-top: 5px;
}

.btn {
    margin-top: 15px;
    padding: 10px 20px;
    border-radius: 20px;
    border: none;
    background: #000;
    color: #fff;
    font-weight: bold;
    cursor: pointer;
    text-decoration: none;
}

.bottom-buttons {
    margin-top: 40px;
    display: flex;
    justify-content: flex-end;
    gap: 15px;
    padding-right: 20px;
}

.btn.exit {
    background: #c0392b;
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

<!-- ===== CONTENT ===== -->
<div class="container">

<h1>COMPARE CAR MODELS</h1>

<div class="compare-wrapper">

    <!-- CAR 1 -->
    <div class="car-box">
        <img id="img1" src="" alt="Car 1">

        <div class="label">SELECT CAR MODEL</div>
        <select id="car1" onchange="updateCar(1)">
            <option value="">-- Select --</option>
            <?php foreach ($options as $opt): ?>
                <option value="<?= htmlspecialchars($opt) ?>"
                    <?= $opt === $preselect ? "selected" : "" ?>>
                    <?= htmlspecialchars($opt) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <div class="label">PRICE</div><div class="value" id="price1"></div>
        <div class="label">ENGINE</div><div class="value" id="engine1"></div>
        <div class="label">TRANSMISSION</div><div class="value" id="trans1"></div>
        <div class="label">CHASSIS</div><div class="value" id="chassis1"></div>
        <div class="label">PERFORMANCE</div><div class="value" id="perf1"></div>

        <button class="btn" onclick="testDrive(1)">Test Drive</button>
    </div>

    <!-- CAR 2 -->
    <div class="car-box">
        <img id="img2" src="" alt="Car 2">

        <div class="label">SELECT CAR MODEL</div>
        <select id="car2" onchange="updateCar(2)">
            <option value="">-- Select --</option>
            <?php foreach ($options as $opt): ?>
                <option value="<?= htmlspecialchars($opt) ?>">
                    <?= htmlspecialchars($opt) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <div class="label">PRICE</div><div class="value" id="price2"></div>
        <div class="label">ENGINE</div><div class="value" id="engine2"></div>
        <div class="label">TRANSMISSION</div><div class="value" id="trans2"></div>
        <div class="label">CHASSIS</div><div class="value" id="chassis2"></div>
        <div class="label">PERFORMANCE</div><div class="value" id="perf2"></div>

        <button class="btn" onclick="testDrive(2)">Test Drive</button>
    </div>

</div>

<div class="bottom-buttons">
    <a href="user_dashboard.php" class="btn">Back</a>
    <button class="btn exit" onclick="exitApp()">Exit</button>
</div>

</div>

<script>
const carData = <?= json_encode($carDetails); ?>;

function updateCar(num) {
    const select = document.getElementById("car" + num).value;
    if (!carData[select]) return;

    document.getElementById("price" + num).innerText   = carData[select].price;
    document.getElementById("engine" + num).innerText  = carData[select].engine;
    document.getElementById("trans" + num).innerText   = carData[select].transmission;
    document.getElementById("chassis" + num).innerText = carData[select].chassis;
    document.getElementById("perf" + num).innerText    = carData[select].performance;
    document.getElementById("img" + num).src           = carData[select].image;
}

function testDrive(num) {
    const car = document.getElementById("car" + num).value;
    if (!car) {
        alert("Please select a car first.");
        return;
    }
    window.location.href = "test_drive.php?car=" + encodeURIComponent(car);
}

function exitApp() {
    if (confirm("Are you sure you want to exit?")) {
        window.location.href = "logout.php";
    }
}

if ("<?= $preselect ?>" !== "") {
    updateCar(1);
}
</script>

</body>
</html>
