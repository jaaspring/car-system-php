<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Get user name
$user_name = isset($_SESSION['name']) ? $_SESSION['name'] : $_SESSION['username'];

include('db_connection.php');

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$error_message = '';
$success_message = '';

// Fetch user details
$name = '';
$phone = '';
$email = '';

$user_sql = "SELECT name, phone, email FROM users WHERE id = ?";
$user_stmt = $conn->prepare($user_sql);
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();

if ($user_result->num_rows > 0) {
    $user_data = $user_result->fetch_assoc();
    $name = $user_data['name'];
    $phone = $user_data['phone'];
    $email = $user_data['email'];
}
$user_stmt->close();

// Fetch car models
$car_models = [];
$car_sql = "SELECT model, variant FROM car_details ORDER BY id ASC";
$car_result = $conn->query($car_sql);
if ($car_result->num_rows > 0) {
    while ($row = $car_result->fetch_assoc()) {
        $car_models[] = $row['model'] . " - " . $row['variant'];
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $car_model = $_POST['car_model'];
    $location = $_POST['location'];
    $showroom = $_POST['showroom'];
    $date = $_POST['date'];
    $time = $_POST['time'];

    // Validate inputs
    if (empty($car_model) || empty($location) || empty($showroom) || empty($date) || empty($time)) {
        $error_message = "All fields are required!";
    } else {
        // Add seconds to time if not present
        if (strlen($time) == 5) {
            $time = $time . ":00";
        }

        $insert_sql = "INSERT INTO test_drive (name, phone, email, car_model_variant, location, showroom, date, time) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("ssssssss", $name, $phone, $email, $car_model, $location, $showroom, $date, $time);

        if ($insert_stmt->execute()) {
            $success_message = "Thank you for booking! We will contact you soon.";
        } else {
            $error_message = "Failed to book test drive. Please try again.";
        }
        $insert_stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Test Drive - Loan Calculator System</title>
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

        .logo {
            display: flex;
            align-items: center;
            gap: 15px;
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
            transition: opacity 0.3s;
            cursor: pointer;
        }

        .nav-link:hover {
            opacity: 0.7;
        }

        /* Logout Button */
        .logout-btn {
            background-color: #ff4500;
            color: #fff;
            border: none;
            padding: 10px 25px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: background-color 0.3s;
            text-decoration: none;
            display: inline-block;
        }

        .logout-btn:hover {
            background-color: #e63e00;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            background: radial-gradient(ellipse at center, #f4d77e 0%, #e6c770 25%, #d4a747 50%, #c89a3d 75%, #9d7730 100%);
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 60px 40px;
            position: relative;
        }

        /* Form Container */
        .form-container {
            text-align: center;
            width: 100%;
            max-width: 500px;
            z-index: 2;
        }

        .section-header {
            font-size: 14px;
            font-weight: 700;
            color: #000;
            text-align: left;
            margin-top: 25px;
            margin-bottom: 15px;
            letter-spacing: 1px;
        }

        .test-drive-form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 6px;
        }

        .form-label {
            font-size: 14px;
            font-weight: 400;
            color: #000;
        }

        .form-input, .form-select {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 14px;
            font-family: 'Century Gothic', sans-serif;
            font-weight: 600;
            background-color: #fff;
            transition: border-color 0.3s;
        }

        .form-input:focus, .form-select:focus {
            outline: none;
            border-color: #000;
        }

        .form-input:disabled {
            background-color: #f5f5f5;
            color: #333;
        }

        /* Date and Time Row */
        .date-time-row {
            display: flex;
            gap: 10px;
        }

        .date-time-row .form-group {
            flex: 1;
        }

        .helper-text {
            font-size: 10px;
            color: #555;
            margin-top: 2px;
        }

        /* Book Button */
        .book-btn {
            margin-top: 20px;
            padding: 12px 50px;
            background-color: #000;
            color: #fff;
            border: none;
            border-radius: 25px;
            font-size: 14px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 2px;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.3s;
            width: 100%;
        }

        .book-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.4);
        }

        /* Bottom Buttons */
        .bottom-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 30px;
        }

        .back-btn, .exit-btn {
            padding: 10px 35px;
            border: none;
            border-radius: 25px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.3s;
            text-decoration: none;
            display: inline-block;
        }

        .back-btn {
            background-color: #000;
            color: #fff;
        }

        .exit-btn {
            background-color: #cc3300;
            color: #fff;
        }

        .back-btn:hover, .exit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }

        /* Messages */
        .error-message {
            background-color: rgba(220, 53, 69, 0.9);
            color: white;
            padding: 12px 20px;
            border-radius: 4px;
            margin-bottom: 20px;
            font-size: 14px;
            font-weight: 600;
        }

        .success-message {
            background-color: rgba(40, 167, 69, 0.9);
            color: white;
            padding: 12px 20px;
            border-radius: 4px;
            margin-bottom: 20px;
            font-size: 14px;
            font-weight: 600;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .header {
                flex-direction: column;
                gap: 20px;
            }

            .header-left {
                flex-direction: column;
                gap: 20px;
            }

            .nav-menu {
                flex-wrap: wrap;
                justify-content: center;
            }

            .model-image {
                width: 350px;
            }

            .models-section {
                flex-direction: column;
            }
        }

        @media (max-width: 768px) {
            .header {
                padding: 15px 20px;
            }

            .main-content {
                padding: 40px 20px;
            }

            .logo-img {
                width: 140px;
                height: 40px;
            }

            .nav-menu {
                gap: 20px;
            }

            .nav-link {
                font-size: 14px;
            }

            .tagline {
                font-size: 22px;
            }

            .model-name {
                font-size: 36px;
            }

            .model-image {
                width: 280px;
            }

            .action-buttons {
                flex-direction: column;
                width: 100%;
                max-width: 300px;
            }

            .action-btn {
                width: 100%;
            }

            .footer {
                position: static;
                text-align: center;
                margin-top: 40px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-left">
            <div class="logo">
                <div class="logo-img">
                    <img src="Images/proton.png" alt="Proton Logo">
                </div>
            </div>
            <nav class="nav-menu">

                <a href="user_dashboard.php" class="nav-link">Home</a>
                <a href="test_drive.php" class="nav-link">Book Test Drive</a>
            </nav>
        </div>
        <a href="logout.php" class="logout-btn">Logout</a>
    </div>

    <div class="main-content">
        <div class="form-container">
            <h1 class="page-title">BOOK A TEST DRIVE</h1>

            <?php if (!empty($error_message)): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($success_message)): ?>
                <div class="success-message">
                    <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>

            <form class="test-drive-form" method="POST" action="">
                <!-- Car Details -->
                <div class="section-header">CAR DETAILS</div>
                
                <div class="form-group">
                    <label class="form-label" for="car_model">Car Model</label>
                    <select class="form-select" id="car_model" name="car_model" required>
                        <option value="">Select Car Model</option>
                        <?php foreach ($car_models as $model): ?>
                            <option value="<?php echo htmlspecialchars($model); ?>">
                                <?php echo htmlspecialchars($model); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Personal Details -->
                <div class="section-header">PERSONAL DETAILS</div>
                
                <div class="form-group">
                    <label class="form-label" for="name">Name</label>
                    <input class="form-input" type="text" id="name" name="name" 
                           value="<?php echo htmlspecialchars($name); ?>" disabled>
                </div>

                <div class="form-group">
                    <label class="form-label" for="phone">Phone</label>
                    <input class="form-input" type="text" id="phone" name="phone" 
                           value="<?php echo htmlspecialchars($phone); ?>" disabled>
                </div>

                <div class="form-group">
                    <label class="form-label" for="email">Email</label>
                    <input class="form-input" type="email" id="email" name="email" 
                           value="<?php echo htmlspecialchars($email); ?>" disabled>
                </div>

                <!-- Test Drive Details -->
                <div class="section-header">TEST DRIVE DETAILS</div>
                
                <div class="form-group">
                    <label class="form-label" for="location">Location</label>
                    <select class="form-select" id="location" name="location" required>
                        <option value="">Select Location</option>
                        <option value="Kuala Lumpur">Kuala Lumpur</option>
                        <option value="Penang">Penang</option>
                        <option value="Johor Bahru">Johor Bahru</option>
                        <option value="Melaka">Melaka</option>
                        <option value="Ipoh">Ipoh</option>
                        <option value="Kota Kinabalu">Kota Kinabalu</option>
                        <option value="Kuching">Kuching</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="showroom">Preferred Showroom</label>
                    <select class="form-select" id="showroom" name="showroom" required>
                        <option value="">Select Showroom</option>
                        <option value="Showroom 1">Showroom 1</option>
                        <option value="Showroom 2">Showroom 2</option>
                        <option value="Showroom 3">Showroom 3</option>
                    </select>
                </div>

                <div class="date-time-row">
                    <div class="form-group">
                        <label class="form-label" for="date">Date*</label>
                        <input class="form-input" type="date" id="date" name="date" required>
                        <span class="helper-text">eg:2002-02-20 (YYYY-MM-DD)</span>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="time">Time*</label>
                        <select class="form-select" id="time" name="time" required>
                            <option value="">Select Time</option>
                            <option value="09:00">09:00</option>
                            <option value="10:00">10:00</option>
                            <option value="11:00">11:00</option>
                            <option value="12:00">12:00</option>
                            <option value="13:00">13:00</option>
                            <option value="14:00">14:00</option>
                            <option value="15:00">15:00</option>
                            <option value="16:00">16:00</option>
                        </select>
                        <span class="helper-text">eg: 11:30</span>
                    </div>
                </div>

                <button class="book-btn" type="submit">BOOK</button>
            </form>


        </div>
    </div>
</body>
</html>
