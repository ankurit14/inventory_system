<?php
session_start();
include_once __DIR__ . '/../../config/path.php';
include(BASE_PATH . '/config/db.php');

header('Content-Type: application/json');

$response = ['success' => false, 'message' => '', 'data' => []];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    
    // CSRF Protection: token must be sent in POST as csrf_token
    $csrf = $_POST['csrf_token'] ?? '';
    if (empty($csrf) || !isset($_SESSION['csrf_token']) || $csrf !== $_SESSION['csrf_token']) {
        $response['message'] = 'Invalid CSRF token';
        echo json_encode($response);
        exit;
    }

    // Add new supplier
    if ($action === 'add_supplier') {
        $name = trim($_POST['name'] ?? '');
        $contact = trim($_POST['contact'] ?? '');
        $address = trim($_POST['address'] ?? '');

        if (empty($name)) {
            $response['message'] = 'Supplier name is required!';
        } elseif (empty($contact)) {
            $response['message'] = 'Supplier contact is required!';
        } else {
            // Check if supplier already exists
            $check = $conn->prepare("SELECT id FROM suppliers WHERE name = ? LIMIT 1");
            $check->bind_param('s', $name);
            $check->execute();
            $check->store_result();
            if ($check->num_rows > 0) {
                $check->bind_result($existing_id);
                $check->fetch();
                $response['success'] = true;
                $response['message'] = 'Supplier already exists. Selected existing.';
                $response['data'] = ['id' => $existing_id, 'name' => $name, 'contact' => $contact, 'address' => $address];
                $check->close();
            } else {
                $check->close();
                $query = "INSERT INTO suppliers (name, contact_person, address, status) VALUES (?, ?, ?, 'active')";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("sss", $name, $contact, $address);

                if ($stmt->execute()) {
                    $new_id = $stmt->insert_id;
                    $response['success'] = true;
                    $response['message'] = 'Supplier added successfully!';
                    $response['data'] = ['id' => $new_id, 'name' => $name, 'contact' => $contact, 'address' => $address];
                } else {
                    $response['message'] = 'Error adding supplier: ' . $stmt->error;
                }
                $stmt->close();
            }
        }
    }

    // Add new category
    if ($action === 'add_category') {
        $name = trim($_POST['name'] ?? '');

        if (empty($name)) {
            $response['message'] = 'Category name is required!';
        } else {
            // Check existing
            $check = $conn->prepare("SELECT id FROM category WHERE name = ? LIMIT 1");
            $check->bind_param('s', $name);
            $check->execute();
            $check->store_result();
            if ($check->num_rows > 0) {
                $check->bind_result($existing_id);
                $check->fetch();
                $response['success'] = true;
                $response['message'] = 'Category already exists. Selected existing.';
                $response['data'] = ['id' => $existing_id, 'name' => $name];
                $check->close();
            } else {
                $check->close();
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
    }

    // Add new sub-category
    if ($action === 'add_sub_category') {
        $category_id = intval($_POST['category_id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if (empty($category_id)) {
            $response['message'] = 'Category is required!';
        } elseif (empty($name)) {
            $response['message'] = 'Sub-category name is required!';
        } else {
            // ensure category exists
            $chk = $conn->prepare("SELECT id FROM category WHERE id = ? LIMIT 1");
            $chk->bind_param('i', $category_id);
            $chk->execute();
            $chk->store_result();
            if ($chk->num_rows === 0) {
                $response['message'] = 'Parent category not found.';
                $chk->close();
            } else {
                $chk->close();
                // check existing sub-category
                $check = $conn->prepare("SELECT id FROM sub_category WHERE category_id = ? AND name = ? LIMIT 1");
                $check->bind_param('is', $category_id, $name);
                $check->execute();
                $check->store_result();
                if ($check->num_rows > 0) {
                    $check->bind_result($existing_id);
                    $check->fetch();
                    $response['success'] = true;
                    $response['message'] = 'Sub-category already exists. Selected existing.';
                    $response['data'] = ['id' => $existing_id, 'name' => $name];
                    $check->close();
                } else {
                    $check->close();
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
    }

    // Add new product
    if ($action === 'add_product') {
        $category_id = intval($_POST['category_id'] ?? 0);
        $sub_category_id = intval($_POST['sub_category_id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $unit = trim($_POST['unit'] ?? '');

        if (empty($sub_category_id) || $sub_category_id <= 0) {
            $response['message'] = 'Sub-category is required!';
        } elseif (empty($name)) {
            $response['message'] = 'Product name is required!';
        } elseif (empty($unit)) {
            $response['message'] = 'Unit is required!';
        } else {
            // ensure sub-category exists
            $chk = $conn->prepare("SELECT id FROM sub_category WHERE id = ? LIMIT 1");
            $chk->bind_param('i', $sub_category_id);
            $chk->execute();
            $chk->store_result();
            if ($chk->num_rows === 0) {
                $response['message'] = 'Sub-category not found.';
                $chk->close();
            } else {
                $chk->close();
                // check existing product with same name under same sub_category
                $check = $conn->prepare("SELECT id, unit FROM products WHERE sub_category_id = ? AND name = ? LIMIT 1");
                $check->bind_param('is', $sub_category_id, $name);
                $check->execute();
                $check->store_result();
                if ($check->num_rows > 0) {
                    $check->bind_result($existing_id, $existing_unit);
                    $check->fetch();
                    $response['success'] = true;
                    $response['message'] = 'Product already exists. Selected existing.';
                    $response['data'] = ['id' => $existing_id, 'name' => $name, 'unit' => $existing_unit];
                    $check->close();
                } else {
                    $check->close();
                    // Generate SKU (use category/subcategory ids for codes)
                    $cat_code = substr(str_replace(' ', '', strtoupper((string)$category_id)), 0, 3);
                    $sub_code = substr(str_replace(' ', '', strtoupper((string)$sub_category_id)), 0, 3);
                    $prod_code = substr(str_replace(' ', '', strtoupper($name)), 0, 5);
                    $sku = $cat_code . '-' . $sub_code . '-' . $prod_code;

                    $query = "INSERT INTO products (category_id, sub_category_id, name, sku, unit, status) VALUES (?, ?, ?, ?, ?, 'active')";
                    $stmt = $conn->prepare($query);
                    $stmt->bind_param("iisss", $category_id, $sub_category_id, $name, $sku, $unit);

                    if ($stmt->execute()) {
                        $new_id = $stmt->insert_id;
                        $response['success'] = true;
                        $response['message'] = 'Product added successfully!';
                        $response['data'] = ['id' => $new_id, 'name' => $name, 'unit' => $unit];
                    } else {
                        $response['message'] = 'Error adding product: ' . $stmt->error;
                    }
                    $stmt->close();
                }
            }
        }
    }

}


echo json_encode($response);
