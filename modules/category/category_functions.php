<?php
include($_SERVER['DOCUMENT_ROOT'].'/inventory_system/config/db.php');

function get_all_categories() {
    global $conn;
    return mysqli_query($conn, "SELECT * FROM category ORDER BY id DESC");
}

function get_category($id) {
    global $conn;
    return mysqli_query($conn, "SELECT * FROM category WHERE id=".intval($id));
}

function add_category($data) {
    global $conn;
    $name = mysqli_real_escape_string($conn, $data['name']);
    $status = $data['status'];
    return mysqli_query($conn, "INSERT INTO category (name,status) VALUES ('$name','$status')");
}

function update_category($id, $data) {
    global $conn;
    $name = mysqli_real_escape_string($conn, $data['name']);
    $status = $data['status'];
    return mysqli_query($conn, "UPDATE category SET name='$name', status='$status' WHERE id=".intval($id));
}

function toggle_category_status($id) {
    global $conn;
    $res = mysqli_fetch_assoc(mysqli_query($conn,"SELECT status FROM category WHERE id=".intval($id)));
    if(!$res) return false;
    $new = $res['status']=='active'?'inactive':'active';
    return mysqli_query($conn, "UPDATE category SET status='$new' WHERE id=".intval($id));
}
?>
