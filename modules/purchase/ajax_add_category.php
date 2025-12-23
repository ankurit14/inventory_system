<?php
include_once __DIR__ . '/../../config/db.php';

$name = $_POST['name'];
$stmt = mysqli_prepare($conn, "INSERT INTO category(name) VALUES (?)");
mysqli_stmt_bind_param($stmt, "s", $name);
mysqli_stmt_execute($stmt);

echo mysqli_insert_id($conn);
?>
