<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit();
}

// Get user name
$user_name = isset($_SESSION['name']) ? $_SESSION['name'] : $_SESSION['username'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard - Loan Calculator System</title>
<link rel="stylesheet" href="../toast.css">

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

/* Header */
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

/* Navigation */
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

/* Logout Button */
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

/* Main Content */
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
    padding: 60px 40px;
}

/* Welcome Section */
.welcome-section {
    text-align: center;
    margin-bottom: 60px;
}

.welcome-text {
    font-size: 20px;
    color: #2a2a2a;
    margin-bottom: 10px;
}

.tagline {
    font-size: 30px;
    font-weight: 700;
    margin-bottom: 5px;
}

.subtagline {
    font-size: 16px;
    color: #2a2a2a;
}

/* ===== SLIDER SECTION ===== */
.models-section {
    display: flex;
    align-items: center;
    gap: 60px;
}

.slider {
    width: 900px;
    overflow: hidden;
}

.slide {
    display: none;
    text-align: center;
}

.slide.active {
    display: block;
}

.model-name {
    font-size: 64px;
    font-weight: 700;
    color: rgba(255, 255, 255, 0.4);
    letter-spacing: 4px;
    margin-bottom: 35px;
}

.model-image {
    width: 850px;
    max-width: 100%;
    filter: drop-shadow(0 25px 60px rgba(0, 0, 0, 0.45));
}

/* Slider Buttons */
.slider-btn {
    background: rgba(0, 0, 0, 0.75);
    color: white;
    border: none;
    font-size: 36px;
    padding: 16px 22px;
    cursor: pointer;
    border-radius: 50%;
}

.slider-btn:hover {
    background: rgba(0, 0, 0, 0.95);
}

/* Footer */
.footer {
    margin-top: 60px;
    font-size: 12px;
    color: #2a2a2a;
}
</style>
</head>

<body>

<?php include('../navigation.php'); ?>

<div class="main-content">

    <div class="welcome-section">
        <p class="welcome-text">Welcome, <?php echo htmlspecialchars($user_name); ?>!</p>
        <h1 class="tagline">DRIVE INTO THE FUTURE OF LUXURY AND INNOVATION</h1>
        <p class="subtagline">
            Explore a world of premium cars designed for comfort, performance, and style.
        </p>
    </div>

    <div class="models-section">
        <button class="slider-btn" onclick="prevSlide()">❮</button>

        <div class="slider">
            <?php
            // Fetch 5 random cars for the slider
            require_once '../db_connection.php';
            $slide_res = $conn->query("SELECT id, model, image FROM car_details ORDER BY RAND() LIMIT 5");
            
            $isActive = true;
            if ($slide_res->num_rows > 0) {
                while ($slide = $slide_res->fetch_assoc()) {
                    $modelName = strtoupper($slide['model']);
                    $imgSrc = "../display_image.php?id=" . $slide['id'];
                    $activeClass = $isActive ? 'active' : '';
                    
                    // Fallback to static if needed happens in display_image or via onerror, 
                    // but here we can't easily check blob content length without fetching it.
                    // We'll trust display_image or use JS onerror.
                    echo "
                    <div class='slide $activeClass'>
                        <img src='$imgSrc' alt='Proton $modelName' class='model-image' 
                             onerror=\"this.src='../Images/" . strtolower($slide['model']) . ".png'\">
                    </div>";
                    
                    $isActive = false;
                }
            } else {
                // Fallback if DB empty
                echo "
                <div class='slide active'>
                    <img src='../Images/x50.png' class='model-image'>
                </div>";
            }
            ?>
        </div>

        <button class="slider-btn" onclick="nextSlide()">❯</button>
    </div>

    <div class="footer">
        © 2025 Proton Holdings Berhad
    </div>

</div>

<script>
let currentSlide = 0;
const slides = document.querySelectorAll('.slide');

function showSlide(index) {
    slides.forEach(slide => slide.classList.remove('active'));
    slides[index].classList.add('active');
}

function nextSlide() {
    currentSlide = (currentSlide + 1) % slides.length;
    showSlide(currentSlide);
}

function prevSlide() {
    currentSlide = (currentSlide - 1 + slides.length) % slides.length;
    showSlide(currentSlide);
}
</script>

<script src="../toast.js"></script>
</body>
</html>
