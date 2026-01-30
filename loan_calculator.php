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
    // Success - redirect with toast
    header("Location: loan_calculator.php?toast_msg=" . urlencode("Loan history saved successfully!") . "&toast_type=success");
    exit();
}

/* =========================
   LOAD PRICING & IMAGE DATA
   ========================= */
$sql = "SELECT id, model, variant, paint_type, price FROM car_details";
$result = mysqli_query($conn, $sql);

$pricing = []; // $pricing[model][variant][paint] = {price, id}

while ($row = mysqli_fetch_assoc($result)) {
    $model = $row['model'];
    $variant = $row['variant'];
    $paint = $row['paint_type'];
    $priceVal = floatval(str_replace(['RM', ',', ' '], '', $row['price']));
    $id = $row['id'];

    // Structure: Model -> Variant -> Paint -> Object {price, id}
    $pricing[$model][$variant][$paint] = [
        'price' => $priceVal,
        'id'    => $id,
        'priceFormatted' => $row['price']
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Loan Calculator</title>
<link rel="stylesheet" href="toast.css">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>
* { box-sizing: border-box; margin: 0; padding: 0; }

body {
    font-family: 'Century Gothic', sans-serif;
    background: radial-gradient(circle, #f4d77e, #c89a3d);
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

/* ===== MAIN CONTENT ===== */
.main-content {
    flex: 1;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 40px;
}

.container-wrapper {
    display: flex;
    background: #fff;
    border-radius: 25px;
    box-shadow: 0 20px 50px rgba(0,0,0,0.2);
    overflow: hidden;
    max-width: 1000px;
    width: 100%;
    min-height: 600px;
}

/* LEFT: PREVIEW */
.car-preview {
    flex: 1;
    background: #f9f9f9;
    padding: 40px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    border-right: 1px solid #eee;
}

.preview-title {
    font-size: 24px;
    font-weight: 800;
    color: #333;
    margin-bottom: 20px;
}

.car-display-img {
    width: 100%;
    max-width: 350px;
    height: auto;
    object-fit: contain;
    filter: drop-shadow(0 10px 20px rgba(0,0,0,0.15));
    transition: transform 0.3s ease;
    border-radius: 10px;
}

.car-price {
    margin-top: 30px;
    font-size: 28px;
    font-weight: bold;
    color: #000;
}

/* RIGHT: CALCULATOR FORM */
.form-section {
    flex: 1;
    padding: 40px;
}

h2 { 
    text-align: center; 
    margin-bottom: 25px;
    font-size: 28px;
}

label { 
    display: block; 
    margin-top: 15px; 
    font-weight: bold; 
    font-size: 14px;
}

select, input {
    width: 100%;
    padding: 10px;
    margin-top: 5px;
    border-radius: 5px;
    border: 1px solid #ccc;
    font-family: inherit;
}

.result-box {
    margin-top: 25px;
    padding: 15px;
    background: #000;
    color: #fff;
    font-size: 20px;
    text-align: center;
    border-radius: 10px;
    font-weight: bold;
}

/* BUTTONS */
.buttons {
    margin-top: 30px;
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.main-actions {
    display: flex;
    gap: 15px;
}

.btn {
    padding: 12px;
    border-radius: 20px;
    border: none;
    background: #000;
    color: #fff;
    font-weight: bold;
    cursor: pointer;
    flex: 1;
    transition: background 0.3s;
}

.btn:hover { background: #333; }

.btn.save { background: #2ecc71; }
.btn.back { background: transparent; color: #000; border: 2px solid #000; }
.btn.back:hover { background: #000; color: #fff; }

@media (max-width: 800px) {
    .container-wrapper { flex-direction: column; }
    .car-preview, .form-section { width: 100%; flex: none; }
    .car-preview { order: -1; padding: 20px; }
}
</style>
</head>

<body>

<?php include('navigation.php'); ?>

<!-- CONTENT -->
<div class="main-content">
    <div class="container-wrapper">
        
        <!-- LEFT: VISUALS -->
        <div class="car-preview">
            <div class="preview-title" id="previewTitle">Select Vehicle</div>
            
            <img src="Images/proton.png" id="previewImg" class="car-display-img" alt="Selected Car">
            
            <div class="car-price" id="previewPrice"></div>
        </div>

        <!-- RIGHT: CALCULATOR -->
        <div class="form-section">
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
            <select id="paint" onchange="updatePreview()"></select>

            <label>Loan Term (Years)</label>
            <select id="terms">
                <?php for ($i = 5; $i <= 10; $i++): ?>
                    <option value="<?= $i ?>"><?= $i ?> Years</option>
                <?php endfor; ?>
            </select>

            <label>Down Payment (RM)</label>
            <input type="number" id="downPayment" placeholder="0">

            <label>Interest Rate (%)</label>
            <input type="number" step="0.01" id="interest" placeholder="3.0">

            <div class="result-box" id="result">RM 0.00</div>

            <!-- HIDDEN SAVE FORM -->
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
                
                <div class="main-actions">
                    <button class="btn save" onclick="saveLoan()">Save</button>
                    <a href="loan_history.php" class="btn back" style="text-align:center;">View Loan History</a>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
// Data from PHP
const pricing = <?= json_encode($pricing) ?>;
let lastInstallment = null;

const vehicleSel = document.getElementById('vehicle');
const variantSel = document.getElementById('variant');
const paintSel   = document.getElementById('paint');
const previewImg = document.getElementById('previewImg');
const previewTitle = document.getElementById('previewTitle');
const previewPrice = document.getElementById('previewPrice');

function updateVariants() {
    variantSel.innerHTML = "";
    paintSel.innerHTML = "";
    previewPrice.innerText = "";
    
    if (!vehicleSel.value) {
        resetPreview();
        return;
    }

    // Populate Variants
    Object.keys(pricing[vehicleSel.value]).forEach(v => {
        const opt = document.createElement("option");
        opt.value = v;
        opt.text = v;
        variantSel.add(opt);
    });
    
    // Auto trigger next updates
    updatePaints();
}

function updatePaints() {
    paintSel.innerHTML = "";
    if (!vehicleSel.value || !variantSel.value) return;

    // Populate Paints
    Object.keys(pricing[vehicleSel.value][variantSel.value]).forEach(p => {
        const opt = document.createElement("option");
        opt.value = p;
        opt.text = p;
        paintSel.add(opt);
    });
    
    updatePreview();
}

function updatePreview() {
    if (!vehicleSel.value || !variantSel.value || !paintSel.value) {
        resetPreview();
        return;
    }

    const data = pricing[vehicleSel.value][variantSel.value][paintSel.value];
    
    // Update visuals
    previewTitle.innerText = vehicleSel.value + " " + variantSel.value;
    previewPrice.innerText = data.priceFormatted;

    // Update Image (using backend ID for dynamic blob, or fallback)
    previewImg.src = "display_image.php?id=" + data.id + "&t=" + new Date().getTime();
    previewImg.onerror = function() {
        this.src = "Images/" + vehicleSel.value.toLowerCase() + ".png";
    };
}

function resetPreview() {
    previewTitle.innerText = "Select Vehicle";
    previewImg.src = "Images/proton.png";
    previewPrice.innerText = "";
}

function calculate() {
    if (!vehicleSel.value || !variantSel.value || !paintSel.value) {
        showToast("Please select a vehicle completely.", "warning");
        return;
    }

    const data = pricing[vehicleSel.value][variantSel.value][paintSel.value];
    const price = data.price;
    const down = parseFloat(document.getElementById('downPayment').value) || 0;
    const rate = (parseFloat(document.getElementById('interest').value) || 0) / 100 / 12;
    const months = document.getElementById('terms').value * 12;
    
    const loan = price - down;
    
    // Simple Interest Formula usually used for cars in Malaysia: (Loan * Rate * Years) + Loan / Months ?
    // Actually, usually it's: (LoanAmount * IR * Years + LoanAmount) / (Years * 12)
    // The previous code used Compound interest formula: (loan * rate) / (1 - Math.pow(1 + rate, -months)) which is for home loans (amortization).
    // Car loans in MY are usually flat rate.
    // Let's stick to the previous code's formula unless requested otherwise, 
    // BUT the previous formula `(loan * rate) / (1 - Math.pow(1 + rate, -months))` is correct for standard bank amortization.
    // If user wants flat rate (P * R * T), they haven't asked. I will keep the existing formula but make sure it works.
    
    if (loan <= 0) {
        showToast("Down payment cannot exceed car price!", "error");
        return;
    }

    if (rate <= 0) {
        // Zero interest
        lastInstallment = loan / months;
    } else {
        lastInstallment = (loan * rate) / (1 - Math.pow(1 + rate, -months));
    }

    document.getElementById('result').innerText = "RM " + lastInstallment.toFixed(2);
}

function saveLoan() {
    if (lastInstallment === null) {
        showToast("Please calculate first.", "warning");
        return;
    }

    document.getElementById('saveVehicle').value = vehicleSel.value;
    document.getElementById('saveVariant').value = variantSel.value;
    document.getElementById('savePaint').value = paintSel.value;
    document.getElementById('saveTerms').value = document.getElementById('terms').value;
    document.getElementById('saveDown').value = document.getElementById('downPayment').value;
    document.getElementById('saveRate').value = document.getElementById('interest').value;
    document.getElementById('saveInstallment').value = lastInstallment.toFixed(2);

    document.getElementById('saveForm').submit();
}

// Initial state
resetPreview();
</script>
<script src="toast.js"></script>
</body>
</html>
