<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

include('db_connection.php');

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$error_message = '';
$success_message = '';

// Fetch user details
$name = '';
$user_sql = "SELECT name FROM users WHERE id = ?";
$user_stmt = $conn->prepare($user_sql);
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();

if ($user_result->num_rows > 0) {
    $user_data = $user_result->fetch_assoc();
    $name = $user_data['name'];
}
$user_stmt->close();

// Handle form submission for rating and review
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $rating = $_POST['rating'];
    $comment = $_POST['comment'];
    $car_model_variant = $_POST['car_model_variant']; // This can come from the test drive or selection

    // Validate inputs
    if (empty($rating) || empty($comment)) {
        $error_message = "Both rating and comment are required!";
    } else {
        // Insert the review into the database
        $insert_sql = "INSERT INTO test_drive_reviews (user_id, car_model_variant, rating, comment) 
                       VALUES (?, ?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("isis", $user_id, $car_model_variant, $rating, $comment);

        if ($insert_stmt->execute()) {
            $success_message = "Thank you for your feedback!";
        } else {
            $error_message = "Failed to submit your review. Please try again.";
        }
        $insert_stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Drive Feedback</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Century Gothic', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Header */
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

        .logo {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .logo-img {
            width: 150px; /* Reduced logo size */
            height: 40px;
        }

        .logo-img img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        /* Navigation */
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
            transition: opacity 0.3s;
            cursor: pointer;
        }

        .nav-link:hover {
            opacity: 0.7;
        }

        /* Logout Button */
        .logout-btn {
            background-color: #ff4500;
            color: #fff;
            border: none;
            padding: 10px 25px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: background-color 0.3s;
            text-decoration: none;
            display: inline-block;
        }

        .logout-btn:hover {
            background-color: #e63e00;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            background: radial-gradient(ellipse at center, #f4d77e 0%, #e6c770 25%, #d4a747 50%, #c89a3d 75%, #9d7730 100%);
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 60px 40px;
        }

        /* Form Container */
        .form-container {
            text-align: center;
            width: 100%;
            max-width: 500px;
        }

        /* Rating Stars */
        .rating {
            display: flex;
            gap: 5px;
            justify-content: center;
            margin-bottom: 20px;
        }

        .star {
            width: 40px;
            height: 40px;
            background-color: #ccc;
            clip-path: polygon(50% 0%, 61% 35%, 98% 35%, 68% 57%, 79% 91%, 50% 70%, 21% 91%, 32% 57%, 2% 35%, 39% 35%);
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .star:hover,
        .star.selected {
            background-color: #000;
        }

        /* Review Form */
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-bottom: 20px;
        }

        .form-label {
            font-size: 14px;
            font-weight: 700;
            color: #000;
        }

        .form-input, .form-textarea {
            padding: 10px;
            font-size: 14px;
            width: 100%;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .form-textarea {
            height: 100px;
            resize: none;
        }

        .submit-btn {
            padding: 12px 50px;
            background-color: #000;
            color: #fff;
            border: none;
            border-radius: 25px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.3s;
            width: 100%;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.4);
        }

        /* Messages */
        .error-message {
            background-color: rgba(220, 53, 69, 0.9);
            color: white;
            padding: 12px 20px;
            border-radius: 4px;
            margin-bottom: 20px;
            font-size: 14px;
            font-weight: 600;
        }

        .success-message {
            background-color: rgba(40, 167, 69, 0.9);
            color: white;
            padding: 12px 20px;
            border-radius: 4px;
            margin-bottom: 20px;
            font-size: 14px;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-left">
            <div class="logo">
                <div class="logo-img">
                    <img src="Images/proton.png" alt="Proton Logo">
                </div>
            </div>
            <nav class="nav-menu">
                <a href="home.php" class="nav-link">Home</a>
                <a href="user_dashboard.php" class="nav-link">Dashboard</a>
                <a href="test_drive.php" class="nav-link">Book Test Drive</a>
            </nav>
        </div>
        <a href="logout.php" class="logout-btn">Logout</a>
    </div>

    <div class="main-content">
        <div class="form-container">
            <h1 class="page-title">Test Drive Feedback</h1>

            <?php if (!empty($error_message)): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($success_message)): ?>
                <div class="success-message">
                    <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>

            <form class="test-drive-form" method="POST" action="">
                <!-- Car Details -->
                <div class="form-group">
                    <label class="form-label" for="car_model_variant">Car Model</label>
                    <input class="form-input" type="text" id="car_model_variant" name="car_model_variant" 
                           value="<?php echo htmlspecialchars($car_model_variant); ?>" disabled>
                </div>

                <!-- Rating Section -->
                <div class="form-group">
                    <label class="form-label" for="rating">Rate Your Test Drive</label>
                    <div class="rating">
                        <div class="star" data-rating="1"></div>
                        <div class="star" data-rating="2"></div>
                        <div class="star" data-rating="3"></div>
                        <div class="star" data-rating="4"></div>
                        <div class="star" data-rating="5"></div>
                    </div>
                </div>

                <!-- Comment Section -->
                <div class="form-group">
                    <label class="form-label" for="comment">Leave a Comment</label>
                    <textarea class="form-textarea" id="comment" name="comment" placeholder="Share your experience..." required></textarea>
                </div>

                <button class="submit-btn" type="submit">Submit Review</button>
            </form>
        </div>
    </div>

    <script>
        const stars = document.querySelectorAll('.star');
        let selectedRating = 0;

        stars.forEach(star => {
            star.addEventListener('click', () => {
                selectedRating = star.dataset.rating;
                stars.forEach(star => {
                    if (star.dataset.rating <= selectedRating) {
                        star.classList.add('selected');
                    } else {
                        star.classList.remove('selected');
                    }
                });
            });
        });
    </script>
</body>
</html>
