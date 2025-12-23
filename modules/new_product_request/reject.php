<?php
session_start();
include_once __DIR__ . '/../../config/path.php';
include_once __DIR__ . '/../../config/db.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['admin','purchase'])) {
    echo "<script>alert('Access denied'); window.history.back();</script>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id = intval($_POST['id'] ?? 0);
    $reason = trim($_POST['reason'] ?? '');

    if ($id <= 0) {
        echo "<script>alert('Invalid ID'); window.location='list.php';</script>";
        exit;
    }

    // Sanitize reason
    $reason = mysqli_real_escape_string($conn, $reason);

    // Update query with optional reason
    if ($reason !== "") {
        // Store the reason in the 'reason' column or append
        $sql = "
            UPDATE new_product_requests 
            SET status='rejected', reason = CONCAT(IFNULL(reason,''), '\nRejected Reason: $reason')
            WHERE id=$id
        ";
    } else {
        // No reason provided
        $sql = "UPDATE new_product_requests SET status='rejected' WHERE id=$id";
    }

    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('Request rejected successfully'); window.location='list.php';</script>";
        exit;
    } else {
        echo "<script>alert('DB Error: " . mysqli_error($conn) . "'); window.location='list.php';</script>";
        exit;
    }
} 

// If not POST, redirect
header("Location: list.php");
exit;
