<?php
include_once __DIR__ . '/../../config/db.php';

$id = intval($_POST['id']);

$sql = "UPDATE clients SET is_deleted=1, deleted_at=NOW() WHERE id=?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

echo "<script>alert('Client moved to Trash!'); window.location='index.php';</script>";
?>
