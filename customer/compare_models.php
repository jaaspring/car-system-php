<?php
session_start();

if (!isset($_SESSION['username'], $_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

require_once '../db_connection.php';

/* =========================
   FETCH CAR DETAILS
   ========================= */
$sql = "SELECT id, model, variant, price, engine, transmission, chassis, performance
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
        // Store ID for dynamic image, and Model for fallback
        "id"           => $row['id'],
        "model"        => $row['model'], 
        "image"        => "../Images/" . strtolower($row['model']) . ".png" // Fallback default
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
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="../toast.css">
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
    flex-wrap: wrap;
}

.car-box {
    background: #fff;
    border-radius: 20px;
    padding: 25px;
    width: 420px;
    text-align: center;
    box-shadow: 0 15px 35px rgba(0,0,0,0.25);
    display: flex;
    flex-direction: column;
}

.image-container {
    height: 220px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 15px;
    overflow: hidden;
}

.car-box img {
    width: 100%;
    height: 100%;
    object-fit: contain;
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

/* Responsive */
@media (max-width: 900px) {
    .compare-wrapper {
        flex-direction: column;
        align-items: center;
    }
}
</style>
</head>

<body>

<?php include('../navigation.php'); ?>

<!-- ===== CONTENT ===== -->
<div class="container">

<h1>COMPARE CAR MODELS</h1>

<div class="compare-wrapper">

    <!-- CAR 1 -->
    <div class="car-box">
        <div class="image-container">
            <img id="img1" src="../Images/proton.png" alt="Car 1">
        </div>

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
        <div class="image-container">
             <img id="img2" src="../Images/proton.png" alt="Car 2">
        </div>

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
</div>

</div>

<script>
const carData = <?= json_encode($carDetails); ?>;

function updateCar(num) {
    const select = document.getElementById("car" + num).value;
    const imgEl = document.getElementById("img" + num);
    
    if (!carData[select]) {
        // Reset if invalid or empty
        imgEl.src = "Images/proton.png";
        document.getElementById("price" + num).innerText   = "";
        document.getElementById("engine" + num).innerText  = "";
        document.getElementById("trans" + num).innerText   = "";
        document.getElementById("chassis" + num).innerText = "";
        document.getElementById("perf" + num).innerText    = "";
        return;
    }

    const data = carData[select];

    document.getElementById("price" + num).innerText   = data.price;
    document.getElementById("engine" + num).innerText  = data.engine;
    document.getElementById("trans" + num).innerText   = data.transmission;
    document.getElementById("chassis" + num).innerText = data.chassis;
    document.getElementById("perf" + num).innerText    = data.performance;
    
    // IMAGE LOGIC: Try dynamic, fail to static model image
    imgEl.src = "../display_image.php?id=" + data.id + "&t=" + new Date().getTime();
    imgEl.onerror = function() {
        this.src = "../Images/" + data.model.toLowerCase() + ".png";
    };
}

function testDrive(num) {
    const car = document.getElementById("car" + num).value;
    if (!car) {
        showToast("Please select a car first.", "warning");
        return;
    }
    window.location.href = "test_drive.php?car=" + encodeURIComponent(car);
}

// Initial check for URL preselect
if ("<?= $preselect ?>" !== "") {
    updateCar(1);
    // Optionally updateCar(2) to something default? No requirement.
}
</script>
<script src="../toast.js"></script>
</body>
</html>
