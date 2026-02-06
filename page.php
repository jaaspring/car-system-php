<?php
session_start();
require_once 'db_connection.php';

$slug = $_GET['slug'] ?? 'about';
$stmt = $conn->prepare("SELECT * FROM static_pages WHERE slug = ?");
$stmt->bind_param("s", $slug);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Page not found.");
}

$page = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?= htmlspecialchars($page['title']) ?></title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family:'Century Gothic', sans-serif; background: radial-gradient(circle, #f4d77e, #c89a3d); min-height:100vh; display:flex; flex-direction:column; }
.container { 
    max-width:1000px; 
    margin:40px auto; 
    background:#fff; 
    padding:40px; 
    border-radius:20px; 
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    flex: 1;
    width: 90%;
}
h1 { text-align:center; margin-bottom:30px; border-bottom: 2px solid #eee; padding-bottom: 10px; }
.content { line-height: 1.6; font-size: 16px; color: #333; }
</style>
</head>
<body>

<?php include('navigation.php'); ?>

<div class="container">
    <h1><?= htmlspecialchars($page['title']) ?></h1>
    <div class="content">
        <?= $page['content'] // Output raw HTML as it is from CMS ?>
    </div>
</div>

</body>
</html>
