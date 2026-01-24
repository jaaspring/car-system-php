<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to PROTON - Loan Calculator System</title>
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

        .logo {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .logo-img {
            width: 230px;
            height: 60px;
        }

        .logo-img img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            background: radial-gradient(ellipse at center, #f4d77e 0%, #e6c770 25%, #d4a747 50%, #c89a3d 75%, #9d7730 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
        }

        /* Welcome Container */
        .welcome-container {
            text-align: center;
            width: 100%;
            max-width: 500px;
        }

        .welcome-title {
            font-size: 48px;
            font-weight: 700;
            color: #000;
            margin-bottom: 20px;
            letter-spacing: 3px;
            font-family: 'Century Gothic', sans-serif;
        }

        .welcome-subtitle {
            font-size: 18px;
            color: #2a2a2a;
            margin-bottom: 60px;
            font-weight: 400;
            letter-spacing: 1px;
        }

        /* Button Container */
        .button-container {
            display: flex;
            flex-direction: column;
            gap: 20px;
            align-items: center;
        }

        .btn {
            width: 280px;
            padding: 16px 50px;
            background-color: #000;
            color: #fff;
            border: none;
            border-radius: 25px;
            font-size: 16px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 2px;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.3s;
            font-family: 'Century Gothic', sans-serif;
            text-decoration: none;
            display: inline-block;
        }

        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.4);
        }

        .btn:active {
            transform: translateY(0);
        }

        .btn-secondary {
            background-color: transparent;
            border: 3px solid #000;
            color: #000;
        }

        .btn-secondary:hover {
            background-color: #000;
            color: #fff;
        }

        /* Feature Section */
        .features {
            margin-top: 80px;
            display: flex;
            justify-content: center;
            gap: 40px;
            flex-wrap: wrap;
        }

        .feature-item {
            text-align: center;
            max-width: 150px;
        }

        .feature-icon {
            font-size: 36px;
            margin-bottom: 10px;
            color: #000;
        }

        .feature-text {
            font-size: 14px;
            color: #2a2a2a;
            font-weight: 600;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .header {
                padding: 15px 20px;
            }

            .logo-img {
                width: 150px;
                height: 40px;
            }

            .welcome-title {
                font-size: 36px;
                margin-bottom: 15px;
            }

            .welcome-subtitle {
                font-size: 16px;
                margin-bottom: 40px;
            }

            .btn {
                width: 240px;
                padding: 14px 40px;
                font-size: 14px;
            }

            .features {
                margin-top: 60px;
                gap: 30px;
            }
        }

        @media (max-width: 480px) {
            .welcome-title {
                font-size: 28px;
            }

            .btn {
                width: 100%;
                max-width: 280px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">
            <div class="logo-img">
                <!-- Replace with your actual logo path -->
                <img src="Images/proton.png" alt="Proton Logo">
            </div>
        </div>
    </div>

    <div class="main-content">
        <div class="welcome-container">
            <h1 class="welcome-title">WELCOME TO PROTON</h1>
            <p class="welcome-subtitle">Your Trusted Loan Calculator System</p>

            <div class="button-container">
                <a href="sign_up.php" class="btn">Sign Up</a>
                <a href="login.php" class="btn btn-secondary">Log In</a>
            </div>

            
        </div>
    </div>
</body>
</html>