<?php
session_start();
include($_SERVER['DOCUMENT_ROOT'].'/inventory_system/config/db.php');

if(!in_array($_SESSION['role'], ['admin','hr'])) {
    echo "fail"; 
    exit;
}

$id = intval($_POST['id']);
$status = $_POST['status'] === 'active' ? 'active' : 'inactive';

$query = "UPDATE category SET status='$status' WHERE id=$id";

if(mysqli_query($conn, $query)){
    echo "success";
} else {
    echo "fail";
}
?>
