<?php
include_once __DIR__ . '/../../config/db.php';

if (isset($_POST['name']) && isset($_POST['category_id'])) {

    $name = trim($_POST['name']);
    $catId = intval($_POST['category_id']);
    $name_safe = mysqli_real_escape_string($conn, $name);

    $query = "SELECT id FROM sub_category WHERE category_id=$catId AND LOWER(name)=LOWER('$name_safe') LIMIT 1";
    $res = mysqli_query($conn, $query);

    if (mysqli_num_rows($res) > 0) {
        echo "exists";
    } else {
        echo "available";
    }
}
