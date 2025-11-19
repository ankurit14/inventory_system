<?php
// stock_ledger.php
include($_SERVER['DOCUMENT_ROOT'].'/inventory_system/config/path.php');

include($_SERVER['DOCUMENT_ROOT'].'/inventory_system/config/db.php');
include(BASE_PATH.'/includes/header.php');
include(BASE_PATH.'/includes/sidebar.php');

include(BASE_PATH.'/modules/stock/get_stock.php');


$product_id = intval($_GET['product_id']);

$res = mysqli_query($conn, "SELECT * FROM stock_master WHERE product_id=$product_id ORDER BY created_at DESC");
?>
<div class="pcoded-content">
<h3>Stock Ledger for Product ID <?= $product_id ?></h3>
<table border="1" cellpadding="6">
<tr><th>#</th><th>Stock In</th><th>Stock Out</th><th>Source</th><th>Ref ID</th><th>Note</th><th>Date</th></tr>
<?php $i=1; while($row = mysqli_fetch_assoc($res)): ?>
<tr>
    <td><?= $i++ ?></td>
    <td><?= $row['stock_in'] ?></td>
    <td><?= $row['stock_out'] ?></td>
    <td><?= $row['source'] ?></td>
    <td><?= $row['ref_id'] ?></td>
    <td><?= htmlspecialchars($row['note']) ?></td>
    <td><?= $row['created_at'] ?></td>
</tr>
<?php endwhile; ?>
</table>
</div>
<?php include(BASE_PATH.'/includes/footer.php'); ?>