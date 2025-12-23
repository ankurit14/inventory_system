<?php
session_start();
include_once __DIR__ . '/../../config/db.php';
include('product_functions.php');

if(!isset($_GET['id']) || !is_numeric($_GET['id'])) die("Invalid ID");

$id = intval($_GET['id']);

if(delete_product($id)){
    header("Location: index.php?msg=deleted");
} else {
    header("Location: index.php?msg=error");
}
?>
