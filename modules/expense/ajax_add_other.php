<?php
include(BASE_PATH.'/config/db.php');

header("Content-Type: application/json");

if (!isset($_POST['name']) || empty(trim($_POST['name']))) {
    echo json_encode(["success" => false, "message" => "Receiver name required"]);
    exit;
}

$name = mysqli_real_escape_string($conn, $_POST['name']);

$sql = "INSERT INTO other_receivers (name, status) VALUES ('$name', 1)";
if (mysqli_query($conn, $sql)) {
    $id = mysqli_insert_id($conn);

    echo json_encode([
        "success" => true,
        "data" => [
            "id" => $id,
            "name" => $name
        ]
    ]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to add receiver"]);
}
