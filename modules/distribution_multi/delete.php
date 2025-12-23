<?php
session_start();
include_once __DIR__ . '/../../config/path.php';
include(BASE_PATH.'/includes/db.php');

if(!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php?msg=invalid"); exit;
}

$id = intval($_GET['id']);
$delete = mysqli_query($conn, "DELETE FROM employee_stock_master WHERE id=$id");

if($delete){
    header("Location: index.php?msg=deleted"); exit;
} else {
    header("Location: index.php?msg=error"); exit;
}
