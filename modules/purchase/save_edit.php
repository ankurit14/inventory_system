<?php
session_start();
ini_set('display_errors',1);
error_reporting(E_ALL);

header("Content-Type: application/json");

include_once __DIR__ . '/../../config/path.php';
include_once __DIR__ . '/../../config/db.php';
include('purchase_functions.php');

// ---------------- INPUT CHECK ----------------
if (!isset($_POST['purchase_id'])) {
    echo json_encode(["status"=>"error","message"=>"Purchase ID missing"]);
    exit;
}

$purchase_id = intval($_POST['purchase_id']);
$purchase = get_purchase_by_id($purchase_id);

if (!$purchase){
    echo json_encode(["status"=>"error","message"=>"Purchase not found"]);
    exit;
}

// ---------------- BASIC DATA ----------------
$supplier_id = intval($_POST['supplier_id']);
$purchase_date = $_POST['purchase_date'];
$status = $_POST['status'] ?? "pending";

$item_existing_ids = $_POST['item_existing_id'] ?? [];
$item_existing_product_ids = $_POST['item_existing_product_id'] ?? [];
$item_existing_qty = $_POST['item_existing_qty'] ?? [];
$item_existing_price = $_POST['item_existing_price'] ?? [];
$item_delete = $_POST['item_delete'] ?? [];
$new_items = $_POST['new_items'] ?? [];

$conn->begin_transaction();

try {

    // ---------------- CALCULATE GRAND TOTAL ----------------
    $grand = 0.0;

    foreach ($item_existing_ids as $i => $item_id) {
        if (in_array($item_id, $item_delete)) continue;
        $grand += floatval($item_existing_qty[$i]) * floatval($item_existing_price[$i]);
    }

    foreach ($new_items as $n) {
        $grand += floatval($n['total']);
    }

    // ---------------- UPDATE PURCHASE MAIN ----------------
    update_purchase($purchase_id, $supplier_id, $purchase_date, $grand, $status);

    // ---------------- DELETE ITEMS ----------------
    if (!empty($item_delete)) {
        foreach ($item_delete as $del_id) {
            delete_purchase_item($del_id);
        }
    }

    // ---------------- UPDATE EXISTING ITEMS ----------------
    foreach ($item_existing_ids as $i => $item_id) {
        if (in_array($item_id, $item_delete)) continue;

        $qty = floatval($item_existing_qty[$i]);
        $price = floatval($item_existing_price[$i]);
        $total = $qty * $price;

        mysqli_query($conn,
            "UPDATE purchase_items SET qty=$qty, unit_price=$price, total=$total WHERE id=$item_id"
        );
    }

    // ---------------- ADD NEW ITEMS ----------------
    foreach ($new_items as $n) {
        insert_purchase_item(
            $purchase_id,
            intval($n['product_id']),
            floatval($n['qty']),
            floatval($n['unit_price']),
            floatval($n['total'])
        );
    }

    $conn->commit();

    echo json_encode([
        "status" => "success",
        "message" => "Purchase updated successfully"
    ]);
    exit;

} catch (Exception $e) {
    $conn->rollback();

    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
    exit;
}
?>
