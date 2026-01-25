<?php
session_start();

if (!isset($_SESSION['username'], $_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once __DIR__ . '/db_connection.php';

/* =========================
   SAVE LOAN HISTORY
   ========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_loan'])) {

    $stmt = mysqli_prepare(
        $conn,
        "INSERT INTO loan_history 
        (user_id, vehicle, variant, paint_type, terms, down_payment, interest_rate, monthly_installment)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "isssiddi",
        $_SESSION['user_id'],
        $_POST['vehicle'],
        $_POST['variant'],
        $_POST['paint_type'],
        $_POST['terms'],
        $_POST['down_payment'],
        $_POST['interest_rate'],
        $_POST['monthly_installment']
    );

    mysqli_stmt_execute($stmt);

    echo "<script>alert('Loan history saved successfully!');</script>";
}

/* =========================
   LOAD PRICING DATA
   ========================= */
$sql = "SELECT model, variant, paint_type, price FROM car_details";
$result = mysqli_query($conn, $sql);

$pricing = [];

while ($row = mysqli_fetch_assoc($result)) {
    $model = $row['model'];
    $variant = $row['variant'];
    $paint = $row['paint_type'];
    $price = floatval(str_replace(['RM', ',', ' '], '', $row['price']));

    $pricing[$model][$variant][$paint] = $price;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Loan Calculator</title>

<style>
* { box-sizing: border-box; margin: 0; padding: 0; }

body {
    font-family: 'Century Gothic', sans-serif;
    background: radial-gradient(circle, #f4d77e, #c89a3d);
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

/* ===== CONTAINER ===== */
.container {
    max-width: 500px;
    background: #fff;
    margin: 60px auto;
    padding: 30px;
    border-radius: 20px;
}

h2 { text-align: center; }

label { display: block; margin-top: 15px; }

select, input {
    width: 100%;
    padding: 8px;
    margin-top: 5px;
}

.result-box {
    margin-top: 20px;
    padding: 15px;
    background: #000;
    color: #fff;
    font-size: 18px;
    text-align: center;
    border-radius: 10px;
}

/* ===== BUTTONS ===== */
.buttons {
    margin-top: 25px;
    display: flex;
    justify-content: space-between;
}

.right-buttons {
    display: flex;
    gap: 10px;
}

.btn {
    padding: 8px 18px;
    border-radius: 20px;
    border: none;
    background: #000;
    color: #fff;
    font-weight: bold;
    cursor: pointer;
}

.btn.exit { background: #c0392b; }
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
    <h2>Loan Calculator</h2>

    <label>Vehicle</label>
    <select id="vehicle" onchange="updateVariants()">
        <option value="">-- Select Vehicle --</option>
        <?php foreach ($pricing as $model => $v): ?>
            <option value="<?= htmlspecialchars($model) ?>">
                <?= htmlspecialchars($model) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label>Variant</label>
    <select id="variant" onchange="updatePaints()"></select>

    <label>Paint Type</label>
    <select id="paint"></select>

    <label>Loan Term (Years)</label>
    <select id="terms">
        <?php for ($i = 1; $i <= 10; $i++): ?>
            <option value="<?= $i ?>"><?= $i ?></option>
        <?php endfor; ?>
    </select>

    <label>Down Payment (RM)</label>
    <input type="number" id="downPayment">

    <label>Interest Rate (%)</label>
    <input type="number" step="0.01" id="interest">

    <div class="result-box" id="result">RM 0.00</div>

    <!-- SAVE FORM -->
    <form method="post" id="saveForm">
        <input type="hidden" name="vehicle" id="saveVehicle">
        <input type="hidden" name="variant" id="saveVariant">
        <input type="hidden" name="paint_type" id="savePaint">
        <input type="hidden" name="terms" id="saveTerms">
        <input type="hidden" name="down_payment" id="saveDown">
        <input type="hidden" name="interest_rate" id="saveRate">
        <input type="hidden" name="monthly_installment" id="saveInstallment">
        <input type="hidden" name="save_loan" value="1">
    </form>

    <div class="buttons">
        <button class="btn" onclick="calculate()">Calculate</button>

        <div class="right-buttons">
            <button class="btn" onclick="saveLoan()">Save</button>
            <a href="user_dashboard.php" class="btn">Back</a>
            <button class="btn exit" onclick="exitApp()">Exit</button>
        </div>
    </div>
</div>

<script>
const pricing = <?= json_encode($pricing) ?>;
let lastInstallment = null;

function updateVariants() {
    variant.innerHTML = "";
    paint.innerHTML = "";
    if (!vehicle.value) return;

    Object.keys(pricing[vehicle.value]).forEach(v => {
        const opt = document.createElement("option");
        opt.value = v;
        opt.text = v;
        variant.add(opt);
    });
    updatePaints();
}

function updatePaints() {
    paint.innerHTML = "";
    if (!vehicle.value || !variant.value) return;

    Object.keys(pricing[vehicle.value][variant.value]).forEach(p => {
        const opt = document.createElement("option");
        opt.value = p;
        opt.text = p;
        paint.add(opt);
    });
}

function calculate() {
    const price = pricing[vehicle.value][variant.value][paint.value];
    const loan = price - downPayment.value;
    const rate = interest.value / 100 / 12;
    const months = terms.value * 12;

    lastInstallment = (loan * rate) / (1 - Math.pow(1 + rate, -months));
    result.innerText = "RM " + lastInstallment.toFixed(2);
}

function saveLoan() {
    if (lastInstallment === null) {
        alert("Please calculate first.");
        return;
    }

    saveVehicle.value = vehicle.value;
    saveVariant.value = variant.value;
    savePaint.value = paint.value;
    saveTerms.value = terms.value;
    saveDown.value = downPayment.value;
    saveRate.value = interest.value;
    saveInstallment.value = lastInstallment.toFixed(2);

    saveForm.submit();
}

function exitApp() {
    if (confirm("Are you sure you want to exit?")) {
        window.location.href = "logout.php";
    }
}
</script>

</body>
</html>
