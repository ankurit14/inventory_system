<?php
include($_SERVER['DOCUMENT_ROOT'].'/inventory_system/config/db.php');
include('product_functions.php');

if(isset($_GET['category_id'])){
    $category_id = intval($_GET['category_id']);
    $subs = get_sub_categories_by_category($category_id);

    echo '<option value="">Select Sub Category</option>';
    while($row = mysqli_fetch_assoc($subs)){
        echo '<option value="'.$row['id'].'">'.htmlspecialchars($row['name']).'</option>';
    }
}
?>
