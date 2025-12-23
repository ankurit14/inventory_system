<?php
include_once __DIR__ . '/../../config/db.php';

/* GET ALL PRODUCTS */
function get_all_products() {
    global $conn;
    return mysqli_query($conn, "SELECT p.*, c.name AS category_name, s.name AS sub_category_name 
                                FROM products p
                                LEFT JOIN category c ON p.category_id=c.id
                                LEFT JOIN sub_category s ON p.sub_category_id=s.id
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


/* ------------------------------------------------------------------
   🔵 NEW FUNCTIONS FOR UNCATEGORIZED PRODUCT FLOW
--------------------------------------------------------------------- */

/* 1️⃣ Create product with only name (no category, no subcategory) */
function add_uncategorized_product($name) {
    global $conn;
    $name = mysqli_real_escape_string($conn, $name);
    return mysqli_query($conn, 
        "INSERT INTO products (name, category_id, sub_category_id, status)
         VALUES ('$name', NULL, NULL, 'pending')");
}

/* 2️⃣ Admin/HR assigns category + subcategory later */
function assign_category_to_product($id, $category_id, $sub_category_id) {
    global $conn;
    $id = intval($id);
    $category_id = intval($category_id);
    $sub_category_id = intval($sub_category_id);

    return mysqli_query($conn,
        "UPDATE products 
         SET category_id=$category_id, sub_category_id=$sub_category_id, status='active'
         WHERE id=$id");
}

/* 3️⃣ Purchase the product after category assignment (stock in) */
function purchase_uncategorized_product($product_id, $qty, $price) {
    global $conn;

    $product_id = intval($product_id);
    $qty = floatval($qty);
    $price = floatval($price);

    // Insert into purchase table
    mysqli_query($conn,
        "INSERT INTO purchases (product_id, qty, price, created_at)
         VALUES ($product_id, $qty, $price, NOW())");

    // Update stock
    return mysqli_query($conn,
        "UPDATE products SET stock = stock + $qty WHERE id=$product_id");
}

/* 4️⃣ Fetch only products without category assigned */
function get_uncategorized_products() {
    global $conn;
    return mysqli_query($conn,
        "SELECT * FROM products 
         WHERE category_id IS NULL OR sub_category_id IS NULL
         ORDER BY id DESC");
}

?>
