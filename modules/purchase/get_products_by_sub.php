<?php
include_once __DIR__ . '/../../config/db.php';

$name             = trim($_POST['name']);
$category_id      = intval($_POST['category_id']);
$sub_category_id  = intval($_POST['sub_category_id']);
$unit             = trim($_POST['unit']);
$sku              = trim($_POST['sku']);

if ($name == "" || $category_id == 0 || $sub_category_id == 0 || $unit == "" || $sku == "") {
    echo "0";
    exit;
}

$stmt = mysqli_prepare($conn, "INSERT INTO products(name, category_id, sub_category_id, sku, unit, status)
VALUES (?, ?, ?, ?, ?, 'active')");

mysqli_stmt_bind_param($stmt, "siiss", $name, $category_id, $sub_category_id, $sku, $unit);
mysqli_stmt_execute($stmt);

echo mysqli_insert_id($conn);
?>
