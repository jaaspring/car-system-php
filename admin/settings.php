<?php
session_start();
include('../db_connection.php');

// Check if user is logged in
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
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
        
        // Handle File Upload
        if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $filename = $_FILES['profile_pic']['name'];
            $filetype = $_FILES['profile_pic']['type'];
            $filesize = $_FILES['profile_pic']['size'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            
            if (in_array($ext, $allowed)) {
                 if ($filesize < 2 * 1024 * 1024) {
                     $imgData = file_get_contents($_FILES['profile_pic']['tmp_name']);
                     $sql = "UPDATE users SET name = ?, email = ?, phone = ?, profile_pic = ? WHERE id = ?";
                     $stmt = $conn->prepare($sql);
                     $stmt->bind_param("ssssi", $name, $email, $phone, $imgData, $user_id);
                 } else {
                     $error_message = "File size exceeds 2MB limit.";
                 }
            } else {
                $error_message = "Invalid file type. Allowed: JPG, PNG, GIF.";
            }
        } else {
            $sql = "UPDATE users SET name = ?, email = ?, phone = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssi", $name, $email, $phone, $user_id);
        }

        if (empty($error_message)) {
            if (isset($stmt) && $stmt->execute()) {
                $_SESSION['name'] = $name;
                $success_message = "Profile updated successfully.";
            } else {
                $error_message = "Error updating profile: " . $conn->error;
            }
        }
        
    } elseif (isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

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
    <title>Admin Settings - Loan Calculator System</title>
    <!-- Share same CSS or inline -->
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family: 'Century Gothic', sans-serif; min-height: 100vh; display: flex; flex-direction: column; }
        .main-content {
            flex: 1;
            background: radial-gradient(ellipse at center, #f4d77e 0%, #e6c770 25%, #d4a747 50%, #c89a3d 75%, #9d7730 100%);
            display: flex; flex-direction: column; align-items: center; padding: 40px 20px;
        }
        .settings-container {
            width: 100%; max-width: 800px; background: rgba(255, 255, 255, 0.9);
            border-radius: 10px; padding: 40px; box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        .page-title { font-size: 32px; font-weight: 700; color: #000; margin-bottom: 30px; text-align: center; }
        .section-title { font-size: 20px; font-weight: 700; color: #333; margin-bottom: 20px; border-bottom: 2px solid #ddd; padding-bottom: 10px; margin-top: 30px; }
        .form-group { margin-bottom: 20px; position: relative; }
        .form-label { display: block; margin-bottom: 8px; font-weight: 600; font-size: 14px; }
        .form-input { width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 4px; font-size: 16px; font-family: inherit; }
        .btn { background-color: #000; color: #fff; border: none; padding: 12px 30px; border-radius: 25px; font-size: 14px; font-weight: 700; cursor: pointer; text-transform: uppercase; transition: transform 0.2s; }
        .btn:hover { transform: translateY(-2px); }
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 4px; font-weight: 600; }
        .alert-success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .profile-preview { width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 3px solid #000; margin-bottom: 15px; display: block; }
        .file-input { margin-top: 5px; }
        .password-toggle { position: absolute; right: 15px; top: 40px; cursor: pointer; font-size: 20px; }
    </style>
</head>
<body>

<?php 
$active_page = 'settings';
include('../navigation.php'); 
?>

<div class="main-content">
    <div class="settings-container">
        <h1 class="page-title">ADMIN SETTINGS</h1>

        <?php if ($success_message): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        <?php if ($error_message): ?>
            <div class="alert alert-error"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <h2 class="section-title">Edit Profile</h2>
        <form method="POST" action="" enctype="multipart/form-data">
            <div class="form-group" style="text-align: center;">
                <img src="../display_profile_pic.php?id=<?= $user['id'] ?>&t=<?= time() ?>" alt="Profile" class="profile-preview" style="margin: 0 auto 15px;">
                <label class="form-label" for="profile_pic">Change Profile Picture</label>
                <input type="file" name="profile_pic" id="profile_pic" class="file-input" accept="image/*">
            </div>

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

        <h2 class="section-title">Change Password</h2>
        <form method="POST" action="">
            <div class="form-group">
                <label class="form-label" for="current_password">Current Password</label>
                <input type="password" class="form-input" id="current_password" name="current_password" required>
                <span class="password-toggle" id="toggleCurrent" onclick="togglePassword('current_password', 'toggleCurrent')">👁️</span>
            </div>

            <div class="form-group">
                <label class="form-label" for="new_password">New Password</label>
                <input type="password" class="form-input" id="new_password" name="new_password" required>
                <span class="password-toggle" id="toggleNew" onclick="togglePassword('new_password', 'toggleNew')">👁️</span>
            </div>

            <div class="form-group">
                <label class="form-label" for="confirm_password">Confirm New Password</label>
                <input type="password" class="form-input" id="confirm_password" name="confirm_password" required>
                <span class="password-toggle" id="toggleConfirm" onclick="togglePassword('confirm_password', 'toggleConfirm')">👁️</span>
            </div>

            <button type="submit" name="change_password" class="btn">Update Password</button>
        </form>
    </div>
</div>

<script src="../assets/js/password_toggle.js"></script>
</body>
</html>
