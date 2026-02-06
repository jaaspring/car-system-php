<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}
require_once '../db_connection.php';

// Handle Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'], $_POST['content'])) {
    $id = intval($_POST['id']);
    $content = $_POST['content'];
    $title = $_POST['title'];

    $stmt = $conn->prepare("UPDATE static_pages SET content = ?, title = ? WHERE id = ?");
    $stmt->bind_param("ssi", $content, $title, $id);
    
    if ($stmt->execute()) {
        require_once 'log_helper.php';
        logAction(null, 'Update Page', "Updated page ID: $id ($title)");
        header("Location: manage_pages.php?toast_msg=Page+Updated&toast_type=success");
        exit();
    }
}

// Fetch Pages
$result = $conn->query("SELECT * FROM static_pages");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Pages</title>
<link rel="stylesheet" href="../assets/css/toast.css">
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family:'Century Gothic', sans-serif; background: radial-gradient(circle, #f4d77e, #c89a3d); min-height:100vh; }
.container { max-width:1000px; margin:40px auto; background:#fff; padding:30px; border-radius:20px; }
h2 { text-align:center; margin-bottom:30px; }
.page-card { border: 1px solid #ddd; padding: 20px; margin-bottom: 20px; border-radius: 10px; background: #fafafa; }
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }
textarea { width: 100%; height: 150px; padding: 10px; border: 1px solid #ccc; border-radius: 5px; font-family: inherit; }
.btn { padding: 10px 20px; background: #000; color: #fff; border: none; border-radius: 20px; cursor: pointer; font-weight: bold; }
input[type="text"] { padding: 8px; border: 1px solid #ccc; border-radius: 5px; width: 300px; font-weight: bold; }
</style>
</head>
<body>
<?php include('../navigation.php'); ?>

<div class="container">
    <h2>Manage Static Pages</h2>
    <?php if (isset($_GET['toast_msg'])): ?>
        <div style="background:#2ecc71; color:#fff; padding:10px; text-align:center; border-radius:5px; margin-bottom:20px;">
            <?= htmlspecialchars($_GET['toast_msg']) ?>
        </div>
    <?php endif; ?>

    <?php while($row = $result->fetch_assoc()): ?>
    <div class="page-card">
        <form method="POST">
            <input type="hidden" name="id" value="<?= $row['id'] ?>">
            <div class="page-header">
                <div>
                    <label>Page Title:</label>
                    <input type="text" name="title" value="<?= htmlspecialchars($row['title']) ?>" required>
                    <small>(Slug: <?= htmlspecialchars($row['slug']) ?>)</small>
                </div>
                <button type="submit" class="btn">Save Changes</button>
            </div>
            <textarea name="content" required><?= htmlspecialchars($row['content']) ?></textarea>
            <div style="margin-top:10px; font-size:12px; color:#666;">
                Supports HTML. Be careful with tags.
            </div>
        </form>
    </div>
    <?php endwhile; ?>
</div>

<script src="../assets/js/toast.js"></script>
</body>
</html>
