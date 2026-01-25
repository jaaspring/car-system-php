<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

require_once __DIR__ . '/db_connection.php';

$error = '';
$success = '';

/* =========================
   DELETE CAR
   ========================= */
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);

    $stmt = $conn->prepare("DELETE FROM car_details WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $success = "Car deleted successfully.";
    } else {
        $error = "Failed to delete car.";
    }
    $stmt->close();
}

/* =========================
   ADD / UPDATE CAR
   ========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id           = $_POST['id'] ?? null;
    $model        = trim($_POST['model']);
    $variant      = trim($_POST['variant']);
    $price        = trim($_POST['price']);
    $engine       = trim($_POST['engine']);
    $transmission = trim($_POST['transmission']);
    $chassis      = trim($_POST['chassis']);
    $performance  = trim($_POST['performance']);
    $paint_type   = trim($_POST['paint_type']);
    $image_path   = trim($_POST['image_path']);

    if (
        empty($model) || empty($variant) || empty($price) ||
        empty($engine) || empty($transmission) ||
        empty($chassis) || empty($performance) ||
        empty($paint_type)
    ) {
        $error = "Please fill all fields.";
    } else {

        if ($id) {
            $stmt = $conn->prepare(
                "UPDATE car_details 
                 SET model=?, variant=?, price=?, engine=?, transmission=?, chassis=?, performance=?, paint_type=?, image=? 
                 WHERE id=?"
            );
            $stmt->bind_param(
                "sssssssssi",
                $model, $variant, $price, $engine, $transmission,
                $chassis, $performance, $paint_type, $image_path, $id
            );
        } else {
            $stmt = $conn->prepare(
                "INSERT INTO car_details 
                 (model, variant, price, engine, transmission, chassis, performance, paint_type, image)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->bind_param(
                "sssssssss",
                $model, $variant, $price, $engine, $transmission,
                $chassis, $performance, $paint_type, $image_path
            );
        }

        if ($stmt->execute()) {
            $success = $id ? "Car updated successfully." : "New car added successfully.";
        } else {
            $error = "Database error.";
        }
        $stmt->close();
    }
}

/* =========================
   FETCH CARS
   ========================= */
$result = $conn->query("SELECT * FROM car_details ORDER BY id DESC");

/* =========================
   FETCH CAR FOR EDIT
   ========================= */
$editCar = null;
if (isset($_GET['edit'])) {
    $editId = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT * FROM car_details WHERE id=?");
    $stmt->bind_param("i", $editId);
    $stmt->execute();
    $editCar = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Manage Cars</title>

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Century Gothic', sans-serif;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
}

