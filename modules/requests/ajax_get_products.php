<?php
include($_SERVER['DOCUMENT_ROOT'].'/inventory_system/config/db.php');

$sub = intval($_POST['sub_category_id']);

$q = mysqli_query($conn, "SELECT id,name FROM products WHERE sub_category_id=$sub AND status='active'");

echo "<option value=''>Select</option>";

while ($r = mysqli_fetch_assoc($q)) {
    echo "<option value='{$r['id']}'>{$r['name']}</option>";
}
