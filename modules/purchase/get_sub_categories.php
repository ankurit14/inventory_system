<?php
include_once __DIR__ . '/../../config/db.php';

$category_id = $_GET['category_id'];

$sql = "SELECT id, name FROM sub_category WHERE category_id = $category_id ORDER BY name";
$result = mysqli_query($conn, $sql);

echo '<option value="">Select Sub Category</option>';
while ($row = mysqli_fetch_assoc($result)) {
    echo "<option value='{$row['id']}'>{$row['name']}</option>";
}
?>