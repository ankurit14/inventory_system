<?php
include_once($_SERVER['DOCUMENT_ROOT'].'/inventory_system/config/db.php');

// Fetch all categories
function get_all_categories() {
    global $conn;
    return mysqli_query($conn, "SELECT * FROM category ORDER BY id DESC");
}

// Fetch single category
// function get_category($id) {
//     global $conn;
//     $id = intval($id);
//     return mysqli_query($conn, "SELECT * FROM category WHERE id=$id");
// }

function get_category($id) {
    global $conn;
    return mysqli_query($conn, "SELECT * FROM category WHERE id=$id");
}

// Add new category
function add_category($data) {
    global $conn;
    $name = mysqli_real_escape_string($conn, $data['name']);
    $description = mysqli_real_escape_string($conn, $data['description']);
    $status = 'active'; // default

    $query = "INSERT INTO category (name, description, status, created_at) 
              VALUES ('$name', '$description', '$status', NOW())";
    return mysqli_query($conn, $query);
}

// Update category
function update_category($id, $data) {
    global $conn;

    $name = mysqli_real_escape_string($conn, $data['name']);
    $description = mysqli_real_escape_string($conn, $data['description']);

    $sql = "UPDATE category SET 
            name='$name',
            description='$description',
            updated_at=NOW()
            WHERE id=$id";

    return mysqli_query($conn, $sql) ? true : mysqli_error($conn);
}

// Delete category
function delete_category($id) {
    global $conn;
    $id = intval($id);
    return mysqli_query($conn, "DELETE FROM category WHERE id=$id");
}

// Toggle status
function toggle_category_status($id) {
    global $conn;
    $id = intval($id);
    $res = mysqli_query($conn, "SELECT status FROM category WHERE id=$id");
    if ($row = mysqli_fetch_assoc($res)) {
        $newStatus = $row['status'] == 'active' ? 'inactive' : 'active';
        return mysqli_query($conn, "UPDATE category SET status='$newStatus' WHERE id=$id");
    }
    return false;
}
?>
