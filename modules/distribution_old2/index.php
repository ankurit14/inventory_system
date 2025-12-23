<?php
session_start();
$emp_id = $_SESSION['user_id']; // employee login

include('distribution_functions.php');
$rows = get_distributions($emp_id);

include(BASE_PATH.'/includes/header.php');
include(BASE_PATH.'/includes/sidebar.php');
?>

<div class="page-header">
    <h3>My Distributions</h3>
</div>

<a href="add.php" class="btn btn-primary mb-3">New Distribution</a>

<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>#</th>
            <th>Product</th>
            <th>Client</th>
            <th>Qty</th>
            <th>Note</th>
            <th>Date</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach($rows as $i=>$r): ?>
        <tr>
            <td><?= $i+1 ?></td>
            <td><?= $r['product_name'] ?></td>
            <td><?= $r['client_name'] ?></td>
            <td><?= $r['stock_out'] ?></td>
            <td><?= $r['note'] ?></td>
            <td><?= $r['created_at'] ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
