<?php
include_once __DIR__ . '/../../config/path.php';
include(BASE_PATH.'/includes/users_functions.php');

if (!isset($_GET['id'])) {
    die("User ID not specified.");
}

$id = intval($_GET['id']);
delete_user($id);

// Redirect back
header("Location: index.php");
exit;
?>
