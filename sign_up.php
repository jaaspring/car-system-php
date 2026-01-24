<?php
session_start();
include('db_connection.php');

$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Check if username already exists
    $check_sql = "SELECT * FROM users WHERE username = ? OR email = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ss", $username, $email);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        $error_message = "Username or email already exists.";
    } else {
        // Use prepared statements to prevent SQL injection
        $sql = "INSERT INTO users (username, name, email, phone, password) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssss", $username, $name, $email, $phone, $password);

        if ($stmt->execute()) {
            $success_message = "Account created successfully! Redirecting to login...";
            header("refresh:2;url=login.php");
        } else {
            $error_message = "Error: " . $stmt->error;
        }
        $stmt->close();
    }
    $check_stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Loan Calculator System</title>
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

        .login-btn {
            background: transparent;
            color: #fff;
            border: none;
            padding: 10px 25px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: opacity 0.3s;
            text-decoration: none;
        }

        .login-btn:hover {
            opacity: 0.8;
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

        /* Signup Container */
        .signup-container {
            text-align: center;
            width: 100%;
            max-width: 450px;
        }

        .signup-title {
            font-size: 32px;
            font-weight: 700;
            color: #000;
            margin-bottom: 40px;
            letter-spacing: 2px;
            font-family: 'Century Gothic', sans-serif;
        }

        /* Form */
        .signup-form {
            display: flex;
            flex-direction: column;
            gap: 25px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 8px;
        }

        .form-label {
            font-size: 14px;
            font-weight: 700;
            color: #000;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-family: 'Century Gothic', sans-serif;
        }

        .form-input {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            font-family: 'Century Gothic', sans-serif;
            background-color: #fff;
            transition: box-shadow 0.3s, border-color 0.3s;
        }

        .form-input:focus {
            outline: none;
            border-color: #000;
            box-shadow: 0 0 0 2px rgba(0, 0, 0, 0.1);
        }

        .signup-btn {
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
            align-self: center;
            font-family: 'Century Gothic', sans-serif;
        }

        .signup-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }

        .signup-btn:active {
            transform: translateY(0);
        }

        /* Error Message */
        .error-message {
            background-color: rgba(220, 53, 69, 0.9);
            color: white;
            padding: 12px 20px;
            border-radius: 4px;
            margin-bottom: 20px;
            font-size: 14px;
            font-weight: 600;
        }

        /* Success Message */
        .success-message {
            background-color: rgba(40, 167, 69, 0.9);
            color: white;
            padding: 12px 20px;
            border-radius: 4px;
            margin-bottom: 20px;
            font-size: 14px;
            font-weight: 600;
        }

        /* Login Link */
        .login-link {
            margin-top: 20px;
            font-size: 14px;
            color: #2a2a2a;
        }

        .login-link a {
            color: #000;
            font-weight: 700;
            text-decoration: none;
            border-bottom: 2px solid #000;
        }

        .login-link a:hover {
            opacity: 0.8;
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

            .signup-title {
                font-size: 28px;
                margin-bottom: 30px;
            }

            .signup-container {
                max-width: 100%;
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
        <a href="login.php" class="login-btn">Log In</a>
    </div>

    <div class="main-content">
        <div class="signup-container">
            <h1 class="signup-title">SIGN UP</h1>
            
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

            <form class="signup-form" method="POST" action="">
                <div class="form-group">
                    <label class="form-label" for="username">Username</label>
                    <input class="form-input" type="text" id="username" name="username" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="name">Full Name</label>
                    <input class="form-input" type="text" id="name" name="name" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="email">Email</label>
                    <input class="form-input" type="email" id="email" name="email" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="phone">Phone</label>
                    <input class="form-input" type="tel" id="phone" name="phone" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <input class="form-input" type="password" id="password" name="password" required>
                </div>

                <button class="signup-btn" type="submit">SIGN UP</button>
            </form>

            <div class="login-link">
                Already have an account? <a href="login.php">Log In</a>
            </div>
        </div>
    </div>
</body>
</html>