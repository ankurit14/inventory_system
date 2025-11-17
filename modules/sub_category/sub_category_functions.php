<?php
include($_SERVER['DOCUMENT_ROOT'].'/inventory_system/config/db.php');

function get_all_subcategories() {
    global $conn;
    return mysqli_query($conn, "
        SELECT sc.*, c.name AS category_name 
        FROM sub_category sc 
        JOIN category c ON sc.category_id = c.id 
        ORDER BY sc.id DESC
    ");
}

function get_subcategory($id) {
    global $conn;
    return mysqli_query($conn, "SELECT * FROM sub_category WHERE id=$id");
}

function add_subcategory($data) {
    global $conn;

    $name = mysqli_real_escape_string($conn, $data['name']);
    $category_id = intval($data['category_id']);
    $description = mysqli_real_escape_string($conn, $data['description']);
    $status = $data['status'];

    $query = "INSERT INTO sub_category (category_id, name, description, status)
              VALUES ('$category_id', '$name', '$description', '$status')";
    return mysqli_query($conn, $query);
}

function update_subcategory($id, $data) {
    global $conn;

    $name = mysqli_real_escape_string($conn, $data['name']);
    $category_id = intval($data['category_id']);
    $description = mysqli_real_escape_string($conn, $data['description']);
    $status = $data['status'];

    $query = "UPDATE sub_category SET 
                category_id = '$category_id',
                name='$name',
                description='$description',
                status='$status'
              WHERE id=$id";

    return mysqli_query($conn, $query);
}

function delete_subcategory($id) {
    global $conn;
    return mysqli_query($conn, "DELETE FROM sub_category WHERE id=$id");
}
?>
