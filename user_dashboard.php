<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
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

        /* Welcome Section */
        .welcome-section {
            text-align: center;
            margin-bottom: 50px;
            z-index: 2;
        }

        .welcome-text {
            font-size: 20px;
            color: #2a2a2a;
            margin-bottom: 10px;
            font-weight: 400;
        }

        .tagline {
            font-size: 28px;
            color: #000;
            font-weight: 700;
            letter-spacing: 1px;
            margin-bottom: 5px;
        }

        .subtagline {
            font-size: 16px;
            color: #2a2a2a;
            font-weight: 400;
        }

        /* Car Models Section */
        .models-section {
            display: flex;
            gap: 40px;
            justify-content: center;
            align-items: center;
            flex-wrap: wrap;
            margin-bottom: 50px;
            z-index: 2;
        }

        .model-card {
            text-align: center;
            position: relative;
        }

        .model-name {
            font-size: 48px;
            font-weight: 700;
            color: rgba(255, 255, 255, 0.4);
            letter-spacing: 3px;
            margin-bottom: 20px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
        }

        .model-image {
            width: 400px;
            height: auto;
            filter: drop-shadow(0 10px 30px rgba(0, 0, 0, 0.3));
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
            z-index: 2;
        }

        .action-btn {
            background-color: #000;
            color: #fff;
            border: none;
            padding: 12px 30px;
            border-radius: 25px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.3s;
            text-decoration: none;
            display: inline-block;
        }

        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.4);
        }

        /* Footer */
        .footer {
            position: absolute;
            bottom: 20px;
            right: 40px;
            font-size: 12px;
            color: #2a2a2a;
            z-index: 2;
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
                <a href="models.php" class="nav-link">Models</a>
                <a href="loan_calculator.php" class="nav-link">Loan Calculator</a>
                <a href="compare_models.php" class="nav-link">Compare Models</a>
                <a href="test_drive.php" class="nav-link">Book Test Drive</a>
                <a href="rating.php" class="nav-link">Rating</a>
            </nav>
        </div>
        <a href="logout.php" class="logout-btn">Logout</a>
    </div>

    <div class="main-content">
        
        <div class="welcome-section">
            <p class="welcome-text">Welcome, <?php echo htmlspecialchars($user_name); ?>!</p>
            <h1 class="tagline">DRIVE INTO THE FUTURE OF LUXURY AND INNOVATION</h1>
            <p class="subtagline">Explore a world of premium cars designed for comfort, performance, and style.</p>
        </div>

        <div class="models-section">
            <div class="model-card">
                <h2 class="model-name">PROTON X50</h2>
                <img src="Images/x50.png" alt="Proton X50" class="model-image">
            </div>
            <div class="model-card">
                <h2 class="model-name">PROTON X70</h2>
                <img src="Images/x70.png" alt="Proton X70" class="model-image">
            </div>
        </div>

        <div class="action-buttons">
            <a href="test_drive.php" class="action-btn">Book Test Drive</a>
            <a href="models.php" class="action-btn">View All Models</a>
            <a href="loan_calculator.php" class="action-btn">Loan Calculator</a>
            <a href="loan_history.php" class="action-btn">View Loan History</a>
        </div>

        <div class="footer">
            2025 Proton Holdings Berhad
        </div>
    </div>
</body>
</html>