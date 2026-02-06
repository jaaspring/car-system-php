<?php
session_start();
include('db_connection.php');

$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];

    $sql = "SELECT id FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $token = bin2hex(random_bytes(16));
        $token_hash = hash("sha256", $token);
        $expiry = date("Y-m-d H:i:s", time() + 60 * 30); // 30 minutes expiry

        $sql = "UPDATE users SET reset_token_hash = ?, reset_token_expires_at = ? WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $token_hash, $expiry, $email);
        $stmt->execute();

        // In a real app, send email here. For now, simulate it.
        // Assuming the app URL handling logic.
        $resetLink = "http://localhost/car/reset_password.php?token=" . $token;
        
        $success_message = "Password reset link has been generated (Simulation): <br> <a href='$resetLink' style='color: white; text-decoration: underline;'>Click here to reset password</a>";
    } else {
        // Don't reveal if user exists or not for security, but for this project maybe it's fine.
        // Let's be generic.
        $success_message = "If an account with that email exists, a reset link has been sent.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Loan Calculator System</title>
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

        .back-btn {
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

        .back-btn:hover {
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

        /* Login Container (Reused for consistency) */
        .login-container {
            text-align: center;
            width: 100%;
            max-width: 400px;
        }

        .login-title {
            font-size: 32px;
            font-weight: 700;
            color: #000;
            margin-bottom: 30px;
            letter-spacing: 2px;
            font-family: 'Century Gothic', sans-serif;
        }

        /* Form */
        .login-form {
            display: flex;
            flex-direction: column;
            gap: 30px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 10px;
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

        .submit-btn {
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

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }

        .submit-btn:active {
            transform: translateY(0);
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
            word-break: break-all;
        }
        
        .description {
            margin-bottom: 20px;
            font-size: 14px;
            color: #333;
            line-height: 1.5;
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

            .login-title {
                font-size: 28px;
                margin-bottom: 40px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">
            <div class="logo-img">
                <img src="Images/proton.png" alt="Proton Logo">
            </div>
        </div>
        <a href="login.php" class="back-btn">Back to Login</a>
    </div>

    <div class="main-content">
        <div class="login-container">
            <h1 class="login-title">FORGOT PASSWORD</h1>
            
            <p class="description">
                Enter your email address below and we'll send you a link to reset your password.
            </p>

            <?php if (!empty($error_message)): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($success_message)): ?>
                <div class="success-message">
                    <?php echo $success_message; // Allow HTML for the link ?>
                </div>
            <?php endif; ?>

            <form class="login-form" method="POST" action="">
                <div class="form-group">
                    <label class="form-label" for="email">Email Address</label>
                    <input class="form-input" type="email" id="email" name="email" required>
                </div>

                <button class="submit-btn" type="submit">SEND RESET LINK</button>
            </form>
        </div>
    </div>
</body>
</html>
