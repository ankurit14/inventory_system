<?php
session_start();
include_once __DIR__ . '/../../config/db.php';

if(!in_array($_SESSION['role'], ['admin','hr'])) {
    echo "fail"; 
    exit;
}



$id = intval($_POST['id']);
$status = $_POST['status'] === 'active' ? 'active' : 'inactive';

$query = "UPDATE sub_category SET status='$status' WHERE id=$id";

if(mysqli_query($conn, $query)){
    echo "success";
} else {
    echo "fail";
}
?>
