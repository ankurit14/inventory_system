<?php
include($_SERVER['DOCUMENT_ROOT'] . '/inventory_system/config/db.php');

// Validate category_id
$category_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : 0;

if ($category_id <= 0) {
    echo '<option value="">Select Sub Category</option>';
    exit;
}

// Query subcategories safely
$res = mysqli_query($conn, "SELECT id, name FROM sub_category WHERE category_id = $category_id ORDER BY name ASC");

if (!$res) {
    // Show MySQL error for debugging (remove in production)
    echo '<option value="">Error loading subcategories</option>';
    exit;
}

echo '<option value="">Select Sub Category</option>';
while ($row = mysqli_fetch_assoc($res)) {
    echo '<option value="'.$row['id'].'">'.$row['name'].'</option>';
}
?>
