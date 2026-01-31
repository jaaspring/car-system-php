<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit();
}

require_once '../db_connection.php';

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
        header("Location: manage_cars.php?toast_msg=" . urlencode("Successfully Deleted") . "&toast_type=success");
        exit();
    } else {
        header("Location: manage_cars.php?toast_msg=" . urlencode("Failed to delete car") . "&toast_type=error");
        exit();
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
    
    // Handle Image Upload
    $imageData = null;
    if (isset($_FILES['car_image']) && $_FILES['car_image']['error'] === UPLOAD_ERR_OK) {
        $imageData = file_get_contents($_FILES['car_image']['tmp_name']);
    }

    if (
        empty($model) || empty($variant) || empty($price) ||
        empty($engine) || empty($transmission) ||
        empty($chassis) || empty($performance) ||
        empty($paint_type)
    ) {
        $error = "Please fill all fields.";
    } else {

        if ($id) {
            // Update
            if ($imageData !== null) {
                // Update with new image
                $stmt = $conn->prepare(
                    "UPDATE car_details 
                     SET model=?, variant=?, price=?, engine=?, transmission=?, chassis=?, performance=?, paint_type=?, image=? 
                     WHERE id=?"
                );
                $null = null;
                $stmt->bind_param(
                    "ssssssssbi",
                    $model, $variant, $price, $engine, $transmission,
                    $chassis, $performance, $paint_type, $null, $id
                );
                $stmt->send_long_data(8, $imageData);
            } else {
                // Update without changing image
                $stmt = $conn->prepare(
                    "UPDATE car_details 
                     SET model=?, variant=?, price=?, engine=?, transmission=?, chassis=?, performance=?, paint_type=? 
                     WHERE id=?"
                );
                $stmt->bind_param(
                    "ssssssssi",
                    $model, $variant, $price, $engine, $transmission,
                    $chassis, $performance, $paint_type, $id
                );
            }
        } else {
            // Insert
            $stmt = $conn->prepare(
                "INSERT INTO car_details 
                 (model, variant, price, engine, transmission, chassis, performance, paint_type, image)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );
            $null = null;
            $stmt->bind_param(
                "ssssssssb",
                $model, $variant, $price, $engine, $transmission,
                $chassis, $performance, $paint_type, $null
            );
            if ($imageData !== null) {
                $stmt->send_long_data(8, $imageData);
            }
        }

        if ($stmt->execute()) {
            $message = $id ? "Successfully Update" : "Successfully Add";
            header("Location: manage_cars.php?toast_msg=" . urlencode($message) . "&toast_type=success");
            exit();
        } else {
            header("Location: manage_cars.php?toast_msg=" . urlencode("Database error: " . $stmt->error) . "&toast_type=error");
            exit();
        }
        $stmt->close();
    }
}

/* =========================
   FETCH CARS
   ========================= */
$sort = $_GET['sort'] ?? 'latest';
$orderBy = "ORDER BY id DESC"; // Default

switch ($sort) {
    case 'oldest': $orderBy = "ORDER BY id ASC"; break;
    case 'az':     $orderBy = "ORDER BY model ASC"; break;
    case 'za':     $orderBy = "ORDER BY model DESC"; break;
    default:       $orderBy = "ORDER BY id DESC"; break;
}

$result = $conn->query("SELECT * FROM car_details $orderBy");

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
<link rel="stylesheet" href="../toast.css">

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
    display: inline-block;
    margin: 0 4px;
    padding: 6px 16px;
    text-decoration: none;
    font-weight: bold;
    font-size: 13px;
    border-radius: 18px;
    color: #fff;
    transition: all 0.2s ease;
}
.actions .edit { 
    background: #2980b9;
}
.actions .edit:hover { 
    background: #1f6391;
    transform: scale(1.05);
}
.actions .delete { 
    background: #e74c3c;
}
.actions .delete:hover { 
    background: #c0392b;
    transform: scale(1.05);
}

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

.form-grid label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
    font-size: 14px;
}
</style>
</head>

<body>

<?php include('../navigation.php'); ?>

<!-- ===== MAIN CONTENT ===== -->
<div class="main-content">
<div class="container">

<h2>MANAGE CARS</h2>

<?php if (isset($_GET['toast_msg'])): ?>
    <div class="alert <?= $_GET['toast_type'] === 'success' ? 'success' : 'error' ?>">
        <?= htmlspecialchars($_GET['toast_msg']) ?>
    </div>
<?php endif; ?>

<?php 
// DETERMINE VIEW MODE: 'form' or 'list'
$viewMode = 'list';
if (isset($_GET['add']) || $editCar) {
    $viewMode = 'form';
}
?>

