<?php
session_start();

if (!isset($_SESSION['username'], $_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

require_once '../db_connection.php';

/* =========================
   DELETE USER
   ========================= */
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: manage_users.php?toast_msg=" . urlencode("Successfully Deleted") . "&toast_type=success");
    exit();
}

/* =========================
   ADD / UPDATE USER
   ========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id       = $_POST['id'] ?? null;
    $username = trim($_POST['username']);
    $name     = trim($_POST['name']);
    $email    = trim($_POST['email']);
    $phone    = trim($_POST['phone']);
    $role     = $_POST['role'];
    $password = $_POST['password'];

    if ($id) {
        // UPDATE
        // Check if password field is filled (if empty, keep old password)
        if (!empty($password)) {
             // Hash password if needed. 
             // Note: Original code didn't seem to have hashing in the INSERT based on the brief, 
             // but 'users' dump showed hashes. Assuming simple text or existing hash logic.
             // Looking at dump, they look like bcrypt ($2y$10$).
             // We should hash it if it's a new password.
             $hashed = password_hash($password, PASSWORD_DEFAULT);
             $stmt = $conn->prepare("UPDATE users SET username=?, name=?, email=?, phone=?, role=?, password=? WHERE id=?");
             $stmt->bind_param("ssssssi", $username, $name, $email, $phone, $role, $hashed, $id);
        } else {
             // Update without password
             $stmt = $conn->prepare("UPDATE users SET username=?, name=?, email=?, phone=?, role=? WHERE id=?");
             $stmt->bind_param("sssssi", $username, $name, $email, $phone, $role, $id);
        }
    } else {
        // INSERT
        // Default to hashing new passwords
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare(
            "INSERT INTO users (username, name, email, phone, password, role)
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param(
            "ssssss",
            $username,
            $name,
            $email,
            $phone,
            $hashed,
            $role
        );
    }
    
    if ($stmt->execute()) {
        // Success
        $message = $id ? "Successfully Update" : "Successfully Add";
        header("Location: manage_users.php?toast_msg=" . urlencode($message) . "&toast_type=success");
        exit();
    }
    $stmt->close();
}

/* =========================
   FETCH USERS
   ========================= */
$sort = $_GET['sort'] ?? 'az';
$orderBy = "ORDER BY username ASC"; // Default

switch ($sort) {
    case 'za':   $orderBy = "ORDER BY username DESC"; break;
    case 'role': $orderBy = "ORDER BY role ASC, username ASC"; break;
    default:     $orderBy = "ORDER BY username ASC"; break;
}

$result = $conn->query(
    "SELECT id, username, name, email, phone, role FROM users $orderBy"
);

/* =========================
   FETCH USER FOR EDIT
   ========================= */
$editUser = null;
if (isset($_GET['edit'])) {
    $editId = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT * FROM users WHERE id=?");
    $stmt->bind_param("i", $editId);
    $stmt->execute();
    $editUser = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Users</title>
<link rel="stylesheet" href="../toast.css">

<!-- Keeping existing CSS -->
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }

body {
    font-family: 'Century Gothic', sans-serif;
    background: radial-gradient(
        ellipse at center,
        #f4d77e 0%,
        #e6c770 30%,
        #d4a747 60%,
        #c89a3d 80%,
        #9d7730 100%
    );
    min-height: 100vh;
}

/* ===== ADMIN HEADER (EXACT MATCH) ===== */
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
    align-items: center;
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
    font-weight: bold;
    text-decoration: none;
}

.logout-btn:hover {
    background-color: #e63e00;
}

/* ===== CONTAINER ===== */
.container {
    max-width: 1100px;
    margin: 60px auto;
    background: #fff;
    padding: 30px;
    border-radius: 22px;
    box-shadow: 0 20px 40px rgba(0,0,0,.25);
}

h1 {
    text-align: center;
    margin-bottom: 30px;
}

/* ===== BUTTONS ===== */
.btn {
    padding: 10px 22px;
    border-radius: 22px;
    border: none;
    background: #000;
    color: #fff;
    font-weight: bold;
    cursor: pointer;
    text-decoration: none;
}

