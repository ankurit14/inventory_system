<?php
include($_SERVER['DOCUMENT_ROOT'].'/inventory_system/config/path.php');
include(BASE_PATH.'/includes/category_functions.php');

if (!isset($_GET['id'])) {
    die("Category ID not specified.");
}

$id = intval($_GET['id']);
delete_category($id);

// Redirect back to category list
header("Location: index.php");
exit;
?>
