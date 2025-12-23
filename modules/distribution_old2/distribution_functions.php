<?php
include_once __DIR__ . '/../../config/path.php';
include_once __DIR__ . '/../../config/db.php';
global $conn;


/* Get employee products with remaining qty */
function get_employee_products($emp_id){
    global $conn;

    $query = "
        SELECT product_id,
               SUM(stock_in) as total_in,
               SUM(stock_out) as total_out
        FROM employee_stock_master
        WHERE employee_id = $emp_id
        GROUP BY product_id
    ";

    $res = mysqli_query($conn, $query);

    $data = [];
    while($row = mysqli_fetch_assoc($res)){
        $row['remaining'] = $row['total_in'] - $row['total_out'];
        $data[] = $row;
    }
    return $data;
}

/* Insert distribution entry */
function add_distribution($emp_id, $product_id, $client_id, $qty, $note){
    global $conn;

    $stmt = mysqli_prepare($conn,
        "INSERT INTO employee_stock_master
         (employee_id, product_id, stock_in, stock_out, client_id, note, request_for)
         VALUES (?, ?, 0, ?, ?, ?, 'distribution')"
    );

    mysqli_stmt_bind_param($stmt, "iiiss",
        $emp_id, $product_id, $qty, $client_id, $note
    );

    return mysqli_stmt_execute($stmt);
}

/* Get distribution list */
function get_distributions($emp_id){
    global $conn;
    $res = mysqli_query($conn,
        "SELECT esm.*, p.title as product_name, c.name as client_name
         FROM employee_stock_master esm
         LEFT JOIN product_master p ON p.id = esm.product_id
         LEFT JOIN clients c ON c.id = esm.client_id
         WHERE esm.employee_id = $emp_id AND esm.request_for='distribution'
         ORDER BY esm.id DESC"
    );

    return mysqli_fetch_all($res, MYSQLI_ASSOC);
}
