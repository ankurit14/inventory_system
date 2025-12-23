<?php
include_once __DIR__ . '/../../config/db.php';

if (isset($_POST['product_id'])) {

    $product_id = intval($_POST['product_id']);

    $sql = "SELECT unit FROM products WHERE id = $product_id";
    $result = mysqli_query($conn, $sql);

    if ($row = mysqli_fetch_assoc($result)) {
        echo $row['unit'];
    } else {
        echo "";
    }
}
?>