.btn.green { background: #2ecc71; }
.btn.red   { background: #c0392b; }

/* ===== TABLE ===== */
table {
    width: 100%;
    border-collapse: collapse;
    background: #fff;
    margin-top: 15px;
}

th, td {
    padding: 12px;
    border: 1px solid #ddd;
    text-align: center;
    font-size: 14px;
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

/* ===== FORM ===== */
/* Reusing grid style from manage_cars for consistency */
.form-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
}
.form-grid > div {
    display: flex;
    flex-direction: column;
}
.form-grid label {
    font-weight: bold;
    margin-bottom: 5px;
    font-size: 14px;
}
.form-grid input, .form-grid select {
    padding: 10px;
    border-radius: 5px;
    border: 1px solid #ccc;
}

/* SUCCESS ALERT */
.alert {
    padding: 12px;
    margin-bottom: 15px;
    border-radius: 6px;
    font-weight: bold;
    text-align: center;
}
.alert.success { background:#2ecc71; color:#fff; }
.alert.error { background:#e74c3c; color:#fff; }
</style>
</head>

<body>

<?php include('../navigation.php'); ?>

<!-- ===== CONTENT ===== -->
<div class="container">

<h1>Manage Users</h1>

<?php if (isset($_GET['toast_msg'])): ?>
    <div class="alert <?= $_GET['toast_type'] === 'success' ? 'success' : 'error' ?>">
        <?= htmlspecialchars($_GET['toast_msg']) ?>
    </div>
<?php endif; ?>

<?php 
// DETERMINE VIEW MODE
$viewMode = 'list';
if (isset($_GET['add']) || $editUser) {
    $viewMode = 'form';
}
?>

<?php if ($viewMode === 'list'): ?>
    <!-- LIST VIEW -->
    
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <!-- SORT FILTER -->
        <form method="GET" style="margin: 0; display: flex; align-items: center; gap: 10px;">
            <label style="margin:0; font-weight:bold;">Sort By:</label>
            <select name="sort" onchange="this.form.submit()" style="padding: 8px; border-radius: 5px; border: 1px solid #ccc;">
                <option value="az" <?= ($_GET['sort'] ?? '') == 'az' ? 'selected' : '' ?>>A-Z (Username)</option>
                <option value="za" <?= ($_GET['sort'] ?? '') == 'za' ? 'selected' : '' ?>>Z-A (Username)</option>
                <option value="role" <?= ($_GET['sort'] ?? '') == 'role' ? 'selected' : '' ?>>Role</option>
            </select>
        </form>

        <a href="?add=1" class="btn green" style="text-decoration:none;">+ Add New User</a>
    </div>

    <table>
    <thead>
    <tr>
        <th>Username</th>
        <th>Name</th>
        <th>Email</th>
        <th>Phone</th>
        <th>Role</th>
        <th>Action</th>
    </tr>
    </thead>
    <tbody>

    <?php if ($result->num_rows === 0): ?>
    <tr><td colspan="6">No users found.</td></tr>
    <?php else: ?>
    <?php while ($u = $result->fetch_assoc()): ?>
    <tr>
        <td><?= htmlspecialchars($u['username']) ?></td>
        <td><?= htmlspecialchars($u['name']) ?></td>
        <td><?= htmlspecialchars($u['email']) ?></td>
        <td><?= htmlspecialchars($u['phone']) ?></td>
        <td><?= htmlspecialchars($u['role']) ?></td>
        <td class="actions">
            <a class="edit" href="#" onclick="confirmEdit(<?= $u['id'] ?>)">Edit</a>
            <a class="delete"
               href="#"
               onclick="confirmDelete(<?= $u['id'] ?>)">
               Delete
            </a>
        </td>
    </tr>
    <?php endwhile; ?>
    <?php endif; ?>

    </tbody>
    </table>

<?php else: ?>
    <!-- FORM VIEW -->
    <form method="POST" id="userForm">
        <h3><?= $editUser ? "Edit User" : "Add New User" ?></h3>
        <input type="hidden" name="id" value="<?= $editUser['id'] ?? '' ?>">

        <div class="form-grid">
            <div>
                <label>Username</label>
                <input name="username" value="<?= $editUser['username'] ?? '' ?>" required>
            </div>
            <div>
                <label>Full Name</label>
                <input name="name" value="<?= $editUser['name'] ?? '' ?>" required>
            </div>
            <div>
                <label>Email</label>
                <input name="email" type="email" value="<?= $editUser['email'] ?? '' ?>" required>
            </div>
            <div>
                <label>Phone</label>
                <input name="phone" value="<?= $editUser['phone'] ?? '' ?>" required>
            </div>
            <div>
                <label>Password <?= $editUser ? '(Leave empty to keep current)' : '' ?></label>
                <input name="password" placeholder="Password" <?= $editUser ? '' : 'required' ?>>
            </div>
            <div>
                <label>Role</label>
                <select name="role" required>
                    <option value="">Select Role</option>
                    <option value="admin" <?= ($editUser['role']??'') === 'admin' ? 'selected' : '' ?>>Admin</option>
                    <option value="user" <?= ($editUser['role']??'') === 'user' ? 'selected' : '' ?>>User</option>
                </select>
            </div>
        </div>

        <br>
        <div style="display:flex; gap:10px;">
            <?php if ($editUser): ?>
                <button class="btn green" type="button" onclick="confirmUpdate()"><?= "Update User" ?></button>
            <?php else: ?>
                <button class="btn green" type="submit"><?= "Add User" ?></button>
            <?php endif; ?>
            <a href="manage_users.php" class="btn">Back</a>
        </div>
    </form>
<?php endif; ?>

</div>

<?php include('../confirm_modal.php'); ?>

<script src="../toast.js"></script>
<script>
function confirmEdit(userId) {
    showConfirm('Are you sure to edit?', function() {
        window.location.href = '?edit=' + userId;
    });
}

function confirmUpdate() {
    showConfirm('Are you sure to update?', function() {
        document.getElementById('userForm').submit();
    });
}

function confirmDelete(userId) {
    showConfirm('Are you sure you want to delete', function() {
        window.location.href = '?delete=' + userId;
    });
}
</script>

</body>
</html>
