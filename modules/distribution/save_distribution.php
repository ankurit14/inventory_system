<?php
session_start();
include_once __DIR__ . '/../../config/path.php';
include_once __DIR__ . '/../../config/db.php';

// Only employees, HR, Admin
$role = $_SESSION['role'];
if(!in_array($role,['employee','hr','admin'])){
    echo "<script>alert('Access Denied');window.location='".BASE_URL."index.php';</script>";
    exit;
}

if(isset($_POST['employee_id'], $_POST['product_id'], $_POST['client_id'], $_POST['qty'])) {

    $employee_id = intval($_POST['employee_id']);
    $product_id  = intval($_POST['product_id']);
    $clients     = $_POST['client_id'];
    $qtys        = $_POST['qty'];
    $notes       = $_POST['note'] ?? [];
    $request_for = 'distribution';
    $created_at  = date('Y-m-d H:i:s');

    // 1️⃣ Check available stock
    $res = mysqli_query($conn, "
        SELECT (SUM(stock_in) - SUM(stock_out)) AS available
        FROM employee_stock_master
        WHERE employee_id=$employee_id AND product_id=$product_id
    ");
    $row = mysqli_fetch_assoc($res);
    $availableStock = $row ? floatval($row['available']) : 0;

    $totalQty = 0;
    foreach($qtys as $q){
        $totalQty += intval($q);
    }

    if($totalQty > $availableStock){
        echo "<script>alert('Total quantity exceeds available stock!');window.history.back();</script>";
        exit;
    }

    // 2️⃣ Insert distribution rows in employee_stock_master
    foreach($clients as $index => $client_id){
        $client_id = intval($client_id);
        $qty       = intval($qtys[$index]);
        $note      = mysqli_real_escape_string($conn, $notes[$index] ?? '');

        if($client_id == 0 || $qty == 0) continue;

        $sql = "
            INSERT INTO employee_stock_master 
            (employee_id, product_id, stock_in, stock_out, client_id, note, request_for, created_at)
            VALUES 
            ($employee_id, $product_id, 0, $qty, $client_id, '$note', '$request_for', '$created_at')
        ";
        mysqli_query($conn, $sql);
    }

    echo "<script>alert('Product distributed successfully!');window.location='add.php';</script>";
    exit;
}

echo "<script>alert('Invalid request!');window.location='add.php';</script>";
exit;
