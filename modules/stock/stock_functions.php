<?php
include($_SERVER['DOCUMENT_ROOT'].'/inventory_system/config/db.php');

function insert_stock($product_id, $stock_in = 0, $stock_out = 0, $source, $ref_id = null, $note = null) {
    global $conn;

    $stmt = mysqli_prepare($conn,
        "INSERT INTO stock_master (product_id, stock_in, stock_out, source, ref_id, note)
         VALUES (?, ?, ?, ?, ?, ?)"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "iddsis",
        $product_id,
        $stock_in,
        $stock_out,
        $source,
        $ref_id,
        $note
    );

    return mysqli_stmt_execute($stmt);
}
?>
