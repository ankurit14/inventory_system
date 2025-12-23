<?php
include_once __DIR__ . '/../../config/db.php';

$name = $_POST['name'];
$company = $_POST['company'];
$email = $_POST['email'];
$phone = $_POST['phone'];
$address = $_POST['address'];
$status = $_POST['status'];

$sql = "INSERT INTO clients (name, company, email, phone, address, status)
        VALUES (?, ?, ?, ?, ?, ?)";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ssssss", $name, $company, $email, $phone, $address, $status);
mysqli_stmt_execute($stmt);

echo "<script>alert('Client Added Successfully!'); window.location='index.php';</script>";
?>
