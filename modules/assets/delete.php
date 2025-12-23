<?php
// modules/assets/delete.php
session_start();
include_once __DIR__ . '/../../config/path.php';
include(BASE_PATH.'/config/db.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}
$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
if (!$id) {
    header('Location: index.php?error=1');
    exit;
}

// simple deletion
$stmt = mysqli_prepare($conn, "DELETE FROM company_assets WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
$ok = mysqli_stmt_execute($stmt);

if ($ok) {
    header('Location: index.php?deleted=1');
    exit;
} else {
    header('Location: index.php?error=1');
    exit;
}
