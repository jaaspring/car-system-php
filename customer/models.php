<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit();
}

$user_name = isset($_SESSION['name']) ? $_SESSION['name'] : $_SESSION['username'];

require_once '../db_connection.php';

/* Get unique car models */
$sql = "SELECT DISTINCT model FROM car_details ORDER BY model ASC";
$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Database Error: " . mysqli_error($conn));
}

/* Model → Image mapping (same idea as Java Swing) */
$modelImages = [
    "S70" => "../Images/s70.png",
    "X50" => "../Images/x50.png",
    "X70" => "../Images/x70.png",
    "Persona" => "../Images/persona.png",
    "Iriz" => "../Images/iriz.png",
    "Saga" => "../Images/saga.png"
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Models - Loan Calculator System</title>
<link rel="stylesheet" href="../assets/css/toast.css">

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

/* ===== HEADER (SAME AS DASHBOARD) ===== */
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

/* ===== MAIN CONTENT (GOLD GRADIENT) ===== */
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
    padding: 60px 40px 80px;
}

/* ===== TITLE ===== */
.page-title {
    text-align: center;
    margin-bottom: 50px;
}

.page-title h1 {
    font-size: 36px;
    font-weight: 700;
    color: #2a2a2a;
}

/* ===== MODELS GRID ===== */
.models-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 35px;
    max-width: 1100px;
    margin: auto;
}

.model-card {
    background: #fff;
    border-radius: 20px;
    text-align: center;
    box-shadow: 0 8px 20px rgba(0,0,0,0.15);
    transition: transform 0.3s ease;
}

.model-card:hover {
    transform: scale(1.05);
}

.model-card img {
    width: 100%;
    height: 240px;
    object-fit: contain;
    padding: 20px;
    cursor: pointer;
    filter: drop-shadow(0 12px 30px rgba(0,0,0,0.35));
}

.model-card h3 {
    font-size: 22px;
    margin-bottom: 20px;
    color: #000;
}

/* ===== BACK & EXIT BUTTONS ===== */
.bottom-buttons {
    margin-top: 60px;
    display: flex;
    justify-content: flex-end;
    gap: 15px;
    max-width: 1100px;
    margin-left: auto;
    margin-right: auto;
}

.btn {
    padding: 10px 25px;
    border-radius: 22px;
    font-weight: 700;
    text-decoration: none;
    color: #fff;
    font-size: 14px;
}

.btn-back {
    background-color: #000;
}

.btn-exit {
    background-color: #c0392b;
}

.btn-exit:hover {
    background-color: #a93226;
}

/* ===== RESPONSIVE ===== */
@media (max-width: 900px) {
    .models-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 600px) {
    .models-grid {
        grid-template-columns: 1fr;
    }
}
/* ===== WISHLIST BUTTON ===== */
.wishlist-btn {
    position: absolute;
    top: 10px;
    right: 10px;
    background: rgba(255,255,255,0.9);
    border-radius: 50%;
    width: 35px;
    height: 35px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    transition: all 0.2s;
    z-index: 10;
    color: #e74c3c;
}
.wishlist-btn:hover {
    transform: scale(1.1);
    background: #fff;
}
</style>
</head>

<body>

<?php include('../widget/navigation.php'); ?>

<!-- MAIN CONTENT -->
<div class="main-content">

    <div class="page-title">
        <h1>ALL PROTON MODELS</h1>
    </div>

    <div class="models-grid">
        <div style="grid-column: 1 / -1; text-align: right; margin-bottom: 20px;">
            <a href="compare_models.php" class="btn" style="background:#333; font-size:14px;">Compare Models</a>
        </div>
        <?php while ($row = mysqli_fetch_assoc($result)):
            $model = $row['model'];
            $image = $modelImages[$model] ?? '../Images/default.png';
        ?>
            <div class="model-card">
                <div style="position:relative;">
                    <a href="car_details.php?model=<?php echo urlencode($model); ?>">
                        <img src="<?php echo $image; ?>" alt="<?php echo htmlspecialchars($model); ?>">
                    </a>
                    <!-- Wishlist Heart -->
                    <div class="wishlist-btn" onclick="toggleWishlist(this, '<?php echo htmlspecialchars($model); ?>')">
                        <svg viewBox="0 0 24 24" width="20" height="20" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
                    </div>
                </div>
                <h3>PROTON <?php echo htmlspecialchars($model); ?></h3>
            </div>
        <?php endwhile; ?>
    </div>

    <!-- BACK BUTTON -->
    <div class="bottom-buttons">
        <a href="wishlist.php" class="btn" style="background:#e74c3c;">My Wishlist</a>
        <a href="user_dashboard.php" class="btn btn-back">Back</a>
    </div>

</div>

<script>
function toggleWishlist(btn, model) {
    // Note: models.php lists 'models' (X50, X70), but wishlist links to specific 'car_id'.
    // Since models.php is a high-level view, we might need to change how we handle this.
    // The previous plan assumed car cards had IDs. 
    // models.php currently shows DISTINCT models. 
    // FIX: models.php currently links to car_details.php?model=X50.
    // We can't easily wishlist a "Model" generally unless we change DB or pick a default variant.
    // OPTION: Only put wishlist on car_details.php (specific variant) OR car_list.php.
    // However, user expects it here. Let's redirect to 'wishlist' logic or just alert 
    // "Please select a variant to wishlist" or we update the plan.
    
    // For now, let's implement the Wishlist page and put the heart on `manage_cars` style list if we had one for customers.
    // But wait, `compare_models.php` allows picking specific cars.
    // `car_details.php` probably lists variants. Let's check `car_details.php`.
    
    alert("Please select a specific variant to add to wishlist.");
    window.location.href = "car_details.php?model=" + encodeURIComponent(model);
    e.preventDefault();
}
</script>

<script src="../assets/js/toast.js"></script>
</body>
</html>
