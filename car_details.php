<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

require_once __DIR__ . '/db_connection.php';

/* =========================
   GET SELECTED MODEL
   ========================= */
if (!isset($_GET['model'])) {
    header("Location: models.php");
    exit();
}

$selectedModel = $_GET['model'];

/* =========================
   FETCH CAR DETAILS
   ========================= */
$sql = "SELECT variant, price, paint_type 
        FROM car_details 
        WHERE model = ?
        ORDER BY variant ASC";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $selectedModel);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) === 0) {
    die("No details found for this model.");
}

/* =========================
   PROCESS DATA
   ========================= */
$variants = [];
$variantPriceMap = [];
$variantPaintTypes = [];

while ($row = mysqli_fetch_assoc($result)) {
    $variant = $row['variant'];
    $price   = $row['price'];
    $paint   = $row['paint_type'];

    $variantPriceMap[$variant] = $price;

    if (!isset($variantPaintTypes[$variant])) {
        $variantPaintTypes[$variant] = [];
    }

    if (!in_array($paint, $variantPaintTypes[$variant])) {
        $variantPaintTypes[$variant][] = $paint;
    }

    if (!in_array($variant, $variants)) {
        $variants[] = $variant;
    }
}

/* Default selections */
$defaultVariant = $variants[0];
$defaultPrice   = $variantPriceMap[$defaultVariant];
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?php echo htmlspecialchars($selectedModel); ?> Details</title>

<style>
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

/* WIDER CONTAINER */
.container {
    max-width: 1300px;
    margin: auto;
    padding: 60px 40px;
}

.title {
    font-size: 22px;
    margin-bottom: 20px;
}

.details-wrapper {
    display: flex;
    gap: 60px;
    align-items: center;
}

/* INFO PANEL */
.info-box {
    background: #fff;
    border-radius: 20px;
    padding: 30px;
    width: 300px;
}

.label {
    font-size: 14px;
    margin-top: 15px;
}

.value {
    font-weight: bold;
    margin-top: 5px;
}

select {
    width: 100%;
    padding: 8px;
    margin-top: 5px;
}

.price {
    font-size: 28px;
    margin-top: 25px;
}

/* IMAGE SECTION */
.image-box {
    flex: 1.3;
    text-align: center;
}

.image-box img {
    width: 620px;
    max-width: 100%;
    filter: drop-shadow(0 20px 50px rgba(0,0,0,0.45));
}

/* IMAGE BUTTONS */
.image-buttons {
    margin-top: 25px;
    display: flex;
    justify-content: center;
    gap: 15px;
}

/* BOTTOM BUTTONS */
.buttons {
    margin-top: 50px;
    display: flex;
    justify-content: flex-end; /* 👉 MOVE TO RIGHT */
    gap: 15px;
}


/* BUTTON STYLE */
.btn {
    padding: 10px 22px;
    border-radius: 20px;
    border: none;
    background: #000;
    color: #fff;
    font-weight: bold;
    cursor: pointer;
}

.btn.exit {
    background: #c0392b;
}
</style>
</head>

<body>

<div class="container">

    <div class="title">
        You have your sights set on a Proton!
    </div>

    <div class="details-wrapper">

        <!-- INFO PANEL -->
        <div class="info-box">
            <div class="label">VEHICLE</div>
            <div class="value"><?php echo htmlspecialchars($selectedModel); ?></div>

            <div class="label">VARIANT</div>
            <select id="variant" onchange="updateDetails()">
                <?php foreach ($variants as $v): ?>
                    <option value="<?php echo htmlspecialchars($v); ?>">
                        <?php echo htmlspecialchars($v); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <div class="label">PAINT TYPE</div>
            <select id="paintType"></select>

            <div class="price" id="price">
                <?php echo htmlspecialchars($defaultPrice); ?>
            </div>

            <div class="label">STARTING PRICE</div>
        </div>

        <!-- IMAGE + ACTION BUTTONS -->
        <div class="image-box">
            <img src="Images/<?php echo strtolower($selectedModel); ?>.png"
                 alt="<?php echo htmlspecialchars($selectedModel); ?>">

            <div class="image-buttons">
                <button class="btn" onclick="bookTestDrive()">Test Drive</button>
                <button class="btn" onclick="compareModel()">Compare Model</button>
            </div>
        </div>

    </div>

    <!-- BACK & EXIT -->
    <div class="buttons">
        <a href="models.php" class="btn">Back</a>
        <button class="btn exit" onclick="exitApp()">Exit</button>
    </div>

</div>

<script>
const variantPrices = <?php echo json_encode($variantPriceMap); ?>;
const variantPaints = <?php echo json_encode($variantPaintTypes); ?>;

function updateDetails() {
    const variant = document.getElementById("variant").value;
    document.getElementById("price").innerText = variantPrices[variant];

    const paintSelect = document.getElementById("paintType");
    paintSelect.innerHTML = "";

    variantPaints[variant].forEach(p => {
        const opt = document.createElement("option");
        opt.text = p;
        paintSelect.add(opt);
    });
}

function bookTestDrive() {
    const variant = document.getElementById("variant").value;
    window.location.href =
        "test_drive.php?car=<?php echo urlencode($selectedModel); ?> - " +
        encodeURIComponent(variant);
}

function compareModel() {
    const variant = document.getElementById("variant").value;
    window.location.href =
        "compare_models.php?car=<?php echo urlencode($selectedModel); ?> - " +
        encodeURIComponent(variant);
}

function exitApp() {
    if (confirm("Are you sure you want to exit?")) {
        window.location.href = "logout.php";
    }
}

updateDetails();
</script>

</body>
</html>
