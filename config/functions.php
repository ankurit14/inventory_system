<?php 
include($_SERVER['DOCUMENT_ROOT'].'/inventory_system/config/path.php');
function get_current_stock($conn, $product_id) {
    $sql = "SELECT 
                SUM(stock_in) AS total_in,
                SUM(stock_out) AS total_out
            FROM stock_master
            WHERE product_id = $product_id";

    $res = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($res);

    $total_in = $row['total_in'] ?? 0;
    $total_out = $row['total_out'] ?? 0;

    return $total_in - $total_out;
}
?>