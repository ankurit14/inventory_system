<?php
session_start();
include_once __DIR__ . '/../../config/path.php';
include_once __DIR__ . '/../../config/db.php';

$issue_id = intval($_GET['id']);

// Fetch items to rollback stock
$item_sql = "SELECT * FROM emergency_issue_items WHERE issue_id = $issue_id";
$item_data = mysqli_query($conn, $item_sql);

while ($row = mysqli_fetch_assoc($item_data)) {

    $product_id = $row['product_id'];
    $qty = $row['qty_issued'];

    // Rollback stock
    mysqli_query($conn, "
        INSERT INTO stock_master (product_id, stock_in, stock_out, source, ref_id, note)
        VALUES ($product_id, $qty, 0, 'issue_rollback', $issue_id, 'Issue deleted rollback')
    ");
}

// Remove issue items
mysqli_query($conn, "DELETE FROM emergency_issue_items WHERE issue_id = $issue_id");

// Remove master record
mysqli_query($conn, "DELETE FROM emergency_issues WHERE id = $issue_id");

echo "<script>alert('Issue deleted & stock restored'); window.location='index.php';</script>";
?>
