<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}
require_once '../db_connection.php';

// Handle Reply
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['review_id'], $_POST['reply'])) {
    $review_id = intval($_POST['review_id']);
    $reply = trim($_POST['reply']);

    $stmt = $conn->prepare("UPDATE test_drive_reviews SET admin_reply = ? WHERE id = ?");
    $stmt->bind_param("si", $reply, $review_id);
    
    if ($stmt->execute()) {
        require_once 'log_helper.php';
        logAction(null, 'Reply Review', "Replied to review ID: $review_id");
        header("Location: manage_reviews.php?toast_msg=Reply+Sent&toast_type=success");
        exit();
    }
}

// Fetch Reviews
$sql = "SELECT r.*, t.car_model_variant, u.username 
        FROM test_drive_reviews r
        JOIN test_drive t ON r.test_drive_id = t.id
        JOIN users u ON t.user_id = u.id
        ORDER BY r.created_at DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Reviews</title>
<link rel="stylesheet" href="../assets/css/toast.css">
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family:'Century Gothic', sans-serif; background: radial-gradient(circle, #f4d77e, #c89a3d); min-height:100vh; }
.container { max-width:1000px; margin:40px auto; background:#fff; padding:30px; border-radius:20px; }
h2 { text-align:center; margin-bottom:30px; }
.review-card { border: 1px solid #eee; padding: 20px; border-radius: 10px; margin-bottom: 20px; background: #fafafa; }
.review-header { display: flex; justify-content: space-between; margin-bottom: 10px; }
.user-info { font-weight: bold; }
.rating { color: #f1c40f; }
.review-text { margin-bottom: 15px; font-style: italic; }
.admin-reply-box { background: #e8f6f3; padding: 15px; border-radius: 8px; border-left: 4px solid #2ecc71; margin-top: 10px; }
.reply-form textarea { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; margin-bottom: 10px; }
.btn { padding: 8px 16px; background: #000; color: #fff; border: none; border-radius: 15px; cursor: pointer; }
</style>
</head>
<body>
<?php include('../navigation.php'); ?>
<div class="container">
    <h2>Customer Reviews</h2>
    <?php if (isset($_GET['toast_msg'])): ?>
        <div style="background:#2ecc71; color:#fff; padding:10px; text-align:center; border-radius:5px; margin-bottom:20px;">
            <?= htmlspecialchars($_GET['toast_msg']) ?>
        </div>
    <?php endif; ?>

    <?php while($row = $result->fetch_assoc()): ?>
    <div class="review-card">
        <div class="review-header">
            <span class="user-info"><?= htmlspecialchars($row['username']) ?> - <?= htmlspecialchars($row['car_model_variant']) ?></span>
            <span class="rating"><?= str_repeat('★', $row['rating']) ?></span>
        </div>
        <div class="review-text">"<?= htmlspecialchars($row['comment']) ?>"</div>
        <?php if (!empty($row['admin_reply'])): ?>
            <div class="admin-reply-box"><strong>Admin Reply:</strong> <?= htmlspecialchars($row['admin_reply']) ?></div>
        <?php else: ?>
            <form method="POST" class="reply-form">
                <input type="hidden" name="review_id" value="<?= $row['id'] ?>">
                <textarea name="reply" rows="2" placeholder="Write a reply..." required></textarea>
                <button type="submit" class="btn">Send Reply</button>
            </form>
        <?php endif; ?>
    </div>
    <?php endwhile; ?>
</div>
<script src="../assets/js/toast.js"></script>
</body>
</html>
