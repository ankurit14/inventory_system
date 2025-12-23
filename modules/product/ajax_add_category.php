<?php
include_once __DIR__ . '/../../config/path.php';
include(BASE_PATH . '/config/db.php');

header('Content-Type: application/json');

$response = ['success' => false, 'message' => '', 'data' => []];

if (!isset($_POST['action'])) {
    echo json_encode(['success'=>false,'message'=>'Invalid request']);
    exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Add new category
    if ($action === 'add_category') {
        $name = trim($_POST['name'] ?? '');

        if (empty($name)) {
            $response['message'] = 'Category name is required!';
        } else {
            $query = "INSERT INTO category (name, status) VALUES (?, 'active')";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("s", $name);

            if ($stmt->execute()) {
                $new_id = $stmt->insert_id;
                $response['success'] = true;
                $response['message'] = 'Category added successfully!';
                $response['data'] = ['id' => $new_id, 'name' => $name];
            } else {
                $response['message'] = 'Error adding category: ' . $stmt->error;
            }
            $stmt->close();
        }
    }

    // Add new sub-category
    if ($action === 'add_sub_category') {
        $category_id = trim($_POST['category_id'] ?? '');
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if (empty($category_id)) {
            $response['message'] = 'Category is required!';
        } elseif (empty($name)) {
            $response['message'] = 'Sub-category name is required!';
        } else {
            $query = "INSERT INTO sub_category (category_id, name, description, status) VALUES (?, ?, ?, 'active')";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("iss", $category_id, $name, $description);

            if ($stmt->execute()) {
                $new_id = $stmt->insert_id;
                $response['success'] = true;
                $response['message'] = 'Sub-category added successfully!';
                $response['data'] = ['id' => $new_id, 'name' => $name];
            } else {
                $response['message'] = 'Error adding sub-category: ' . $stmt->error;
            }
            $stmt->close();
        }
    }
}

echo json_encode($response);
