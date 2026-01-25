<?php
session_start();

if (!isset($_SESSION['username'], $_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

require_once __DIR__ . '/db_connection.php';

/* =========================
   DELETE USER
   ========================= */
if (isset($_GET['delete'])) {
    $username = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->close();
    header("Location: manage_users.php");
    exit();
}

/* =========================
   ADD USER
   ========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    $stmt = $conn->prepare(
        "INSERT INTO users (username, name, email, phone, password, role)
         VALUES (?, ?, ?, ?, ?, ?)"
    );
    $stmt->bind_param(
        "ssssss",
        $_POST['username'],
        $_POST['name'],
        $_POST['email'],
        $_POST['phone'],
        $_POST['password'],
        $_POST['role']
    );
    $stmt->execute();
    $stmt->close();
}

/* =========================
   FETCH USERS
   ========================= */
$result = $conn->query(
    "SELECT username, name, email, phone, role FROM users ORDER BY username ASC"
);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Users</title>

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

.actions .delete {
    color: #e74c3c;
    font-weight: bold;
    text-decoration: none;
}

/* ===== ADD USER FORM ===== */
.add-form {
    margin-bottom: 25px;
    display: grid;
    grid-template-columns: repeat(6, 1fr);
    gap: 10px;
}

.add-form input,
.add-form select {
    padding: 8px;
}

/* ===== BOTTOM BAR ===== */
.bottom-bar {
    margin-top: 30px;
    display: flex;
    justify-content: flex-end;
    gap: 12px;
}
</style>
</head>

<body>

<!-- ===== HEADER ===== -->
<div class="header">
    <div class="header-left">
        <div class="logo-img">
            <img src="Images/proton.png" alt="Proton Logo">
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

<!-- ===== CONTENT ===== -->
<div class="container">

<h1>Manage Users</h1>

<form method="POST" class="add-form">
    <input name="username" placeholder="Username" required>
    <input name="name" placeholder="Name" required>
    <input name="email" type="email" placeholder="Email" required>
    <input name="phone" placeholder="Phone" required>
    <input name="password" placeholder="Password" required>
    <select name="role" required>
        <option value="">Role</option>
        <option value="admin">Admin</option>
        <option value="user">User</option>
    </select>
    <input type="hidden" name="add_user" value="1">
    <button class="btn green" type="submit">Add User</button>
</form>

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
        <a class="delete"
           href="?delete=<?= urlencode($u['username']) ?>"
           onclick="return confirm('Delete this user?')">
           Delete
        </a>
    </td>
</tr>
<?php endwhile; ?>
<?php endif; ?>

</tbody>
</table>

<div class="bottom-bar">
    <a href="admin_dashboard.php" class="btn">Back</a>
    <a href="logout.php" class="btn red"
       onclick="return confirm('Exit system?')">Exit</a>
</div>

</div>

</body>
</html>
