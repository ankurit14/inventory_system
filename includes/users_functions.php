<?php
    include(BASE_PATH.'/config/db.php');


function get_user_by_id($id)
{
    global $conn;
    $sql = "SELECT * FROM users WHERE id = $id LIMIT 1";
    return mysqli_fetch_assoc(mysqli_query($conn, $sql));
}
function update_user_profile($id, $data)
{
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
function get_all_users()
{
    global $conn;
    return mysqli_query($conn, "SELECT * FROM users ORDER BY name ASC");
}

/*-------------------------
  Fetch single user
--------------------------*/
function get_user($id)
{
    global $conn;
    $id = intval($id);
    return mysqli_query($conn, "SELECT * FROM users WHERE id=$id");
}

/*-------------------------
  Check if username exists
--------------------------*/
function username_exists($username, $exclude_id = null)
{
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

    $name              = mysqli_real_escape_string($conn, $data['name']);
    $username          = mysqli_real_escape_string($conn, $data['username']);
    $email             = mysqli_real_escape_string($conn, $data['email']);
    $contact           = mysqli_real_escape_string($conn, $data['contact_no']);
    $office_mobile_no  = mysqli_real_escape_string($conn, $data['office_mobile_no']);
    $address           = mysqli_real_escape_string($conn, $data['address']);
    $department        = mysqli_real_escape_string($conn, $data['department']);
    $designation       = mysqli_real_escape_string($conn, $data['designation']);
    $role              = mysqli_real_escape_string($conn, $data['role']);
    $password          = password_hash($data['password'], PASSWORD_BCRYPT);

    $query = "INSERT INTO users 
        (
            name,
            username,
            password,
            role,
            email,
            contact_no,
            office_mobile_no,
            address,
            department,
            designation,
            status,
            created_at
        )
        VALUES
        (
            '$name',
            '$username',
            '$password',
            '$role',
            '$email',
            '$contact',
            '$office_mobile_no',
            '$address',
            '$department',
            '$designation',
            'active',
            NOW()
        )";

    return mysqli_query($conn, $query);
}


/*-------------------------
  Update user (WITHOUT IMAGE)
--------------------------*/
function update_user($id, $data, $file)
{
    global $conn;
    $id = intval($id);

    $name             = mysqli_real_escape_string($conn, $data['name']);
    $username         = mysqli_real_escape_string($conn, $data['username']);
    $email            = mysqli_real_escape_string($conn, $data['email']);
    $contact_no       = mysqli_real_escape_string($conn, $data['contact_no']);
    $office_mobile_no = mysqli_real_escape_string($conn, $data['office_mobile_no']);
    $address          = mysqli_real_escape_string($conn, $data['address']);
    $department       = mysqli_real_escape_string($conn, $data['department']);
    $designation      = mysqli_real_escape_string($conn, $data['designation']);
    $role             = mysqli_real_escape_string($conn, $data['role']);
    $status           = isset($data['status']) 
                        ? mysqli_real_escape_string($conn, $data['status']) 
                        : 'active';

    // Password update only if provided (already hashed expected)
    $password_clause = '';
    if (isset($data['password']) && $data['password'] !== '') {
        $pwd = mysqli_real_escape_string($conn, $data['password']);
        $password_clause = ", password='$pwd'";
    }

    $query = "UPDATE users SET 
        name='$name',
        username='$username',
        email='$email',
        contact_no='$contact_no',
        office_mobile_no='$office_mobile_no',
        address='$address',
        department='$department',
        designation='$designation',
        role='$role',
        status='$status'
        $password_clause
        WHERE id=$id";

    if (!mysqli_query($conn, $query)) {
        return "Database error: " . mysqli_error($conn);
    }

    return true;
}


/*-------------------------
  Delete user
--------------------------*/


function delete_user($id)
{
    global $conn;
    $id = intval($id);
    return mysqli_query($conn, "DELETE FROM users WHERE id=$id");
}


function quick_add_user($name)
{
    global $conn;

    $clean_name = mysqli_real_escape_string($conn, $name);
    $username = strtolower(str_replace(' ', '', $clean_name)) . '@123';
    $password = password_hash($name.'@123', PASSWORD_BCRYPT);
    $role = 'employee';

    $query = "INSERT INTO users (name, username, password, role, status, created_at)
              VALUES ('$clean_name', '$username', '$password', '$role', 'active', NOW())";

    if (mysqli_query($conn, $query)) {
        return mysqli_insert_id($conn);
    } else {
        return false;
    }
}



function get_users_count() {
    global $conn;
    $sql = "SELECT COUNT(*) as total FROM users";
    $result = mysqli_query($conn, $sql);
    $data = mysqli_fetch_assoc($result);
    return $data['total'];
}

function get_users_paginated($limit, $offset) {
    global $conn;
    $sql = "SELECT * FROM users ORDER BY name LIMIT $limit OFFSET $offset";
    return mysqli_query($conn, $sql);
}

