<?php
include($_SERVER['DOCUMENT_ROOT'].'/inventory_system/config/db.php');

function get_current_stock($product_id) {
    global $conn;

    // Current stock + last purchase price
    $sql = "
        SELECT 
            COALESCE(SUM(sm.stock_in), 0) - COALESCE(SUM(sm.stock_out), 0) AS stock,
            (
                SELECT pi.unit_price 
                FROM purchase_items pi
                WHERE pi.product_id = sm.product_id
                ORDER BY pi.id DESC 
                LIMIT 1
            ) AS price
        FROM stock_master sm
        WHERE sm.product_id = $product_id
    ";

    $res = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($res);

    return [
        'stock' => $row['stock'] ?? 0,
        'price' => $row['price'] ?? 0
    ];
}
?>
