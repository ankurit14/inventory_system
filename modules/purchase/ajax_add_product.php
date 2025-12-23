<?php
include_once __DIR__ . '/../../config/db.php';

$name = trim($_POST['name']);
$category_id = intval($_POST['category_id']);
$sub_category_id = intval($_POST['sub_category_id']);
$unit = trim($_POST['unit']);
$sku = trim($_POST['sku']);

if ($name == "" || $category_id == 0 || $sub_category_id == 0 || $unit == "" || $sku == "") {
    echo "0";
    exit;
}

$sql = "INSERT INTO products (category_id, sub_category_id, name, sku, unit) 
        VALUES (?, ?, ?, ?, ?)";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "iisss", $category_id, $sub_category_id, $name, $sku, $unit);
mysqli_stmt_execute($stmt);

echo mysqli_insert_id($conn);
?>
