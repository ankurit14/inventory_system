<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/inventory_system/config/db.php');


function get_user_by_id($id) {
    global $conn;
    $sql = "SELECT * FROM users WHERE id = $id LIMIT 1";
    return mysqli_fetch_assoc(mysqli_query($conn, $sql));
}
function update_user_profile($id, $data) {
    global $conn;

    $name = mysqli_real_escape_string($conn, $data['name']);
    $email = mysqli_real_escape_string($conn, $data['email']);
    $contact_no = mysqli_real_escape_string($conn, $data['contact_no']);
    $address = mysqli_real_escape_string($conn, $data['address']);
    $department = mysqli_real_escape_string($conn, $data['department']);
    $designation = mysqli_real_escape_string($conn, $data['designation']);

    $sql = "
        UPDATE users SET 
            name='$name',
            email='$email',
            contact_no='$contact_no',
            address='$address',
            department='$department',
            designation='$designation'
        WHERE id=$id
    ";

    return mysqli_query($conn, $sql);
}
/*-------------------------
  Fetch all users
--------------------------*/
function get_all_users() {
    global $conn;
    return mysqli_query($conn, "SELECT * FROM users ORDER BY id DESC");
}

/*-------------------------
  Fetch single user
--------------------------*/
function get_user($id) {
    global $conn;
    $id = intval($id);
    return mysqli_query($conn, "SELECT * FROM users WHERE id=$id");
}

/*-------------------------
  Check if username exists
--------------------------*/
function username_exists($username, $exclude_id = null) {
    global $conn;
    $username = mysqli_real_escape_string($conn, $username);
    $query = $exclude_id 
        ? "SELECT id FROM users WHERE username='$username' AND id!=$exclude_id" 
        : "SELECT id FROM users WHERE username='$username'";
    $res = mysqli_query($conn, $query);
    return mysqli_num_rows($res) > 0;
}

/*-------------------------
  Insert new user (WITHOUT IMAGE)
--------------------------*/
function add_user($data, $files)
{
    global $conn;

    $name        = mysqli_real_escape_string($conn, $data['name']);
    $username    = mysqli_real_escape_string($conn, $data['username']);
    $email       = mysqli_real_escape_string($conn, $data['email']);
    $contact     = mysqli_real_escape_string($conn, $data['contact_no']);
    $address     = mysqli_real_escape_string($conn, $data['address']);
    $department  = mysqli_real_escape_string($conn, $data['department']);
    $designation = mysqli_real_escape_string($conn, $data['designation']);
    $role        = mysqli_real_escape_string($conn, $data['role']);
    $password    = password_hash($data['password'], PASSWORD_BCRYPT);

    $query = "INSERT INTO users 
        (name, username, password, role, email, contact_no, address, department, designation, status, created_at)
        VALUES 
        ('$name', '$username', '$password', '$role', '$email', '$contact', '$address', '$department', '$designation','active', NOW())";

    return mysqli_query($conn, $query);
}

/*-------------------------
  Update user (WITHOUT IMAGE)
--------------------------*/
function update_user($id, $data, $file) {
    global $conn;
    $id = intval($id);

    $name = mysqli_real_escape_string($conn, $data['name']);
    $username = mysqli_real_escape_string($conn, $data['username']);
    $email = mysqli_real_escape_string($conn, $data['email']);
    $contact_no = mysqli_real_escape_string($conn, $data['contact_no']);
    $address = mysqli_real_escape_string($conn, $data['address']);
    $department = mysqli_real_escape_string($conn, $data['department']);
    $designation = mysqli_real_escape_string($conn, $data['designation']);
    $role = mysqli_real_escape_string($conn, $data['role']);
    $status = isset($data['status']) ? mysqli_real_escape_string($conn, $data['status']) : 'active';

    $query = "UPDATE users SET 
        name='$name',
        username='$username',
        email='$email',
        contact_no='$contact_no',
        address='$address',
        department='$department',
        designation='$designation',
        role='$role',
        status='$status'
        WHERE id=$id";

    if (!mysqli_query($conn, $query)) {
        return "Database error: " . mysqli_error($conn);
    }

    return true;
}

/*-------------------------
  Delete user
--------------------------*/
function delete_user($id) {
    global $conn;
    $id = intval($id);
    return mysqli_query($conn, "DELETE FROM users WHERE id=$id");
}
?>
