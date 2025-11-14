<?php
session_start();
include('./config/db.php');

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if ($username == '' || $password == '') {
        $error = "Please enter both username and password.";
    } else {
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? AND status = 'active' LIMIT 1");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['name'] = $user['name'];

                if ($user['role'] === 'admin') {
                    header("Location: dashboard/admin.php");
                } elseif ($user['role'] === 'hr') {
                    header("Location: dashboard/hr.php");
                } else {
                    header("Location: dashboard/employee.php");
                }
                exit;
            } else {
                $error = "Invalid password.";
            }
        } else {
            $error = "User not found or inactive.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Login | Inventory Management System</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,500" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="assets/css/bootstrap/css/bootstrap.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #e3f2fd, #bbdefb);
            font-family: 'Roboto', sans-serif;
        }
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 6px 25px rgba(0,0,0,0.1);
            width: 380px;
            padding: 2rem 2rem 1.5rem;
            text-align: center;
        }
        .login-icon {
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
        h4 {
            font-weight: 600;
            color: #212529;
            margin-bottom: 0.3rem;
        }
        p.subtitle {
            color: #6c757d;
            margin-bottom: 1.5rem;
        }
        .btn-primary {
            background-color: #007bff;
            border: none;
            border-radius: 6px;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        .btn-primary:hover {
            background-color: #0056b3;
        }
        .form-control {
            border-radius: 6px;
            padding: 0.6rem 0.75rem;
        }
        .form-control:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.1rem rgba(0,123,255,0.25);
        }
        .footer-text {
            font-size: 0.85rem;
            color: #999;
            margin-top: 1.5rem;
        }
        /* Fade-out animation for alert */
        .fade-out {
            opacity: 1;
            transition: opacity 1s ease-out;
        }
        .fade-out.hide {
            opacity: 0;
        }
    </style>
</head>
<body>

<div class="login-container">
    <div class="login-card">
        <div class="login-icon">
            <i class="fas fa-boxes-stacked"></i>
        </div>
        <h4>Inventory Management System</h4>
        <p class="subtitle">Sign in to continue</p> 

        <?php if ($error): ?>
            <div id="alertBox" class="alert alert-danger text-center py-2 fade-out">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-3 text-start">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" required="">
            </div>
            <div class="mb-3 text-start">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required="">
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2">Sign In</button>
        </form>

        <div class="footer-text">
            &copy; <?= date('Y') ?> Inventory Management System
        </div>
    </div>
</div>

<script>
    // Auto-hide alert after 3 seconds
    setTimeout(() => {
        const alert = document.getElementById('alertBox');
        if (alert) {
            alert.classList.add('hide');
            setTimeout(() => alert.remove(), 300); // remove from DOM after fade
        }
    }, 3000);
</script>

<script src="assets/js/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
