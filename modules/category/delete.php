<?php
session_start();
include_once __DIR__ . '/../../config/path.php';
include(BASE_PATH.'/config/db.php');
include(BASE_PATH . '/includes/category_functions.php');
global $conn;
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php?msg=invalid");
    exit;
}

$id = intval($_GET['id']);
$id;
// 1) CHECK IF CATEGORY IS USED IN SUB-CATEGORY TABLE
$check = mysqli_query($conn, "SELECT id FROM sub_category WHERE category_id = $id LIMIT 1");

if (mysqli_num_rows($check) > 0) {
    // Category is used → cannot delete
    header("Location: index.php?msg=used");
    exit;
}

// 2) DELETE CATEGORY
$delete = mysqli_query($conn, "DELETE FROM category WHERE id = $id");

if ($delete) {
    header("Location: index.php?msg=deleted");
} else {
    header("Location: index.php?msg=error");
}
exit;
?>
