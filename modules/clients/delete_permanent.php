<?php
include_once __DIR__ . '/../../config/db.php';

$id = intval($_GET['id']);

$sql = "DELETE FROM clients WHERE id=?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

echo "<script>alert('Client Permanently Deleted!'); window.location='trash.php';</script>";
?>
