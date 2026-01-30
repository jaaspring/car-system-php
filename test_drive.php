<?php
session_start();

// Auth check
if (!isset($_SESSION['username'], $_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once __DIR__ . '/db_connection.php';

$user_id  = $_SESSION['user_id'];
$username = $_SESSION['username'];

$error_message = '';
$success_message = '';

/* =========================
   FETCH USER INFO (DISPLAY)
   ========================= */
$name = $phone = $email = '';

$user_stmt = $conn->prepare(
    "SELECT name, phone, email FROM users WHERE id = ?"
);
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();

if ($user_result->num_rows > 0) {
    $u = $user_result->fetch_assoc();
    $name  = $u['name'];
    $phone = $u['phone'];
    $email = $u['email'];
}
$user_stmt->close();

/* =========================
   FETCH CAR MODELS + DETAILS
   ========================= */
// We need model, variant, price, and ID (for image)
$car_data = []; // Stores details for JS: key="Model - Variant" val={price, imageId}
$car_options = [];

$res = $conn->query(
    "SELECT id, model, variant, price FROM car_details ORDER BY model ASC, variant ASC"
);

while ($row = $res->fetch_assoc()) {
    $fullName = $row['model'] . " - " . $row['variant'];
    $car_options[] = $fullName;
    
    $car_data[$fullName] = [
        'price' => $row['price'],
        'id'    => $row['id'], // Use this for display_image.php?id=...
        'model' => $row['model'] // For fallback image path
    ];
}

/* =========================
   HANDLE BOOKING
   ========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $car_model = trim($_POST['car_model'] ?? '');
    $location  = trim($_POST['location'] ?? '');
    $showroom  = trim($_POST['showroom'] ?? '');
    $date      = $_POST['date'] ?? '';
    $time      = $_POST['time'] ?? '';

    if (
        empty($car_model) || empty($location) ||
        empty($showroom) || empty($date) || empty($time)
    ) {
        $error_message = "All fields are required.";
    } else {

        if (strlen($time) === 5) {
            $time .= ":00";
        }

        /* CHECK TIME AVAILABILITY */
        $check_stmt = $conn->prepare(
            "SELECT id FROM test_drive
             WHERE date = ? AND time = ? AND showroom = ?"
        );
        $check_stmt->bind_param("sss", $date, $time, $showroom);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            $error_message =
                "Sorry, this time slot is already booked. Please choose another time.";
        } else {

            $insert_stmt = $conn->prepare(
                "INSERT INTO test_drive
                 (user_id, car_model_variant, location, showroom, date, time)
                 VALUES (?, ?, ?, ?, ?, ?)"
            );

            $insert_stmt->bind_param(
                "isssss",
                $user_id,
                $car_model,
                $location,
                $showroom,
                $date,
                $time
            );

            if ($insert_stmt->execute()) {
                $success_message =
                    "Your test drive has been booked successfully!";
            } else {
                $error_message = "Booking failed. Please try again.";
            }

            $insert_stmt->close();
        }

        $check_stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Book Test Drive</title>
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

.nav-link:hover { opacity: 0.7; }

.logout-btn {
    background: #ff4500;
    color: #fff;
    padding: 10px 25px;
    border-radius: 20px;
    text-decoration: none;
    font-weight: bold;
}

.logout-btn:hover {
    background: #e63e00;
}

/* ===== MAIN LAYOUT ===== */
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
    min-height: 550px;
}

/* LEFT SIDE: IMAGE & DETAILS */
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

.car-price-label {
    font-size: 12px;
    text-transform: uppercase;
    color: #777;
    margin-top: 5px;
}

/* RIGHT SIDE: FORM */
.form-section {
    flex: 1;
    padding: 40px;
}

h1 {
    margin-bottom: 25px;
    font-size: 26px;
    text-align: center; 
}

