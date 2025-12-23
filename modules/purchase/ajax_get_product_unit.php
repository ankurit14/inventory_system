<?php
include_once __DIR__ . '/../../config/db.php';

$id = intval($_GET['product_id']);

$q = mysqli_query($conn, "SELECT unit FROM products WHERE id = $id");
$r = mysqli_fetch_assoc($q);

echo $r ? $r['unit'] : "";
