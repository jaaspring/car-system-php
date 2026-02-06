<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}
require_once '../db_connection.php';

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$limit = 20;
$offset = ($page - 1) * $limit;

// Fetch Logs
$totalRes = $conn->query("SELECT COUNT(*) FROM audit_logs");
$totalLogs = $totalRes->fetch_row()[0];
$totalPages = ceil($totalLogs / $limit);

$sql = "SELECT a.*, u.username 
        FROM audit_logs a 
        LEFT JOIN users u ON a.user_id = u.id 
        ORDER BY a.created_at DESC 
        LIMIT ?, ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $offset, $limit);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Audit Logs</title>
<link rel="stylesheet" href="../assets/css/toast.css">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body {
    font-family: 'Century Gothic', sans-serif;
    background: radial-gradient(circle, #f4d77e, #c89a3d);
    min-height: 100vh;
}
.header { background: #000; padding: 20px 40px; display: flex; justify-content: space-between; align-items: center; }
.header-left { display: flex; align-items: center; gap: 40px; }
.logo-img img { height: 45px; }
.nav-menu { display: flex; gap: 30px; }
.nav-link { color: #fff; text-decoration: none; font-weight: 600; }
.nav-link:hover { opacity: .7; }
.logout-btn { background: #ff4500; color: #fff; padding: 10px 25px; border-radius: 20px; text-decoration: none; font-weight: bold; }

.container {
    max-width: 1200px;
    margin: 40px auto;
    background: #fff;
    padding: 30px;
    border-radius: 20px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.15);
}

h2 { text-align: center; margin-bottom: 20px; }

table { width: 100%; border-collapse: collapse; margin-top: 20px; }
th, td { padding: 12px; border: 1px solid #eee; text-align: left; }
th { background: #000; color: #fff; }
tr:nth-child(even) { background: #f9f9f9; }

.pagination {
    margin-top: 20px;
    display: flex;
    justify-content: center;
    gap: 10px;
}
.page-link {
    padding: 8px 12px;
    background: #eee;
    text-decoration: none;
    color: #333;
    border-radius: 5px;
}
.page-link.active {
    background: #000;
    color: #fff;
}
</style>
</head>
<body>

<?php include('../navigation.php'); ?>

<div class="container">
    <h2>System Audit Logs</h2>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Time</th>
                <th>User</th>
                <th>Action</th>
                <th>Details</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= $row['created_at'] ?></td>
                <td><?= htmlspecialchars($row['username'] ?? 'Unknown/System') ?></td>
                <td><strong><?= htmlspecialchars($row['action']) ?></strong></td>
                <td><?= htmlspecialchars($row['details']) ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <div class="pagination">
        <?php for($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?page=<?= $i ?>" class="page-link <?= $i == $page ? 'active' : '' ?>">
                <?= $i ?>
            </a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>

</div>
</body>
</html>