.alert {
    padding: 12px;
    border-radius: 6px;
    margin-bottom: 15px;
    font-weight: bold;
    text-align: center;
}
.alert.error { background: #e74c3c; color: #fff; }
.alert.success { background: #2ecc71; color: #fff; }

label {
    font-weight: bold;
    margin-top: 15px;
    display:block;
    font-size: 14px;
}

input, select {
    width: 100%;
    padding: 10px;
    margin-top: 5px;
    border: 1px solid #ccc;
    border-radius: 5px;
    font-family: inherit;
}

button {
    margin-top: 25px;
    width: 100%;
    padding: 14px;
    border: none;
    border-radius: 25px;
    background: #000;
    color: #fff;
    font-weight: bold;
    cursor: pointer;
    transition: background 0.3s;
}

button:hover {
    background: #333;
}

.secondary-btn {
    margin-top: 12px;
    background: transparent;
    color: #000;
    border: 2px solid #000;
}

.secondary-btn:hover {
    background: #000;
    color: #fff;
}

/* Responsive */
@media (max-width: 800px) {
    .container-wrapper {
        flex-direction: column;
    }
    .car-preview, .form-section {
        flex: none;
        width: 100%;
    }
    .car-preview {
        padding: 20px;
        order: -1; /* Image on top */
    }
}
</style>
</head>

<body>

<?php include('navigation.php'); ?>

<div class="main-content">

    <div class="container-wrapper">
        
        <!-- LEFT: VISUALS -->
        <div class="car-preview">
            <div class="preview-title" id="previewTitle">Select Your Car</div>
            
            <!-- Default placeholder image -->
            <img src="Images/proton.png" id="previewImg" class="car-display-img" alt="Selected Car">
            
            <div class="car-price" id="previewPrice"></div>
            <div class="car-price-label" id="previewPriceLabel" style="display:none;">Starting Price</div>
        </div>

        <!-- RIGHT: FORM -->
        <div class="form-section">
            <h1>Book a Test Drive</h1>

            <?php if ($error_message): ?>
                <div class="alert error"><?= htmlspecialchars($error_message) ?></div>
            <?php endif; ?>

            <?php if ($success_message): ?>
                <div class="alert success"><?= htmlspecialchars($success_message) ?></div>
            <?php endif; ?>

            <form method="POST">

                <label>Car Model</label>
                <select name="car_model" id="carModelSelect" required onchange="updatePreview()">
                    <option value="">-- Select Car --</option>
                    <?php 
                    // Priority: POST (Error reload) -> GET (Link from other page)
                    $sticky_model = $_POST['car_model'] ?? ($_GET['car'] ?? '');
                    
                    foreach ($car_options as $m): 
                    ?>
                        <option value="<?= htmlspecialchars($m) ?>"
                            <?= ($m === $sticky_model) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($m) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <!-- Hidden inputs for pure specific visuals processing if needed later -->

                <label>Name</label>
                <input type="text" value="<?= htmlspecialchars($name) ?>" disabled>
                <!-- Hidden fields if you actually need to submit these, but DB uses session user_id usually -->

                <div style="display: flex; gap: 15px;">
                    <div style="flex:1;">
                        <label>Phone</label>
                        <input type="text" value="<?= htmlspecialchars($phone) ?>" disabled>
                    </div>
                    <div style="flex:1;">
                        <label>Email</label>
                        <input type="email" value="<?= htmlspecialchars($email) ?>" disabled>
                    </div>
                </div>

                <div style="display: flex; gap: 15px;">
                    <div style="flex:1;">
                        <label>Location</label>
                        <select name="location" required>
                            <option value="">Select</option>
                            <option>Kuala Lumpur</option>
                            <option>Penang</option>
                            <option>Johor Bahru</option>
                        </select>
                    </div>
                    <div style="flex:1;">
                        <label>Showroom</label>
                        <select name="showroom" required>
                            <option value="">Select</option>
                            <option>Showroom 1</option>
                            <option>Showroom 2</option>
                        </select>
                    </div>
                </div>

                <div style="display: flex; gap: 15px;">
                    <div style="flex:1;">
                        <label>Date</label>
                        <input type="date" name="date" required>
                    </div>
                    <div style="flex:1;">
                        <label>Time</label>
                        <select name="time" required>
                            <option value="">Select</option>
                            <option>09:00</option>
                            <option>10:00</option>
                            <option>11:00</option>
                            <option>12:00</option>
                            <option>14:00</option>
                            <option>15:00</option>
                            <option>16:00</option>
                        </select>
                    </div>
                </div>

                <button type="submit">BOOK TEST DRIVE</button>

                <a href="test_drive_history.php" style="text-decoration:none;">
                    <button type="button" class="secondary-btn">
                        View Test Drive History
                    </button>
                </a>

            </form>
        </div>
        
    </div>
</div>

<script>
// Pass PHP data to JS
const carData = <?= json_encode($car_data); ?>;

function updatePreview() {
    const select = document.getElementById("carModelSelect");
    const selectedValue = select.value;
    
    const titleEl = document.getElementById("previewTitle");
    const imgEl = document.getElementById("previewImg");
    const priceEl = document.getElementById("previewPrice");
    const priceLabelEl = document.getElementById("previewPriceLabel");

    if (selectedValue && carData[selectedValue]) {
        // Data exists
        const data = carData[selectedValue];
        
        titleEl.innerText = selectedValue;
        priceEl.innerText = data.price;
        priceLabelEl.style.display = "block";
        
        // Image Logic: Try ID first, then fallback to model name logic
        // We use display_image.php for dynamic
        imgEl.src = "display_image.php?id=" + data.id + "&t=" + new Date().getTime();
        
        // If display_image fails (no blob), we can set a fallback handling on error directly on the img tag if needed,
        // but let's try to be smart. If the ID is valid, display_image usually returns something.
        // We can add onerror to the image tag in HTML to fallback to standard folder path.
        imgEl.onerror = function() {
            this.src = "Images/" + data.model.toLowerCase() + ".png";
        };
        
    } else {
        // Reset
        titleEl.innerText = "Select Your Car";
        imgEl.src = "Images/proton.png";
        priceEl.innerText = "";
        priceLabelEl.style.display = "none";
    }
}

// Initialize on load (in case of sticky form or GET param)
window.onload = updatePreview;
</script>

</body>
</html>
