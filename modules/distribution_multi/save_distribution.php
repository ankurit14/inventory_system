<?php
session_start();
include_once __DIR__ . '/../../config/path.php';
include_once __DIR__ . '/../../config/db.php';

/* ================= AUTH ================= */
$role = $_SESSION['role'] ?? '';
if(!in_array($role,['employee','hr','admin'])){
    echo "<script>alert('Access Denied');window.location='".BASE_URL."index.php';</script>";
    exit;
}

/* ================= VALID REQUEST ================= */
if(
    !isset($_POST['employee_id'], $_POST['product_id'], $_POST['client_id'], $_POST['qty'])
){
    echo "<script>alert('Invalid request!');window.location='add.php';</script>";
    exit;
}

$employee_id = intval($_POST['employee_id']);
$product_ids = $_POST['product_id'];   // array
$clients     = $_POST['client_id'];    // array
$qtys        = $_POST['qty'];          // array
$notes       = $_POST['note'] ?? [];   // array

$request_for = 'distribution';
$created_at  = date('Y-m-d H:i:s');

/* ================= 1️⃣ CALCULATE PRODUCT-WISE TOTAL ================= */
$productTotals = [];

foreach($product_ids as $i => $pid){
    $pid = intval($pid);
    $qty = intval($qtys[$i] ?? 0);

    if($pid <= 0 || $qty <= 0) continue;

    if(!isset($productTotals[$pid])){
        $productTotals[$pid] = 0;
    }
    $productTotals[$pid] += $qty;
}

/* ================= 2️⃣ CHECK AVAILABLE STOCK PRODUCT-WISE ================= */
foreach($productTotals as $pid => $totalQty){

    $res = mysqli_query($conn, "
        SELECT (SUM(stock_in) - SUM(stock_out)) AS available
        FROM employee_stock_master
        WHERE employee_id = $employee_id
          AND product_id  = $pid
    ");

    $row = mysqli_fetch_assoc($res);
    $availableStock = $row ? floatval($row['available']) : 0;

    if($totalQty > $availableStock){
        echo "<script>
            alert('Stock exceeded for one or more products!');
            window.history.back();
        </script>";
        exit;
    }
}

/* ================= 3️⃣ INSERT DISTRIBUTION ROWS ================= */
foreach($product_ids as $i => $pid){

    $pid       = intval($pid);
    $client_id = intval($clients[$i] ?? 0);
    $qty       = intval($qtys[$i] ?? 0);
    $note      = mysqli_real_escape_string($conn, $notes[$i] ?? '');

    if($pid <= 0 || $client_id <= 0 || $qty <= 0){
        continue;
    }

    $sql = "
        INSERT INTO employee_stock_master
        (employee_id, product_id, stock_in, stock_out, client_id, note, request_for, created_at)
        VALUES
        ($employee_id, $pid, 0, $qty, $client_id, '$note', '$request_for', '$created_at')
    ";

    mysqli_query($conn, $sql);
}

/* ================= SUCCESS ================= */
echo "<script>
    alert('Products distributed successfully!');
    window.location='add.php';
</script>";
exit;
