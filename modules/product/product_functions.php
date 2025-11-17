<?php
include($_SERVER['DOCUMENT_ROOT'].'/inventory_system/config/db.php');

/* GET ALL PRODUCTS */
function get_all_products() {
    global $conn;
    return mysqli_query($conn, "SELECT p.*, c.name AS category_name, s.name AS sub_category_name 
                                FROM products p
                                JOIN category c ON p.category_id=c.id
                                JOIN sub_category s ON p.sub_category_id=s.id
                                ORDER BY p.id DESC");
}

/* GET SINGLE PRODUCT */
function get_product($id) {
    global $conn;
    $id = intval($id);
    return mysqli_query($conn, "SELECT * FROM products WHERE id=$id");
}

/* ADD PRODUCT */
function add_product($data) {
    global $conn;
    $category_id = intval($data['category_id']);
    $sub_category_id = intval($data['sub_category_id']);
    $name = mysqli_real_escape_string($conn, $data['name']);
    $sku = mysqli_real_escape_string($conn, $data['sku']);
    $unit = mysqli_real_escape_string($conn, $data['unit']);
    $description = mysqli_real_escape_string($conn, $data['description']);
    $status = 'active';

    $sql = "INSERT INTO products (category_id, sub_category_id, name, sku, unit, description, status)
            VALUES ($category_id, $sub_category_id, '$name', '$sku', '$unit', '$description', '$status')";
    return mysqli_query($conn, $sql);
}

/* UPDATE PRODUCT */
function update_product($id, $data) {
    global $conn;
    $id = intval($id);
    $category_id = intval($data['category_id']);
    $sub_category_id = intval($data['sub_category_id']);
    $name = mysqli_real_escape_string($conn, $data['name']);
    $sku = mysqli_real_escape_string($conn, $data['sku']);
    $unit = mysqli_real_escape_string($conn, $data['unit']);
    $description = mysqli_real_escape_string($conn, $data['description']);
    $status = $data['status'] ?? 'active';

    $sql = "UPDATE products SET 
            category_id=$category_id,
            sub_category_id=$sub_category_id,
            name='$name',
            sku='$sku',
            unit='$unit',
            description='$description',
            status='$status'
            WHERE id=$id";
    return mysqli_query($conn, $sql);
}

/* DELETE PRODUCT */
function delete_product($id) {
    global $conn;
    $id = intval($id);
    return mysqli_query($conn, "DELETE FROM products WHERE id=$id");
}


function get_sub_categories_by_category($category_id) {
    global $conn;
    $category_id = intval($category_id);
    return mysqli_query($conn, "SELECT * FROM sub_category WHERE category_id=$category_id ORDER BY name ASC");
}
?>
