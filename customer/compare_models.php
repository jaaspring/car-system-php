<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit();
}
require_once '../db_connection.php';

// Fetch all models for dropdowns
$cars = [];
$res = $conn->query("SELECT * FROM car_details ORDER BY model ASC, variant ASC");
while($row = $res->fetch_assoc()) {
    $cars[] = $row;
}

// Handle Selection
$car1 = null;
$car2 = null;

if (isset($_GET['car1']) && !empty($_GET['car1'])) {
    $id1 = intval($_GET['car1']);
    foreach($cars as $c) { if ($c['id'] == $id1) { $car1 = $c; break; } }
}

if (isset($_GET['car2']) && !empty($_GET['car2'])) {
    $id2 = intval($_GET['car2']);
    foreach($cars as $c) { if ($c['id'] == $id2) { $car2 = $c; break; } }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Compare Models</title>
<link rel="stylesheet" href="../assets/css/toast.css">
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body {
    font-family: 'Century Gothic', sans-serif;
    background: radial-gradient(circle, #f4d77e, #c89a3d);
    min-height: 100vh;
    display: flex;
    flex-direction: column;
}
.header { background: #000; padding: 20px 40px; display: flex; justify-content: space-between; align-items: center; }
.header-left { display: flex; align-items: center; gap: 40px; }
.logo-img img { height: 45px; }
.nav-menu { display: flex; gap: 30px; }
.nav-link { color: #fff; text-decoration: none; font-weight: 600; }
.nav-link:hover { opacity: .7; }
.logout-btn { background: #ff4500; color: #fff; padding: 10px 25px; border-radius: 20px; text-decoration: none; font-weight: bold; }

.container {
    max-width: 1200px;
    margin: 40px auto;
    background: #fff;
    padding: 30px;
    border-radius: 25px;
    box-shadow: 0 20px 50px rgba(0,0,0,0.2);
    width: 90%;
}

h1 { text-align: center; margin-bottom: 30px; }

.selectors {
    display: flex;
    gap: 20px;
    justify-content: center;
    margin-bottom: 40px;
}

select {
    padding: 12px;
    border-radius: 10px;
    border: 1px solid #ccc;
    width: 300px;
    font-size: 16px;
}

.compare-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 40px;
}

.car-col {
    text-align: center;
}

.car-img-box {
    height: 200px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 20px;
}
.car-img-box img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
}

.spec-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}
.spec-table td {
    padding: 12px;
    border-bottom: 1px solid #eee;
}
.spec-label {
    font-weight: bold;
    color: #555;
    width: 40%;
    text-align: right;
    padding-right: 15px;
}
.spec-val {
    font-weight: bold;
    text-align: left;
    padding-left: 15px;
}

.vs-badge {
    position: absolute;
    left: 50%;
    transform: translateX(-50%);
    background: #000;
    color: #fff;
    padding: 10px 15px;
    border-radius: 50%;
    font-weight: bold;
    font-style: italic;
    top: 260px; /* Adjust based on layout */
}

@media(max-width: 800px) {
    .compare-grid { grid-template-columns: 1fr; }
    .vs-badge { display: none; }
}
</style>
</head>
<body>

<?php include('../widget/navigation.php'); ?>

<div class="container" style="position:relative;">
    <h1>Compare Models</h1>

    <div class="selectors">
        <select id="select1" onchange="updateCompare()">
            <option value="">-- Select Car 1 --</option>
            <?php foreach($cars as $c): ?>
                <option value="<?= $c['id'] ?>" <?= ($car1 && $car1['id'] == $c['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($c['model'] . ' ' . $c['variant']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <select id="select2" onchange="updateCompare()">
            <option value="">-- Select Car 2 --</option>
            <?php foreach($cars as $c): ?>
                <option value="<?= $c['id'] ?>" <?= ($car2 && $car2['id'] == $c['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($c['model'] . ' ' . $c['variant']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <!-- VS BADGE -->
    <div class="vs-badge">VS</div>

    <div class="compare-grid">
        <!-- CAR 1 -->
        <div class="car-col">
            <?php if ($car1): ?>
                <div class="car-img-box">
                    <img src="../display_image.php?id=<?= $car1['id'] ?>" onerror="this.src='../Images/proton.png'">
                </div>
                <h2><?= htmlspecialchars($car1['model']) ?></h2>
                <h4 style="color:#777;"><?= htmlspecialchars($car1['variant']) ?></h4>
                <div style="font-size:20px; color:#c0392b; font-weight:bold; margin: 10px 0;">
                    <?= htmlspecialchars($car1['price']) ?>
                </div>
                
                <table class="spec-table">
                    <tr><td class="spec-label">Engine</td><td class="spec-val"><?= htmlspecialchars($car1['engine']) ?></td></tr>
                    <tr><td class="spec-label">Trans.</td><td class="spec-val"><?= htmlspecialchars($car1['transmission']) ?></td></tr>
                    <tr><td class="spec-label">Chassis</td><td class="spec-val"><?= htmlspecialchars($car1['chassis']) ?></td></tr>
                    <tr><td class="spec-label">Perf.</td><td class="spec-val"><?= htmlspecialchars($car1['performance']) ?></td></tr>
                </table>
            <?php else: ?>
                <div style="padding: 50px; color:#aaa;">Select a car to view specs</div>
            <?php endif; ?>
        </div>

        <!-- CAR 2 -->
        <div class="car-col">
            <?php if ($car2): ?>
                <div class="car-img-box">
                    <img src="../display_image.php?id=<?= $car2['id'] ?>" onerror="this.src='../Images/proton.png'">
                </div>
                <h2><?= htmlspecialchars($car2['model']) ?></h2>
                <h4 style="color:#777;"><?= htmlspecialchars($car2['variant']) ?></h4>
                <div style="font-size:20px; color:#c0392b; font-weight:bold; margin: 10px 0;">
                    <?= htmlspecialchars($car2['price']) ?>
                </div>

                <table class="spec-table">
                    <tr><td class="spec-label">Engine</td><td class="spec-val"><?= htmlspecialchars($car2['engine']) ?></td></tr>
                    <tr><td class="spec-label">Trans.</td><td class="spec-val"><?= htmlspecialchars($car2['transmission']) ?></td></tr>
                    <tr><td class="spec-label">Chassis</td><td class="spec-val"><?= htmlspecialchars($car2['chassis']) ?></td></tr>
                    <tr><td class="spec-label">Perf.</td><td class="spec-val"><?= htmlspecialchars($car2['performance']) ?></td></tr>
                </table>
            <?php else: ?>
                <div style="padding: 50px; color:#aaa;">Select a car to view specs</div>
            <?php endif; ?>
        </div>
    </div>
    
    <div style="text-align:center; margin-top:30px;">
        <a href="models.php" style="background:#000; color:#fff; padding:10px 20px; border-radius:15px; text-decoration:none;">Back to Models</a>
    </div>
</div>

<script>
function updateCompare() {
    const c1 = document.getElementById('select1').value;
    const c2 = document.getElementById('select2').value;
    window.location.href = '?car1=' + c1 + '&car2=' + c2;
}
</script>
<script src="../assets/js/toast.js"></script>

</body>
</html>
