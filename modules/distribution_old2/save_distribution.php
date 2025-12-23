<?php
session_start();
include('distribution_functions.php');

$emp_id = intval($_POST['emp_id']);
$product_id = intval($_POST['product_id']);
$client_id = intval($_POST['client_id']);
$qty = floatval($_POST['qty']);
$note = $_POST['note'] ?? '';

$products = get_employee_products($emp_id);

$remaining = 0;
foreach($products as $p){
    if($p['product_id'] == $product_id){
        $remaining = $p['remaining'];
        break;
    }
}

if($qty > $remaining){
    echo "<script>alert('Not enough stock. Remaining: $remaining'); history.back();</script>";
    exit;
}

add_distribution($emp_id, $product_id, $client_id, $qty, $note);

header("Location: index.php");
exit;
?>
