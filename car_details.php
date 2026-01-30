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
$user_name = isset($_SESSION['name']) ? $_SESSION['name'] : $_SESSION['username'];

/* =========================
   FETCH CAR DETAILS
   ========================= */
$sql = "SELECT id, variant, price, paint_type 
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
$variantImageMap = [];

while ($row = mysqli_fetch_assoc($result)) {
    $variant = $row['variant'];
    $price   = $row['price'];
    $paint   = $row['paint_type'];
    $id      = $row['id'];

    $variantPriceMap[$variant] = $price;
    
    // Store first ID found for variant to use as image source
    if (!isset($variantImageMap[$variant])) {
        $variantImageMap[$variant] = $id;
    }

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
$defaultId      = $variantImageMap[$defaultVariant];
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?php echo htmlspecialchars($selectedModel); ?> Details</title>

<!-- IMPORT DASHBOARD STYLES FOR CONSISTENCY -->
<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Century Gothic', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
}

/* Header & Nav (Copied from Dashboard) */
.header {
    background-color: #000;
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
    align-items: center;
}

.nav-link {
    color: #fff;
    text-decoration: none;
    font-size: 16px;
    font-weight: 600;
}

.nav-link:hover {
    opacity: 0.7;
}

.logout-btn {
    background-color: #ff4500;
    color: #fff;
    padding: 10px 25px;
    border-radius: 20px;
    font-size: 14px;
    font-weight: 700;
    text-decoration: none;
}

.logout-btn:hover {
    background-color: #e63e00;
}

/* Main Background */
.main-content {
    flex: 1;
    background: radial-gradient(
        ellipse at center,
        #f4d77e 0%,
        #e6c770 25%,
        #d4a747 50%,
        #c89a3d 75%,
        #9d7730 100%
    );
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 40px;
}

/* Details Page Specifics */
.details-container {
    width: 100%;
    max-width: 1300px;
}

.title {
    font-size: 24px;
    margin-bottom: 30px;
    font-weight: bold;
    color: #2a2a2a;
    text-align: center;
}

.details-wrapper {
    display: flex;
    gap: 60px;
    align-items: center;
    justify-content: center;
}

/* Information Container */
.info-box {
    background: #fff;
    border-radius: 20px;
    padding: 30px;
    width: 350px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
}

.label {
    font-size: 13px;
    margin-top: 20px;
    font-weight: 900;       /* ULTRA BOLD */
    text-transform: uppercase;
    color: #666;
    letter-spacing: 0.5px;
}

.label:first-child {
    margin-top: 0;
}

.value {
    font-weight: 700;
    font-size: 18px;
    margin-top: 5px;
    color: #000;
}

select {
    width: 100%;
    padding: 10px;
    margin-top: 5px;
    border: 1px solid #ccc;
    border-radius: 5px;
    background: #fdfdfd;
    font-family: inherit;
    font-size: 15px;
}

.price {
    font-size: 32px;
    margin-top: 30px;
    font-weight: bold;
    color: #000;
}

.price-label {
    font-size: 12px;
    text-transform: uppercase;
    color: #666;
    font-weight: bold;
    margin-top: 5px;
}

/* Image Container */
.image-box {
    text-align: center;
}

.image-box img {
    width: 650px;
    max-width: 100%;
    filter: drop-shadow(0 25px 60px rgba(0,0,0,0.45));
    transition: transform 0.3s ease;
}

.image-box img:hover {
    transform: scale(1.02);
}

.image-buttons {
    margin-top: 30px;
    display: flex;
    justify-content: center;
    gap: 20px;
}

/* Buttons */
.btn {
    padding: 12px 28px;
    border-radius: 25px;
    border: none;
    background: #000;
    color: #fff;
    font-weight: bold;
    cursor: pointer;
    font-size: 14px;
    text-decoration: none;
    display: inline-block;
    transition: all 0.2s;
}

.btn:hover {
    background: #333;
    transform: translateY(-2px);
}

.btn.exit {
    background: #c0392b;
}
.btn.exit:hover {
    background: #a93226;
}

.footer-nav {
    margin-top: 40px;
    display: flex;
    justify-content: flex-end;
}
</style>
</head>

<body>

<?php include('navigation.php'); ?>

<div class="main-content">
    <div class="details-container">
        <!-- Title -->
        <div class="title">
            You have your sights set on a Proton!
        </div>

        <div class="details-wrapper">
            <!-- Left Info Panel -->
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
                <div class="price-label">STARTING PRICE</div>
            </div>

            <!-- Right Image Panel -->
            <div class="image-box">
                <img id="carImage" 
                     src="display_image.php?id=<?php echo $defaultId; ?>"
                     onerror="this.src='Images/<?php echo strtolower($selectedModel); ?>.png'" 
                     alt="<?php echo htmlspecialchars($selectedModel); ?>">

                <div class="image-buttons">
                    <button class="btn" onclick="bookTestDrive()">Test Drive</button>
                    <button class="btn" onclick="compareModel()">Compare Model</button>
                </div>
            </div>
        </div>

        <!-- Back Button Area -->
        <div class="footer-nav">
            <a href="models.php" class="btn">Back to Models</a>
        </div>
    </div>
</div>

<script>
const variantPrices = <?php echo json_encode($variantPriceMap); ?>;
const variantPaints = <?php echo json_encode($variantPaintTypes); ?>;
const variantImages = <?php echo json_encode($variantImageMap); ?>;

function updateDetails() {
    const variant = document.getElementById("variant").value;
    document.getElementById("price").innerText = variantPrices[variant];

    // Update Image
    const imgId = variantImages[variant];
    const carImg = document.getElementById("carImage");
    carImg.src = "display_image.php?id=" + imgId + "&t=" + new Date().getTime();

    // Update Paint Options
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
    const model = <?php echo json_encode($selectedModel); ?>;
    const fullString = model + " - " + variant;
    window.location.href = "test_drive.php?car=" + encodeURIComponent(fullString);
}

function compareModel() {
    const variant = document.getElementById("variant").value;
    const model = <?php echo json_encode($selectedModel); ?>;
    const fullString = model + " - " + variant;
    window.location.href = "compare_models.php?car=" + encodeURIComponent(fullString);
}

// Initialize
updateDetails();
</script>

</body>
</html>
