<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit();
}
require_once '../db_connection.php';

$user_id = $_SESSION['user_id'];

// Fetch Wishlist Items
$sql = "SELECT c.*, w.id as wishlist_id 
        FROM user_wishlist w 
        JOIN car_details c ON w.car_id = c.id 
        WHERE w.user_id = ? 
        ORDER BY w.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Wishlist</title>
<link rel="stylesheet" href="../assets/css/toast.css">

<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body {
    font-family: 'Century Gothic', sans-serif;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    background: radial-gradient(circle, #f4d77e, #c89a3d);
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
    padding: 0 20px;
    flex: 1;
}

h1 { text-align: center; margin-bottom: 40px; font-size: 36px; }

.wishlist-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 30px;
}

.car-card {
    background: #fff;
    border-radius: 20px;
    padding: 20px;
    text-align: center;
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    position: relative;
    transition: transform 0.3s;
}
.car-card:hover { transform: translateY(-5px); }

.car-card img {
    width: 100%;
    height: 180px;
    object-fit: contain;
    margin-bottom: 15px;
}

.car-title { font-size: 20px; font-weight: bold; margin-bottom: 5px; }
.car-variant { color: #666; font-size: 14px; margin-bottom: 10px; }
.car-price { color: #c0392b; font-weight: bold; font-size: 18px; margin-bottom: 15px; }

.remove-btn {
    position: absolute;
    top: 15px;
    right: 15px;
    background: rgba(0,0,0,0.1);
    border-radius: 50%;
    width: 30px;
    height: 30px;
    display: flex; /* Flexbox needed for centering */
    align-items: center;
    justify-content: center;
    cursor: pointer;
    color: #c0392b;
    transition: background 0.2s;
}
.remove-btn:hover { background: #c0392b; color: #fff; }

.btn {
    display: inline-block;
    padding: 10px 20px;
    background: #000;
    color: #fff;
    border-radius: 20px;
    text-decoration: none;
    font-size: 14px;
    font-weight: bold;
}
.btn:hover { opacity: 0.8; }

.back-btn-container { text-align: center; margin-top: 50px; }
.back-btn { background: #000; color: #fff; padding: 12px 30px; border-radius: 25px; text-decoration: none; font-weight: bold; }

@media(max-width:900px) { .wishlist-grid { grid-template-columns: repeat(2,1fr); } }
@media(max-width:600px) { .wishlist-grid { grid-template-columns: 1fr; } }
</style>
</head>
<body>

<?php include('../widget/navigation.php'); ?>

<div class="container">
    <h1>My Wishlist</h1>

    <?php if ($result->num_rows === 0): ?>
        <div style="text-align:center; font-size:18px; color:#333;">
            You haven't saved any cars yet. <br><br>
            <a href="models.php" class="btn">Browse Cars</a>
        </div>
    <?php else: ?>
        <div class="wishlist-grid">
            <?php while($row = $result->fetch_assoc()): ?>
                <div class="car-card" id="card-<?= $row['id'] ?>">
                    <div class="remove-btn" onclick="removeItem(<?= $row['id'] ?>)">
                         ×
                    </div>
                    <img src="../display_image.php?id=<?= $row['id'] ?>" onerror="this.src='../Images/proton.png'">
                    <div class="car-title"><?= htmlspecialchars($row['model']) ?></div>
                    <div class="car-variant"><?= htmlspecialchars($row['variant']) ?></div>
                    <div class="car-price"><?= htmlspecialchars($row['price']) ?></div>
                    <a href="car_details.php?model=<?= urlencode($row['model']) ?>" class="btn">View Details</a>
                </div>
            <?php endwhile; ?>
        </div>
    <?php endif; ?>
    
    <div class="back-btn-container">
        <a href="user_dashboard.php" class="back-btn">Back to Dashboard</a>
    </div>
</div>

<script>
function removeItem(carId) {
    if (!confirm('Remove this car from your wishlist?')) return;

    fetch('../ajax/toggle_wishlist.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ car_id: carId })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success && data.action === 'removed') {
            const card = document.getElementById('card-' + carId);
            card.style.opacity = '0';
            setTimeout(() => card.remove(), 300);
            showToast("Removed from wishlist", "success");
        } else {
            showToast("Failed to remove", "error");
        }
    })
    .catch(err => console.error(err));
}
</script>
<script src="../assets/js/toast.js"></script>

</body>
</html>
