<?php
include_once __DIR__ . '/../../config/path.php';
include(BASE_PATH . '/includes/users_functions.php');

header('Content-Type: application/json');

// Validate input
if (!isset($_POST['name']) || empty(trim($_POST['name']))) {
    echo json_encode([
        'status' => 'error',
        'msg' => 'Name required'
    ]);
    exit;
}

$name = trim($_POST['name']);

// Create user
$new_id = quick_add_user($name);

if ($new_id) {

    // Username generated inside quick_add_user()
    $generated_username = strtolower(str_replace(' ', '', $name)) . '@123';

    // Password is always 123456 (hashed in DB)
    $plain_password = '123456';

    echo json_encode([
        'status' => 'success',
        'id' => $new_id,
        'name' => $name,
        'username' => $generated_username,
        'password' => $plain_password
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'msg' => 'Failed to create user'
    ]);
}
