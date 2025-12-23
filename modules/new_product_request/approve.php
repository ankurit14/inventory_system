<?php
session_start();
include_once __DIR__ . '/../../config/path.php';
include_once __DIR__ . '/../../config/db.php';

// role check
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['admin','purchase'])) {
    echo "<script>alert('Access denied'); window.history.back();</script>";
    exit;
}

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    echo "<script>alert('Invalid request'); window.location='list.php';</script>";
    exit;
}

// fetch request
$q = mysqli_query($conn, "SELECT * FROM new_product_requests WHERE id = $id LIMIT 1");
if (!$q || mysqli_num_rows($q) == 0) {
    echo "<script>alert('Request not found'); window.location='list.php';</script>";
    exit;
}
$row = mysqli_fetch_assoc($q);

// redirect to product_add.php with prefilled name and req_id
$prefill_name = urlencode($row['product_name']);
// header("Location: /inventory_system/modules/product/add.php?req_id={$row['id']}&name={$prefill_name}");
header("Location: " . BASE_URL . "modules/product/add.php?req_id={$row['id']}&name={$prefill_name}");
exit;


