<?php
include($_SERVER['DOCUMENT_ROOT'].'/inventory_system/config/db.php');

$cat = intval($_POST['category_id']);

$q = mysqli_query($conn, "SELECT id,name FROM sub_category WHERE category_id=$cat AND status='active'");

echo "<option value=''>Select</option>";

while ($r = mysqli_fetch_assoc($q)) {
    echo "<option value='{$r['id']}'>{$r['name']}</option>";
}
