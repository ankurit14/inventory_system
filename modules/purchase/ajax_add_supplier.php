<?php
include_once __DIR__ . '/../../config/db.php';

$name = trim($_POST['name']);
if ($name == "") { echo "0"; exit; }

$stmt = mysqli_prepare($conn, "INSERT INTO suppliers(name) VALUES (?)");
mysqli_stmt_bind_param($stmt, "s", $name);
mysqli_stmt_execute($stmt);

echo mysqli_insert_id($conn);
?>

