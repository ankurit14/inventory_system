<?php
include_once __DIR__ . '/../../config/db.php';

$id = intval($_POST['id']);
$name = $_POST['name'];
$company = $_POST['company'];
$email = $_POST['email'];
$phone = $_POST['phone'];
$address = $_POST['address'];
$status = $_POST['status'];

$sql = "UPDATE clients SET 
            name=?, 
            company=?, 
            email=?, 
            phone=?, 
            address=?, 
            status=? 
        WHERE id=?";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ssssssi", $name, $company, $email, $phone, $address, $status, $id);
mysqli_stmt_execute($stmt);

echo "<script>alert('Client Updated Successfully!'); window.location='index.php';</script>";
?>
