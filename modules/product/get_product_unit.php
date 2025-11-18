<?php
include($_SERVER['DOCUMENT_ROOT'].'/inventory_system/config/db.php');

$product_id = intval($_GET['product_id']);

$res = mysqli_query($conn, "SELECT unit FROM products WHERE id = $product_id");

$row = mysqli_fetch_assoc($res);

echo $row['unit'] ?? '';
?>
