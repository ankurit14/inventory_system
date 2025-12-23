<?php
session_start();
include_once __DIR__ . '/../../config/path.php';
include('supplier_functions.php');

if(!isset($_GET['id'])) die("Invalid");

$id = intval($_GET['id']);
delete_supplier($id);

header("Location: index.php");
exit;
?>
