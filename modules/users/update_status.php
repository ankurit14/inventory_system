<?php

session_start();
// include(BASE_PATH.'/config/db.php');
include_once __DIR__ . '/../../config/db.php';

// normalize and validate role

if (!in_array($_SESSION['role'], ['admin', 'hr'])) {
    echo "fail: unauthorized";
    exit;
}

// validate POST params
/*if (!isset($_POST['id']) || !isset($_POST['status'])) {
    echo 'fail: missing_parameters';
    exit;
}*/

$id = intval($_POST['id']);
$status = $_POST['status'] === 'active' ? 'active' : 'inactive';

$sql = "UPDATE users SET status='$status' WHERE id=$id";
if (mysqli_query($conn, $sql)) {
    echo 'success';
} else {
    echo 'fail: ' . mysqli_error($conn);
}
