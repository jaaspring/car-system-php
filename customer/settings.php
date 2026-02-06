<?php
session_start();
include('../db_connection.php');

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$success_message = '';
$error_message = '';

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_profile'])) {
        $name = $_POST['name'];
        $email = $_POST['email'];
        $phone = $_POST['phone'];

        $sql = "UPDATE users SET name = ?, email = ?, phone = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $name, $email, $phone, $user_id);
        
        if ($stmt->execute()) {
            $_SESSION['name'] = $name; // Update session name
            $success_message = "Profile updated successfully.";
        } else {
            $error_message = "Error updating profile: " . $conn->error;
        }
    } elseif (isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        // Verify current password
        $sql = "SELECT password FROM users WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        if (password_verify($current_password, $row['password'])) {
            if ($new_password === $confirm_password) {
                if (strlen($new_password) >= 6) {
                    $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
                    $update_sql = "UPDATE users SET password = ? WHERE id = ?";
                    $update_stmt = $conn->prepare($update_sql);
                    $update_stmt->bind_param("si", $new_password_hash, $user_id);
                    
                    if ($update_stmt->execute()) {
                        $success_message = "Password changed successfully.";
                    } else {
                        $error_message = "Error updating password.";
                    }
                } else {
                    $error_message = "New password must be at least 6 characters.";
                }
            } else {
                $error_message = "New passwords do not match.";
            }
        } else {
            $error_message = "Incorrect current password.";
        }
    }
}

// Fetch current user data
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Loan Calculator System</title>
    <!-- Use same CSS file for toast if needed, or inline styles relative to dashboard -->
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

        /* Main Content reused from dashboard */
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
            padding: 40px 20px;
        }

        .settings-container {
            width: 100%;
            max-width: 800px;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 10px;
            padding: 40px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }

        .page-title {
            font-size: 32px;
            font-weight: 700;
            color: #000;
            margin-bottom: 30px;
            text-align: center;
            letter-spacing: 2px;
        }

        .section-title {
            font-size: 20px;
            font-weight: 700;
            color: #333;
            margin-bottom: 20px;
            border-bottom: 2px solid #ddd;
            padding-bottom: 10px;
            margin-top: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            font-size: 14px;
        }

        .form-input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
            font-family: inherit;
        }

        .btn {
            background-color: #000;
            color: #fff;
            border: none;
            padding: 12px 30px;
            border-radius: 25px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            text-transform: uppercase;
            transition: transform 0.2s;
        }

        .btn:hover {
            transform: translateY(-2px);
        }

        /* Messages */
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            font-weight: 600;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>

<?php 
// Ensure correct path to navigation since we are in customer folder
$active_page = 'settings';
include('../navigation.php'); 
?>

<div class="main-content">
    <div class="settings-container">
        <h1 class="page-title">ACCOUNT SETTINGS</h1>

        <?php if ($success_message): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        <?php if ($error_message): ?>
            <div class="alert alert-error"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <!-- Profile Update Form -->
        <h2 class="section-title">Edit Profile</h2>
        <form method="POST" action="">
            <div class="form-group">
                <label class="form-label">Username</label>
                <input type="text" class="form-input" value="<?php echo htmlspecialchars($user['username']); ?>" disabled style="background: #f9f9f9;">
            </div>
            
            <div class="form-group">
                <label class="form-label" for="name">Full Name</label>
                <input type="text" class="form-input" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="email">Email</label>
                <input type="email" class="form-input" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="phone">Phone</label>
                <input type="tel" class="form-input" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
            </div>

            <button type="submit" name="update_profile" class="btn">Save Changes</button>
        </form>

        <!-- Change Password Form -->
        <h2 class="section-title">Change Password</h2>
        <form method="POST" action="">
            <div class="form-group">
                <label class="form-label" for="current_password">Current Password</label>
                <input type="password" class="form-input" id="current_password" name="current_password" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="new_password">New Password</label>
                <input type="password" class="form-input" id="new_password" name="new_password" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="confirm_password">Confirm New Password</label>
                <input type="password" class="form-input" id="confirm_password" name="confirm_password" required>
            </div>

            <button type="submit" name="change_password" class="btn">Update Password</button>
        </form>
    </div>
</div>

</body>
</html>
