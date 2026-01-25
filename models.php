<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$user_name = isset($_SESSION['name']) ? $_SESSION['name'] : $_SESSION['username'];

require_once __DIR__ . '/db_connection.php';

/* Get unique car models */
$sql = "SELECT DISTINCT model FROM car_details ORDER BY model ASC";
$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Database Error: " . mysqli_error($conn));
}

/* Model → Image mapping (same idea as Java Swing) */
$modelImages = [
    "S70" => "Images/s70.png",
    "X50" => "Images/x50.png",
    "X70" => "Images/x70.png",
    "Persona" => "Images/persona.png",
    "Iriz" => "Images/iriz.png",
    "Saga" => "Images/saga.png"
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Models - Loan Calculator System</title>

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
</style>
</head>

<body>

<!-- HEADER -->
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

<!-- MAIN CONTENT -->
<div class="main-content">

    <div class="page-title">
        <h1>ALL PROTON MODELS</h1>
    </div>

    <div class="models-grid">
        <?php while ($row = mysqli_fetch_assoc($result)):
            $model = $row['model'];
            $image = $modelImages[$model] ?? 'Images/default.png';
        ?>
            <div class="model-card">
                <a href="car_details.php?model=<?php echo urlencode($model); ?>">
                    <img src="<?php echo $image; ?>" alt="<?php echo htmlspecialchars($model); ?>">
                </a>
                <h3>PROTON <?php echo htmlspecialchars($model); ?></h3>
            </div>
        <?php endwhile; ?>
    </div>

    <!-- BACK & EXIT BUTTONS -->
    <div class="bottom-buttons">
        <a href="user_dashboard.php" class="btn btn-back">Back</a>
        <a href="#" class="btn btn-exit" onclick="confirmExit()">Exit</a>
    </div>

</div>

<script>
function confirmExit() {
    if (confirm("Are you sure you want to exit?")) {
        window.location.href = "logout.php";
    }
}
</script>

</body>
</html>
