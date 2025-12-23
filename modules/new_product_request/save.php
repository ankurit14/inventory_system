<?php
session_start();
include_once __DIR__ . '/../../config/path.php';
include_once __DIR__ . '/../../config/db.php';

if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Please login first'); window.location='<?php echo BASE_URL; ?>login.php';</script>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = intval($_SESSION['user_id']);
    $product_name = mysqli_real_escape_string($conn, trim($_POST['product_name'] ?? ''));
    $qty = intval($_POST['qty'] ?? 1);
    $reason = mysqli_real_escape_string($conn, trim($_POST['reason'] ?? ''));

    if ($product_name === '') {
        echo "<script>alert('Product name is required'); window.history.back();</script>";
        exit;
    }

    $sql = "INSERT INTO new_product_requests (requested_by, product_name, qty, reason) 
            VALUES ($user_id, '$product_name', $qty, '$reason')";
    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('New product request submitted successfully'); window.location='list.php';</script>";
        exit;
    } else {
        echo "<script>alert('Database error: ". mysqli_real_escape_string($conn, mysqli_error($conn)) ."'); window.history.back();</script>";
        exit;
    }
} else {
    header("Location: add.php");
    exit;
}