/* ===== HEADER (ADMIN – EXACT COPY) ===== */
.header {
    background-color: #000;
    padding: 20px 40px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.header-left {
    display: flex;
    align-items: center;
    gap: 30px;
}

.logo-img {
    width: 150px;
    height: 40px;
}

.logo-img img {
    width: 100%;
    height: 100%;
    object-fit: contain;
}

.nav-menu {
    display: flex;
    gap: 35px;
}

.nav-link {
    color: #fff;
    text-decoration: none;
    font-size: 16px;
    font-weight: 600;
}

.nav-link:hover {
    opacity: 0.7;
}

.logout-btn {
    background-color: #ff4500;
    color: #fff;
    padding: 10px 25px;
    border-radius: 20px;
    font-weight: 700;
    text-decoration: none;
}

/* ===== MAIN CONTENT ===== */
.main-content {
    flex: 1;
    background: radial-gradient(circle, #f4d77e, #c89a3d);
    padding: 40px;
}

.container {
    max-width: 1200px;
    margin: auto;
    background: #fff;
    padding: 30px;
    border-radius: 25px;
}

h2 { text-align: center; }

.alert {
    padding: 12px;
    margin-bottom: 15px;
    border-radius: 6px;
    font-weight: bold;
}
.alert.error { background:#e74c3c; color:#fff; }
.alert.success { background:#2ecc71; color:#fff; }

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}
th, td {
    padding: 10px;
    border: 1px solid #ddd;
    text-align: center;
}
th {
    background: #000;
    color: #fff;
}

.actions a {
    margin: 0 5px;
    font-weight: bold;
    text-decoration: none;
}
.actions .edit { color: #2980b9; }
.actions .delete { color: #c0392b; }

form {
    margin-top: 30px;
}

input {
    width: 100%;
    padding: 8px;
    margin-top: 6px;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(2,1fr);
    gap: 15px;
}

.btn {
    padding: 10px 22px;
    border-radius: 20px;
    border: none;
    background: #000;
    color: #fff;
    font-weight: bold;
    cursor: pointer;
    text-decoration: none;
}
.btn.green { background:#2ecc71; }
.btn.exit { background:#c0392b; }
</style>
</head>

<body>

<!-- ===== HEADER ===== -->
<div class="header">
    <div class="header-left">
        <div class="logo-img">
            <img src="Images/proton.png" alt="Proton">
        </div>
        <nav class="nav-menu">
            <a href="admin_dashboard.php" class="nav-link">Dashboard</a>
            <a href="manage_cars.php" class="nav-link">Manage Cars</a>
            <a href="manage_users.php" class="nav-link">Manage Users</a>
            <a href="manage_appointments.php" class="nav-link">Manage Appointments</a>
            <a href="admin_view_reviews.php" class="nav-link">Manage Reviews</a>
        </nav>
    </div>
    <a href="logout.php" class="logout-btn">Logout</a>
</div>

<div class="main-content">
<div class="container">

<h2>MANAGE CARS</h2>

<?php if ($error): ?><div class="alert error"><?= $error ?></div><?php endif; ?>
<?php if ($success): ?><div class="alert success"><?= $success ?></div><?php endif; ?>

<table>
<tr>
    <th>ID</th>
    <th>Model</th>
    <th>Variant</th>
    <th>Price</th>
    <th>Engine</th>
    <th>Transmission</th>
    <th>Chassis</th>
    <th>Performance</th>
    <th>Paint</th>
    <th>Actions</th>
</tr>

<?php while ($row = $result->fetch_assoc()): ?>
<tr>
    <td><?= $row['id'] ?></td>
    <td><?= htmlspecialchars($row['model']) ?></td>
    <td><?= htmlspecialchars($row['variant']) ?></td>
    <td><?= htmlspecialchars($row['price']) ?></td>
    <td><?= htmlspecialchars($row['engine']) ?></td>
    <td><?= htmlspecialchars($row['transmission']) ?></td>
    <td><?= htmlspecialchars($row['chassis']) ?></td>
    <td><?= htmlspecialchars($row['performance']) ?></td>
    <td><?= htmlspecialchars($row['paint_type']) ?></td>
    <td class="actions">
        <a class="edit" href="?edit=<?= $row['id'] ?>">Edit</a>
        <a class="delete" href="?delete=<?= $row['id'] ?>" onclick="return confirm('Delete this car?')">Delete</a>
    </td>
</tr>
<?php endwhile; ?>
</table>

<form method="post">
<h3><?= $editCar ? "Edit Car" : "Add New Car" ?></h3>

<input type="hidden" name="id" value="<?= $editCar['id'] ?? '' ?>">

<div class="form-grid">
<input name="model" placeholder="Model" value="<?= $editCar['model'] ?? '' ?>">
<input name="variant" placeholder="Variant" value="<?= $editCar['variant'] ?? '' ?>">
<input name="price" placeholder="Price" value="<?= $editCar['price'] ?? '' ?>">
<input name="engine" placeholder="Engine" value="<?= $editCar['engine'] ?? '' ?>">
<input name="transmission" placeholder="Transmission" value="<?= $editCar['transmission'] ?? '' ?>">
<input name="chassis" placeholder="Chassis" value="<?= $editCar['chassis'] ?? '' ?>">
<input name="performance" placeholder="Performance" value="<?= $editCar['performance'] ?? '' ?>">
<input name="paint_type" placeholder="Paint Type" value="<?= $editCar['paint_type'] ?? 'Solid' ?>">
<input name="image_path" placeholder="Image Path (optional)">
</div>

<br>
<button class="btn green"><?= $editCar ? "Update Car" : "Add Car" ?></button>
<a href="admin_dashboard.php" class="btn">Back</a>
<a href="logout.php" class="btn exit">Exit</a>
</form>

</div>
</div>

</body>
</html>
