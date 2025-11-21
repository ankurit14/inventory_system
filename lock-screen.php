<?php
session_start();
include('./config/db.php');

// If not logged in, redirect to login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$username = $_SESSION['username'];
$user_id = $_SESSION['user_id'];
$name = $_SESSION['name'];

// Fetch user record
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

$error = "";

// Unlock attempt
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $password = trim($_POST['password']);

    if (password_verify($password, $user['password'])) {
        unset($_SESSION['locked']); // Unlock
        header("Location: dashboard/" . $_SESSION['role'] . ".php");
        exit;
    } else {
        $error = "Incorrect password!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Locked | Inventory Management System</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,500" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/bootstrap/css/bootstrap.min.css">

    <style>
        body {
            background: linear-gradient(135deg, #e3f2fd, #bbdefb);
            font-family: 'Roboto', sans-serif;
        }
        .lock-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .lock-card {
            background: #fff;
            width: 380px;
            padding: 2rem 2rem 1.5rem;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 6px 25px rgba(0,0,0,0.1);
        }
        .lock-icon {
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: #fff;
            width: 85px;
            height: 85px;
            border-radius: 50%;
            font-size: 38px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            box-shadow: 0 6px 12px rgba(0,123,255,0.3);
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(0,123,255,0.4); }
            50% { transform: scale(1.08); box-shadow: 0 0 0 12px rgba(0,123,255,0.0); }
            100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(0,123,255,0.0); }
        }
        .footer-text {
            font-size: 0.85rem;
            color: #999;
            margin-top: 1.5rem;
        }
    </style>
</head>

<body>

<div class="lock-container">
    <div class="lock-card">

        <div class="lock-icon">
            <i class="fas fa-lock"></i>
        </div>

        <h4>Welcome Back, <?= htmlspecialchars($name); ?></h4>
        <p class="subtitle text-muted">Your screen is locked</p>

        <?php if ($error): ?>
            <div id="alertBox" class="alert alert-danger py-2">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3 text-start">
                <label class="form-label">Enter Password to Unlock</label>
                <input type="password" name="password" class="form-control" required autofocus>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2">Unlock</button>

            <a href="logout.php" class="d-block mt-3 text-danger">Logout</a>
        </form>

        <div class="footer-text">
            &copy; <?= date('Y') ?> Inventory Management System
        </div>
    </div>
</div>

<script src="assets/js/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
