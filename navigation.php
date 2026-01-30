<?php
// navigation.php - Reusable navigation component
// Usage: include('navigation.php'); 
// Optional: Set $active_page = 'home' before including to highlight active link

$user_name = isset($_SESSION['name']) ? $_SESSION['name'] : $_SESSION['username'];
$role = $_SESSION['role'] ?? 'user';
$active_page = $active_page ?? '';
?>

<style>
/* Navigation Styles */
.header {
    background: #000;
    padding: 20px 40px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: relative;
    z-index: 100;
}

.header-left {
    display: flex;
    align-items: center;
    gap: <?= $role === 'admin' ? '30px' : '40px' ?>;
}

.logo-img {
    width: <?= $role === 'admin' ? '150px' : '180px' ?>;
    height: <?= $role === 'admin' ? '40px' : '50px' ?>;
}

.logo-img img {
    width: 100%;
    height: 100%;
    object-fit: contain;
}

.nav-menu {
    display: flex;
    gap: <?= $role === 'admin' ? '35px' : '30px' ?>;
    align-items: center;
}

.nav-item {
    position: relative;
}

.nav-link {
    color: #fff;
    text-decoration: none;
    font-size: 16px;
    font-weight: 600;
    padding: 8px 0;
    display: block;
}

.nav-link:hover {
    opacity: 0.7;
}

/* Dropdown Menu */
.dropdown {
    position: relative;
}

.dropdown-content {
    display: none;
    position: absolute;
    top: 100%;
    left: 0;
    background: #fff;
    min-width: 200px;
    box-shadow: 0 8px 16px rgba(0,0,0,0.2);
    border-radius: 8px;
    margin-top: 0; /* NO gap - attached directly */
    z-index: 1000;
}

.dropdown:hover .dropdown-content {
    display: block;
}

.dropdown-content a {
    color: #000;
    padding: 12px 16px;
    text-decoration: none;
    display: block;
    font-size: 14px;
    font-weight: 600;
    transition: background 0.2s;
}

.dropdown-content a:hover {
    background: #f1f1f1;
}

.dropdown-content a:first-child {
    border-radius: 8px 8px 0 0;
}

.dropdown-content a:last-child {
    border-radius: 0 0 8px 8px;
}

.logout-btn {
    background: #ff4500;
    color: #fff;
    padding: 10px 25px;
    border-radius: 20px;
    text-decoration: none;
    font-weight: bold;
    font-size: 14px;
}

.logout-btn:hover {
    background: #e63e00;
}
</style>

<div class="header">
    <div class="header-left">
        <div class="logo-img">
            <img src="Images/proton.png" alt="Proton Logo">
        </div>
        <nav class="nav-menu">
            <?php if ($role === 'admin'): ?>
                <!-- Admin Navigation -->
                <div class="nav-item">
                    <a href="admin_dashboard.php" class="nav-link">Dashboard</a>
                </div>
                <div class="nav-item">
                    <a href="manage_cars.php" class="nav-link">Manage Cars</a>
                </div>
                <div class="nav-item">
                    <a href="manage_users.php" class="nav-link">Manage Users</a>
                </div>
                <div class="nav-item">
                    <a href="manage_appointments.php" class="nav-link">Manage Appointments</a>
                </div>
                <div class="nav-item">
                    <a href="admin_view_reviews.php" class="nav-link">Manage Reviews</a>
                </div>
            <?php else: ?>
                <!-- User Navigation -->
                <div class="nav-item">
                    <a href="user_dashboard.php" class="nav-link">Home Page</a>
                </div>
                <div class="nav-item">
                    <a href="models.php" class="nav-link">Models</a>
                </div>
                
                <!-- Loan Calculator Dropdown -->
                <div class="nav-item dropdown">
                    <a href="loan_calculator.php" class="nav-link">Loan Calculator</a>
                    <div class="dropdown-content">
                        <a href="loan_calculator.php">Loan Calculator</a>
                        <a href="loan_history.php">Loan History</a>
                    </div>
                </div>
                
                <div class="nav-item">
                    <a href="compare_models.php" class="nav-link">Compare Models</a>
                </div>
                
                <!-- Book Dropdown -->
                <div class="nav-item dropdown">
                    <a href="test_drive.php" class="nav-link">Book</a>
                    <div class="dropdown-content">
                        <a href="test_drive.php">Book Test Drive</a>
                        <a href="test_drive_history.php">Test Drive History</a>
                        <a href="rating.php">Rating</a>
                    </div>
                </div>
            <?php endif; ?>
        </nav>
    </div>
    <a href="logout.php" class="logout-btn">Logout</a>
</div>
