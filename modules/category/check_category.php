<?php
include_once __DIR__ . '/../../config/db.php';

if (isset($_POST['name'])) {

    $name = trim($_POST['name']);
    $name = mysqli_real_escape_string($conn, $name);

    $query = "SELECT id FROM category WHERE LOWER(name)=LOWER('$name') LIMIT 1";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        echo "exists";
    } else {
        echo "available";
    }
}
