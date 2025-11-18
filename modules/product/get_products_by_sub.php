<?php
include($_SERVER['DOCUMENT_ROOT'].'/inventory_system/config/db.php');

$sub_id = intval($_GET['sub_id']);

$res = mysqli_query($conn, "SELECT id, name FROM products WHERE sub_category_id = $sub_id ORDER BY name ASC");

echo '<option value="">Select Product</option>';

while ($row = mysqli_fetch_assoc($res)) {
    echo '<option value="'.$row['id'].'">'.$row['name'].'</option>';
}
?>
