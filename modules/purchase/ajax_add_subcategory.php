<?php
include_once __DIR__ . '/../../config/db.php';

$name = trim($_POST['name']);
$category_id = intval($_POST['category_id']);

if ($name == "" || $category_id == 0) { 
    echo "0"; 
    exit; 
}

$stmt = mysqli_prepare($conn, "INSERT INTO sub_category(name, category_id) VALUES (?, ?)");
mysqli_stmt_bind_param($stmt, "si", $name, $category_id);
mysqli_stmt_execute($stmt);

echo mysqli_insert_id($conn);
?>
