<?php
session_start();
include($_SERVER['DOCUMENT_ROOT'].'/inventory_system/config/db.php');

if(!in_array($_SESSION['role'], ['admin','hr'])) {
    echo "fail"; exit;
}

$id = intval($_POST['id']);
$status = $_POST['status'] === 'active' ? 'active' : 'inactive';

if(mysqli_query($conn, "UPDATE users SET status='$status' WHERE id=$id")) {
    echo 'success';
} else {
    echo 'fail';
}
?>
