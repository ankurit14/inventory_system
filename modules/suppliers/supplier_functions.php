<?php
include($_SERVER['DOCUMENT_ROOT'] . '/inventory_system/config/db.php');

/* FETCH ALL SUPPLIERS */
function get_all_suppliers() {
    global $conn;
    return mysqli_query($conn, "SELECT * FROM suppliers ORDER BY id DESC");
}

/* GET SINGLE SUPPLIER */
function get_supplier($id) {
    global $conn;
    $id = intval($id);
    $res = mysqli_query($conn, "SELECT * FROM suppliers WHERE id=$id");
    if(!$res) die("DB Error: ".mysqli_error($conn));
    return $res;
}

/* INSERT SUPPLIER */
function add_supplier($data) {
    global $conn;

    $name = mysqli_real_escape_string($conn, $data['name'] ?? '');
    $phone = mysqli_real_escape_string($conn, $data['phone'] ?? '');
    $email = mysqli_real_escape_string($conn, $data['email'] ?? '');
    $address = mysqli_real_escape_string($conn, $data['address'] ?? '');
    $gst_no = mysqli_real_escape_string($conn, $data['gst_no'] ?? '');

    $sql = "INSERT INTO suppliers (name, phone, email, address, gst_no) 
            VALUES ('$name', '$phone', '$email', '$address', '$gst_no')";

    return mysqli_query($conn, $sql);
}

/* UPDATE SUPPLIER */
function update_supplier($id, $data) {
    global $conn;

    $id = intval($id);
    $name = mysqli_real_escape_string($conn, $data['name'] ?? '');
    $phone = mysqli_real_escape_string($conn, $data['phone'] ?? '');
    $email = mysqli_real_escape_string($conn, $data['email'] ?? '');
    $address = mysqli_real_escape_string($conn, $data['address'] ?? '');
    $gst_no = mysqli_real_escape_string($conn, $data['gst_no'] ?? '');

    $sql = "UPDATE suppliers SET 
            name='$name',
            phone='$phone',
            email='$email',
            address='$address',
            gst_no='$gst_no'
            WHERE id=$id";

    return mysqli_query($conn, $sql);
}

/* DELETE SUPPLIER */
function delete_supplier($id) {
    global $conn;
    $id = intval($id);
    return mysqli_query($conn, "DELETE FROM suppliers WHERE id=$id");
}
