<?php
include($_SERVER['DOCUMENT_ROOT'].'/inventory_system/config/db.php');

/* ==========================
   GET ALL PURCHASES
========================== */
function get_all_purchases() {
    global $conn;
    $sql = "SELECT p.*, s.name AS supplier_name 
            FROM purchases p 
            LEFT JOIN suppliers s ON p.supplier_id = s.id
            ORDER BY p.id DESC";
    return mysqli_query($conn, $sql);
}

/* ==========================
   GET SINGLE PURCHASE
========================== */
function get_purchase($id) {
    global $conn;
    return mysqli_query($conn, "SELECT * FROM purchases WHERE id=$id");
}

/* ==========================
   GET PURCHASE ITEMS
========================== */
function get_purchase_items($purchase_id) {
    global $conn;
    $sql = "SELECT pi.*, pr.name AS product_name, pr.unit 
            FROM purchase_items pi
            LEFT JOIN products pr ON pi.product_id = pr.id
            WHERE pi.purchase_id=$purchase_id";
    return mysqli_query($conn, $sql);
}

/* ==========================
   INSERT PURCHASE
========================== */
function insert_purchase($supplier_id, $date, $total, $status) {
    global $conn;
    $stmt = mysqli_prepare($conn,
        "INSERT INTO purchases (supplier_id, purchase_date, total_amount, status)
         VALUES (?, ?, ?, ?)"
    );
    mysqli_stmt_bind_param($stmt, "isds", $supplier_id, $date, $total, $status);
    mysqli_stmt_execute($stmt);
    return mysqli_insert_id($conn);
}


/* ==========================
   INSERT PURCHASE ITEM
========================== */
function insert_purchase_item($pid, $product_id, $qty, $unit_price, $total) {
    global $conn;

    
    // Skip invalid rows
    if (!$pid || !$product_id || $qty === "" || $unit_price === "" || $total === "") {
        return false;
    }

    // Cast to numeric values
    $qty = floatval($qty);
    $unit_price = floatval($unit_price);
    $total = floatval($total);

    $stmt = mysqli_prepare($conn,
        "INSERT INTO purchase_items (purchase_id, product_id, qty, unit_price, total) 
         VALUES (?, ?, ?, ?, ?)"
    );

    if (!$stmt) {
        echo "Prepare failed: " . mysqli_error($conn);
        return false;
    }

    mysqli_stmt_bind_param($stmt, "iiidd", $pid, $product_id, $qty, $unit_price, $total);

    if (!mysqli_stmt_execute($stmt)) {
        echo "Execute failed: " . mysqli_stmt_error($stmt);
        return false;
    }

    mysqli_stmt_close($stmt);
    return true;
}

/* ==========================
   DELETE PURCHASE
========================== */
function delete_purchase($id) {
    global $conn;

    mysqli_query($conn, "DELETE FROM purchase_items WHERE purchase_id=$id");
    return mysqli_query($conn, "DELETE FROM purchases WHERE id=$id");
}