<?php if ($viewMode === 'list'): ?>
    <!-- LIST VIEW -->
    
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
        <!-- SORT FILTER -->
        <form method="GET" style="margin: 0; display: flex; align-items: center; gap: 10px;">
            <label style="margin:0; font-weight:bold;">Sort By:</label>
            <select name="sort" onchange="this.form.submit()" style="padding: 8px; border-radius: 5px; border: 1px solid #ccc;">
                <option value="latest" <?= ($_GET['sort'] ?? '') == 'latest' ? 'selected' : '' ?>>Latest</option>
                <option value="oldest" <?= ($_GET['sort'] ?? '') == 'oldest' ? 'selected' : '' ?>>Oldest</option>
                <option value="az" <?= ($_GET['sort'] ?? '') == 'az' ? 'selected' : '' ?>>A-Z (Model)</option>
                <option value="za" <?= ($_GET['sort'] ?? '') == 'za' ? 'selected' : '' ?>>Z-A (Model)</option>
            </select>
        </form>

        <a href="?add=1" class="btn green" style="text-decoration:none;">+ Add New Car</a>
    </div>

    <table>
    <tr>
        <th>ID</th>
        <th>Image</th>
        <th>Model</th>
        <th>Variant</th>
        <th>Price</th>
        <th>Engine</th>
        <th>Actions</th>
    </tr>

    <?php while ($row = $result->fetch_assoc()): ?>
    <tr>
        <td><?= $row['id'] ?></td>
        <td>
            <?php if (!empty($row['image'])): ?>
                <img src="../display_image.php?id=<?= $row['id'] ?>" style="width:100px; height:60px; object-fit:cover;">
            <?php else: ?>
                <span style="color:#888;">No Image</span>
            <?php endif; ?>
        </td>
        <td><?= htmlspecialchars($row['model']) ?></td>
        <td><?= htmlspecialchars($row['variant']) ?></td>
        <td><?= htmlspecialchars($row['price']) ?></td>
        <td><?= htmlspecialchars($row['engine']) ?></td>
        <td class="actions">
            <a class="edit" href="#" onclick="confirmEdit(<?= $row['id'] ?>)">Edit</a>
            <a class="delete" href="#" onclick="confirmDelete(<?= $row['id'] ?>)">Delete</a>
        </td>
    </tr>
    <?php endwhile; ?>
    </table>

<?php else: ?>
    <!-- FORM VIEW -->
    
    <form method="post" enctype="multipart/form-data" id="carForm">
    <h3><?= $editCar ? "Edit Car" : "Add New Car" ?></h3>

    <input type="hidden" name="id" value="<?= $editCar['id'] ?? '' ?>">

    <div class="form-grid">
    
    <div>
        <label>Model</label>
        <input name="model" placeholder="e.g. X50" value="<?= $editCar['model'] ?? '' ?>" required>
    </div>

    <div>
        <label>Variant</label>
        <input name="variant" placeholder="e.g. 1.5T Standard" value="<?= $editCar['variant'] ?? '' ?>" required>
    </div>

    <div>
        <label>Price</label>
        <input name="price" placeholder="e.g. RM 86,300" value="<?= $editCar['price'] ?? '' ?>" required>
    </div>

    <div>
        <label>Engine</label>
        <input name="engine" placeholder="e.g. 1.5L Turbocharged" value="<?= $editCar['engine'] ?? '' ?>" required>
    </div>

    <div>
        <label>Transmission</label>
        <input name="transmission" placeholder="e.g. 7-Speed DCT" value="<?= $editCar['transmission'] ?? '' ?>" required>
    </div>

    <div>
        <label>Chassis</label>
        <input name="chassis" placeholder="e.g. SUV" value="<?= $editCar['chassis'] ?? '' ?>" required>
    </div>

    <div>
        <label>Performance</label>
        <input name="performance" placeholder="e.g. Eco Mode" value="<?= $editCar['performance'] ?? '' ?>" required>
    </div>

    <div>
        <label>Paint Type</label>
        <input name="paint_type" placeholder="e.g. Solid" value="<?= $editCar['paint_type'] ?? 'Solid' ?>" required>
    </div>

    <!-- Image Upload Field -->
    <div style="margin-top: 6px;">
        <label>Car Image</label>
        <?php if ($editCar && !empty($editCar['image'])): ?>
            <div style="margin-bottom: 10px;">
                <img src="../display_image.php?id=<?= $editCar['id'] ?>" 
                     style="max-width: 300px; max-height: 200px; border-radius: 8px; border: 1px solid #ccc;">
            </div>
            <p style="font-size:12px; margin-bottom:5px;">Change Image:</p>
        <?php endif; ?>
        <input type="file" name="car_image" accept="image/*" style="padding: 5px;">
    </div>

    </div>

    <br>
    <?php if ($editCar): ?>
        <button type="button" class="btn green" onclick="confirmUpdate()"><?= "Update Car" ?></button>
    <?php else: ?>
        <button type="submit" class="btn green"><?= "Add Car" ?></button>
    <?php endif; ?>
    <!-- Back button now cancels and returns to list view -->
    <a href="manage_cars.php" class="btn">Back</a>
    </form>

<?php endif; ?>

</div>
</div>

<?php include('../confirm_modal.php'); ?>

<script src="../toast.js"></script>
<script>
function confirmEdit(carId) {
    showConfirm('Are you sure to edit?', function() {
        window.location.href = '?edit=' + carId;
    });
}

function confirmUpdate() {
    showConfirm('Are you sure to update?', function() {
        document.getElementById('carForm').submit();
    });
}

function confirmDelete(carId) {
    showConfirm('Are you sure you want to delete', function() {
        window.location.href = '?delete=' + carId;
    });
}
</script>

</body>
</html>
