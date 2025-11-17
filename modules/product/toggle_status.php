<?php
session_start();
include($_SERVER['DOCUMENT_ROOT'].'/inventory_system/config/db.php');

// Check if ID is provided
if(!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo 'error';
    exit;
}

$id = intval($_GET['id']);

// Fetch current product status
$res = mysqli_query($conn, "SELECT status FROM products WHERE id=$id");
if(!$res || mysqli_num_rows($res) === 0) {
    echo 'error';
    exit;
}

$row = mysqli_fetch_assoc($res);
$currentStatus = strtolower($row['status']); // normalize

// Determine new status
$newStatus = ($currentStatus === 'active') ? 'inactive' : 'active';

// Update status in DB
$stmt = mysqli_prepare($conn, "UPDATE products SET status=? WHERE id=?");
mysqli_stmt_bind_param($stmt, 'si', $newStatus, $id);

if(mysqli_stmt_execute($stmt)) {
    echo 'success';
} else {
    echo 'error';
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>
