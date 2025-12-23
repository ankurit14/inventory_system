<?php
session_start();
include_once __DIR__ . '/../../config/db.php';

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $id = intval($_POST['id']);
    $status = $_POST['status'] ?? '';
    $type = $_POST['type'] ?? '';

    if($id && in_array($status, ['Active','Inactive'])){
        if($type === 'client'){
            $stmt = mysqli_prepare($conn, "UPDATE clients SET status=? WHERE id=?");
            mysqli_stmt_bind_param($stmt, "si", $status, $id);
            if(mysqli_stmt_execute($stmt)){
                echo 'success';
            } else {
                echo 'error';
            }
            mysqli_stmt_close($stmt);
        } else {
            echo 'invalid type';
        }
    } else {
        echo 'invalid data';
    }
} else {
    echo 'invalid request';
}
?>
