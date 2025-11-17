<?php
include($_SERVER['DOCUMENT_ROOT'].'/inventory_system/config/path.php');
include('sub_category_functions.php');

if (!isset($_GET['id'])) die("ID not provided");

$id = intval($_GET['id']);
delete_subcategory($id);

header("Location: index.php");
exit;
